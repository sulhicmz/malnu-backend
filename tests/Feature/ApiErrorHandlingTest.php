<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\Api\BaseController;

class ApiErrorHandlingTest extends TestCase
{
    public function test_success_response_format()
    {
        // Create a mock controller that extends BaseController
        $controller = $this->getMockForAbstractClass(BaseController::class);
        
        // Test success response format
        $response = $controller->successResponse(['test' => 'data'], 'Test message', 200);
        
        $this->assertIsArray($response);
        $this->assertTrue($response['data']['success']);
        $this->assertEquals(['test' => 'data'], $response['data']['data']);
        $this->assertEquals('Test message', $response['data']['message']);
        $this->assertArrayHasKey('timestamp', $response['data']);
    }

    public function test_error_response_format()
    {
        // Create a mock controller that extends BaseController
        $controller = $this->getMockForAbstractClass(BaseController::class);
        
        // Test error response format
        $response = $controller->errorResponse('Test error message', 'TEST_ERROR', ['field' => 'error'], 400);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['data']['success']);
        $this->assertEquals('Test error message', $response['data']['error']['message']);
        $this->assertEquals('TEST_ERROR', $response['data']['error']['code']);
        $this->assertEquals(['field' => 'error'], $response['data']['error']['details']);
        $this->assertArrayHasKey('timestamp', $response['data']);
    }

    public function test_not_found_response()
    {
        // Create a mock controller that extends BaseController
        $controller = $this->getMockForAbstractClass(BaseController::class);
        
        // Test not found response
        $response = $controller->notFoundResponse();
        
        $this->assertIsArray($response);
        $this->assertFalse($response['data']['success']);
        $this->assertEquals('Resource not found', $response['data']['error']['message']);
        $this->assertEquals('NOT_FOUND', $response['data']['error']['code']);
        $this->assertEquals(404, $response['status']);
    }

    public function test_validation_error_response()
    {
        // Create a mock controller that extends BaseController
        $controller = $this->getMockForAbstractClass(BaseController::class);
        
        // Test validation error response
        $errors = ['email' => ['The email field is required.']];
        $response = $controller->validationErrorResponse($errors);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['data']['success']);
        $this->assertEquals('Validation failed', $response['data']['error']['message']);
        $this->assertEquals('VALIDATION_ERROR', $response['data']['error']['code']);
        $this->assertEquals($errors, $response['data']['error']['details']);
        $this->assertEquals(422, $response['status']);
    }
}