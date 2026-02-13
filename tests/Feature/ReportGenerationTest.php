<?php

declare(strict_types=1);

namespace HyperfTest\Feature;

use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\ReportTemplate;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Hyperf\Testing\TestCase;

class ReportGenerationTest extends TestCase
{
    protected User $user;
    protected Student $student;
    protected ClassModel $class;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher@test.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'Guru',
        ]);

        // Create test class
        $this->class = ClassModel::create([
            'name' => 'Class 10A',
            'grade_level' => 'high_school',
        ]);

        // Create test student
        $this->student = Student::create([
            'name' => 'Test Student',
            'nis' => '1234567890',
            'class_id' => $this->class->id,
        ]);

        // Create test subject
        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH101',
        ]);
    }

    public function testGenerateReportCardWithValidData()
    {
        // Create grades for student
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.5,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/report-cards', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Report card generated successfully', $data['message']);
        $this->assertNotNull($data['data']['id']);
        $this->assertEquals($this->student->id, $data['data']['student_id']);
    }

    public function testGenerateReportCardWithInvalidStudent()
    {
        $response = $this->post('/api/reports/report-cards', [
            'student_id' => 'invalid-uuid',
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(404);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertFalse($data['success']);
    }

    public function testGenerateTranscriptWithValidStudent()
    {
        // Create grades across multiple semesters
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.5,
            'semester' => 1,
            'academic_year' => '2023-2024',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 88.0,
            'semester' => 2,
            'academic_year' => '2023-2024',
            'grade_type' => 'exam',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/transcripts', [
            'student_id' => $this->student->id,
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Transcript generated successfully', $data['message']);
        $this->assertNotNull($data['data']['average_grade']);
    }

    public function testGenerateProgressReport()
    {
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.5,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/progress-reports', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Progress report generated successfully', $data['message']);
    }

    public function testBatchGenerateReportCards()
    {
        // Create another student in the same class
        Student::create([
            'name' => 'Another Student',
            'nis' => '1234567891',
            'class_id' => $this->class->id,
        ]);

        $response = $this->post('/api/reports/batch-report-cards', [
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(200);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('successful', $data['data']);
        $this->assertArrayHasKey('failed', $data['data']);
    }

    public function testGetStudentReports()
    {
        // Create a report first
        Report::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'average_grade' => 85.5,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get('/api/reports/student/' . $this->student->id, [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(200);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['data']);
    }

    public function testPublishReport()
    {
        $report = Report::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'average_grade' => 85.5,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/' . $report->id . '/publish', [], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(200);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['is_published']);
    }

    public function testAddSignatureToReport()
    {
        $report = Report::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'average_grade' => 85.5,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/' . $report->id . '/signatures', [
            'signer_name' => 'Principal Name',
            'signer_title' => 'Principal',
            'notes' => 'Official signature',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Principal Name', $data['data']['signer_name']);
        $this->assertEquals('Principal', $data['data']['signer_title']);
    }

    public function testCreateReportTemplate()
    {
        $response = $this->post('/api/report-templates', [
            'name' => 'Default Report Card Template',
            'type' => 'report_card',
            'grade_level' => 'high_school',
            'header_template' => '<div class="header"><h1>School Report Card</h1></div>',
            'content_template' => '<div class="content">{{grades_table}}</div>',
            'footer_template' => '<div class="footer">{{generation_date}}</div>',
            'css_styles' => 'body { font-family: Arial; }',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Default Report Card Template', $data['data']['name']);
        $this->assertEquals('report_card', $data['data']['type']);
    }

    public function testGetReportTemplates()
    {
        ReportTemplate::create([
            'name' => 'Template 1',
            'type' => 'report_card',
            'header_template' => '<header></header>',
            'content_template' => '<content></content>',
            'footer_template' => '<footer></footer>',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get('/api/report-templates', [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(200);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertGreaterThan(0, count($data['data']));
    }

    public function testClassRankCalculation()
    {
        // Create multiple students with grades
        $student2 = Student::create([
            'name' => 'Student Two',
            'nis' => '1234567892',
            'class_id' => $this->class->id,
        ]);

        // Student 1 has higher grades
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 90.0,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        // Student 2 has lower grades
        Grade::create([
            'student_id' => $student2->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 80.0,
            'semester' => 1,
            'academic_year' => '2024-2025',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/report-cards', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'semester' => 1,
            'academic_year' => '2024-2025',
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals(1, $data['data']['rank_in_class']);
    }

    public function testCumulativeGpaCalculation()
    {
        // Create grades across multiple years
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.0,
            'semester' => 1,
            'academic_year' => '2022-2023',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 90.0,
            'semester' => 1,
            'academic_year' => '2023-2024',
            'grade_type' => 'exam',
            'created_by' => $this->user->id,
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 88.0,
            'semester' => 2,
            'academic_year' => '2023-2024',
            'grade_type' => 'assignment',
            'created_by' => $this->user->id,
        ]);

        $response = $this->post('/api/reports/transcripts', [
            'student_id' => $this->student->id,
        ], [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(201);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);
        // GPA should be (85 + 90 + 88) / 3 = 87.67
        $this->assertEquals(87.67, $data['data']['average_grade']);
    }

    public function testReportTemplateFiltering()
    {
        ReportTemplate::create([
            'name' => 'Elementary Template',
            'type' => 'report_card',
            'grade_level' => 'elementary',
            'header_template' => '<header></header>',
            'content_template' => '<content></content>',
            'footer_template' => '<footer></footer>',
            'created_by' => $this->user->id,
        ]);

        ReportTemplate::create([
            'name' => 'High School Template',
            'type' => 'report_card',
            'grade_level' => 'high_school',
            'header_template' => '<header></header>',
            'content_template' => '<content></content>',
            'footer_template' => '<footer></footer>',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get('/api/report-templates?type=report_card&grade_level=high_school', [
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
        ]);

        $response->assertStatus(200);
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($data['success']);

        foreach ($data['data'] as $template) {
            $this->assertEquals('report_card', $template['type']);
            if ($template['grade_level'] !== null) {
                $this->assertEquals('high_school', $template['grade_level']);
            }
        }
    }

    protected function getAuthToken(): string
    {
        $response = $this->post('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['data']['token'] ?? '';
    }
}
