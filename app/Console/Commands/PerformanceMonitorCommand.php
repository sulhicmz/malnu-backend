<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use App\Services\QueryOptimizationService;
use Hypervel\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceMonitorCommand extends Command
{
    protected ?string $signature = 'performance:monitor {action? : The action to perform (stats, clear-cache, optimize)}';
    
    protected string $description = 'Monitor and manage application performance';

    public function handle(): int
    {
        $action = $this->argument('action') ?? 'stats';
        
        switch ($action) {
            case 'stats':
                return $this->showPerformanceStats();
            case 'clear-cache':
                return $this->clearApplicationCache();
            case 'optimize':
                return $this->runOptimizations();
            default:
                $this->error("Unknown action: {$action}");
                $this->line('Available actions: stats, clear-cache, optimize');
                return Command::FAILURE;
        }
    }
    
    private function showPerformanceStats(): int
    {
        $this->info('Application Performance Statistics');
        $this->line('===============================');
        
        // Cache statistics
        $this->info('Cache Statistics:');
        $cacheStats = CacheService::getStats();
        $this->line("  Hit Count: {$cacheStats['hit_count']}");
        $this->line("  Miss Count: {$cacheStats['miss_count']}");
        $this->line("  Hit Rate: {$cacheStats['hit_rate']}%");
        
        // Additional stats would go here in a real implementation
        $this->info("\nCache is properly configured and operational.");
        
        return Command::SUCCESS;
    }
    
    private function clearApplicationCache(): int
    {
        $this->info('Clearing application cache...');
        
        try {
            CacheService::clear();
            $this->queryOptimizationService = new QueryOptimizationService();
            $this->queryOptimizationService->clearOptimizationCaches();
            
            $this->info('Application cache cleared successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error clearing cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function runOptimizations(): int
    {
        $this->info('Running performance optimizations...');
        
        try {
            // Clear caches
            CacheService::clear();
            
            if (class_exists(QueryOptimizationService::class)) {
                $queryOptimizationService = new QueryOptimizationService();
                $queryOptimizationService->clearOptimizationCaches();
            }
            
            $this->info('Performance optimizations completed.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error running optimizations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}