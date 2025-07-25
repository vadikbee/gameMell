<?php
//////////////////////////////////////// изображение 360 на 636 ////////////////////////////////////////////////////////////
// Проверяем наличие расширения GD
if (!extension_loaded('gd')) {
    die("Ошибка: Требуется расширение GD для работы с изображениями. Установите его через php.ini");
}

function colorDistance($color1, $color2) {
    $dR = $color1['red'] - $color2['red'];
    $dG = $color1['green'] - $color2['green'];
    $dB = $color1['blue'] - $color2['blue'];
    return sqrt($dR * $dR + $dG * $dG + $dB * $dB);
}

function imageToGrid($imagePath, $cellSize) {
    // Проверяем существование файла
    if (!file_exists($imagePath)) {
        die("Ошибка: Изображение не найдено по пути: $imagePath");
    }

    // Загружаем изображение с учетом прозрачности
    $image = @imagecreatefrompng($imagePath);
    if (!$image) {
        die("Ошибка: Не удалось загрузить изображение. Убедитесь, что это корректный PNG-файл.");
    }
    
    // Включаем обработку альфа-канала
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Проверяем, что изображение делится нацело
    if ($width % $cellSize !== 0 || $height % $cellSize !== 0) {
        die("Ошибка: Размер изображения {$width}x{$height} не делится нацело на размер клетки $cellSize");
    }
    
    // Определяем цвета с увеличенным порогом
    $wallColor = ['red' => 255, 'green' => 246, 'blue' => 0];
    $finishColor = ['red' => 48, 'green' => 254, 'blue' => 0];
    $startColor = ['red' => 255, 'green' => 0, 'blue' => 0];

    // Увеличенный порог для сравнения цветов
    $colorThreshold = 50;
    
    $grid = [
        'cols' => intdiv($width, $cellSize),
        'rows' => intdiv($height, $cellSize),
        'cell_size' => $cellSize,
        'walls' => [],
        'finish' => [],
        'start' => []
    ];

    // Функция для сравнения цветов с использованием евклидова расстояния
    // Отладочная информация
    $debugColors = [];
    $debugCount = 0;
    
    // Создаем карту всех пикселей с их типами
    $pixelGrid = array_fill(0, $grid['rows'], array_fill(0, $grid['cols'], [
        'start' => 0,
        'finish' => 0,
        'wall' => 0
    ]));
    
    // Создаем карту для хранения точных позиций объектов
    $pixelObjects = [
        'start' => [],
        'finish' => [],
        'wall' => []
    ];
    
    // Анализируем каждый пиксель изображения
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $colorIndex = imagecolorat($image, $x, $y);
            $rgb = imagecolorsforindex($image, $colorIndex);
            
            // Пропускаем полностью прозрачные пиксели
            if ($rgb['alpha'] > 120) continue;
            
            // Определяем координаты клетки
            $gridX = intdiv($x, $cellSize);
            $gridY = intdiv($y, $cellSize);
            
            // Сохраняем первые 10 цветов для отладки
            if ($debugCount < 10) {
                $debugColors[] = [
                    'pixelX' => $x, 'pixelY' => $y,
                    'gridX' => $gridX, 'gridY' => $gridY,
                    'color' => $rgb
                ];
                $debugCount++;
            }
            
            // Сравниваем с эталонными цветами
            $wallDist = colorDistance($rgb, $wallColor);
            $finishDist = colorDistance($rgb, $finishColor);
            $startDist = colorDistance($rgb, $startColor);
            
            // Определяем минимальное расстояние
            $minDist = min($wallDist, $finishDist, $startDist);
            
            // Если минимальное расстояние в пределах порога
            if ($minDist <= $colorThreshold) {
                if ($startDist == $minDist) {
                    $pixelGrid[$gridY][$gridX]['start']++;
                    $pixelObjects['start'][] = ['x' => $x, 'y' => $y, 'gridX' => $gridX, 'gridY' => $gridY];
                }
                elseif ($finishDist == $minDist) {
                    $pixelGrid[$gridY][$gridX]['finish']++;
                    $pixelObjects['finish'][] = ['x' => $x, 'y' => $y, 'gridX' => $gridX, 'gridY' => $gridY];
                }
                elseif ($wallDist == $minDist) {
                    $pixelGrid[$gridY][$gridX]['wall']++;
                    $pixelObjects['wall'][] = ['x' => $x, 'y' => $y, 'gridX' => $gridX, 'gridY' => $gridY];
                }
            }
        }
    }
    
    // Обрабатываем результаты: приоритет старта > финиша > стен
    $startCells = [];
    $finishCells = [];
    $wallCells = [];
    
    // Сначала собираем все стартовые клетки
    foreach ($pixelGrid as $rowIndex => $row) {
        foreach ($row as $colIndex => $cell) {
            if ($cell['start'] > 0) {
                $startCells[$colIndex . '-' . $rowIndex] = true;
                $grid['start'][] = [
                    'x' => $colIndex,
                    'y' => $rowIndex,
                    'id' => count($grid['start']) + 1,
                    'pixels' => $cell['start']
                ];
            }
        }
    }
    
    // Затем финишные (кроме тех, где есть старт)
    foreach ($pixelGrid as $rowIndex => $row) {
        foreach ($row as $colIndex => $cell) {
            $key = $colIndex . '-' . $rowIndex;
            if ($cell['finish'] > 0 && !isset($startCells[$key])) {
                $finishCells[$key] = true;
                $grid['finish'][] = [
                    'x' => $colIndex,
                    'y' => $rowIndex,
                    'id' => count($grid['finish']) + 1,
                    'pixels' => $cell['finish']
                ];
            }
        }
    }
    
    // Затем стены (кроме старта и финиша)
    foreach ($pixelGrid as $rowIndex => $row) {
        foreach ($row as $colIndex => $cell) {
            $key = $colIndex . '-' . $rowIndex;
            if ($cell['wall'] > 0 && !isset($startCells[$key]) && !isset($finishCells[$key])) {
                $grid['walls'][] = [$colIndex, $rowIndex];
            }
        }
    }

    imagedestroy($image);
    
    // Возвращаем результат с отладочной информацией
    return [
        'grid' => $grid,
        'debug' => [
            'colors' => $debugColors,
            'expected_wall' => $wallColor,
            'expected_start' => $startColor,
            'expected_finish' => $finishColor,
            'color_threshold' => $colorThreshold,
            'pixel_objects' => $pixelObjects
        ]
    ];
}

