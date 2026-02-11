<?php

namespace Tests\Feature;

use App\Models\SchoolManagement\Subject;
use Tests\TestCase;

class SubjectControllerTest extends TestCase
{
    public function test_index_returns_subjects()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/subjects');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_store_creates_subject()
    {
        $data = [
            'code' => 'MATH101',
            'name' => 'Mathematics',
            'description' => 'Mathematics course',
            'credit_hours' => 4,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/subjects', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_requires_code()
    {
        $data = [
            'name' => 'Mathematics',
            'description' => 'Mathematics course',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/subjects', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_name()
    {
        $data = [
            'code' => 'MATH101',
            'description' => 'Mathematics course',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/subjects', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_show_returns_subject()
    {
        $subject = Subject::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/subjects/' . $subject->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_modifies_subject()
    {
        $subject = Subject::factory()->create();

        $data = [
            'code' => 'MATH102',
            'name' => 'Advanced Mathematics',
            'description' => 'Advanced math course',
            'credit_hours' => 4,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->put('/api/school/subjects/' . $subject->id, $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_destroy_removes_subject()
    {
        $subject = Subject::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->delete('/api/school/subjects/' . $subject->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_credit_hours()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/subjects?credit_hours=4');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_name()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/subjects?search=Math');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_code()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/subjects?search=MATH101');

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getValidToken(): string
    {
        return 'test-token';
    }
}
