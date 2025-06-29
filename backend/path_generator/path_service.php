<?php

require_once 'PathGenerator.php';
require_once 'a_star.php';

// Загрузка конфига
$configPath = __DIR__.'/../config/race_grid.json';
$gridConfig = json_decode(file_get_contents($configPath), true);

if (!$gridConfig) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Grid config load error']);
    exit;
}

// Обработка запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    
    $generator = new PathGenerator($gridConfig);
    $result = $generator->generatePaths(
        $data['bug_count'] ?? 7,
        $data['duration'] ?? 10000,
        $data['max_moves'] ?? 200
    );
    
    header('Content-Type: application/json');
    echo json_encode($result);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'service' => 'Tarakan Path Generator',
        'grid' => ['cols' => $gridConfig['cols'], 'rows' => $gridConfig['rows']]
    ]);
}
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}