// Пути к файлам
$imagePath = __DIR__.'/../public/images/maze-360.png'; // Изменено имя файла
$configPath = __DIR__.'/../config/race_grid-360.json'; // Изменено имя конфига

// Проверяем существование изображения
if (!file_exists($imagePath)) {
    die("Ошибка: Файл изображения не найден: $imagePath");
}

// Создаем директорию для конфига, если нужно
$configDir = dirname($configPath);
if (!is_dir($configDir)) {
    mkdir($configDir, 0777, true);
}

// Генерируем сетку с размером клетки 12 (360/12=30, 636/12=53)
$result = imageToGrid($imagePath, 12); // Изменен размер клетки
$grid = $result['grid'];

// Добавляем смещения (может потребоваться корректировка)
$grid['offset_x'] = 3.5;
$grid['offset_y'] = 60;

// Сохраняем результат
file_put_contents($configPath, json_encode($grid, JSON_PRETTY_PRINT));

echo "Сетка лабиринта успешно сгенерирована и сохранена в: $configPath\n";
echo "Статистика:\n";
echo "- Колонок: {$grid['cols']}\n";
echo "- Строк: {$grid['rows']}\n";
echo "- Стен: " . count($grid['walls']) . "\n";
echo "- Стартовых точек: " . count($grid['start']) . "\n";
echo "- Финишных точек: " . count($grid['finish']) . "\n";

// Выводим отладочную информацию
echo "\nОтладочная информация:\n";
echo "Ожидаемый цвет стен: R={$result['debug']['expected_wall']['red']} G={$result['debug']['expected_wall']['green']} B={$result['debug']['expected_wall']['blue']}\n";
echo "Ожидаемый цвет старта: R={$result['debug']['expected_start']['red']} G={$result['debug']['expected_start']['green']} B={$result['debug']['expected_start']['blue']}\n";
echo "Ожидаемый цвет финиша: R={$result['debug']['expected_finish']['red']} G={$result['debug']['expected_finish']['green']} B={$result['debug']['expected_finish']['blue']}\n";
echo "Порог сравнения цветов: {$result['debug']['color_threshold']}\n";
echo "Первые 10 точек в изображении:\n";

foreach ($result['debug']['colors'] as $colorInfo) {
    $c = $colorInfo['color'];
    echo "- Пиксель ({$colorInfo['pixelX']}, {$colorInfo['pixelY']}) → Клетка [{$colorInfo['gridX']}, {$colorInfo['gridY']}]: ";
    echo "R={$c['red']} G={$c['green']} B={$c['blue']} Alpha={$c['alpha']}\n";
}
// Генерация HTML-превью для визуальной проверки
$previewPath = __DIR__.'/../public/images/maze2_preview.html';
$pixelPreviewPath = __DIR__.'/../public/images/maze2_pixel_preview.html';
$previewContent = generatePreview($grid, $result['debug']);
$pixelPreviewContent = generatePixelPreview($result['debug'], $grid['cols'], $grid['rows'], $grid['cell_size']);
file_put_contents($previewPath, $previewContent);
file_put_contents($pixelPreviewPath, $pixelPreviewContent);
echo "\nВизуальная проверка:";
echo "\n- Общая карта: $previewPath";
echo "\n- Детализация по пикселям: $pixelPreviewPath\n";

