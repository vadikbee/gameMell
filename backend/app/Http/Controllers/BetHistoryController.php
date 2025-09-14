<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class BetHistoryController extends Controller
{
    const HISTORY_FILE = 'bets_history.json';
    const MAX_BETS = 10; // Сохраняем только последние 10 ставок

    /**
     * Универсальный и единственный публичный метод для сохранения ставок.
     * Обрабатывает как одиночные ставки, так и пакеты (batch).
     */
    public function saveBets(Request $request, $code) // $code пока не используется, но может пригодиться
    {
        Log::info('--- Received request to save bets ---', $request->all());

        // Определяем, пришел ли пакет (ключ 'bets' с массивом) или одиночная ставка
        $isBatch = $request->has('bets') && is_array($request->input('bets'));
        $betsToValidate = $isBatch ? $request->input('bets') : [$request->all()];

        // Правила валидации, применяемые к КАЖДОМУ элементу в проверяемом массиве
        $rules = [
            '*.user_id' => 'required|integer',
            '*.amount' => 'required|numeric',
            '*.type' => 'required|string|in:win,place,trap,position,overtaking,section',
            '*.coefficient' => 'nullable|numeric',
            '*.timestamp' => 'nullable|string',
            '*.bugColors' => 'nullable|array',

            // Условные правила для каждого типа ставки
            '*.bugId' => 'required_if:*.type,position|integer',
            '*.position' => 'required_if:*.type,position|integer',

            '*.overtaker' => 'required_if:*.type,overtaking|integer',
            '*.overtaken' => 'required_if:*.type,overtaking|integer',

            '*.trapId' => 'required_if:*.type,trap,section|integer',
            '*.selection' => 'required_if:*.type,trap,section|array',
        ];

        $validator = Validator::make($betsToValidate, $rules);

        if ($validator->fails()) {
            Log::error('Validation failed for incoming bets:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedBets = $validator->validated();
        Log::info('Validation successful. Bets to save:', $validatedBets);

        try {
            $filePath = $this->getFilePath();
            $history = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) ?? [] : [];

            // Добавляем каждую валидированную ставку в историю
            foreach ($validatedBets as $betData) {
                if (empty($betData['timestamp']) && empty($betData['time'])) {
                    $betData['time'] = now()->format('H:i:s');
                }

                array_unshift($history, [
                    'id' => uniqid(),
                    'timestamp' => now()->toDateTimeString(),
                    'data' => $betData
                ]);
            }

            $history = array_slice($history, 0, self::MAX_BETS);
            file_put_contents($filePath, json_encode($history, JSON_PRETTY_PRINT));

            return response()->json([
                'status' => 'success',
                'saved_count' => count($validatedBets)
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving bets batch: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save bets batch.'], 500);
        }
    }

    public function getLatestBets()
    {
        try {
            $filePath = $this->getFilePath();
            if (!file_exists($filePath)) {
                return response()->json([]);
            }
            return response()->json(json_decode(file_get_contents($filePath), true) ?? []);
        } catch (\Exception $e) {
            Log::error('Error loading bet history: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    private function getFilePath()
    {
        $storagePath = storage_path('app');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        return $storagePath . '/' . self::HISTORY_FILE;
    }

    public function repeatBets(Request $request, $code)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['code' => $code]), [
                'code' => 'required|in:cockroaches-space-maze',
                'game_session_id' => 'required|integer',
                'account_session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $activeSessionPath = storage_path('app/active_session.json');
            if (!file_exists($activeSessionPath)) {
                return response()->json(['error' => 'Active session not found'], 404);
            }

            $activeSession = json_decode(file_get_contents($activeSessionPath), true);
            if (!$activeSession || !$activeSession['is_active']) {
                return response()->json(['error' => 'No active session'], 404);
            }

            $betsHistoryPath = $this->getFilePath();
            if (!file_exists($betsHistoryPath)) {
                return response()->json(['error' => 'No bets history found'], 404);
            }

            $betsHistory = json_decode(file_get_contents($betsHistoryPath), true);
            $userBets = array_filter($betsHistory, function($bet) {
                return isset($bet['data']['user_id']) && $bet['data']['user_id'] == 1;
            });

            if (empty($userBets)) {
                return response()->json(['error' => 'No previous bets found'], 404);
            }

            $repeatedBets = [];
            foreach ($userBets as $bet) {
                $newBet = [
                    'id' => uniqid(),
                    'timestamp' => now()->toDateTimeString(),
                    'data' => $bet['data']
                ];
                $repeatedBets[] = $newBet;
                $betsHistory[] = $newBet;
            }

            file_put_contents($betsHistoryPath, json_encode($betsHistory));

            $response = [];
            foreach ($repeatedBets as $bet) {
                $response[] = [
                    'bet_amount' => (string)$bet['data']['amount'],
                    'game_coefficient_id' => ''
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Repeat bets error: '.$e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function getLatestUserBets(Request $request, $code)
    {
        try {
            $validator = Validator::make(
                array_merge(['code' => $code], $request->query()),
                [
                    'code' => 'required|in:cockroaches-space-maze',
                    'account_session' => 'required|string|min:1'
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $filePath = $this->getFilePath();
            if (!file_exists($filePath)) {
                return response()->json([]);
            }

            $history = json_decode(file_get_contents($filePath), true) ?? [];

            $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
            $userBets = array_filter($history, function($bet) use ($thirtyMinutesAgo) {
                $betTime = Carbon::parse($bet['timestamp']);
                return $betTime->gte($thirtyMinutesAgo) &&
                       isset($bet['data']['user_id']) &&
                       $bet['data']['user_id'] == 1;
            });

            $formattedBets = array_map(function($bet) {
                $betData = $bet['data'];
                return [
                    'id' => $bet['id'] ?? uniqid(),
                    'is_win' => false,
                    'is_loss' => true,
                    'bet_key' => $this->generateBetKey($betData),
                    'bet_amount' => (string)($betData['amount'] ?? 0),
                    'is_rollback' => false,
                    'bet_currency' => 'RUB',
                    'is_processed' => true,
                    'final_outcome' => '0.00',
                    'game_session_id' => '',
                    'game_coefficient_id' => ''
                ];
            }, array_slice($userBets, 0, 100));

            return response()->json($formattedBets);

        } catch (\Exception $e) {
            Log::error('Error loading user bets: '.$e->getMessage());
            return response()->json([], 500);
        }
    }

    private function generateBetKey($betData)
    {
        $type = $betData['type'] ?? '';
        $selection = $betData['selection'] ?? [];
        switch ($type) {
            case 'win':
                return "result:{$this->getColorName($betData['color'])}:1";
            case 'place':
                return "result:{$this->getColorName($betData['color'])}:2";
            case 'trap':
                return "section:{$betData['trapId']}:" . implode(',', $selection);
            default:
                return "unknown:{$type}";
        }
    }

    private function getColorName($hexColor)
    {
        $colorMap = [
            '#FFFF00' => 'yellow',
            '#FFA500' => 'orange',
            '#8B0000' => 'dark-orange',
            '#0000FF' => 'blue',
            '#FF0000' => 'red',
            '#800080' => 'purple',
            '#00FF00' => 'green'
        ];
        return $colorMap[$hexColor] ?? 'unknown';
    }
}
