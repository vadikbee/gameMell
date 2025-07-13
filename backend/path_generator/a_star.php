<?php
class AStar {
    public static function findPath(array $start, array $end, array $walls, int $width, int $height): array {
        // Создаем сетку стен для быстрого доступа O(1)
        $wallsGrid = array_fill(0, $height, array_fill(0, $width, false));
        foreach ($walls as $wall) {
            $x = $wall['x'] ?? $wall[0];
            $y = $wall['y'] ?? $wall[1];
            if ($x < $width && $y < $height) {
                $wallsGrid[$y][$x] = true;
            }
        }

        $openSet = new \SplPriorityQueue();
        $openSet->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
        
        $startKey = "{$start[0]},{$start[1]}";
        $openSet->insert($startKey, 0);
        
        $gScore = array_fill(0, $height, array_fill(0, $width, INF));
        $gScore[$start[1]][$start[0]] = 0;
        
        $fScore = array_fill(0, $height, array_fill(0, $width, INF));
        $fScore[$start[1]][$start[0]] = self::heuristic($start, $end);
        
        $cameFrom = [];

        while (!$openSet->isEmpty()) {
            $current = $openSet->extract();
            $currentKey = $current['data'];
            [$x, $y] = explode(',', $currentKey);
            $x = (int)$x; $y = (int)$y;
            
            if ($x === $end[0] && $y === $end[1]) {
                return self::reconstructPath($cameFrom, $end);
            }
            
            foreach (self::getNeighbors($x, $y, $wallsGrid, $width, $height) as $neighbor) {
                [$nx, $ny] = $neighbor;
                $tentativeGScore = $gScore[$y][$x] + 1;
                
                if ($tentativeGScore < $gScore[$ny][$nx]) {
                    $cameFrom["$nx,$ny"] = [$x, $y];
                    $gScore[$ny][$nx] = $tentativeGScore;
                    $fScore[$ny][$nx] = $tentativeGScore + self::heuristic($neighbor, $end);
                    
                    // Обновляем приоритет
                    $openSet->insert("$nx,$ny", -$fScore[$ny][$nx]); // Отрицательный приоритет для min-heap
                }
            }
        }
        
        return [];
    }


    private static function heuristic(array $a, array $b): float {
    $base = abs($a[0] - $b[0]) + abs($a[1] - $b[1]);
    // Добавляем случайное отклонение (до 00% от базового расстояния)
    $randomFactor = 600000 * $base * (mt_rand(0, 100) / 100);
    return $base + $randomFactor;
}

    private static function lowestFScore(array $openSet, array $fScore): string {
        $minScore = INF;
        $minNode = '';
        
        foreach ($openSet as $node) {
            [$x, $y] = explode(',', $node);
            if ($fScore[$y][$x] < $minScore) {
                $minScore = $fScore[$y][$x];
                $minNode = $node;
            }
        }
        
        return $minNode;
    }

    private static function getNeighbors(int $x, int $y, array $wallsGrid, int $width, int $height): array {
    $neighbors = [];
    $directions = [[0,1], [1,0], [0,-1], [-1,0]];
    
    // Перемешиваем направления для случайного порядка
    shuffle($directions);
    
    foreach ($directions as $dir) {
        $nx = $x + $dir[0];
        $ny = $y + $dir[1];
        if ($nx >=0 && $nx < $width && $ny >=0 && $ny < $height && !$wallsGrid[$ny][$nx]) {
            $neighbors[] = [$nx, $ny];
        }
    }
    return $neighbors;
}

    private static function reconstructPath(array $cameFrom, array $current): array {
        $path = [$current];
        
        while (isset($cameFrom[implode(',', $current)])) {
            $current = $cameFrom[implode(',', $current)];
            array_unshift($path, $current);
        }
        
        return $path;
    }
}
