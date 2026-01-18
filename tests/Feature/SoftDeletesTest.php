<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SoftDeletesTest extends TestCase
{
    public function testModelHasSoftDeletesTrait()
    {
        $this->assertTrue(method_exists(Model::class, 'bootSoftDeletes'), 'Model class should have bootSoftDeletes method');
    }

    public function testSoftDeleteSetsDeletedAtTimestamp()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $this->assertNull($student->deleted_at, 'deleted_at should be null for new record');

        $student->delete();

        $this->assertNotNull($student->deleted_at, 'deleted_at should be set after soft delete');
        $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => $student->deleted_at]);
    }

    public function testSoftDeletedRecordExcludedFromNormalQueries()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567891',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $studentId = $student->id;
        $student->delete();

        $allStudents = Student::all();
        $this->assertCount(0, $allStudents, 'Soft-deleted records should not appear in normal queries');
    }

    public function testWithTrashedIncludesSoftDeletedRecords()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567892',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $studentId = $student->id;
        $student->delete();

        $allStudentsWithTrashed = Student::withTrashed()->get();
        $this->assertCount(1, $allStudentsWithTrashed, 'withTrashed() should include soft-deleted records');
        $this->assertEquals($studentId, $allStudentsWithTrashed->first()->id);
    }

    public function testOnlyTrashedReturnsOnlySoftDeletedRecords()
    {
        $activeStudent = Student::create([
            'name' => 'Active Student',
            'nisn' => '1234567893',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $deletedStudent = Student::create([
            'name' => 'Deleted Student',
            'nisn' => '1234567894',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $deletedStudent->delete();

        $onlyTrashed = Student::onlyTrashed()->get();
        $this->assertCount(1, $onlyTrashed, 'onlyTrashed() should return only soft-deleted records');
        $this->assertEquals($deletedStudent->id, $onlyTrashed->first()->id);
    }

    public function testRestoreBringsBackSoftDeletedRecord()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567895',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $student->delete();
        $this->assertNotNull($student->deleted_at);

        $result = $student->restore();
        $this->assertTrue($result, 'restore() should return true on success');

        $student->refresh();
        $this->assertNull($student->deleted_at, 'deleted_at should be null after restore');

        $restoredStudent = Student::find($student->id);
        $this->assertNotNull($restoredStudent, 'Restored student should be findable');
        $this->assertEquals($student->name, $restoredStudent->name);
    }

    public function testForceDeletePermanentlyRemovesRecord()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567896',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $student->delete();
        $this->assertNotNull($student->deleted_at);

        $result = $student->forceDelete();
        $this->assertTrue($result, 'forceDelete() should return true on success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function testMultipleSoftDeletesAndRestores()
    {
        $students = [];
        for ($i = 0; $i < 3; ++$i) {
            $student = Student::create([
                'name' => "Test Student {$i}",
                'nisn' => "123456789{$i}",
                'class_id' => 1,
                'enrollment_year' => 2024,
                'status' => 'active',
            ]);
            $students[] = $student;
        }

        foreach ($students as $student) {
            $student->delete();
        }

        $allStudents = Student::all();
        $this->assertCount(0, $allStudents, 'All students should be soft-deleted');

        foreach ($students as $student) {
            $student->restore();
        }

        $restoredStudents = Student::all();
        $this->assertCount(3, $restoredStudents, 'All students should be restored');
    }

    public function testSoftDeleteOnMultipleModelTypes()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567897',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'nip' => '1987654321',
            'subject_id' => 1,
            'join_date' => '2024-01-01',
        ]);

        $student->delete();
        $teacher->delete();

        $this->assertNotNull($student->deleted_at, 'Student should have deleted_at set');
        $this->assertNotNull($teacher->deleted_at, 'Teacher should have deleted_at set');

        $student->restore();
        $teacher->restore();

        $this->assertNull($student->fresh()->deleted_at, 'Student should be restored');
        $this->assertNull($teacher->fresh()->deleted_at, 'Teacher should be restored');
    }
}
