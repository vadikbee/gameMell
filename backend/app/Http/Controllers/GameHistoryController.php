<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator; // Добавлен импорт Validator

class GameHistoryController extends Controller
{
    const HISTORY_FILE = 'game_history.json';
    const ACTIVE_SESSION_FILE = 'active_session.json';
    const MAX_GAMES = 5;

    
   // GameHistoryController.php
public function getLastGames()
{
    try {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) return response()->json([]);

        $history = json_decode(file_get_contents($filePath), true) ?? [];
        
        $processedGames = [];
        foreach (array_slice($history, 0, self::MAX_GAMES) as $game) {
            // Сортируем результаты по позициям
            $sortedResults = collect($game['results'])
                ->sortBy('position', SORT_NATURAL) // Сортировка по позициям
                ->values()
                ->toArray();
                
            $processedGames[] = [
                'id' => $game['id'],
                'timestamp' => $game['timestamp'],
                'results' => $sortedResults
            ];
        }
        
        return response()->json($processedGames);
    } catch (\Exception $e) {
        Log::error('Error loading history: '.$e->getMessage());
        return response()->json([]);
    }
}
///////////////////////////////////////ЭНДПОИНТ (lastGame)/////...//////////////////////////////////
  public function getLastSessions(Request $request, $code)
    {
        try {
            // Валидация параметра code
            $validator = Validator::make(['code' => $code], [
                'code' => 'required|string|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Проверяем код инстанса
            if ($code !== 'cockroaches-space-maze') {
                return response()->json(['error' => 'Invalid game code'], 404);
            }

            $sessions = [];

            // Проверяем активную сессию
            $activeSessionData = $this->getActiveSessionData();
            if ($activeSessionData && $activeSessionData['is_active']) {
                $betsAvailableTill = Carbon::parse($activeSessionData['bets_available_till']);
                if (Carbon::now()->lte($betsAvailableTill)) {
                    // Активная сессия валидна
                    $sessions[] = [
                        'id' => $activeSessionData['id'],
                        'results' => (object)[], // активная сессия не имеет результатов
                        'ended_at' => null,
                        'is_active' => true,
                        'started_at' => $activeSessionData['started_at'],
                        'result_is_valid' => true,
                        'bets_available_till' => $activeSessionData['bets_available_till']
                    ];
                } else {
                    // Время ставок истекло, деактивируем сессию
                    $this->deactivateSession($activeSessionData);
                }
            }

            // Загружаем историю игр
            $history = $this->loadGameHistory();

            foreach ($history as $game) {
                if (count($sessions) >= self::MAX_GAMES) break;

                $timestamp = Carbon::parse($game['timestamp']);
                
                $resultsObject = [];
                foreach ($game['results'] as $result) {
                    if ($result['position'] !== null) {
                        $resultsObject[$result['position']] = [
                            'position' => $result['position'],
                            'color' => $result['color']
                        ];
                    }
                }

                $sessions[] = [
                    'id' => $game['id'],
                    'results' => (object)$resultsObject,
                    'ended_at' => $timestamp->toIso8601String(),
                    'is_active' => false,
                    'started_at' => $timestamp->copy()->subSeconds(12)->toIso8601String(),
                    'result_is_valid' => true,
                    'bets_available_till' => $timestamp->copy()->subSeconds(12)->toIso8601String()
                ];
            }

            return response()->json($sessions);
        } catch (\Exception $e) {
            Log::error('Error loading sessions: '.$e->getMessage());
            return response()->json([], 500);
        }
    }

    private function getActiveSessionData()
    {
        $filePath = storage_path('app/' . self::ACTIVE_SESSION_FILE);
        if (!file_exists($filePath)) {
            return null;
        }
        return json_decode(file_get_contents($filePath), true);
    }

    private function deactivateSession($sessionData)
    {
        $sessionData['is_active'] = false;
        $sessionData['ended_at'] = Carbon::now()->toISOString();
        $this->saveActiveSessionData($sessionData);
    }

    private function saveActiveSessionData($data)
    {
        $filePath = storage_path('app/' . self::ACTIVE_SESSION_FILE);
        file_put_contents($filePath, json_encode($data));
    }

    private function loadGameHistory()
    {
        $filePath = storage_path('app/' . self::HISTORY_FILE);
        if (!file_exists($filePath)) {
            return [];
        }
        return json_decode(file_get_contents($filePath), true) ?? [];
    }
///////////////////////////////////////ЭНДПОИНТ (lastGame)///////////////////////////////////////
   public function saveGameResult(Request $request)
{
    try {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.position' => 'nullable|integer|min:1',
            'results.*.color' => 'required|string',
            'results.*.trapId' => 'nullable|integer', // Добавляем поле trapId
            'coefficient' => 'sometimes|numeric', // Добавляем поле коэффициента
        ]);
        $validated['coefficient'] = $request->input('coefficient', 1);
        $results = $validated['results'];
        $filePath = $this->getFilePath();
        $history = [];

        if (file_exists($filePath)) {
            $history = json_decode(file_get_contents($filePath), true) ?? [];
        }

        // Убрано дополнение до 7 элементов - теперь принимаем данные как есть
        array_unshift($history, [
            'id' => uniqid(),
            'timestamp' => now()->toDateTimeString(),
            'results' => $results
        ]);

        // Сохраняем только последние MAX_GAMES игр
        file_put_contents(
            $filePath, 
            json_encode(array_slice($history, 0, self::MAX_GAMES))
        );

        return response()->json([
            'status' => 'success',
            'results' => $results // Возвращаем сохраненные результаты
        ]);
    } catch (\Exception $e) {
        Log::error('Error saving history: '.$e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
    

    private function getFilePath()
    {
        return storage_path('app/' . self::HISTORY_FILE);
    }
public function calculateWinnings(Request $request)
{
    try {
          Log::info('Starting winnings calculation with data:', $request->all());
         Log::info('Starting winnings calculation');

        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.position' => 'nullable|integer',
            'results.*.color' => 'required|string',
            'results.*.trapId' => 'nullable|integer',
            'bets' => 'required|array',
            'bets.*.type' => 'required|string',
            'bets.*.amount' => 'required|numeric',
            'bets.*.bugId' => 'required_if:type,position|integer',
            'bets.*.position' => 'required_if:type,position|integer',
            'bets.*.overtaker' => 'required_if:type,overtaking|integer',
            'bets.*.overtaken' => 'required_if:type,overtaking|integer',
            'bets.*.trapId' => 'required_if:type,section|integer',
            'bets.*.bugIds' => 'required_if:type,section|array',
            'bets.*.coefficient' => 'sometimes|numeric',
        ]);
        Log::info('Validated data:', $validated);
        Log::info('Request validated successfully');
        $results = $validated['results'];
        $bets = $validated['bets'];
        Log::info('Results data:', $results);
        Log::info('Bets data:', $bets);
        // Map colors to bug IDs (0-6)
        $colorToId = [
            '#FFFF00' => 0, // Желтый
            '#FFA500' => 1, // Оранжевый
            '#8B0000' => 2, // Темно-оранжевый
            '#0000FF' => 3, // Синий
            '#FF0000' => 4, // Красный
            '#800080' => 5, // Фиолетовый
            '#00FF00' => 6  // Зеленый
        ];

        // Map IDs to colors
        $idToColor = [
            0 => '#FFFF00',
            1 => '#FFA500', 
            2 => '#8B0000',
            3 => '#0000FF',
            4 => '#FF0000',
            5 => '#800080',
            6 => '#00FF00'
        ];

        $winningBets = [];
        $losingBets = [];


        Log::info('Processing ' . count($bets) . ' bets');

        foreach ($bets as $bet) {
            $coefficient = $bet['coefficient'] ?? 1;
            Log::info('Processing bet type: ' . $bet['type'] . ' with coefficient: ' . $coefficient);

            switch ($bet['type']) {
                case 'position':
                    $bugId = ($bet['bugId'] ?? 1) - 1; // Convert to 0-6
                    $expectedPosition = $bet['position'] ?? 1;
                    
                    $result = collect($results)->first(function ($r) use ($bugId, $idToColor) {
                        return $r['color'] === $idToColor[$bugId];
                    });
                    
                    $isWin = $result && 
                             isset($result['position']) && 
                             $result['position'] === $expectedPosition;
                    
                    if ($isWin) {
                        $winAmount = $bet['amount'] * $coefficient;
                        $winningBets[] = [
                            'type' => 'position',
                            'amount' => $bet['amount'],
                            'winAmount' => $winAmount,
                            'bugId' => $bugId + 1,
                            'position' => $expectedPosition,
                            'coefficient' => $coefficient,
                            'bugColors' => [$idToColor[$bugId]]
                        ];
                    } else {
                        $losingBets[] = $bet;
                    }
                    break;

              case 'overtaking':
    Log::info('Processing overtaking bet:', $bet);
    
    $overtakerId = ($bet['overtaker'] ?? 1) ;
    $overtakenId = ($bet['overtaken'] ?? 1) ;
    
    Log::info('Converted IDs - Overtaker: '.$overtakerId.', Overtaken: '.$overtakenId);
    
    $overtakerResult = collect($results)->first(function ($r) use ($overtakerId, $idToColor) {
        return $r['color'] === $idToColor[$overtakerId];
    });
    
    $overtakenResult = collect($results)->first(function ($r) use ($overtakenId, $idToColor) {
        return $r['color'] === $idToColor[$overtakenId];
    });

    Log::info('Overtaker result:', $overtakerResult ?? []);
    Log::info('Overtaken result:', $overtakenResult ?? []);

    // Проверяем, что обгоняющий финишировал
    $overtakerFinished = $overtakerResult && 
                        isset($overtakerResult['position']) &&
                        $overtakerResult['position'] !== null;
    
    // Проверяем, что обгоняемый либо не финишировал, либо финишировал после обгоняющего
    $overtakenLost = !$overtakenResult || 
                    !isset($overtakenResult['position']) || 
                    $overtakenResult['position'] === null ||
                    ($overtakerFinished && $overtakenResult['position'] !== null && 
                     $overtakerResult['position'] < $overtakenResult['position']);
    
    $isWin = $overtakerFinished && $overtakenLost;
    
    Log::info('Overtaking bet win check result: '.($isWin ? 'WIN' : 'LOSE'));
    
    if ($isWin) {
        $coefficient = $bet['coefficient'] ?? 1;
        $winAmount = $bet['amount'] * $coefficient;
        
        Log::info('Overtaking bet WON - Amount: '.$bet['amount'].', Coefficient: '.$coefficient.', Win: '.$winAmount);
        
        $winningBets[] = [
            'type' => 'overtaking',
            'amount' => $bet['amount'],
            'winAmount' => $winAmount,
            'overtaker' => $overtakerId + 1,
            'overtaken' => $overtakenId + 1,
            'coefficient' => $coefficient,
            'bugColors' => [$idToColor[$overtakerId], $idToColor[$overtakenId]]
        ];
    } else {
        Log::info('Overtaking bet LOST');
        $losingBets[] = $bet;
    }
    break;
                
                case 'section':
                    $winningBugs = [];
                    $bugIds = $bet['bugIds'] ?? [];
                    $trapId = $bet['trapId'] ?? null;
                    
                    foreach ($bugIds as $bugId) {
                        $result = collect($results)->first(function ($r) use ($bugId, $idToColor) {
                            return $r['color'] === $idToColor[$bugId];
                        });
                        
                        if ($result && isset($result['trapId']) && $result['trapId'] == $trapId) {
                            $winningBugs[] = $bugId;
                        }
                    }

                    if (count($winningBugs) > 0) {
                        $winAmountPerBug = ($bet['amount'] / count($bugIds)) * $coefficient;
                        $totalWin = $winAmountPerBug * count($winningBugs);
                        
                        $winningBets[] = [
                            'type' => 'section',
                            'amount' => $bet['amount'],
                            'winAmount' => $totalWin,
                            'winAmountPerBug' => $winAmountPerBug,
                            'trapId' => $trapId,
                            'bugIds' => $bugIds,
                            'winningBugs' => $winningBugs,
                            'coefficient' => $coefficient,
                            'bugColors' => array_map(function($id) use ($idToColor) {
                                return $idToColor[$id];
                            }, $winningBugs)
                        ];
                    } else {
                        $losingBets[] = $bet;
                    }
                    break;
                    
                default:
                    $losingBets[] = $bet;
                    break;
            }
        }
        Log::info('Calculation completed. Winning bets: ' . count($winningBets) . ', Losing bets: ' . count($losingBets));

        return response()->json([
            'winningBets' => $winningBets,
            'losingBets' => $losingBets
        ]);

    } catch (\Exception $e) {
        Log::error('Error calculating winnings: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());
        return response()->json(['error' => 'Calculation failed: '.$e->getMessage()], 500);
    }
}
}