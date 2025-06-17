<?php
# Контроллер для игровой логики
namespace App\Http\Controllers;

use App\Services\AtmosferaService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function start(Request $request)
    {
        // 1. Получаем токен из ATMOSFERA
        $atmosfera = new AtmosferaService();
        $tokenResponse = $atmosfera->validateToken($request->token);
        
        if (!$tokenResponse['result']) {
            abort(401, 'Invalid token');
        }

        // 2. Получаем баланс пользователя
        $account = $atmosfera->getAccount(
            $tokenResponse['user_id'],
            $tokenResponse['session_id'] ?? null
        );

        // 3. Передаем данные в игровой интерфейс
        return view('game', [
            'token' => $request->token,
            'lang' => $request->lang ?? 'en',
            'userId' => $tokenResponse['user_id'],
            'balance' => $account['amount'],
            'currency' => $account['currency']
        ]);
    }

    public function callback(Request $request)
    {
        // Обработка транзакций: withdraw, deposit, rollback
        $data = $request->all();
        $atmosfera = new AtmosferaService();
        
        switch ($data['action']) {
            case 'withdraw':
                return $atmosfera->withdraw(
                    $data['user_id'],
                    $data['amount'],
                    $data['bonus_amount'] ?? 0,
                    // ... остальные параметры
                );
                
            case 'deposit':
                // ... аналогично
        }
    }
}