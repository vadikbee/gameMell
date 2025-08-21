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
        ]);

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

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        Log::error('Error saving history: '.$e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
    

    private function getFilePath()
    {
        return storage_path('app/' . self::HISTORY_FILE);
    }
}