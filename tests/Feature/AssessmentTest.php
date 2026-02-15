<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\Submission;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hypervel\Support\Facades\DB;
use Hypervel\Testing\TestClient;

class AssessmentTest extends AbstractTestCase
{
    protected $client;
    protected $adminUser;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);

        Db::table('users')->insert([
            'id' => 'admin-001',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adminUser = User::find('admin-001');

        Db::table('users')->insert([
            'id' => 'student-001',
            'name' => 'Student User',
            'email' => 'student@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->studentUser = User::find('student-001');
    }

    public function testCreateAssessment()
    {
        $response = $this->client->post('/assessments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
            ],
            'json' => [
                'title' => 'Math Quiz 1',
                'assessment_type' => 'quiz',
                'subject_id' => 'subject-001',
                'class_id' => 'class-001',
                'total_points' => 100,
                'passing_grade' => 60,
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    public function testGetAssessments()
    {
        Db::table('assessments')->insert([
            'id' => 'assessment-001',
            'title' => 'Science Test',
            'assessment_type' => 'test',
            'subject_id' => 'subject-001',
            'class_id' => 'class-001',
            'total_points' => 100,
            'passing_grade' => 60,
            'is_published' => true,
            'created_by' => $this->adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->client->get('/assessments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken(),
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    public function testStartAssessment()
    {
        Db::table('assessments')->insert([
            'id' => 'assessment-002',
            'title' => 'History Quiz',
            'assessment_type' => 'quiz',
            'subject_id' => 'subject-001',
            'class_id' => 'class-001',
            'total_points' => 50,
            'passing_grade' => 50,
            'is_published' => true,
            'created_by' => $this->adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->client->post('/assessments/assessment-002/start', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getStudentToken(),
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
    }

    protected function getValidToken(): string
    {
        return 'valid_admin_token';
    }

    protected function getStudentToken(): string
    {
        return 'valid_student_token';
    }
}
