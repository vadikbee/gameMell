<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BetHistoryController extends Controller
{
    const HISTORY_FILE = 'bets_history.json';
    const MAX_BETS = 50; // Сохраняем последние 50 ставок

    public function saveBet(Request $request)
    {
         try {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
            'type' => 'required|in:win,place,trap',
            'selection' => 'required|array',
            'color' => 'required|string',
            //'time' => 'required|date_format:H:i:s'
        ]);

            $filePath = $this->getFilePath();
            $history = [];

            if (file_exists($filePath)) {
                $history = json_decode(file_get_contents($filePath), true) ?? [];
            }

            array_unshift($history, [
                'id' => uniqid(),
                'timestamp' => now()->toDateTimeString(),
                'data' => $validated
            ]);

            file_put_contents(
                $filePath, 
                json_encode(array_slice($history, 0, self::MAX_BETS))
            );

             return response()->json([
            'status' => 'success',
            'bet_id' => uniqid(),
            'new_balance' => 5000 // Временное значение
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
        return storage_path('app/' . self::HISTORY_FILE);
    }
}