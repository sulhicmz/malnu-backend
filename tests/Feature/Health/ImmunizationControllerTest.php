<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use Tests\TestCase;

class ImmunizationControllerTest extends TestCase
{
    public function test_can_get_immunizations_by_student()
    {
        $studentId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->getJson('/api/health/immunizations/student/' . $studentId);

        $this->assertIsArray($response);
    }

    public function test_can_get_overdue_immunizations()
    {
        $response = $this->getJson('/api/health/immunizations/overdue');

        $this->assertIsArray($response);
    }

    public function test_can_get_compliance_report()
    {
        $studentId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->getJson('/api/health/immunizations/student/' . $studentId . '/compliance');

        $this->assertArrayHasKey('compliance_rate', $response);
        $this->assertArrayHasKey('total_immunizations', $response);
    }
}