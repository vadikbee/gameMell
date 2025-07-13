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

    public function generatePaths(int $bugCount, int $duration, int $maxMoves): array {
    $paths = [];
    $results = [];
    $startPoints = $this->getStartPoints();
    $finishPoints = $this->getFinishPoints();

    for ($i = 0; $i < $bugCount; $i++) {
        // Выбираем случайную стартовую точку для каждого таракана
        $start = $startPoints[array_rand($startPoints)];
        $finish = $finishPoints[array_rand($finishPoints)];
        
        $path = $this->findPath(
            [$start['x'], $start['y']],
            [$finish['x'], $finish['y']]
        );

        // Обработка пути (добавление петель и отклонений)
        $path = $this->addLoops($path, 1000);
        $path = $this->addRandomDeviations($path, 1000);
        
        // Интерполяция и сглаживание пути
        $interpolatedPath = $this->interpolatePath($path, $maxMoves);
        $smoothPath = $this->smoothPath($interpolatedPath);

        $paths[$i] = $smoothPath;
        $results[$i] = [
            'start_id' => $start['id'],
            'finish_id' => $finish['id'],
            'path_length' => count($path),
            'finish_time' => $duration * mt_rand(80, 100) / 100
        ];
    }

    // Конвертация координат в пиксели
    foreach ($paths as &$bugPath) {
        foreach ($bugPath as &$point) {
            $point = $this->convertGridToPixels($point);
        }
    }       

    return [
        'paths' => $paths,
        'results' => $results
    ];
}

    private function addLoops(array $path, float $intensity): array {
        if (count($path) < 2) return $path;

        $newPath = [];
        $loopChance = $intensity;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $current = $path[$i];
            $newPath[] = $current;

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

    private function generateSmallLoop(array $current, array $next): array {
        $dx = $next[0] - $current[0];
        $dy = $next[1] - $current[1];

        // Определяем основное направление
        $mainDir = null;
        if ($dx != 0) {
            $mainDir = [$dx > 0 ? 1 : -1, 0];
        } else if ($dy != 0) {
            $mainDir = [0, $dy > 0 ? 1 : -1];
        } else {
            return [];
        }

        // Перпендикулярные направления
        $perpDirs = [];
        if ($mainDir[0] != 0) {
            $perpDirs[] = [0, 1];
            $perpDirs[] = [0, -1];
        } else {
            $perpDirs[] = [1, 0];
            $perpDirs[] = [-1, 0];
        }

        shuffle($perpDirs);

        foreach ($perpDirs as $pDir) {
            $p1 = [$current[0] + $pDir[0], $current[1] + $pDir[1]];
            $p2 = [$p1[0] + $mainDir[0], $p1[1] + $mainDir[1]];
            $p3 = $next;

            if ($this->isValidPoint($p1) && $this->isValidPoint($p2)) {
                return [$p1, $p2, $p3];
            }
        }

        return [];
    }

    private function isValidPoint(array $point): bool {
        $x = (int)round($point[0]);
        $y = (int)round($point[1]);
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
            return false;
        }
        return !$this->wallsGrid[$y][$x];
    }

    // Остальные методы без изменений (getStartPoints, getFinishPoints, convertGridToPixels, 
    // findPath, addRandomDeviations, interpolatePath, distance, smoothPath) 


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
        $deviationRange = 0.1; // Максимальное отклонение в клетках
        
        foreach ($path as $point) {
            if (count($newPath) > 0 && mt_rand(0, 100) < ($intensity * 100)) {
                $lastPoint = end($newPath);
                $devX = mt_rand(-$deviationRange, $deviationRange);
                $devY = mt_rand(-$deviationRange, $deviationRange);
                
                // Плавный переход к отклонению
                $newPath[] = [
                    $lastPoint[0] + $devX * 0.3,
                    $lastPoint[1] + $devY * 0.3
                ];
                
                $newPath[] = [
                    $lastPoint[0] + $devX * 0.7,
                    $lastPoint[1] + $devY * 0.7
                ];
            }
            $newPath[] = $point;
        }
        
        return $newPath ?: $path;
    }

   private function interpolatePath(array $path, int $maxMoves): array {
    if (count($path) < 2) return $path;
    
    // Рассчитываем общую длину пути
    $totalDistance = 0;
    for ($i = 0; $i < count($path) - 1; $i++) {
        $totalDistance += $this->distance($path[$i], $path[$i+1]);
    }
    
    // Рассчитываем шаг для интерполяции
    $step = $totalDistance / ($maxMoves - 1);
    $interpolated = [$path[0]];
    $currentDistance = 0;
    $currentIndex = 0;
    
    for ($i = 1; $i < $maxMoves; $i++) {
        $targetDistance = $i * $step;
        
        // Находим сегмент, на котором находится целевая дистанция
        while ($currentIndex < count($path) - 1 && 
               $currentDistance + $this->distance($path[$currentIndex], $path[$currentIndex+1]) < $targetDistance) {
            $currentDistance += $this->distance($path[$currentIndex], $path[$currentIndex+1]);
            $currentIndex++;
        }
        
        // Если достигли конца пути
        if ($currentIndex >= count($path) - 1) {
            $interpolated[] = end($path);
            continue;
        }
        
        // Рассчитываем позицию внутри сегмента
        $segmentStart = $path[$currentIndex];
        $segmentEnd = $path[$currentIndex+1];
        $segmentDistance = $this->distance($segmentStart, $segmentEnd);
        $t = ($targetDistance - $currentDistance) / $segmentDistance;
        
        $x = $segmentStart[0] + $t * ($segmentEnd[0] - $segmentStart[0]);
        $y = $segmentStart[1] + $t * ($segmentEnd[1] - $segmentStart[1]);
        
        $interpolated[] = [$x, $y];
    }
    
    return $interpolated;
}

private function distance(array $a, array $b): float {
    // Используем евклидово расстояние для плавности
    return sqrt(pow($a[0] - $b[0], 2) + pow($a[1] - $b[1], 2));
}

private function smoothPath(array $path): array {
    // Уменьшаем окно сглаживания для сохранения деталей
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
        
        $smoothed[] = [$sumX / $count, $sumY / $count];
    }
    
    return $smoothed;
}

}
