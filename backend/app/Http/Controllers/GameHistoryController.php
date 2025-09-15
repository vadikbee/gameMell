<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
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
        $configPath = storage_path('app/bet_buttons.json');
        $sectionCoefficients = [];
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $sectionCoefficients = $config['coefficients']['section'] ?? [];
        }

        try {
            Log::info('Starting winnings calculation');
            $validated = $request->validate([
                'results' => 'required|array',
                'results.*.position' => 'nullable|integer',
                'results.*.color' => 'required|string',
                'results.*.trapId' => 'nullable|integer',
                'bets' => 'present|array',
                'bets.*.type' => 'required|string',
                'bets.*.amount' => 'required|numeric',
                'bets.*.bugId' => 'sometimes|integer',
                'bets.*.position' => 'sometimes|integer',
                'bets.*.overtaker' => 'sometimes|integer',
                'bets.*.overtaken' => 'sometimes|integer',
                'bets.*.trapId' => 'sometimes|integer',
                'bets.*.bugIds' => 'sometimes|array',
                'bets.*.coefficient' => 'sometimes|numeric',
            ]);

            $results = $validated['results'];
            $bets = $validated['bets'];

            $colorToId = ['#FFFF00' => 0, '#FFA500' => 1, '#8B0000' => 2, '#0000FF' => 3, '#FF0000' => 4, '#800080' => 5, '#00FF00' => 6];
            $idToColor = [0 => '#FFFF00', 1 => '#FFA500', 2 => '#8B0000', 3 => '#0000FF', 4 => '#FF0000', 5 => '#800080', 6 => '#00FF00'];

            $losingBets = [];
            $multibetGroups = [];

            foreach ($bets as $bet) {
                $betGroup = $bet['multibet_group'] ?? 'default';
                $winningBet = null;

                switch ($bet['type']) {
                    case 'position':
                        $winningBet = $this->calculatePositionBet($bet, $results, $idToColor);
                        break;
                    case 'overtaking':
                        $winningBet = $this->calculateOvertakingBet($bet, $results, $idToColor);
                        break;
                    case 'trap':
                    case 'section':
                        $winningBet = $this->calculateSectionBet($bet, $results, $sectionCoefficients, $colorToId, $idToColor);
                        break;
                }

                if ($winningBet) {
                    if (!isset($multibetGroups[$betGroup])) {
                        $multibetGroups[$betGroup] = [];
                    }
                    $multibetGroups[$betGroup][] = $winningBet;
                } else {
                    $losingBets[] = $bet;
                }
            }

            // Объединяем все выигрышные ставки из одной группы в один общий выигрыш
            $combinedWinningBets = [];
            foreach ($multibetGroups as $groupBets) {
                if (count($groupBets) > 0) {
                    $combinedBet = [
                        'type' => 'multibet',
                        'amount' => array_sum(array_column($groupBets, 'amount')),
                        'winAmount' => array_sum(array_column($groupBets, 'winAmount')),
                        'bets' => $groupBets,
                        'coefficient' => 1,
                        'bugColors' => array_merge(...array_column($groupBets, 'bugColors'))
                    ];
                    $combinedWinningBets[] = $combinedBet;
                }
            }

            Log::info('Calculation completed. Winning bets: ' . count($combinedWinningBets) . ', Losing bets: ' . count($losingBets));

            // ... (Ваш блок с отправкой WebSocket-события)

            return response()->json([
                'winningBets' => $combinedWinningBets,
                'losingBets' => $losingBets
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating winnings: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Calculation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Рассчитывает выигрыш для ставки типа 'position'.
     * @param array $bet
     * @param array $results
     * @param array $idToColor
     * @return array|null
     */
    private function calculatePositionBet(array $bet, array $results, array $idToColor): ?array
    {
        $bugId = ($bet['bugId'] ?? 1) - 1; // Convert to 0-6
        $expectedPosition = $bet['position'] ?? 1;

        $result = collect($results)->first(function ($r) use ($bugId, $idToColor) {
            return isset($r['color'], $idToColor[$bugId]) && $r['color'] === $idToColor[$bugId];
        });

        $isWin = $result && isset($result['position']) && $result['position'] === $expectedPosition;

        if ($isWin) {
            $coefficient = $bet['coefficient'] ?? 1;
            return [
                'type' => 'position',
                'amount' => $bet['amount'],
                'winAmount' => $bet['amount'] * $coefficient,
                'bugId' => $bugId + 1,
                'position' => $expectedPosition,
                'coefficient' => $coefficient,
                'bugColors' => [$idToColor[$bugId]]
            ];
        }

        return null;
    }

    /**
     * Рассчитывает выигрыш для ставки типа 'overtaking'.
     * @param array $bet
     * @param array $results
     * @param array $idToColor
     * @return array|null
     */
    private function calculateOvertakingBet(array $bet, array $results, array $idToColor): ?array
    {
        $overtakerId = ($bet['overtaker'] ?? 1);
        $overtakenId = ($bet['overtaken'] ?? 1);

        $resultsCollection = collect($results);
        $overtakerResult = $resultsCollection->first(fn($r) => isset($r['color']) && $r['color'] === $idToColor[$overtakerId]);
        $overtakenResult = $resultsCollection->first(fn($r) => isset($r['color']) && $r['color'] === $idToColor[$overtakenId]);

        $overtakerFinished = $overtakerResult && isset($overtakerResult['position']) && $overtakerResult['position'] !== null;
        $overtakenLost = !$overtakenResult || !isset($overtakenResult['position']) || $overtakenResult['position'] === null ||
            ($overtakerFinished && $overtakerResult['position'] < $overtakenResult['position']);

        if ($overtakerFinished && $overtakenLost) {
            $coefficient = $bet['coefficient'] ?? 1;
            return [
                'type' => 'overtaking',
                'amount' => $bet['amount'],
                'winAmount' => $bet['amount'] * $coefficient,
                'overtaker' => $overtakerId + 1,
                'overtaken' => $overtakenId + 1,
                'coefficient' => $coefficient,
                'bugColors' => [$idToColor[$overtakerId], $idToColor[$overtakenId]]
            ];
        }

        return null;
    }

    /**
     * Рассчитывает выигрыш для ставки типа 'section' (trap).
     * @param array $bet
     * @param array $results
     * @param array $sectionCoefficients
     * @param array $colorToId
     * @param array $idToColor
     * @return array|null
     */
    private function calculateSectionBet(array $bet, array $results, array $sectionCoefficients, array $colorToId, array $idToColor): ?array
{
    $bugIds = $bet['bugIds'] ?? $bet['selection'] ?? [];
    $trapId = $bet['trapId'] ?? null;

    // Проверяем, что в ставке есть тараканы и указана секция
    if (empty($bugIds) || $trapId === null) {
        return null;
    }

    // Находим ВСЕХ, кто финишировал в нужной секции
    $finishedInSection = collect($results)->filter(fn($r) => isset($r['trapId']) && $r['trapId'] == $trapId && $r['position'] !== null);

    if ($finishedInSection->isNotEmpty()) {
        // Получаем ID всех победителей в этой секции
        $winnerBugIds = $finishedInSection->map(fn($result) => $colorToId[$result['color']] ?? -1)->all();

        // Находим пересечение: тараканы, на которых мы ставили И которые победили
        $actualWinnersFromBet = array_intersect($winnerBugIds, $bugIds);

        // Если есть хотя бы один победитель из нашей ставки
        if (!empty($actualWinnersFromBet)) {
            $coefficient = $sectionCoefficients[$trapId] ?? 1;

            // --- КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ ЗДЕСЬ ---
            // Рассчитываем сумму ставки на одного таракана
            $amountPerBug = $bet['amount'] / count($bugIds);

            // Рассчитываем итоговый выигрыш пропорционально количеству победителей
            $totalWin = $amountPerBug * count($actualWinnersFromBet) * $coefficient;

            // Рассчитываем выигрыш на одного таракана (для удобного отображения на фронте)
            $winAmountPerBug = $amountPerBug * $coefficient;

            return [
                'type' => 'section',
                'amount' => $bet['amount'], // Общая сумма ставки
                'winAmount' => $totalWin,   // <-- ИСПРАВЛЕНО: Теперь это пропорциональный выигрыш
                'trapId' => $trapId,
                'bugIds' => $bugIds, // На кого ставили
                'winningBugs' => array_values($actualWinnersFromBet), // Кто реально выиграл
                'coefficient' => $coefficient,
                'bugColors' => collect($actualWinnersFromBet)->map(fn($id) => $idToColor[$id])->values()->all(),
                'winAmountPerBug' => $winAmountPerBug // Полезно для UI
            ];
        }
    }

    // Если никто из наших тараканов не выиграл, возвращаем null
    return null;
}



}
