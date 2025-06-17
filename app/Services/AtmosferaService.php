# Основной класс для работы с API

<?php

namespace App\Services;

class AtmosferaService
{
    protected $baseUrl;
    protected $contractor;
    protected $privateKey;
    protected $version;

    public function __construct()
    {
        $config = config('atmosfera');
        $this->baseUrl = $config['base_url'];
        $this->contractor = $config['contractor'];
        $this->privateKey = $config['private_key'];
        $this->version = $config['version'];
    }

    private function generateHash($data)
    {
        $time = now()->format('d-m-Y H:i:s');
        $jsonData = json_encode($data);
        $hashString = $time . $jsonData . $this->privateKey;
        return [
            'hash' => hash('sha256', $hashString),
            'time' => $time,
            'data' => $jsonData
        ];
    }

    private function sendRequest($endpoint, $data)
    {
        $hashData = $this->generateHash($data);
        
        $postData = [
            'contractor' => $this->contractor,
            'version' => $this->version,
            'time' => $hashData['time'],
            'data' => $hashData['data'],
            'hash' => $hashData['hash']
        ];

        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Для тестов
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    // Основные методы API
    public function validateToken($token)
    {
        return $this->sendRequest('atmosfera/validate_token', [
            'token' => $token
        ]);
    }

    public function getAccount($userId, $sessionId = null)
    {
        $data = ['user_id' => $userId];
        if ($sessionId) $data['session_id'] = $sessionId;
        return $this->sendRequest('atmosfera/get_account', $data);
    }

    // Добавьте остальные методы: withdraw, deposit, rollback, get_personal
}