<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class MfaControllerTest extends TestCase
{
    private string $baseUrl = 'http://localhost:9501';

    private ?string $authToken = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->authToken) {
            $headers['Authorization'] = 'Bearer ' . $this->authToken;
        }

        return $headers;
    }

    public function test_mfa_setup_requires_authentication(): void
    {
        $response = $this->makeRequest('POST', '/api/mfa/setup', []);
        
        $this->assertEquals(401, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_mfa_status_requires_authentication(): void
    {
        $response = $this->makeRequest('GET', '/api/mfa/status', []);
        
        $this->assertEquals(401, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_mfa_enable_requires_verification_code(): void
    {
        $this->authToken = $this->getValidAuthToken();
        
        $response = $this->makeRequest('POST', '/api/mfa/enable', [
            'code' => '',
        ]);
        
        $this->assertEquals(422, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_mfa_disable_requires_verification_code(): void
    {
        $this->authToken = $this->getValidAuthToken();
        
        $response = $this->makeRequest('POST', '/api/mfa/disable', [
            'code' => '',
        ]);
        
        $this->assertEquals(422, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_mfa_verify_requires_email_and_code(): void
    {
        $response = $this->makeRequest('POST', '/api/mfa/verify', [
            'email' => '',
            'code' => '',
        ]);
        
        $this->assertEquals(422, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_mfa_verify_validates_email_format(): void
    {
        $this->authToken = $this->getValidAuthToken();
        
        $response = $this->makeRequest('POST', '/api/mfa/verify', [
            'email' => 'invalid-email',
            'code' => '123456',
        ]);
        
        $this->assertEquals(422, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    public function test_backup_codes_regenerate_requires_code(): void
    {
        $this->authToken = $this->getValidAuthToken();
        
        $response = $this->makeRequest('POST', '/api/mfa/backup-codes/regenerate', [
            'code' => '',
        ]);
        
        $this->assertEquals(422, $response['status']);
        $this->assertFalse($response['body']['success']);
    }

    private function makeRequest(string $method, string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = $this->getHeaders();
        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        if (! empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'body' => json_decode($response, true) ?? [],
        ];
    }

    private function getValidAuthToken(): string
    {
        return 'test-token';
    }
}
