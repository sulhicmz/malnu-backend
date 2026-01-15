<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

/**
 * ComplianceSystemTest.
 *
 * Feature tests for compliance and regulatory reporting system.
 * @internal
 * @coversNothing
 */
class ComplianceSystemTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function testCreateCompliancePolicy()
    {
        $response = $this->client->post('/api/compliance/policies', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'title' => 'FERPA Compliance Policy',
                'description' => 'Policy for student data privacy',
                'content' => 'Full policy content here...',
                'category' => 'FERPA',
                'effective_date' => '2026-01-01',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', json_decode($response->getBody(), true));
    }

    public function testCreatePolicyWithInvalidCategory()
    {
        $response = $this->client->post('/api/compliance/policies', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'title' => 'Test Policy',
                'content' => 'Content here...',
                'category' => 'INVALID',
                'effective_date' => '2026-01-01',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testGetPolicies()
    {
        $response = $this->client->get('/api/compliance/policies', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetPoliciesByCategory()
    {
        $response = $this->client->get('/api/compliance/policies?category=FERPA', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAcknowledgePolicy()
    {
        $policyId = 'test-policy-id';
        $response = $this->client->post("/api/compliance/policies/{$policyId}/acknowledge", [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateComplianceTraining()
    {
        $response = $this->client->post('/api/compliance/training', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'title' => 'GDPR Compliance Training',
                'description' => 'Annual GDPR compliance training',
                'content' => 'Training content here...',
                'training_type' => 'GDPR',
                'duration_minutes' => 45,
                'category' => 'Privacy',
                'required_for_all' => true,
                'valid_from' => '2026-01-01',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateTrainingWithInvalidType()
    {
        $response = $this->client->post('/api/compliance/training', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'title' => 'Test Training',
                'content' => 'Content here...',
                'training_type' => 'INVALID',
                'duration_minutes' => 45,
                'category' => 'General',
                'valid_from' => '2026-01-01',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCompleteTraining()
    {
        $trainingId = 'test-training-id';
        $response = $this->client->post("/api/compliance/training/{$trainingId}/complete", [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'score' => 95,
                'passed' => true,
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetTraining()
    {
        $response = $this->client->get('/api/compliance/training', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateRiskAssessment()
    {
        $response = $this->client->post('/api/compliance/risks', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'risk_title' => 'Weak Password Policies',
                'description' => 'Risk of unauthorized access due to weak password policies',
                'risk_category' => 'access_control',
                'likelihood' => 'possible',
                'impact' => 'major',
                'affected_systems' => ['user_management', 'student_records'],
                'applicable_regulations' => ['FERPA', 'GDPR'],
                'mitigation_plan' => 'Implement MFA and password complexity requirements',
                'mitigation_priority' => 'high',
                'target_mitigation_date' => '2026-02-01',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', json_decode($response->getBody(), true));
    }

    public function testCreateRiskWithInvalidLikelihood()
    {
        $response = $this->client->post('/api/compliance/risks', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'risk_title' => 'Test Risk',
                'description' => 'Test description',
                'risk_category' => 'testing',
                'likelihood' => 'invalid',
                'impact' => 'minor',
                'mitigation_priority' => 'medium',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testGetRisks()
    {
        $response = $this->client->get('/api/compliance/risks', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetHighPriorityRisks()
    {
        $response = $this->client->get('/api/compliance/risks?mitigation_priority=high', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateRisk()
    {
        $riskId = 'test-risk-id';
        $response = $this->client->put("/api/compliance/risks/{$riskId}", [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'mitigation_status' => 'in_progress',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateIncident()
    {
        $response = $this->client->post('/api/compliance/incidents', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'incident_type' => 'unauthorized_access',
                'severity' => 'high',
                'title' => 'Unauthorized database access',
                'description' => 'Unknown user accessed student database on 2026-01-15',
                'affected_records' => 150,
                'data_types_affected' => ['student_records', 'personal_info', 'grades'],
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateIncidentWithInvalidSeverity()
    {
        $response = $this->client->post('/api/compliance/incidents', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'incident_type' => 'test',
                'severity' => 'invalid',
                'title' => 'Test incident',
                'description' => 'Test description',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testGetIncidents()
    {
        $response = $this->client->get('/api/compliance/incidents', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetCriticalIncidents()
    {
        $response = $this->client->get('/api/compliance/incidents?severity=critical', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateIncident()
    {
        $incidentId = 'test-incident-id';
        $response = $this->client->put("/api/compliance/incidents/{$incidentId}", [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'status' => 'investigating',
                'root_cause' => 'Investigation in progress',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateReport()
    {
        $response = $this->client->post('/api/compliance/reports', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'report_type' => 'FERPA_access',
                'title' => 'Q1 2026 FERPA Access Report',
                'description' => 'Quarterly FERPA data access report',
                'report_period_start' => '2026-01-01',
                'report_period_end' => '2026-03-31',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testSubmitReport()
    {
        $reportId = 'test-report-id';
        $response = $this->client->post("/api/compliance/reports/{$reportId}/submit", [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
            'json' => [
                'submitted_to' => 'Department of Education',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetReports()
    {
        $response = $this->client->get('/api/compliance/reports', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetDashboard()
    {
        $response = $this->client->get('/api/compliance/dashboard', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('active_policies', $data['data']);
        $this->assertArrayHasKey('pending_acknowledgments', $data['data']);
        $this->assertArrayHasKey('open_risks', $data['data']);
        $this->assertArrayHasKey('open_incidents', $data['data']);
    }

    public function testGetComplianceScore()
    {
        $response = $this->client->get('/api/compliance/compliance-score', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('overall_score', $data['data']);
        $this->assertArrayHasKey('policy_compliance', $data['data']);
        $this->assertArrayHasKey('training_compliance', $data['data']);
    }

    public function testGetAudits()
    {
        $response = $this->client->get('/api/compliance/audits', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAuditsBySeverity()
    {
        $response = $this->client->get('/api/compliance/audits?severity=high', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetRecentAudits()
    {
        $response = $this->client->get('/api/compliance/audits?days=7', [
            'headers' => [
                'Authorization' => 'Bearer test_token',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
