<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; // Добавлен импорт Validator
use Illuminate\Support\Carbon; // Добавлен импорт Carbon
class BetHistoryController extends Controller
{
    const HISTORY_FILE = 'bets_history.json';
    const MAX_BETS = 10; // Сохраняем только последние 10 ставок

    public function saveBet(Request $request)
    {
        try {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
            'type' => 'required|in:win,place,trap',
            'selection' => 'required|array', // Теперь это массив ID
            'trapId' => 'required_if:type,trap|integer', // Добавляем поле trapId
            'color' => 'required|string',
            'time' => 'required|date_format:H:i:s'
        ]);
            // Для ставок на секцию сохраняем ID тараканов
        if ($validated['type'] === 'trap') {
            $validated['selection'] = $request->input('selection'); // ID тараканов
        } else {
            // Для других ставок сохраняем цвета
            $validated['color'] = $request->input('color', '');
        }
            
            // Если время не пришло - генерируем текущее
            if (empty($validated['time'])) {
                $validated['time'] = now()->format('H:i:s');
            }

            $filePath = $this->getFilePath();
            $history = [];

            if (file_exists($filePath)) {
                $history = json_decode(file_get_contents($filePath), true) ?? [];
            }

            // Добавляем новую ставку в начало
            array_unshift($history, [
                'id' => uniqid(),
                'timestamp' => now()->toDateTimeString(),
                'data' => $validated
            ]);

            // Оставляем только последние MAX_BETS ставок
            $history = array_slice($history, 0, self::MAX_BETS);

            file_put_contents($filePath, json_encode($history));

            return response()->json([
                'status' => 'success',
                'bet_id' => uniqid(),
                'new_balance' => 5000
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving bet: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getLatestBets()
    {
        try {
            $filePath = $this->getFilePath();
            if (!file_exists($filePath)) return response()->json([]);

            // Возвращаем все ставки (уже ограничено 10)
            return response()->json(
                json_decode(file_get_contents($filePath), true) ?? []
            );
        } catch (\Exception $e) {
            Log::error('Error loading bet history: '.$e->getMessage());
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
    // BetHistoryController.php
public function repeatBets(Request $request, $code)
{
    try {
        // Валидация параметров
        $validator = Validator::make(array_merge($request->all(), ['code' => $code]), [
            'code' => 'required|in:cockroaches-space-maze',
            'game_session_id' => 'required|integer',
            'account_session' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Проверяем активную сессию
        $activeSessionPath = storage_path('app/active_session.json');
        if (!file_exists($activeSessionPath)) {
            return response()->json(['error' => 'Active session not found'], 404);
        }

        $activeSession = json_decode(file_get_contents($activeSessionPath), true);
        if (!$activeSession || !$activeSession['is_active']) {
            return response()->json(['error' => 'No active session'], 404);
        }

        // Загружаем историю ставок
        $betsHistoryPath = storage_path('app/bets_history.json');
        if (!file_exists($betsHistoryPath)) {
            return response()->json(['error' => 'No bets history found'], 404);
        }

        $betsHistory = json_decode(file_get_contents($betsHistoryPath), true);
        
        // Фильтруем ставки по account_session (в демо-режиме используем user_id = 1)
        $userBets = array_filter($betsHistory, function($bet) {
            return isset($bet['data']['user_id']) && $bet['data']['user_id'] == 1;
        });

        if (empty($userBets)) {
            return response()->json(['error' => 'No previous bets found'], 404);
        }

        // Создаем новые ставки на основе предыдущих
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

        // Сохраняем обновленную историю
        file_put_contents($betsHistoryPath, json_encode($betsHistory));

        // Форматируем ответ
        $response = [];
        foreach ($repeatedBets as $bet) {
            $response[] = [
                'bet_amount' => (string)$bet['data']['amount'],
                'game_coefficient_id' => '' // Заглушка, т.к. в текущей реализации нет этого поля
            ];
        }

        return response()->json($response);

    } catch (\Exception $e) {
        Log::error('Repeat bets error: '.$e->getMessage());
        return response()->json(['error' => 'Internal server error'], 500);
    }
}
}