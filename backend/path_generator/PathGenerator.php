<?php
require_once __DIR__ . '/a_star.php';
class PathGenerator {
    private $grid;
    private $width;
    private $height;
    private $cellSize = 13;
    private $wallsGrid;

    public function __construct(array $grid) {
        $this->grid = $grid;
        $this->width = $grid['cols'];
        $this->height = $grid['rows'];
        $this->wallsGrid = array_fill(0, $this->height, array_fill(0, $this->width, false));
        foreach ($grid['walls'] as $wall) {
            $x = $wall['x'] ?? $wall[0];
            $y = $wall['y'] ?? $wall[1];
            if ($x < $this->width && $y < $this->height) {
                $this->wallsGrid[$y][$x] = true;
            }
        }
    }
// PathGenerator.php
private function createDirectPath(array $start, array $end, int $numPoints): array {
    $path = [];
    for ($i = 0; $i < $numPoints; $i++) {
        $t = $i / max(1, $numPoints - 1);
        $x = $start[0] + $t * ($end[0] - $start[0]);
        $y = $start[1] + $t * ($end[1] - $start[1]);
        $path[] = [$x, $y];
    }
    return $path;
}
 // Новый метод для телепортации точек к финишу
    private function teleportToFinish(array $point, array $targetFinish): array {
        // Если точка уже валидна - возвращаем как есть
        if ($this->isValidPoint($point)) {
            return $point;
        }

        // Ищем ближайшую валидную точку в целевой финишной зоне
        $validPoints = [];
        foreach ($this->getFinishPoints() as $finish) {
            if ($finish['id'] === $targetFinish['id'] && $this->isValidPoint([$finish['x'], $finish['y']])) {
                $validPoints[] = [$finish['x'], $finish['y']];
            }
        }

        if (empty($validPoints)) {
            return $point; // fallback
        }

        // Выбираем первую валидную точку
        return $validPoints[0];
    }
public function generatePaths(int $bugCount, int $duration, int $maxMoves): array {
    $paths = [];
    $results = [];
    $speedProfiles = []; // Массив для хранения профилей скорости
    $startPoints = $this->getStartPoints();
    $finishPoints = $this->getFinishPoints();

    // --- НАЧАЛО НОВОЙ ЛОГИКИ: РЕШАЕМ СУДЬБУ ТАРАКАНОВ ---

    // 1. Определяем, сколько тараканов ВЗОРВЕТСЯ.
    // Чтобы гонка была интересной, пусть финиширует минимум 2 таракана.
    $minFinishers = 2;
    $maxExplosions = $bugCount - $minFinishers;
    // Случайное количество взрывов, например от 1 до $maxExplosions.
    $explodingCount = mt_rand(1, $maxExplosions);

    // 2. Выбираем, КАКИЕ ИМЕННО тараканы взорвутся.
    $allBugIndices = range(0, $bugCount - 1);
    shuffle($allBugIndices);
    $explodingBugsIndices = array_slice($allBugIndices, 0, $explodingCount);

    // --- КОНЕЦ НОВОЙ ЛОГИКИ ---

    $effectiveDuration = $duration * 1.25;
    $baseSpeedFactor = 1.0 / $effectiveDuration;

    $startIndex = 0;
    for ($i = 0; $i < $bugCount; $i++) {
        // --- Эта часть остается вашей оригинальной логикой генерации пути ---
        $start = $startPoints[$startIndex % count($startPoints)];
        $startIndex++;

        $validFinishes = array_filter($finishPoints, function($f) {
            return $this->isValidPoint([$f['x'], $f['y']]);
        });

        if (empty($validFinishes)) {
            $validFinishes = $finishPoints;
        }
        $finish = $validFinishes[array_rand($validFinishes)];

        $mainPath = $this->findPath(
            [$start['x'], $start['y']],
            [$finish['x'], $finish['y']]
        );

        $lastPoint = $this->ensureFinishPointValid(end($mainPath), $finish);
        $mainPath[count($mainPath)-1] = $lastPoint;

        $waypoints = [];
        for ($w = 0; $w < mt_rand(1, 2); $w++) {
            $waypoints[] = $this->getRandomWaypoint($mainPath);
        }

        $fullPath = [$mainPath[0]];
        $currentPoint = $mainPath[0];

        foreach ($waypoints as $waypoint) {
            $segment = $this->findPath($currentPoint, $waypoint);
            $fullPath = array_merge($fullPath, array_slice($segment, 1));
            $currentPoint = $waypoint;
        }

        $finalSegment = $this->findPath($currentPoint, end($mainPath));
        $fullPath = array_merge($fullPath, array_slice($finalSegment, 1));

        $path = $this->addLoops($fullPath, 100);
        $path = $this->addRandomDeviations($path, 1500);
        $interpolatedPath = $this->interpolatePath($path, $maxMoves);
        $smoothPath = $this->smoothPath($interpolatedPath);
        // --- Конец логики генерации пути ---

        $paths[$i] = $smoothPath;
        // 3. Добавляем информацию о судьбе таракана в его результаты.
        $isExploding = in_array($i, $explodingBugsIndices);
       $results[$i] = [
            'start_id' => $start['id'],
            'finish_id' => $finish['id'],
            'path_length' => count($smoothPath),
            'finish_time' => $effectiveDuration * mt_rand(90, 110) / 100,
            'will_explode' => $isExploding, // <-- Новое поле
            // Если таракан взрывается, определяем, на каком проценте пути.
            // Делаем это ближе к концу для большей драмы (от 75% до 98%).
            'explosion_progress' => $isExploding ? (mt_rand(750, 980) / 1000.0) : null // <-- Новое поле
        ];

        // 2. ГЕНЕРАЦИЯ ПРОФИЛЯ СКОРОСТИ
        // Создаем индивидуальный профиль скорости для текущего таракана.
        $profile = [
            // Базовая скорость имеет небольшое случайное отклонение от идеальной.
            'base_speed' => $baseSpeedFactor * (mt_rand(90, 110) / 100.0),
            'speed_changes' => []
        ];

        $timeAlongRace = 0;
        $numChanges = mt_rand(3, 5); // Таракан изменит скорость 3-5 раз за гонку.

        for ($j = 0; $j < $numChanges; $j++) {
            // Определяем, через сколько миллисекунд произойдет следующее изменение.
            $interval = mt_rand(1500, 3500);

            // Убеждаемся, что изменение произойдет в рамках длительности гонки.
            if ($timeAlongRace + $interval < $duration) {
                $timeAlongRace += $interval;
                $profile['speed_changes'][] = [
                    'interval' => $interval,
                    // Новая скорость также рассчитывается на основе базового фактора,
                    // что позволяет таракану как ускоряться, так и замедляться.
                    'new_speed' => $baseSpeedFactor * (mt_rand(80, 120) / 100.0)
                ];
            }
        }
        $speedProfiles[] = $profile;
    }

    // Конвертируем координаты в пиксели перед отправкой (ваша существующая логика)
    foreach ($paths as &$bugPath) {
        foreach ($bugPath as &$point) {
            $point = $this->convertGridToPixels($point);
        }
    }

    $pixelFinishZones = [];
    if (isset($this->grid['finish'])) {
        foreach ($this->grid['finish'] as $finishZone) {
            // Координата X зоны в сетке
            $gridX = $finishZone['x'];

            $pixelFinishZones[] = [
                'minX' => $gridX * $this->cellSize + $this->grid['offset_x'],
                'maxX' => ($gridX + 1) * $this->cellSize + $this->grid['offset_x'],
                'buttonId' => $finishZone['id']
            ];
        }
    }
    // 3. ВОЗВРАЩАЕМ РЕЗУЛЬТАТ
    // Отправляем на фронтенд полные данные: пути, информацию о заезде и профили скорости.
    return [
        'paths' => $paths,
        'results' => $results,
        'speed_profiles' => $speedProfiles,
        'pixel_finish_zones' => $pixelFinishZones // <-- ДОБАВЛЕНО НОВОЕ ПОЛЕ
    ];
}
// Новый метод для генерации случайных промежуточных точек
private function getRandomWaypoint(array $mainPath): array {
    // Берем случайную точку из основной траектории (кроме начала и конца)
    $index = mt_rand(ceil(count($mainPath) * 0.3), floor(count($mainPath) * 0.7));
    $point = $mainPath[$index];

    // Случайное смещение
    $deviation = mt_rand(3, 7);
    $newPoint = [
        $point[0] + mt_rand(-$deviation, $deviation),
        $point[1] + mt_rand(-$deviation, $deviation)
    ];

    // Корректируем точку, если она невалидна
    if (!$this->isValidPoint($newPoint)) {
        return $point;
    }

    return $newPoint;
}
    private function addLoops(array $path, float $intensity): array {
        if (count($path) < 2) return $path;

        $newPath = [];
        $loopChance = $intensity;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $current = $path[$i];
            $next = $path[$i+1]; // Перенесено сюда
            $newPath[] = $current;
            // Не генерируем петли возле финиша
            if ($this->isNearFinish($current) || $this->isNearFinish($next)) {
            continue;
            }
            // Пропускаем начало и конец
            if ($i > 3 && $i < count($path)-5 && mt_rand(0, 100) < ($loopChance * 100)) {
                $next = $path[$i+1];
                $loopPath = $this->generateSmallLoop($current, $next);
                if ($loopPath) {
                    $newPath = array_merge($newPath, $loopPath);
                    $i++; // пропускаем следующую точку (next)
                    continue;
                }
            }
        }

