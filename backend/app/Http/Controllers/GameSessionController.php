<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GameSessionController extends Controller
{
    const ACTIVE_SESSION_FILE = 'active_session.json';

  public function getActiveSession($code)
{
    // Валидация параметров
    $validator = Validator::make(
        ['code' => $code],
        ['code' => 'required|string|min:1']
    );

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // Проверяем код инстанса
    if ($code !== 'cockroaches-space-maze') {
        return response()->json([], 404);
    }

    // Проверяем существование активной сессии
    $sessionData = $this->getActiveSessionData();
    
    if (!$sessionData || !$sessionData['is_active']) {
        return response()->json([], 404);
    }

    // Проверяем не истекло ли время сессии
    $betsAvailableTill = Carbon::parse($sessionData['bets_available_till']);
    if (Carbon::now()->gt($betsAvailableTill)) {
        // Сессия истекла, деактивируем ее
        $this->deactivateSession();
        return response()->json([], 404);
    }

    // Добавляем next_game_session и previous_game_session
    $sessionData['next_game_session'] = null;
    $sessionData['previous_game_session'] = $this->getPreviousSession();

    return response()->json($sessionData);
}

private function getPreviousSession()
{
    $historyPath = storage_path('app/game_history.json');
    
    if (!file_exists($historyPath)) {
        return null;
    }

    $history = json_decode(file_get_contents($historyPath), true);
    if (empty($history)) {
        return null;
    }

    $lastGame = $history[0];
    $endedAt = Carbon::parse($lastGame['timestamp']);
    $startedAt = $endedAt->copy()->subSeconds(12);

    return [
        'id' => $lastGame['id'],
        'results' => $lastGame['results'],
        'ended_at' => $endedAt->toISOString(),
        'is_active' => false,
        'started_at' => $startedAt->toISOString(),
        'result_is_valid' => true,
        'bets_available_till' => $startedAt->toISOString(),
        'game_coefficients' => []
    ];
}

    /**
     * Создание новой активной сессии
     */
   public function createActiveSession()
{
    $sessionData = [
        'id' => uniqid(),
        'results' => [],
        'ended_at' => null,
        'is_active' => true,
        'started_at' => Carbon::now()->toISOString(),
        'result_is_valid' => true,
        'bets_available_till' => Carbon::now()->addMinutes(1)->toISOString(),
        'game_coefficients' => [
            [
                'id' => 101,
                'key' => 'stake_456',
                'value' => 2.5,
                'game_session_id' => 'session_abc123',
                'game_instance_id' => 12
            ]
        ],
        'next_game_session' => null,
        'previous_game_session' => null
    ];

    $this->saveActiveSessionData($sessionData);
    
    return response()->json($sessionData);
}

    /**
     * Деактивация сессии
     */
    public function deactivateSession()
    {
        $sessionData = $this->getActiveSessionData();
        if ($sessionData) {
            $sessionData['is_active'] = false;
            $sessionData['ended_at'] = Carbon::now()->toISOString();
            $this->saveActiveSessionData($sessionData);
        }
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Получение данных активной сессии
     */
    private function getActiveSessionData()
    {
        $filePath = storage_path('app/' . self::ACTIVE_SESSION_FILE);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        return json_decode($content, true);
    }

    /**
     * Сохранение данных активной сессии
     */
    private function saveActiveSessionData($data)
    {
        $filePath = storage_path('app/' . self::ACTIVE_SESSION_FILE);
        file_put_contents($filePath, json_encode($data));
    }
}