function generatePreview($grid, $debug) {
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Лабиринт 360x636: предпросмотр</title><style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { position: relative; width: ' . ($grid['cols'] * 20) . 'px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: repeat(' . $grid['cols'] . ', 20px); gap: 0; }
        .cell { width: 20px; height: 20px; border: 1px solid #eee; box-sizing: border-box; position: relative; }
        .wall { background-color: rgba(255, 255, 0, 0.7); }
        .start { background-color: rgba(255, 0, 0, 0.7); }
        .finish { background-color: rgba(0, 255, 0, 0.7); }
        .coord { position: absolute; font-size: 8px; color: #999; }
        .info { text-align: center; margin: 20px 0; }
        .legend { display: flex; justify-content: center; gap: 20px; margin: 20px 0; }
        .legend-item { display: flex; align-items: center; }
        .legend-color { width: 20px; height: 20px; margin-right: 5px; border: 1px solid #ddd; }
        .cell-label { position: absolute; font-size: 8px; bottom: 0; right: 0; background: rgba(0,0,0,0.3); color: white; }
    </style></head><body>';
    
    $html .= '<div class="info"><h2>Предпросмотр лабиринта 360x636 (сгруппировано по клеткам)</h2></div>';
    $html .= '<div class="legend">
        <div class="legend-item"><div class="legend-color wall"></div> Стена</div>
        <div class="legend-item"><div class="legend-color start"></div> Старт</div>
        <div class="legend-item"><div class="legend-color finish"></div> Финиш</div>
    </div>';
    
    $html .= '<div class="container"><div class="grid">';
    
    for ($y = 0; $y < $grid['rows']; $y++) {
        for ($x = 0; $x < $grid['cols']; $x++) {
            $class = '';
            $title = "Клетка: $x, $y\n";
            $stats = '';
            
            // Проверяем тип клетки
            $isStart = false;
            $isFinish = false;
            $isWall = false;
            
            foreach ($grid['start'] as $start) {
                if ($start['x'] == $x && $start['y'] == $y) {
                    $class = 'start';
                    $isStart = true;
                    $stats = "Старт: {$start['pixels']} пикс.";
                    $title .= $stats;
                    break;
                }
            }
            
            if (!$isStart) {
                foreach ($grid['finish'] as $finish) {
                    if ($finish['x'] == $x && $finish['y'] == $y) {
                        $class = 'finish';
                        $isFinish = true;
                        $stats = "Финиш: {$finish['pixels']} пикс.";
                        $title .= $stats;
                        break;
                    }
                }
            }
            
            if (!$isStart && !$isFinish) {
                foreach ($grid['walls'] as $wall) {
                    if ($wall[0] == $x && $wall[1] == $y) {
                        $class = 'wall';
                        $isWall = true;
                        $title .= "Стена";
                        break;
                    }
                }
            }
            
            $html .= '<div class="cell ' . $class . '" title="' . htmlspecialchars($title) . '">';
            if ($stats) {
                $html .= '<div class="cell-label">' . substr($stats, 0, 5) . '</div>';
            }
            $html .= '</div>';
        }
    }
    
    $html .= '</div>';
    
    // Добавляем координаты
    for ($x = 0; $x < $grid['cols']; $x++) {
        $html .= '<div class="coord" style="left: ' . ($x * 20 + 5) . 'px; top: -15px;">' . $x . '</div>';
    }
    
    for ($y = 0; $y < $grid['rows']; $y++) {
        $html .= '<div class="coord" style="top: ' . ($y * 20 + 5) . 'px; left: -25px;">' . $y . '</div>';
    }
    
    $html .= '</div>';
    
    // Добавляем информацию о цветах
    $html .= '<div class="info" style="margin-top: 30px;">';
    $html .= '<h3>Использованные цвета:</h3>';
    $html .= '<p>Стены: RGB(' . $debug['expected_wall']['red'] . ', ' . $debug['expected_wall']['green'] . ', ' . $debug['expected_wall']['blue'] . ')</p>';
    $html .= '<p>Старт: RGB(' . $debug['expected_start']['red'] . ', ' . $debug['expected_start']['green'] . ', ' . $debug['expected_start']['blue'] . ')</p>';
    $html .= '<p>Финиш: RGB(' . $debug['expected_finish']['red'] . ', ' . $debug['expected_finish']['green'] . ', ' . $debug['expected_finish']['blue'] . ')</p>';
    $html .= '<p>Порог цветового различия: ' . $debug['color_threshold'] . '</p>';
    $html .= '</div>';
    
    $html .= '</body></html>';
    
    return $html;
}

function generatePixelPreview($debug, $cols, $rows, $cellSize) {
    $pixelWidth = $cols * $cellSize;
    $pixelHeight = $rows * $cellSize;
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Лабиринт 360x636: детализация по пикселям</title><style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 100%; overflow: auto; }
        .pixel-grid { display: grid; grid-template-columns: repeat(' . $pixelWidth . ', 1px); gap: 0; }
        .pixel { width: 1px; height: 1px; }
        .pixel-start { background-color: #f00; }
        .pixel-finish { background-color: #0f0; }
        .pixel-wall { background-color: #ff0; }
        .info { margin: 20px 0; }
        .legend { display: flex; justify-content: center; gap: 20px; margin: 20px 0; }
        .legend-item { display: flex; align-items: center; }
        .legend-color { width: 20px; height: 20px; margin-right: 5px; border: 1px solid #ddd; }
        .controls { text-align: center; margin: 20px 0; }
        .zoom-control { margin: 0 10px; padding: 5px 10px; background: #eee; border: 1px solid #ccc; cursor: pointer; }
    </style>
    <script>
        let currentZoom = 1;
        
        function setZoom(zoom) {
            currentZoom = zoom;
            document.querySelectorAll(".pixel").forEach(pixel => {
                pixel.style.width = zoom + "px";
                pixel.style.height = zoom + "px";
            });
        }
    </script>
    </head><body>';
    
    $html .= '<div class="info"><h2>Детализация по пикселям (360x636)</h2></div>';
    $html .= '<div class="legend">
        <div class="legend-item"><div class="legend-color" style="background-color: #f00;"></div> Старт</div>
        <div class="legend-item"><div class="legend-color" style="background-color: #0f0;"></div> Финиш</div>
        <div class="legend-item"><div class="legend-color" style="background-color: #ff0;"></div> Стена</div>
    </div>';
    
    $html .= '<div class="controls">
        <span class="zoom-control" onclick="setZoom(1)">1x</span>
        <span class="zoom-control" onclick="setZoom(2)">2x</span>
        <span class="zoom-control" onclick="setZoom(4)">4x</span>
        <span class="zoom-control" onclick="setZoom(8)">8x</span>
    </div>';
    
    $html .= '<div class="container"><div class="pixel-grid">';
    
    // Создаем пустую карту пикселей
    $pixelMap = array_fill(0, $pixelHeight, array_fill(0, $pixelWidth, ''));
    
    // Заполняем карту пикселей
    if (isset($debug['pixel_objects'])) {
        if (isset($debug['pixel_objects']['start'])) {
            foreach ($debug['pixel_objects']['start'] as $pixel) {
                if ($pixel['y'] < $pixelHeight && $pixel['x'] < $pixelWidth) {
                    $pixelMap[$pixel['y']][$pixel['x']] = 'start';
                }
            }
        }
        
        if (isset($debug['pixel_objects']['finish'])) {
            foreach ($debug['pixel_objects']['finish'] as $pixel) {
                if ($pixel['y'] < $pixelHeight && $pixel['x'] < $pixelWidth) {
                    $pixelMap[$pixel['y']][$pixel['x']] = 'finish';
                }
            }
        }
        
        if (isset($debug['pixel_objects']['wall'])) {
            foreach ($debug['pixel_objects']['wall'] as $pixel) {
                if ($pixel['y'] < $pixelHeight && $pixel['x'] < $pixelWidth) {
                    $pixelMap[$pixel['y']][$pixel['x']] = 'wall';
                }
            }
        }
    }
    
    // Генерируем пиксели
    for ($y = 0; $y < $pixelHeight; $y++) {
        for ($x = 0; $x < $pixelWidth; $x++) {
            $class = '';
            $title = "Пиксель: $x, $y";
            
            if ($pixelMap[$y][$x] == 'start') {
                $class = 'pixel-start';
                $title .= "\nТип: Старт";
            } 
            elseif ($pixelMap[$y][$x] == 'finish') {
                $class = 'pixel-finish';
                $title .= "\nТип: Финиш";
            }
            elseif ($pixelMap[$y][$x] == 'wall') {
                $class = 'pixel-wall';
                $title .= "\nТип: Стена";
            }
            
            $html .= '<div class="pixel ' . $class . '" title="' . htmlspecialchars($title) . '"></div>';
        }
    }
    
    $html .= '</div></div>';
    $html .= '<script>setZoom(1);</script>';
    $html .= '</body></html>';
    
    return $html;
}
// В конце generate_grid-360.php
file_put_contents($configPath, json_encode($grid, JSON_PRETTY_PRINT));