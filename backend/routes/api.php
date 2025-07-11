<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameHistoryController;

require_once base_path('path_generator/PathGenerator.php');
require_once base_path('path_generator/a_star.php');

// Кешированная конфигурация сетки
$cachedGridConfig = null;
// Кешированные сгенерированные пути
$pathsCache = [];
///////////////////////////////////////ЭНДПОИНТ (lastGame)///////////////////////////////////////
Route::get('/api/v1/gameplay/games/sessions/{code}/last', [GameHistoryController::class, 'getLastGamesForSession']);
Route::options('/api/v1/gameplay/games/sessions/{code}/last', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
///////////////////////////////////////ЭНДПОИНТ (lastGame)///////////////////////////////////////
// Обработка CORS для OPTIONS запроса
Route::options('/generate-paths', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400');
});

// Основной обработчик
Route::match(['GET', 'POST'], '/generate-paths', function (Request $request) use (&$cachedGridConfig, &$pathsCache) {
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

        // Генерация ключа кэша
        $cacheKey = md5(serialize([
            'bug_count' => $request->input('bug_count', 7),
            'duration' => $request->input('duration', 10000),
            'max_moves' => $request->input('max_moves', 200),
            'include_grid' => $request->input('include_grid', false)
        ]));
        
        // Проверка кэша
        if (isset($pathsCache[$cacheKey])) {
            return response()->json($pathsCache[$cacheKey])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        }

        // Для POST-запросов генерируем пути
        $gridConfigPath = base_path('config/race_grid.json');
        
        // Загружаем конфиг только если он еще не закеширован
        if ($cachedGridConfig === null) {
            if (!file_exists($gridConfigPath)) {
                throw new \Exception("Grid config file not found: $gridConfigPath");
            }
            
            $cachedGridConfig = json_decode(file_get_contents($gridConfigPath), true);
            
            if (!$cachedGridConfig || !isset($cachedGridConfig['cols'], $cachedGridConfig['rows'])) {
                throw new \Exception("Invalid grid configuration");
            }
        }
        
        $generator = new \PathGenerator($cachedGridConfig);
        
        $result = $generator->generatePaths(
            $request->input('bug_count', 7),
            $request->input('duration', 10000),
            $request->input('max_moves', 200)
        );
        
        // Добавляем информацию о сетке, если запрошено
        if ($request->input('include_grid', false)) {
            $result['grid'] = array_merge($cachedGridConfig, [
                'finish' => $generator->getFinishPoints()
            ]);
        }

        // Сохраняем результат в кэш
        $pathsCache[$cacheKey] = $result;

        return response()->json($result)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        
    } catch (\Exception $e) {
        Log::error('Path generation error: ' . $e->getMessage());
        
        return response()->json(['error' => $e->getMessage()], 500)
            ->header('Access-Control-Allow-Origin', '*');
    }
});

Route::options('/game-history/save', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::post('/game-history/save', [GameHistoryController::class, 'saveGameResult']);
Route::get('/game-history/last', [GameHistoryController::class, 'getLastGames']);