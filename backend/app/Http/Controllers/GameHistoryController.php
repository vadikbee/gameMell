<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GameHistoryController extends Controller
{
    const HISTORY_FILE = 'game_history.json';
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