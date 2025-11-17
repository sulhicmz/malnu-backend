<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hypervel\Foundation\Testing\TestCase;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;

class SchoolManagementControllersTest extends TestCase
{
    /**
     * Test that we can access the students index endpoint.
     */
    public function test_can_access_students_index(): void
    {
        $response = $this->getJson('/api/school-management/students');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data',
                         'links' => [
                             'first',
                             'last',
                             'prev',
                             'next'
                         ],
                         'meta' => [
                             'current_page',
                             'from',
                             'last_page',
                             'links',
                             'path',
                             'per_page',
                             'to',
                             'total'
                         ]
                     ],
                     'message'
                 ]);
    }

    /**
     * Test that we can access the teachers index endpoint.
     */
    public function test_can_access_teachers_index(): void
    {
        $response = $this->getJson('/api/school-management/teachers');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data',
                         'links' => [
                             'first',
                             'last',
                             'prev',
                             'next'
                         ],
                         'meta' => [
                             'current_page',
                             'from',
                             'last_page',
                             'links',
                             'path',
                             'per_page',
                             'to',
                             'total'
                         ]
                     ],
                     'message'
                 ]);
    }

    /**
     * Test that we can access the classes index endpoint.
     */
    public function test_can_access_classes_index(): void
    {
        $response = $this->getJson('/api/school-management/classes');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data',
                         'links' => [
                             'first',
                             'last',
                             'prev',
                             'next'
                         ],
                         'meta' => [
                             'current_page',
                             'from',
                             'last_page',
                             'links',
                             'path',
                             'per_page',
                             'to',
                             'total'
                         ]
                     ],
                     'message'
                 ]);
    }

    /**
     * Test student CRUD operations.
     */
    public function test_student_crud_operations(): void
    {
        // Create a user first
        $user = User::factory()->create();
        
        // Create a class first
        $class = ClassModel::factory()->create();
        
        // Test store student
        $studentData = [
            'user_id' => $user->id,
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'birth_date' => '2000-01-01',
            'birth_place' => 'Jakarta',
            'address' => 'Jl. Test No. 123',
            'parent_id' => null,
            'enrollment_date' => '2023-01-01',
            'status' => 'active'
        ];
        
        $response = $this->postJson('/api/school-management/students', $studentData);
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student created successfully'
                 ]);
        
        $studentId = $response->json('data.id');
        
        // Test show student
        $response = $this->getJson("/api/school-management/students/{$studentId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $studentId,
                         'nisn' => '1234567890'
                     ],
                     'message' => 'Student retrieved successfully'
                 ]);
        
        // Test update student
        $updateData = [
            'status' => 'inactive'
        ];
        
        $response = $this->putJson("/api/school-management/students/{$studentId}", $updateData);
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student updated successfully'
                 ]);
        
        // Test delete student
        $response = $this->deleteJson("/api/school-management/students/{$studentId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student deleted successfully'
                 ]);
    }

    /**
     * Test teacher CRUD operations.
     */
    public function test_teacher_crud_operations(): void
    {
        // Create a user first
        $user = User::factory()->create();
        
        // Test store teacher
        $teacherData = [
            'user_id' => $user->id,
            'nip' => '1234567890',
            'expertise' => 'Mathematics',
            'join_date' => '2023-01-01',
            'status' => 'active'
        ];
        
        $response = $this->postJson('/api/school-management/teachers', $teacherData);
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Teacher created successfully'
                 ]);
        
        $teacherId = $response->json('data.id');
        
        // Test show teacher
        $response = $this->getJson("/api/school-management/teachers/{$teacherId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $teacherId,
                         'nip' => '1234567890'
                     ],
                     'message' => 'Teacher retrieved successfully'
                 ]);
        
        // Test update teacher
        $updateData = [
            'status' => 'inactive'
        ];
        
        $response = $this->putJson("/api/school-management/teachers/{$teacherId}", $updateData);
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Teacher updated successfully'
                 ]);
        
        // Test delete teacher
        $response = $this->deleteJson("/api/school-management/teachers/{$teacherId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Teacher deleted successfully'
                 ]);
    }

    /**
     * Test class CRUD operations.
     */
    public function test_class_crud_operations(): void
    {
        // Create a teacher first
        $user = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '9876543210',
            'expertise' => 'Science',
            'join_date' => '2023-01-01',
            'status' => 'active'
        ]);
        
        // Test store class
        $classData = [
            'name' => 'Class A',
            'level' => '10',
            'homeroom_teacher_id' => $teacher->id,
            'academic_year' => '2023/2024',
            'capacity' => 30
        ];
        
        $response = $this->postJson('/api/school-management/classes', $classData);
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Class created successfully'
                 ]);
        
        $classId = $response->json('data.id');
        
        // Test show class
        $response = $this->getJson("/api/school-management/classes/{$classId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $classId,
                         'name' => 'Class A'
                     ],
                     'message' => 'Class retrieved successfully'
                 ]);
        
        // Test update class
        $updateData = [
            'capacity' => 35
        ];
        
        $response = $this->putJson("/api/school-management/classes/{$classId}", $updateData);
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Class updated successfully'
                 ]);
        
        // Test delete class
        $response = $this->deleteJson("/api/school-management/classes/{$classId}");
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Class deleted successfully'
                 ]);
    }
}