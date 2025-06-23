<?php
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

        for ($i = 0; $i < $bugCount; $i++) {
            // Случайный выбор стартовой и финишной точки
            $start = $startPoints[array_rand($startPoints)];
            $finish = $finishPoints[array_rand($finishPoints)];

            // Генерация пути с помощью A*
            $path = $this->findPath(
                [$start['x'], $start['y']],
                [$finish['x'], $finish['y']]
            );

            // Добавление случайных отклонений
            $path = $this->addRandomDeviations($path, 0.3);
            
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

        return [
            'paths' => $paths,
            'results' => $results
        ];
    }

    private function getStartPoints(): array {
        $points = [];
        foreach ($this->grid['start'] as $start) {
            // Конвертация координат сетки в пиксели
            $points[] = [
                'x' => ($start['x'] + 0.5) * $this->cellSize,
                'y' => ($start['y'] + 0.5) * $this->cellSize,
                'id' => $start['id']
            ];
        }
        return $points;
    }

    private function getFinishPoints(): array {
        $points = [];
        foreach ($this->grid['finish'] as $finish) {
            $points[] = [
                'x' => ($finish['x'] + 0.5) * $this->cellSize,
                'y' => ($finish['y'] + 0.5) * $this->cellSize,
                'id' => $finish['id']
            ];
        }
        return $points;
    }

    private function findPath(array $start, array $end): array {
        // Используем алгоритм A* из отдельного файла
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
        $deviationRange = 5; // Максимальное отклонение в пикселях
        
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
        $windowSize = 3;
        
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