<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
}