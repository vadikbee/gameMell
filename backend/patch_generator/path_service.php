<?php
require_once 'PathGenerator.php';
require_once 'a_star.php';

// Загрузка конфигурации сетки
$gridConfig = json_decode(file_get_contents(__DIR__.'/../config/race_grid.json'), true);

header('Content-Type: application/json');

// Разрешаем CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $generator = new PathGenerator($gridConfig);
    $result = $generator->generatePaths(
        $data['bug_count'] ?? 7,
        $data['duration'] ?? 60,
        $data['max_moves'] ?? 200
    );
    
    echo json_encode($result);
    exit;
}

// Для GET запросов - информация о сервисе
echo json_encode([
    'status' => 'active',
    'service' => 'Path Generator',
    'endpoints' => [
        'POST /' => 'Generate paths with parameters: bug_count, duration, max_moves'
    ]
]);