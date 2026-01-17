<?php

namespace Tests\Feature;

use App\Models\SchoolManagement\ClassModel;
use Tests\TestCase;

class ClassControllerTest extends TestCase
{
    public function test_index_returns_classes()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/classes');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_store_creates_class()
    {
        $data = [
            'name' => 'Test Class',
            'level' => '10',
            'academic_year' => '2024-2025',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/classes', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_requires_name()
    {
        $data = [
            'level' => '10',
            'academic_year' => '2024-2025',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/classes', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_level()
    {
        $data = [
            'name' => 'Test Class',
            'academic_year' => '2024-2025',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/classes', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_academic_year()
    {
        $data = [
            'name' => 'Test Class',
            'level' => '10',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/classes', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_show_returns_class()
    {
        $class = ClassModel::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/classes/' . $class->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_modifies_class()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Updated Class',
            'level' => '11',
            'academic_year' => '2024-2025',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->put('/api/school/classes/' . $class->id, $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_destroy_removes_class()
    {
        $class = ClassModel::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->delete('/api/school/classes/' . $class->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_level()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/classes?level=10');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_academic_year()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/classes?academic_year=2024-2025');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_name()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/classes?search=Test');

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getValidToken(): string
    {
        return 'test-token';
    }
}
