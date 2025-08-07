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
public function getLastSessions(Request $request, $code)
{
    try {
        if ($code !== 'cockroaches-space-maze') {
            Log::error("Invalid game code: {$code}");
            return response()->json(['error' => 'Invalid game code'], 404);
        }

        $filePath = $this->getFilePath();
        
        // Проверка существования файла
        if (!file_exists($filePath)) {
            Log::error("History file not found: {$filePath}");
            return response()->json([]);
        }

        // Чтение и декодирование JSON с проверкой ошибок
        $content = file_get_contents($filePath);
        if ($content === false) {
            Log::error("Failed to read history file: {$filePath}");
            return response()->json([]);
        }

        $history = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON decode error: " . json_last_error_msg());
            return response()->json([]);
        }

        // Проверка на пустую историю
        if (empty($history)) {
            Log::info("History is empty");
            return response()->json([]);
        }

        $sessions = [];
        foreach (array_slice($history, 0, self::MAX_GAMES) as $game) {
            $timestamp = \Carbon\Carbon::parse($game['timestamp']);
            
            // Формируем results как объект
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