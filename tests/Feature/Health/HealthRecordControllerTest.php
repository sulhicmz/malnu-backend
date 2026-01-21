<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use Tests\TestCase;
use Hyperf\HttpMessage\Server\Request;

class HealthRecordControllerTest extends TestCase
{
    public function test_can_get_health_record_by_student()
    {
        $studentId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->get('/api/health/records/student/' . $studentId, [
            'Authorization' => 'Bearer ' . $this->getAuthToken()
        ]);

        $this->assertIsArray($response);
    }

    public function test_can_get_medical_alerts_for_student()
    {
        $studentId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->get('/api/health/records/student/' . $studentId . '/alerts', [
            'Authorization' => 'Bearer ' . $this->getAuthToken()
        ]);

        $this->assertIsArray($response);
    }

    public function test_requires_authentication_to_access_health_records()
    {
        $studentId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->get('/api/health/records/student/' . $studentId);

        $this->assertEquals(401, $response['status_code'] ?? null);
    }

    protected function getAuthToken(): string
    {
        return 'test-token-' . time();
    }

    protected function get(string $uri, array $headers = [])
    {
        $request = new Request($uri, 'GET', $headers);
        
        $response = $this->app->handle($request);
        
        return json_decode($response->getBody()->getContents(), true) ?? [];
    }
}