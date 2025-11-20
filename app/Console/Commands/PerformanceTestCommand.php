<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use App\Services\QueryOptimizationService;
use Hypervel\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerformanceTestCommand extends Command
{
    protected ?string $signature = 'performance:test {--iterations=10 : Number of iterations for testing}';
    
    protected string $description = 'Run performance tests to validate optimizations';

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        
        $this->info("Running performance tests with {$iterations} iterations...");
        
        // Create some test data if not exists
        if (User::count() < 10) {
            $this->info("Creating test data...");
            for ($i = 0; $i < 10; $i++) {
                User::create([
                    'name' => 'Performance Test User ' . $i,
                    'username' => 'perf_user_' . $i,
                    'email' => 'perf_test_' . $i . '@example.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'full_name' => 'Performance Test User ' . $i,
                    'is_active' => true,
                ]);
            }
        }

        // Test 1: Cache performance
        $this->info("\nTest 1: Cache Performance");
        $this->runCachePerformanceTest($iterations);

        // Test 2: Query optimization
        $this->info("\nTest 2: Query Optimization");
        $this->runQueryOptimizationTest($iterations);

        // Test 3: N+1 prevention
        $this->info("\nTest 3: N+1 Query Prevention");
        $this->runNPlusOneTest($iterations);

        $this->info("\nPerformance tests completed!");
        return Command::SUCCESS;
    }

    private function runCachePerformanceTest(int $iterations): void
    {
        $userService = new UserService();
        $users = User::limit(5)->get();

        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($users as $user) {
                $userService->getUserById($user->id);
            }
        }
        $cacheTime = microtime(true) - $start;

        $this->info("  Cache retrieval time for {$iterations} iterations: " . number_format($cacheTime * 1000, 2) . "ms");
        $this->info("  Average per retrieval: " . number_format(($cacheTime / ($iterations * $users->count())) * 1000, 2) . "ms");
    }

    private function runQueryOptimizationTest(int $iterations): void
    {
        $queryOptimizationService = new QueryOptimizationService();

        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $queryOptimizationService->getUsersWithRolesOptimized();
        }
        $queryTime = microtime(true) - $start;

        $this->info("  Optimized query time for {$iterations} iterations: " . number_format($queryTime * 1000, 2) . "ms");
        $this->info("  Average per query: " . number_format(($queryTime / $iterations) * 1000, 2) . "ms");
    }

    private function runNPlusOneTest(int $iterations): void
    {
        // First, test without optimization (potential N+1)
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            // This would cause N+1 if relationships aren't eager loaded
            $users = User::limit(10)->get();
            foreach ($users as $user) {
                // Accessing relationship without eager loading would cause N+1
                $user->roles; // This is just to trigger the relationship
            }
        }
        $nPlusOneTime = microtime(true) - $start;

        // Then test with optimization (eager loading)
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            // This uses eager loading to prevent N+1
            $users = User::with('roles')->limit(10)->get();
            foreach ($users as $user) {
                $user->roles; // This won't cause additional queries
            }
        }
        $optimizedTime = microtime(true) - $start;

        $this->info("  N+1 query time: " . number_format($nPlusOneTime * 1000, 2) . "ms");
        $this->info("  Optimized query time: " . number_format($optimizedTime * 1000, 2) . "ms");
        $this->info("  Improvement: " . number_format((($nPlusOneTime - $optimizedTime) / $nPlusOneTime) * 100, 2) . "%");
    }
}