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
      'time' => 'nullable|string',
      'coefficient' => 'sometimes|numeric'
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
Route::get('/gameplay/games/accounts/{code}/{session}', function ($code, $session) {
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
Route::options('/gameplay/games/accounts/{code}/{session}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
///////////////////////////////////////получения данных игрового аккаунта///////////////////////////////////////
////////////////////////////////////////gameplay/games/bets/{code}/lates///////////////////////////////////////
Route::get('/gameplay/games/bets/{code}/latest', [BetHistoryController::class, 'getLatestUserBets']);
Route::options('/gameplay/games/bets/{code}/latest', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
////////////////////////////////////////gameplay/games/bets/{code}/lates///////////////////////////////////////
///////////////////////////////////////ЭНДПОИНТ (game instance)///////////////////////////////////////
Route::get('/gameplay/games/instances/cockroaches-space-maze', function () {
    $configPath = storage_path('/app/bet_buttons.json');

    Log::info('Loading config from: ' . $configPath);

    if (!file_exists($configPath)) {
        Log::error('Config file not found: ' . $configPath);
        return response()->json(['error' => 'Configuration not found'], 404);
    }

    $config = json_decode(file_get_contents($configPath), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('Invalid JSON in config file: ' . json_last_error_msg());
        return response()->json(['error' => 'Invalid configuration'], 500);
    }

    Log::info('Config loaded successfully', $config);

    return response()->json([
        'bet_buttons' => $config['bet_buttons'],
        'currency' => $config['currency'],
        'min_bet' => $config['min_bet'],
        'max_bet' => $config['max_bet'],
        'bet_step' => $config['bet_step'],
        'coefficients' => $config['coefficients']
    ]);
});

// Обработка CORS для OPTIONS запроса
Route::options('/gameplay/games/instances/cockroaches-space-maze', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Эндпоинт для размещения ставок
///////////////////////////////////////GET/gameplay/games/bets/{code}/repeat///////////////////////////////////////
// api.php
Route::get('/gameplay/games/bets/{code}/repeat', [BetHistoryController::class, 'repeatBets']);
Route::options('/gameplay/games/bets/{code}/repeat', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});


///////////////////////////////////////GET/gameplay/games/bets/{code}/repeat///////////////////////////////////////
///////////////////////////////////////ЭНДПОИНТ (place bet)///////////////////////////////////////
Route::post('/gameplay/games/bets/{code}', function (Request $request, $code) {
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



///////////////////////////////////////ЭНДПОИНТ (bet history)///////////////////////////////////////



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

////////////////////////////////////////gameplay/games/sessions/cockroaches-space-maze/last///////////////////////////////////////


// Добавить этот маршрут если его нет
Route::get('/gameplay/games/sessions/{code}/last', [GameHistoryController::class, 'getLastSessions']);

// И добавить обработчик CORS для этого маршрута
Route::options('/gameplay/games/sessions/{code}/last', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});


////////////////////////////////////////gameplay/games/sessions/cockroaches-space-maze/last///////////////////////////////////////

////////////////////////////////////////gameplay/games/sessions/cockroaches-space-maze/active///////////////////////////////////////

use App\Http\Controllers\GameSessionController;

// Добавляем маршрут для активной игровой сессии
// Добавьте эти маршруты в конец файла
Route::post('/gameplay/games/sessions/{code}/activate', [GameSessionController::class, 'createActiveSession']);
Route::post('/gameplay/games/sessions/{code}/deactivate', [GameSessionController::class, 'deactivateSession']);

// Обработка CORS для новых эндпоинтов
Route::options('/gameplay/games/sessions/{code}/activate', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::options('/gameplay/games/sessions/{code}/deactivate', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});
////////////////////////////////////////gameplay/games/sessions/{code}/active///////////////////////////////////////

Route::post('/calculate-winnings', [GameHistoryController::class, 'calculateWinnings']);



Route::post('/save-bets-batch', [BetHistoryController::class, 'saveBetsBatch']);
