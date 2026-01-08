<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CacheService;
use Exception;
use Hyperf\DbConnection\Db;
use Hypervel\Console\Command;
use Psr\Container\ContainerInterface;

class CacheWarmupCommand extends Command
{
    protected ?string $signature = 'cache:warmup {--model= : Specific model to warmup (students, teachers, all)}';

    protected string $description = 'Warm up cache for frequently accessed data';

    private CacheService $cache;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->cache = new CacheService($container);
    }

    public function handle()
    {
        $model = $this->option('model') ?? 'all';

        $this->info('Starting cache warmup...');

        $startTime = microtime(true);

        try {
            switch ($model) {
                case 'students':
                    $this->warmUpStudents();
                    break;
                case 'teachers':
                    $this->warmUpTeachers();
                    break;
                case 'all':
                default:
                    $this->warmUpStudents();
                    $this->warmUpTeachers();
                    $this->warmUpClasses();
                    $this->warmUpSubjects();
                    break;
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $metrics = $this->cache->getMetrics();

            $this->info('Cache warmup completed successfully!');
            $this->info("Time taken: {$duration}s");
            $this->info("Total keys in cache: {$metrics['total_keys']}");
            $this->newLine();

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Cache warmup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function warmUpStudents(): void
    {
        $this->info('Warming up students cache...');

        $count = Db::table('students')->count();
        $students = Db::table('students')
            ->select('students.*', 'classes.name as class_name')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->orderBy('students.name', 'asc')
            ->limit(100)
            ->get();

        foreach ($students as $student) {
            $this->cache->set(
                $this->cache->generateModelKey('Student', $student->id),
                (array) $student,
                CacheService::TTL_LONG
            );
        }

        $this->info("  - Cached {$count} students");
    }

    private function warmUpTeachers(): void
    {
        $this->info('Warming up teachers cache...');

        $count = Db::table('teachers')->count();
        $teachers = Db::table('teachers')
            ->select('teachers.*', 'classes.name as class_name', 'subjects.name as subject_name')
            ->leftJoin('classes', 'teachers.class_id', '=', 'classes.id')
            ->leftJoin('subjects', 'teachers.subject_id', '=', 'subjects.id')
            ->orderBy('teachers.name', 'asc')
            ->limit(100)
            ->get();

        foreach ($teachers as $teacher) {
            $this->cache->set(
                $this->cache->generateModelKey('Teacher', $teacher->id),
                (array) $teacher,
                CacheService::TTL_LONG
            );
        }

        $this->info("  - Cached {$count} teachers");
    }

    private function warmUpClasses(): void
    {
        $this->info('Warming up classes cache...');

        $count = Db::table('classes')->count();
        $classes = Db::table('classes')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($classes as $class) {
            $this->cache->set(
                $this->cache->generateModelKey('Class', $class->id),
                (array) $class,
                CacheService::TTL_VERY_LONG
            );
        }

        $this->info("  - Cached {$count} classes");
    }

    private function warmUpSubjects(): void
    {
        $this->info('Warming up subjects cache...');

        $count = Db::table('subjects')->count();
        $subjects = Db::table('subjects')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($subjects as $subject) {
            $this->cache->set(
                $this->cache->generateModelKey('Subject', $subject->id),
                (array) $subject,
                CacheService::TTL_VERY_LONG
            );
        }

        $this->info("  - Cached {$count} subjects");
    }
}
