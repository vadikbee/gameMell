<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GameHistoryController extends Controller
{
    const HISTORY_FILE = 'game_history.json';
    const MAX_GAMES = 5;

    public function getLastGames()
    {
        try {
            $filePath = $this->getFilePath();
            
            if (!file_exists($filePath)) {
                return response()->json([]);
            }

            $content = file_get_contents($filePath);
            $history = json_decode($content, true) ?? [];

            return response()->json(array_slice($history, 0, self::MAX_GAMES));
        } catch (\Exception $e) {
            Log::error('Error loading history: '.$e->getMessage());
            return response()->json([]);
        }
    }

    public function saveGameResult(Request $request)
    {
        try {
        $validated = $request->validate([
             'results' => 'required|array',
            'results.*.position' => 'nullable|integer|min:1',
            'results.*.color' => 'required|string',
        ]);

            $results = $validated['results'];
            $history = [];
            $filePath = $this->getFilePath();

            // Создаем директорию если не существует
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $history = json_decode($content, true) ?? [];
            }

            array_unshift($history, [
                'id' => uniqid(),
                'timestamp' => now()->toDateTimeString(),
                'results' => $results
            ]);

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