<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use App\Models\SchoolManagement\ClassModel;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\Quiz;
use App\Models\Grading\Grade;
use App\Models\OnlineExam\Exam;
use App\Models\DigitalLibrary\BookLoan;
use Tests\TestCase;
use Hypervel\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class UserRelationshipsTest extends TestCase
{
    /**
     * Test user parent relationship with actual data.
     */
    public function testUserParentRelationship(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $parent = ParentOrtu::create([
            'user_id' => $user->id,
            'full_name' => 'John Parent',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($parent->id, $user->parent->id);
    }

    /**
     * Test user teacher relationship with actual data.
     */
    public function testUserTeacherRelationship(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'full_name' => 'Mr. Smith',
            'nip' => '98765',
            'subject_id' => null,
        ]);

        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    /**
     * Test user student relationship with actual data.
     */
    public function testUserStudentRelationship(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'parent_id' => null,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => null,
        ]);

        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    /**
     * Test user staff relationship with actual data.
     */
    public function testUserStaffRelationship(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'full_name' => 'John Staff',
            'nip' => '54321',
            'position' => 'Administrator',
        ]);

        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($staff->id, $user->staff->id);
    }

    /**
     * Test user has many assignments relationship.
     */
    public function testUserHasManyAssignments(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $assignment = Assignment::create([
            'title' => 'Test Assignment',
            'description' => 'Test Description',
            'created_by' => $user->id,
            'due_date' => now()->addDays(7),
        ]);

        $this->assertTrue($user->assignmentsCreated->contains($assignment));
        $this->assertEquals($user->id, $assignment->created_by);
    }

    /**
     * Test user has many quizzes relationship.
     */
    public function testUserHasManyQuizzes(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'description' => 'Test Description',
            'created_by' => $user->id,
        ]);

        $this->assertTrue($user->quizzesCreated->contains($quiz));
        $this->assertEquals($user->id, $quiz->created_by);
    }

    /**
     * Test user has many grades relationship.
     */
    public function testUserHasManyGrades(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $grade = Grade::create([
            'student_id' => null,
            'subject_id' => null,
            'grade_value' => 'A',
            'created_by' => $user->id,
        ]);

        $this->assertTrue($user->gradesCreated->contains($grade));
        $this->assertEquals($user->id, $grade->created_by);
    }

    /**
     * Test user has many exams relationship.
     */
    public function testUserHasManyExams(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $exam = Exam::create([
            'title' => 'Test Exam',
            'description' => 'Test Description',
            'created_by' => $user->id,
        ]);

        $this->assertTrue($user->examsCreated->contains($exam));
        $this->assertEquals($user->id, $exam->created_by);
    }

    /**
     * Test user has many book loans relationship.
     */
    public function testUserHasManyBookLoans(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $bookLoan = BookLoan::create([
            'book_id' => null,
            'borrower_id' => $user->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $this->assertTrue($user->bookLoans->contains($bookLoan));
        $this->assertEquals($user->id, $bookLoan->borrower_id);
    }

    /**
     * Test ParentOrtu model exists and has correct relationships.
     */
    public function testParentOrtuModelExists(): void
    {
        $parent = new ParentOrtu();
        
        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
        
        // Test user relationship
        $userRelation = $parent->user();
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
        
        // Test students relationship
        $studentsRelation = $parent->students();
        $this->assertEquals('parent_id', $studentsRelation->getForeignKeyName());
    }

    /**
     * Test user model relationships return correct foreign key names.
     */
    public function testUserModelRelationshipForeignKeys(): void
    {
        $user = new User();
        
        // Test parent relationship
        $parentRelation = $user->parent();
        $this->assertEquals('user_id', $parentRelation->getForeignKeyName());
        
        // Test teacher relationship
        $teacherRelation = $user->teacher();
        $this->assertEquals('user_id', $teacherRelation->getForeignKeyName());
        
        // Test student relationship
        $studentRelation = $user->student();
        $this->assertEquals('user_id', $studentRelation->getForeignKeyName());
        
        // Test staff relationship
        $staffRelation = $user->staff();
        $this->assertEquals('user_id', $staffRelation->getForeignKeyName());
    }
}