        $newPath[] = end($path);
        return $newPath;
    }
    private function isNearFinish(array $point): bool {
    $finishPoints = $this->getFinishPoints();
    $threshold = 1; // Увеличено с 1.5 до 3 клеток

    foreach ($finishPoints as $finish) {
        $distance = sqrt(pow($point[0] - $finish['x'], 2) + pow($point[1] - $finish['y'], 2));
        if ($distance <= $threshold) {
            return true;
        }
    }
    return false;
}
    private function generateSmallLoop(array $current, array $next): array {
    $dx = $next[0] - $current[0];
    $dy = $next[1] - $current[1];

    // Определяем основные направления
    $mainDir = match(true) {
        abs($dx) > abs($dy) => [$dx > 0 ? 1 : -1, 0],
        default => [0, $dy > 0 ? 1 : -1]
    };

    // Перпендикулярные направления
    $perpDirs = $mainDir[0] != 0
        ? [[0, 1], [0, -1]]
        : [[1, 0], [-1, 0]];

    shuffle($perpDirs);

    foreach ($perpDirs as $pDir) {
        $p1 = [$current[0] + $pDir[0], $current[1] + $pDir[1]];
        $p2 = [$p1[0] + $mainDir[0], $p1[1] + $mainDir[1]];
        $p3 = $next;

        // Усиленная проверка ВСЕХ точек
        if ($this->isValidPoint($p1) &&
            $this->isValidPoint($p2) &&
            $this->isValidPoint($p3) &&
            $this->isValidPoint([($p1[0]+$p2[0])/2, ($p1[1]+$p2[1])/2]) && // Проверка середины
            $this->isValidPoint([($p2[0]+$p3[0])/2, ($p2[1]+$p3[1])/2])) {
            return [$p1, $p2, $p3];
        }
    }

    return [];
}
private function isValidPoint(array $point): bool {
    $x = (int)round($point[0]);
    $y = (int)round($point[1]);


    // Проверка границ
    if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
        return false;
    }

    // Усиленная проверка стен для финишных зон
    if ($this->isNearFinish($point)) {
        // Проверяем все 8 соседних клеток
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                $nx = $x + $dx;
                $ny = $y + $dy;

                if ($nx >= 0 && $nx < $this->width && $ny >= 0 && $ny < $this->height) {
                    if ($this->wallsGrid[$ny][$nx]) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    return !$this->wallsGrid[$y][$x];
}

    private function getStartPoints(): array {
        $points = [];
        foreach ($this->grid['start'] as $start) {
            $points[] = [
                'x' => $start['x'],
                'y' => $start['y'],
                'id' => $start['id']
            ];
        }
        return $points;
    }

    public function getFinishPoints(): array {
        $points = [];
        foreach ($this->grid['finish'] as $finish) {
            $points[] = [
                'x' => $finish['x'],
                'y' => $finish['y'],
                'id' => $finish['id']
            ];
        }
        return $points;
    }

private function convertGridToPixels(array $point): array {
    return [
        $point[0] * $this->cellSize + $this->grid['offset_x'],
        $point[1] * $this->cellSize + $this->grid['offset_y']
    ];
}

    private function findPath(array $start, array $end): array {
    return AStar::findPath(
        $start,
        $end,
        $this->grid['walls'],
        $this->width,
        $this->height
    );
}

    private function addRandomDeviations(array $path, float $intensity): array {
    $newPath = [];
    $deviationRange = 0.3; // Максимальное отклонение

    foreach ($path as $point) {
        if (count($newPath) > 0 && mt_rand(0, 100) < ($intensity * 100)) {
            $lastPoint = end($newPath);
            $devX = mt_rand(-$deviationRange, $deviationRange);
            $devY = mt_rand(-$deviationRange, $deviationRange);

            // Создаем новые точки с отклонениями
            $newPoint1 = [
                $lastPoint[0] + $devX * 0.3,
                $lastPoint[1] + $devY * 0.3
            ];
            $newPoint2 = [
                $lastPoint[0] + $devX * 0.7,
                $lastPoint[1] + $devY * 0.7
            ];

            // Проверяем валидность обеих точек
            if ($this->isValidPoint($newPoint1) && $this->isValidPoint($newPoint2)) {
                $newPath[] = $newPoint1;
                $newPath[] = $newPoint2;
            }
        }
        $newPath[] = $point;
    }

    return $newPath ?: $path;
}

   private function interpolatePath(array $path, int $maxMoves): array {

    if (count($path) < 2) return $path;

    $totalDistance = 0;
    for ($i = 0; $i < count($path) - 1; $i++) {
        $totalDistance += $this->distance($path[$i], $path[$i+1]);
    }

    $step = $totalDistance / ($maxMoves - 1);
    $interpolated = [$path[0]];
    $currentDistance = 0;
    $currentIndex = 0;

    for ($i = 1; $i < $maxMoves; $i++) {
        $targetDistance = $i * $step;

        while ($currentIndex < count($path) - 1 &&
               $currentDistance + $this->distance($path[$currentIndex], $path[$currentIndex+1]) < $targetDistance) {
            $currentDistance += $this->distance($path[$currentIndex], $path[$currentIndex+1]);
            $currentIndex++;
        }

        if ($currentIndex >= count($path) - 1) {
            $interpolated[] = end($path);
            continue;
        }

        $segmentStart = $path[$currentIndex];
        $segmentEnd = $path[$currentIndex+1];
        $segmentDistance = $this->distance($segmentStart, $segmentEnd);
        $t = ($targetDistance - $currentDistance) / $segmentDistance;

        $x = $segmentStart[0] + $t * ($segmentEnd[0] - $segmentStart[0]);
        $y = $segmentStart[1] + $t * ($segmentEnd[1] - $segmentStart[1]);
        $point = [$x, $y];

        // Критически важная проверка
        if (!$this->isValidPoint($point)) {
            // Используем последнюю безопасную точку
            $point = count($interpolated) > 0
                ? $interpolated[count($interpolated)-1]
                : $segmentStart;
        }

        $interpolated[] = $point;
    }

    return $interpolated;
}

private function distance(array $a, array $b): float {
    // Используем евклидово расстояние для плавности
    return sqrt(pow($a[0] - $b[0], 2) + pow($a[1] - $b[1], 2));
}
// Новый метод для гарантии валидности финиша
private function ensureFinishPointValid(array $point, array $targetFinish): array {
    if ($this->isValidPoint($point)) {
        return $point;
    }

    // Поиск ближайшей валидной точки в целевой финишной зоне
    $validPoints = array_filter(
        $this->getFinishPoints(),
        fn($f) => $f['id'] === $targetFinish['id'] && $this->isValidPoint([$f['x'], $f['y']])
    );

    if (!empty($validPoints)) {
        $closest = $validPoints[0];
        $minDistance = INF;

        foreach ($validPoints as $fp) {
            $d = hypot($point[0]-$fp['x'], $point[1]-$fp['y']);
            if ($d < $minDistance) {
                $minDistance = $d;
                $closest = $fp;
            }
        }
        return [$closest['x'], $closest['y']];
    }

    return $point; // fallback
}
private function smoothPath(array $path): array {
    $windowSize = 3;
    $smoothed = [];

    for ($i = 0; $i < count($path); $i++) {
        $sumX = 0;
        $sumY = 0;
        $count = 0;

        $start = max(0, $i - $windowSize);
        $end = min(count($path) - 1, $i + $windowSize);

        for ($j = $start; $j <= $end; $j++) {
            $sumX += $path[$j][0];
            $sumY += $path[$j][1];
            $count++;
        }

        $smoothedPoint = [$sumX / $count, $sumY / $count];

        // Проверка валидности сглаженной точки
        if (!$this->isValidPoint($smoothedPoint)) {
            $smoothedPoint = $path[$i]; // Возвращаем исходную точку
        }
        // Дополнительная проверка для точек возле финиша
        if ($this->isNearFinish($smoothedPoint)) {
            $safePoint = $path[$i];
            $steps = 5;

            // Ищем безопасную точку на пути назад
            for ($j = 1; $j <= $steps; $j++) {
                $backIndex = max(0, $i - $j);
                if ($this->isValidPoint($path[$backIndex])) {
                    $safePoint = $path[$backIndex];
                    break;
                }
            }

            $smoothedPoint = $safePoint;
        }
        $smoothed[] = $smoothedPoint;
    }

    return $smoothed;
}

}
