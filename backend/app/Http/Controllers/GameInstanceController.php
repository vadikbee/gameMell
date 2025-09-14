<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameInstanceController extends Controller
{
    /**
     * Возвращает информацию об игровом инстансе по его коду.
     *
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstanceDetails($code)
    {
        $configPath = storage_path('app/game_instances.json');

        if (!file_exists($configPath)) {
            // Log::error('Configuration file not found: ' . $configPath);
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        $allInstances = json_decode(file_get_contents($configPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log::error('Invalid JSON in config file: ' . json_last_error_msg());
            return response()->json(['error' => 'Invalid server configuration'], 500);
        }

        if (!isset($allInstances[$code])) {
            return response()->json(['error' => 'Game instance not found'], 404);
        }

        // Возвращаем данные для конкретного запрошенного инстанса
        return response()->json($allInstances[$code]);
    }
}
