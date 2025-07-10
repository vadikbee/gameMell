<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

require_once base_path('path_generator/PathGenerator.php');
require_once base_path('path_generator/a_star.php');

// Обработка CORS для OPTIONS запроса
Route::options('/generate-paths', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400');
});
// api.php
Route::options('/generate-paths', function () {
    return response()->make('OK', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
// Основной обработчик
// Основной обработчик
Route::match(['GET', 'POST'], '/generate-paths', function (Request $request) {
    try {
        // Для GET-запросов возвращаем информацию о сервисе
        if ($request->isMethod('GET')) {
            return response()->json([
                'service' => 'Tarakan Path Generator',
                'description' => 'Generates racing paths for bugs',
                'endpoints' => [
                    'POST /api/generate-paths' => 'Generate paths with parameters',
                    'GET /api/generate-paths' => 'Service information'
                ],
                'parameters' => [
                    'bug_count' => 'Number of bugs (default: 7)',
                    'duration' => 'Race duration in ms (default: 10000)',
                    'max_moves' => 'Max path points (default: 200)',
                    'include_grid' => 'Include grid configuration (optional)'
                ]
            ]);
        }

        // Для POST-запросов генерируем пути
        $gridConfigPath = base_path('config/race_grid.json');
        
        if (!file_exists($gridConfigPath)) {
            throw new \Exception("Grid config file not found: $gridConfigPath");
        }
        
        // Правильное чтение JSON-файла
        $gridConfig = json_decode(file_get_contents($gridConfigPath), true);
        
        if (!$gridConfig || !isset($gridConfig['cols'], $gridConfig['rows'])) {
            throw new \Exception("Invalid grid configuration");
        }
        
        $generator = new \PathGenerator($gridConfig);
        
        $result = $generator->generatePaths(
            $request->input('bug_count', 7),
            $request->input('duration', 10000),
            $request->input('max_moves', 200)
        );
        
        // Добавляем информацию о сетке, если запрошено
        if ($request->input('include_grid', false)) {
            $result['grid'] = $gridConfig;
        }
        
        return response()->json($result)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        
    } catch (\Exception $e) {
        Log::error('Path generation error: ' . $e->getMessage());
        
        return response()->json(['error' => $e->getMessage()], 500)
            ->header('Access-Control-Allow-Origin', '*');
    }
});