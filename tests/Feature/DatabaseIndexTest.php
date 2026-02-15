<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Hypervel\Support\Facades\DB;
use Hypervel\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
class DatabaseIndexTest extends TestCase
{
    private array $expectedIndexes = [
        'students' => [
            'single' => ['class_id', 'status'],
            'composite' => [['class_id', 'status']],
        ],
        'teachers' => [
            'single' => ['status'],
        ],
        'staff' => [
            'single' => ['status'],
        ],
        'staff_attendances' => [
            'single' => ['status'],
        ],
        'ppdb_registrations' => [
            'single' => ['status', 'registration_date'],
            'composite' => [['status', 'registration_date']],
        ],
        'learning_materials' => [
            'single' => ['virtual_class_id', 'material_type'],
        ],
        'assignments' => [
            'single' => ['virtual_class_id', 'due_date'],
        ],
        'quizzes' => [
            'single' => ['virtual_class_id'],
        ],
        'exam_results' => [
            'single' => ['exam_id', 'student_id'],
        ],
    ];

    public function testAllExpectedIndexesExist(): void
    {
        foreach ($this->expectedIndexes as $table => $indexes) {
            $this->assertArrayHasKey('single', $indexes, "Table {$table} should have single-column indexes defined");

            foreach ($indexes['single'] as $column) {
                $this->assertIndexExists($table, $column);
            }

            if (isset($indexes['composite'])) {
                foreach ($indexes['composite'] as $columns) {
                    $this->assertCompositeIndexExists($table, $columns);
                }
            }
        }
    }

    public function testStudentsTableIndexes(): void
    {
        $this->assertIndexExists('students', 'class_id');
        $this->assertIndexExists('students', 'status');
        $this->assertCompositeIndexExists('students', ['class_id', 'status']);
    }

    public function testTeachersTableIndexes(): void
    {
        $this->assertIndexExists('teachers', 'status');
    }

    public function testStaffTableIndexes(): void
    {
        $this->assertIndexExists('staff', 'status');
    }

    public function testStaffAttendancesTableIndexes(): void
    {
        $this->assertIndexExists('staff_attendances', 'status');
    }

    public function testPpdbRegistrationsTableIndexes(): void
    {
        $this->assertIndexExists('ppdb_registrations', 'status');
        $this->assertIndexExists('ppdb_registrations', 'registration_date');
        $this->assertCompositeIndexExists('ppdb_registrations', ['status', 'registration_date']);
    }

    public function testLearningMaterialsTableIndexes(): void
    {
        $this->assertIndexExists('learning_materials', 'virtual_class_id');
        $this->assertIndexExists('learning_materials', 'material_type');
    }

    public function testAssignmentsTableIndexes(): void
    {
        $this->assertIndexExists('assignments', 'virtual_class_id');
        $this->assertIndexExists('assignments', 'due_date');
    }

    public function testQuizzesTableIndexes(): void
    {
        $this->assertIndexExists('quizzes', 'virtual_class_id');
    }

    public function testExamResultsTableIndexes(): void
    {
        $this->assertIndexExists('exam_results', 'exam_id');
        $this->assertIndexExists('exam_results', 'student_id');
    }

    private function assertIndexExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        $exists = $this->indexExists($table, $indexName);

        $this->assertTrue($exists, "Index '{$indexName}' on column '{$column}' in table '{$table}' should exist");
    }

    private function assertCompositeIndexExists(string $table, array $columns): void
    {
        $columnsStr = implode('_', $columns);
        $indexName = "{$table}_{$columnsStr}_index";
        $exists = $this->indexExists($table, $indexName);

        $columnsStr = implode(', ', $columns);
        $this->assertTrue($exists, "Composite index '{$indexName}' on columns ({$columnsStr}) in table '{$table}' should exist");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = Db::select(
                "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                [$indexName]
            );

            return count($indexes) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
