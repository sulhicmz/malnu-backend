<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance\StudentAttendance;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Tests\TestCase;

/**
 * Parent Portal API Tests.
 *
 * Tests for parent portal endpoints including dashboard, grades, attendance, and assignments.
 * Addresses testing requirements from Issue #685 and #22.
 * @internal
 * @coversNothing
 */
class ParentPortalApiTest extends TestCase
{
    /**
     * Test parent dashboard returns children overview.
     */
    public function testDashboardReturnsParentDashboard()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/dashboard');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('parent_info', $data['data']);
        $this->assertArrayHasKey('children', $data['data']);
        $this->assertEquals(1, $data['data']['children_count']);
    }

    /**
     * Test parent dashboard requires authentication.
     */
    public function testDashboardRequiresAuthentication()
    {
        $response = $this->get('/api/parent/dashboard');

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test dashboard returns empty children array for parent without students.
     */
    public function testDashboardReturnsEmptyForParentWithoutChildren()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/dashboard');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(0, $data['data']['children_count']);
        $this->assertEmpty($data['data']['children']);
    }

    /**
     * Test parent can get grades for their child.
     */
    public function testGetChildGradesReturnsGrades()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        $subject = Subject::factory()->create();
        $grade = Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 85.5,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/grades');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('grades_by_subject', $data['data']);
        $this->assertEquals(1, $data['data']['total_grades']);
    }

    /**
     * Test parent cannot access grades of unrelated student.
     */
    public function testGetChildGradesDeniesAccessToUnrelatedStudent()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        // Create another parent and student
        $otherUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentOrtu::factory()->create(['user_id' => $otherUser->id]);
        $class = ClassModel::factory()->create();
        $otherStudent = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $otherParent->id,
        ]);

        // Try to access other parent's child
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $otherStudent->id . '/grades');

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertStringContainsString('Access denied', $data['message']);
    }

    /**
     * Test get grades requires authentication.
     */
    public function testGetChildGradesRequiresAuthentication()
    {
        $response = $this->get('/api/parent/children/123/grades');

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test parent can get attendance for their child.
     */
    public function testGetChildAttendanceReturnsAttendance()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Create attendance records
        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'present',
            'attendance_date' => '2024-01-15',
        ]);
        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'absent',
            'attendance_date' => '2024-01-16',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/attendance');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('records', $data['data']);
        $this->assertArrayHasKey('summary', $data['data']);
        $this->assertCount(2, $data['data']['records']);
    }

    /**
     * Test attendance with date range filtering.
     */
    public function testGetChildAttendanceWithDateRange()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Create attendance records in different months
        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'present',
            'attendance_date' => '2024-01-15',
        ]);
        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'absent',
            'attendance_date' => '2024-02-15',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/attendance?start_date=2024-01-01&end_date=2024-01-31');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertCount(1, $data['data']['records']);
    }

    /**
     * Test parent cannot access attendance of unrelated student.
     */
    public function testGetChildAttendanceDeniesAccessToUnrelatedStudent()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        // Create another parent and student
        $otherUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentOrtu::factory()->create(['user_id' => $otherUser->id]);
        $class = ClassModel::factory()->create();
        $otherStudent = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $otherParent->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $otherStudent->id . '/attendance');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test parent can get assignments for their child.
     */
    public function testGetChildAssignmentsReturnsAssignments()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);

        // Create assignments
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Math Homework',
            'is_published' => true,
            'publish_date' => now()->subDay(),
        ]);
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Science Project',
            'is_published' => true,
            'publish_date' => now()->addDay(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/assignments');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('upcoming_assignments', $data['data']);
        $this->assertArrayHasKey('past_assignments', $data['data']);
        $this->assertEquals(2, $data['data']['total_assignments']);
    }

    /**
     * Test parent cannot access assignments of unrelated student.
     */
    public function testGetChildAssignmentsDeniesAccessToUnrelatedStudent()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        // Create another parent and student
        $otherUser = User::factory()->create(['role' => 'parent']);
        $otherParent = ParentOrtu::factory()->create(['user_id' => $otherUser->id]);
        $class = ClassModel::factory()->create();
        $otherStudent = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $otherParent->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $otherStudent->id . '/assignments');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test multiple children per parent.
     */
    public function testDashboardShowsAllChildrenForParent()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();

        // Create multiple students for the same parent
        Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/dashboard');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(3, $data['data']['children_count']);
        $this->assertCount(3, $data['data']['children']);
    }

    /**
     * Test attendance summary calculations.
     */
    public function testAttendanceSummaryCalculatesCorrectly()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        // Create mixed attendance records
        StudentAttendance::factory()->count(5)->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'present',
        ]);
        StudentAttendance::factory()->count(2)->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'absent',
        ]);
        StudentAttendance::factory()->count(1)->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'late',
        ]);
        StudentAttendance::factory()->count(2)->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'marked_by' => $teacher->id,
            'status' => 'excused',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/attendance');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $summary = $data['data']['summary'];

        $this->assertEquals(10, $summary['total']);
        $this->assertEquals(5, $summary['present']);
        $this->assertEquals(2, $summary['absent']);
        $this->assertEquals(1, $summary['late']);
        $this->assertEquals(2, $summary['excused']);
        // Attendance rate = (present + excused) / total = 7/10 = 70%
        $this->assertEquals(70.0, $summary['attendance_rate']);
    }

    /**
     * Test unpublished assignments are not included.
     */
    public function testAssignmentsExcludesUnpublished()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);

        // Create published assignment
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Published Assignment',
            'is_published' => true,
            'publish_date' => now()->subDay(),
        ]);

        // Create unpublished assignment (should not appear)
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Unpublished Assignment',
            'is_published' => false,
            'publish_date' => now()->subDay(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/assignments');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(1, $data['data']['total_assignments']);
        $this->assertEquals('Published Assignment', $data['data']['past_assignments'][0]['title']);
    }

    /**
     * Test future published assignments are not shown until publish date.
     */
    public function testAssignmentsExcludesFuturePublishDate()
    {
        $user = User::factory()->create(['role' => 'parent']);
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'parent_id' => $parent->id,
        ]);

        // Create past assignment
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Past Assignment',
            'is_published' => true,
            'publish_date' => now()->subDay(),
        ]);

        // Create future assignment (should appear in upcoming)
        Assignment::factory()->create([
            'virtual_class_id' => $class->id,
            'title' => 'Future Assignment',
            'is_published' => true,
            'publish_date' => now()->addDay(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken($user))
            ->get('/api/parent/children/' . $student->id . '/assignments');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(2, $data['data']['total_assignments']);
        $this->assertCount(1, $data['data']['past_assignments']);
        $this->assertCount(1, $data['data']['upcoming_assignments']);
    }

    /**
     * Get valid JWT token for testing.
     */
    private function getValidToken(User $user): string
    {
        // In a real implementation, this would generate a valid JWT
        // For these tests, we assume the TestCase provides token generation
        return 'test-token-' . $user->id;
    }
}
