<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameHistoryController;
use App\Http\Controllers\BetHistoryController;

require_once base_path('path_generator/PathGenerator.php');
require_once base_path('path_generator/a_star.php');


// Кешированная конфигурация сетки
$cachedGridConfig = null;
// Кешированные сгенерированные пути
$pathsCache = [];

Route::post('/api/save-bet', function (Request $request) {
    
      try {
    $data = $request->validate([
      'user_id' => 'required|integer',
      'amount' => 'required|numeric',
      'type' => 'required|in:win,place,trap,position,overtaking,section',
      // Условные параметры
      'bugId' => 'required_if:type,position|integer',
      'position' => 'required_if:type,position|integer',
      'overtaker' => 'required_if:type,overtaking|integer',
      'overtaken' => 'required_if:type,overtaking|integer',
      'selection' => 'required_if:type,section|array',
      'trapId' => 'required_if:type,section|integer',
      'color' => 'nullable|string',
      'time' => 'nullable|string'
    ]);
        
        // Сохранение ставки в базу данных или файл
        $bet = [
            'id' => uniqid(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'data' => $data
            
        ];
        
        // Логирование данных ставки
        Log::info('Bet saved: ', $bet);

        return response()->json(['status' => 'success', 'bet_id' => $bet['id']]);
        
    } catch (\Exception $e) {
        Log::error('Save bet error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});


// Добавим новые маршруты
Route::post('/api/save-bet', [BetHistoryController::class, 'saveBet']);
Route::post('/save-bet', [BetHistoryController::class, 'saveBet']);
Route::get('/get-bets', [BetHistoryController::class, 'getLatestBets']);

// Обновим OPTIONS для новых эндпоинтов
Route::options('/save-bet', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::options('/get-bets', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
///////////////////////////////////////получения данных игрового аккаунта///////////////////////////////////////
// Эндпоинт получения данных игрового аккаунта
Route::get('/api/v1/gameplay/games/accounts/{code}/{session}', function ($code, $session) {
    try {
        // Валидация параметров
        if (empty($code) || empty($session)) {
            return response()->json([
                'errors' => [
                    'parameters' => ['Параметры code и session обязательны']
                ]
            ], 422);
        }

        // Здесь должна быть реальная логика получения данных аккаунта
        // Для примера используем демо-данные
        $accountData = [
            'name' => 'PlayerOne',
            'lang' => 'EN',
            'session' => $session,
            'currency' => 'USD',
            'balance' => '1500.75'
        ];

        return response()->json($accountData);

    } catch (\Exception $e) {
        Log::error('Account error: ' . $e->getMessage());
        return response()->json(['error' => 'Internal server error'], 500);
    }
});

// Обработка CORS для OPTIONS запроса
Route::options('/api/v1/gameplay/games/accounts/{code}/{session}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
///////////////////////////////////////получения данных игрового аккаунта///////////////////////////////////////

///////////////////////////////////////ЭНДПОИНТ (game instance)///////////////////////////////////////
Route::get('/api/v1/gameplay/games/instances/{code}', function ($code) {
    // Возвращает настройки игрового инстанса
    return response()->json([
        'bet_buttons' => [25, 50, 100, 150], // Номиналы кнопок
        'currency' => 'RUB', // Валюта
        'coefficients' => [
            'win' => 6.3, // Коэффициенты для разных типов ставок
            'place' => 4.2,
            'trap' => 8.5
        ],
        'available_currencies' => ['RUB', 'USD', 'EUR'], // Доступные валюты
        'theme_params' => [ // Параметры темы
            'background' => 'space',
            'color_scheme' => 'dark'
        ]
    ]);
});

Route::options('/api/v1/gameplay/games/instances/{code}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

///////////////////////////////////////ЭНДПОИНТ (place bet)///////////////////////////////////////
Route::post('/api/v1/gameplay/games/bets/{code}', function (Request $request, $code) {
    // Обработка ставки
    $data = $request->validate([
        'user_id' => 'required|integer',
        'amount' => 'required|numeric',
        'type' => 'required|in:win,place,trap',
        'selection' => 'required|array' // Выбранные элементы (тарканы/ловушки)
    ]);
    
    // Здесь должна быть логика обработки ставки:
    // 1. Проверка доступности ставки (гонка не началась)
    // 2. Списание средств
    // 3. Сохранение ставки в БД
    
    return response()->json([
        'status' => 'success',
        'bet_id' => rand(1000, 9999),
        'new_balance' => 5000 // Пример нового баланса
    ]);
});

Route::options('/api/v1/gameplay/games/bets/{code}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

///////////////////////////////////////ЭНДПОИНТ (bet history)///////////////////////////////////////
Route::get('/api/v1/gameplay/games/bets/{code}/latest', function ($code) {
    // Возвращает последние ставки
    return response()->json([
        'bets' => [
            [
                'id' => 1,
                'user' => 'Player1',
                'amount' => 100,
                'type' => 'win',
                'selection' => [3],
                'time' => '15:30:42',
                'color' => 'linear-gradient(180deg, #FF170F 0%, #FF005E 100%)'
            ],
            [
                'id' => 2,
                'user' => 'Player2',
                'amount' => 50,
                'type' => 'place',
                'selection' => [1, 2],
                'time' => '15:31:15',
                'color' => 'linear-gradient(180deg, #FDF735 0%, #FD7E00 100%)'
            ]
        ]
    ]);
});

Route::options('/api/v1/gameplay/games/bets/{code}/latest', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
///////////////////////////////////////ЭНДПОИНТ (bet history)///////////////////////////////////////



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
// Добавьте в конец файла
Route::options('/game-history/last', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::get('/game-history/last', [GameHistoryController::class, 'getLastGames']);

///////////////////////////////////////ЭНДПОИНТ (lastGame)///////////////////////////////////////


// Затем динамический маршрут для остальных кодов
Route::get('/gameplay/games/sessions/{code}/last', [GameHistoryController::class, 'getLastSessions']);


///////////////////////////////////////ЭНДПОИНТ (lastGame)///////////////////////////////////////
// Добавьте в конец файла
