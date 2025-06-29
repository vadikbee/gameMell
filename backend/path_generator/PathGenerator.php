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

    private function getFinishPoints(): array {
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
        
        $interpolated = [];
        $totalPoints = count($path);
        $step = ($totalPoints - 1) / ($maxMoves - 1);
        
        for ($i = 0; $i < $maxMoves; $i++) {
            $pos = $i * $step;
            $idx1 = floor($pos);
            $idx2 = min($idx1 + 1, $totalPoints - 1);
            $ratio = $pos - $idx1;
            
            $x = $path[$idx1][0] + ($path[$idx2][0] - $path[$idx1][0]) * $ratio;
            $y = $path[$idx1][1] + ($path[$idx2][1] - $path[$idx1][1]) * $ratio;
            
            $interpolated[] = [$x, $y];
        }
        
        return $interpolated;
    }

    private function smoothPath(array $path): array {
        $smoothed = [];
        $windowSize = 5;
        
        for ($i = 0; $i < count($path); $i++) {
            $sumX = 0;
            $sumY = 0;
            $count = 0;
            
            for ($j = max(0, $i - $windowSize); $j <= min(count($path) - 1, $i + $windowSize); $j++) {
                $sumX += $path[$j][0];
                $sumY += $path[$j][1];
                $count++;
            }
            
            $smoothed[] = [$sumX / $count, $sumY / $count];
        }
        
        return $smoothed;
    }
}