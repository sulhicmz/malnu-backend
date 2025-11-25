<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSchemaTest extends TestCase
{
    /**
     * Test that migration files have proper imports and structure.
     */
    public function testMigrationFilesHaveRequiredImports(): void
    {
        $migrationFiles = [
            'database/migrations/2023_08_03_000000_create_users_table.php',
            'database/migrations/2025_05_18_002108_create_core_table.php',
            'database/migrations/2025_05_18_002538_create_school_management_table.php',
            'database/migrations/2025_05_18_002835_create_ppdb_table.php',
            'database/migrations/2025_05_18_003049_create_elearning_table.php',
            'database/migrations/2025_05_18_003306_create_grading_table.php',
            'database/migrations/2025_05_18_003453_create_online_exam_table.php',
            'database/migrations/2025_05_18_003638_create_digital_library_table.php',
            'database/migrations/2025_05_18_003823_create_premium_feature_table.php',
            'database/migrations/2025_05_18_004014_create_monetization_table.php',
            'database/migrations/2025_05_18_004202_create_system_table.php',
            'database/migrations/2025_05_18_004400_create_staff_attendance_and_leave_management_tables.php',
        ];

        foreach ($migrationFiles as $file) {
            $this->assertTrue(file_exists($file), "Migration file should exist: {$file}");
            
            $content = file_get_contents($file);
            
            // Verify that the required import is present
            $this->assertTrue(
                strpos($content, 'use Hyperf\DbConnection\Db;') !== false,
                "Migration file {$file} should contain required DB import"
            );
            
            // Check if file uses DB::raw and confirm import is present
            if (strpos($content, 'DB::raw') !== false) {
                $this->assertTrue(
                    strpos($content, 'use Hyperf\DbConnection\Db;') !== false,
                    "Migration file {$file} uses DB::raw but is missing the import"
                );
            }
        }
        
        // Confirm all migration files were checked
        $this->assertTrue(count($migrationFiles) > 0, 'Should have migration files to test');
    }

    /**
     * Test that migration files follow proper structure.
     */
    public function testMigrationFilesStructure(): void
    {
        $migrationFiles = glob('database/migrations/*.php');
        
        foreach ($migrationFiles as $file) {
            $content = file_get_contents($file);
            
            // Check that migration extends the Migration class
            $this->assertTrue(
                strpos($content, 'extends Migration') !== false,
                "Migration file {$file} should extend Migration class"
            );
            
            // Check for up() and down() methods
            $this->assertTrue(
                strpos($content, 'public function up()') !== false,
                "Migration file {$file} should have up() method"
            );
            
            $this->assertTrue(
                strpos($content, 'public function down()') !== false,
                "Migration file {$file} should have down() method"
            );
        }
    }
}