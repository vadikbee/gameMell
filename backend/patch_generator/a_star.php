<?php
class AStar {
    public static function findPath(array $start, array $end, array $walls, int $width, int $height): array {
        $openSet = [implode(',', $start)];
        $cameFrom = [];
        
        $gScore = array_fill(0, $height, array_fill(0, $width, INF));
        $gScore[$start[1]][$start[0]] = 0;
        
        $fScore = array_fill(0, $height, array_fill(0, $width, INF));
        $fScore[$start[1]][$start[0]] = self::heuristic($start, $end);
        
        while (!empty($openSet)) {
            $current = self::lowestFScore($openSet, $fScore);
            
            if ($current == implode(',', $end)) {
                return self::reconstructPath($cameFrom, $end);
            }
            
            $openSet = array_diff($openSet, [$current]);
            [$x, $y] = explode(',', $current);
            $x = (int)$x;
            $y = (int)$y;
            
            foreach (self::getNeighbors($x, $y, $walls, $width, $height) as $neighbor) {
                [$nx, $ny] = $neighbor;
                $tentativeGScore = $gScore[$y][$x] + 1;
                
                if ($tentativeGScore < $gScore[$ny][$nx]) {
                    $cameFrom[implode(',', $neighbor)] = [$x, $y];
                    $gScore[$ny][$nx] = $tentativeGScore;
                    $fScore[$ny][$nx] = $tentativeGScore + self::heuristic($neighbor, $end);
                    
                    if (!in_array(implode(',', $neighbor), $openSet)) {
                        $openSet[] = implode(',', $neighbor);
                    }
                }
            }
        }
        
        return []; // Путь не найден
    }

    private static function heuristic(array $a, array $b): float {
        return abs($a[0] - $b[0]) + abs($a[1] - $b[1]);
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

    private static function getNeighbors(int $x, int $y, array $walls, int $width, int $height): array {
        $neighbors = [];
        $directions = [
            [0, -1], [1, 0], [0, 1], [-1, 0], // Вверх, вправо, вниз, влево
            [-1, -1], [1, -1], [1, 1], [-1, 1] // Диагонали
        ];
        
        foreach ($directions as $dir) {
            $nx = $x + $dir[0];
            $ny = $y + $dir[1];
            
            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                $isWall = false;
                foreach ($walls as $wall) {
                    if ($wall[0] == $nx && $wall[1] == $ny) {
                        $isWall = true;
                        break;
                    }
                }
                
                if (!$isWall) {
                    $neighbors[] = [$nx, $ny];
                }
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