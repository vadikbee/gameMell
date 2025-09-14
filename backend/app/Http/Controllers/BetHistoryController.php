<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException; // <-- ВАЖНО: Добавьте этот импорт

class BetHistoryController extends Controller
{
    const HISTORY_FILE = 'bets_history.json';
    const MAX_BETS = 20; // Увеличим для надежности

    /**
     * Сохраняет пачку ставок.
     */
    public function saveBetsBatch(Request $request)
    {
        try {
            // Правила валидации для всех типов ставок
            $validated = $request->validate([
                'bets' => 'required|array',
                'bets.*.user_id' => 'required|integer',
                'bets.*.amount' => 'required|numeric|min:0',
                'bets.*.type' => 'required|string|in:win,place,trap,position,overtaking,section',

                // Поля для ставки 'position'
                'bets.*.bugId' => 'required_if:bets.*.type,position|integer',
                'bets.*.position' => 'required_if:bets.*.type,position|integer',

                // Поля для ставки 'overtaking'
                'bets.*.overtaker' => 'required_if:bets.*.type,overtaking|integer',
                'bets.*.overtaken' => 'required_if:bets.*.type,overtaking|integer',

                // Поля для ставки 'trap' (секция)
                'bets.*.selection' => 'required_if:bets.*.type,trap|array',
                'bets.*.trapId' => 'required_if:bets.*.type,trap|integer',

                'bets.*.coefficient' => 'sometimes|numeric',
                'bets.*.time' => 'sometimes|string', // Принимаем время, но не требуем
                'bets.*.color' => 'sometimes|string',
                'bets.*.bugColors' => 'sometimes|array',
            ]);

            $history = $this->loadHistory();

            $savedBets = [];
            foreach ($validated['bets'] as $betData) {
                $bet = [
                    'id' => uniqid(),
                    'timestamp' => now()->toDateTimeString(), // Сервер всегда генерирует свежую метку времени
                    'data' => $betData
                ];
                array_unshift($history, $bet);
                $savedBets[] = $bet;
            }

            $this->saveHistory($history);

            return response()->json([
                'status' => 'success',
                'saved_count' => count($savedBets)
            ]);

        } catch (ValidationException $e) {
            // ЭТОТ БЛОК ОТЛОВИТ ОШИБКУ ВАЛИДАЦИИ
            Log::error('Save bets batch validation error: ', $e->errors());
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // ЭТОТ БЛОК ОТЛОВИТ ЛЮБЫЕ ДРУГИЕ ОШИБКИ
            Log::error('Save bets batch generic error: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json([
                'error' => 'An internal server error occurred.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Возвращает последние ставки.
     */
    public function getLatestBets()
    {
        try {
            $history = $this->loadHistory();
            return response()->json($history);
        } catch (\Exception $e) {
            Log::error('Error loading bet history: '.$e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Загружает историю из файла.
     */
    private function loadHistory()
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return [];
        }
        return json_decode(file_get_contents($filePath), true) ?? [];
    }

    /**
     * Сохраняет историю в файл.
     */
    private function saveHistory(array $history)
    {
        $filePath = $this->getFilePath();
        $historyToSave = array_slice($history, 0, self::MAX_BETS);
        file_put_contents($filePath, json_encode($historyToSave, JSON_PRETTY_PRINT));
    }

    /**
     * Возвращает путь к файлу истории.
     */
    private function getFilePath()
    {
        $storagePath = storage_path('app');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0775, true);
        }
        return $storagePath . '/' . self::HISTORY_FILE;
    }
}
