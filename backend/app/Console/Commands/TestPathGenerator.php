<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPathGenerator extends Command
{
    protected $signature = 'test:generator';
    protected $description = 'Test path generator service';

    public function handle()
    {
        $this->info("Starting path generator test...");
        
        // Загрузка конфигурации сетки - ИСПРАВЛЕННАЯ СТРОКА
        $configPath = base_path('config/race_grid.json');
        $jsonContent = file_get_contents($configPath);
        $gridConfig = json_decode($jsonContent, true);
        
        if (!$gridConfig) {
            $this->error("Failed to load race_grid.json");
            return;
        }
        
        $this->info("Grid config loaded successfully");
        $this->info("Columns: {$gridConfig['cols']}, Rows: {$gridConfig['rows']}");
        $this->info("Start positions: " . count($gridConfig['start']));
        $this->info("Finish positions: " . count($gridConfig['finish']));
        $this->info("Walls: " . count($gridConfig['walls']));
        
        try {
            $generator = new \PathGenerator($gridConfig);
            $result = $generator->generatePaths(5, 8000, 100);
            
            $this->info("Path generation successful!");
            $this->info("Generated paths: " . count($result['paths']));
            
            foreach ($result['results'] as $i => $bugResult) {
                $this->line("Bug #" . ($i + 1) . ":");
                $this->line("  Start ID: {$bugResult['start_id']}");
                $this->line("  Finish ID: {$bugResult['finish_id']}");
                $this->line("  Path length: {$bugResult['path_length']}");
                $this->line("  Finish time: {$bugResult['finish_time']}ms");
            }
            
            // Сохраняем результат для отладки
            file_put_contents(
                storage_path('logs/path_test.json'),
                json_encode($result, JSON_PRETTY_PRINT)
            );
            $this->info("Full result saved to: storage/logs/path_test.json");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
        }
    }
}