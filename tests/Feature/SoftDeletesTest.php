<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\Grading\Grade;
use App\Models\DigitalLibrary\Book;
use Tests\TestCase;

/**
 * Soft Deletes Feature Test Suite
 * 
 * Tests soft delete functionality for critical models.
 * Soft deletes prevent permanent data loss by marking records as deleted
 * instead of removing them from the database.
 */
class SoftDeletesTest extends TestCase
{
    /**
     * Test User model soft delete.
     */
    public function testUserSoftDelete(): void
    {
        $user = User::factory()->create(['email' => 'softdelete@test.com']);
        $userId = $user->id;

        // Soft delete the user
        $user->delete();

        // User should not appear in normal queries
        $this->assertNull(User::find($userId));

        // User should still exist with trashed() scope
        $this->assertNotNull(User::withTrashed()->find($userId));
        
        // User should have deleted_at timestamp
        $trashedUser = User::withTrashed()->find($userId);
        $this->assertNotNull($trashedUser->deleted_at);
    }

    /**
     * Test User model restore.
     */
    public function testUserRestore(): void
    {
        $user = User::factory()->create(['email' => 'restore@test.com']);
        $userId = $user->id;

        // Soft delete the user
        $user->delete();

        // Restore the user
        $user->restore();

        // User should now appear in normal queries
        $this->assertNotNull(User::find($userId));
        
        // deleted_at should be null
        $this->assertNull(User::find($userId)->deleted_at);
    }

    /**
     * Test User model force delete (permanent).
     */
    public function testUserForceDelete(): void
    {
        $user = User::factory()->create(['email' => 'forcedelete@test.com']);
        $userId = $user->id;

        // Force delete the user
        $user->forceDelete();

        // User should not exist even withTrashed()
        $this->assertNull(User::withTrashed()->find($userId));
    }

    /**
     * Test Student model soft delete.
     */
    public function testStudentSoftDelete(): void
    {
        $student = Student::factory()->create();
        $studentId = $student->id;

        // Soft delete the student
        $student->delete();

        // Student should not appear in normal queries
        $this->assertNull(Student::find($studentId));

        // Student should still exist with trashed() scope
        $this->assertNotNull(Student::withTrashed()->find($studentId));
        
        // Student should have deleted_at timestamp
        $trashedStudent = Student::withTrashed()->find($studentId);
        $this->assertNotNull($trashedStudent->deleted_at);
    }

    /**
     * Test Teacher model soft delete.
     */
    public function testTeacherSoftDelete(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherId = $teacher->id;

        // Soft delete the teacher
        $teacher->delete();

        // Teacher should not appear in normal queries
        $this->assertNull(Teacher::find($teacherId));

        // Teacher should still exist with trashed() scope
        $this->assertNotNull(Teacher::withTrashed()->find($teacherId));
        
        // Teacher should have deleted_at timestamp
        $trashedTeacher = Teacher::withTrashed()->find($teacherId);
        $this->assertNotNull($trashedTeacher->deleted_at);
    }

    /**
     * Test Grade model soft delete.
     */
    public function testGradeSoftDelete(): void
    {
        $grade = Grade::factory()->create();
        $gradeId = $grade->id;

        // Soft delete the grade
        $grade->delete();

        // Grade should not appear in normal queries
        $this->assertNull(Grade::find($gradeId));

        // Grade should still exist with trashed() scope
        $this->assertNotNull(Grade::withTrashed()->find($gradeId));
        
        // Grade should have deleted_at timestamp
        $trashedGrade = Grade::withTrashed()->find($gradeId);
        $this->assertNotNull($trashedGrade->deleted_at);
    }

    /**
     * Test Book model soft delete.
     */
    public function testBookSoftDelete(): void
    {
        $book = Book::factory()->create();
        $bookId = $book->id;

        // Soft delete the book
        $book->delete();

        // Book should not appear in normal queries
        $this->assertNull(Book::find($bookId));

        // Book should still exist with trashed() scope
        $this->assertNotNull(Book::withTrashed()->find($bookId));
        
        // Book should have deleted_at timestamp
        $trashedBook = Book::withTrashed()->find($bookId);
        $this->assertNotNull($trashedBook->deleted_at);
    }

    /**
     * Test onlyTrashed() scope.
     */
    public function testOnlyTrashedScope(): void
    {
        $user1 = User::factory()->create(['email' => 'active1@test.com']);
        $user2 = User::factory()->create(['email' => 'softdeleted@test.com']);
        $user3 = User::factory()->create(['email' => 'active2@test.com']);

        // Soft delete user2
        $user2->delete();

        // onlyTrashed should return only soft-deleted records
        $trashedUsers = User::onlyTrashed()->get();
        $this->assertCount(1, $trashedUsers);
        $this->assertEquals($user2->id, $trashedUsers->first()->id);
    }

    /**
     * Test withTrashed() scope includes both.
     */
    public function testWithTrashedScope(): void
    {
        $user1 = User::factory()->create(['email' => 'active1@test.com']);
        $user2 = User::factory()->create(['email' => 'softdeleted@test.com']);
        $user3 = User::factory()->create(['email' => 'active2@test.com']);

        // Soft delete user2
        $user2->delete();

        // withTrashed should return all records (active and soft-deleted)
        $allUsers = User::withTrashed()->get();
        $this->assertCount(3, $allUsers);
    }

    /**
     * Test multiple soft deletes and restores.
     */
    public function testMultipleSoftDeletesAndRestores(): void
    {
        $users = User::factory()->count(3)->create();

        // Soft delete all users
        foreach ($users as $user) {
            $user->delete();
        }

        // No users should be found without withTrashed()
        $this->assertCount(0, User::all());

        // All users should be found with withTrashed()
        $this->assertCount(3, User::withTrashed()->get());

        // Restore all users
        foreach ($users as $user) {
            $user->restore();
        }

        // All users should be found again
        $this->assertCount(3, User::all());
    }

    /**
     * Test that forceDelete permanently removes record.
     */
    public function testForceDeletePermanentlyRemovesRecord(): void
    {
        $user = User::factory()->create(['email' => 'permanentdelete@test.com']);
        $userId = $user->id;

        // Force delete the user (permanent)
        $user->forceDelete();

        // User should not exist even with withTrashed()
        $this->assertNull(User::withTrashed()->find($userId));
        
        // User should not exist in onlyTrashed() either
        $this->assertCount(0, User::onlyTrashed()->where('id', $userId)->get());
    }
}
