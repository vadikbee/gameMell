<?php
require_once __DIR__ . '/a_star.php';
class PathGenerator {
    private $grid;
    private $width;
    private $height;
    private $cellSize = 13; // Размер клетки в пикселях

    public function __construct(array $grid) {
        $this->grid = $grid;
        $this->width = $grid['cols'];
        $this->height = $grid['rows'];
    }

    public function generatePaths(int $bugCount, int $duration, int $maxMoves): array {
        $paths = [];
        $results = [];
        $startPoints = $this->getStartPoints();
        $finishPoints = $this->getFinishPoints();

        // Все тараканы стартуют из одной и той же точки
        $start = $startPoints[0] ?? ['x' => 15, 'y' => 3, 'id' => 1];
        
        for ($i = 0; $i < $bugCount; $i++) {
            // Генерация пути до случайной финишной точки
            $finish = $finishPoints[array_rand($finishPoints)];
            $path = $this->findPath(
                [$start['x'], $start['y']],
                [$finish['x'], $finish['y']]
            );

            // Добавление случайных отклонений
            $path = $this->addRandomDeviations($path, 0.4);
            
            // Интерполяция пути
            $interpolatedPath = $this->interpolatePath($path, $maxMoves);
            
            // Добавление плавности
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
