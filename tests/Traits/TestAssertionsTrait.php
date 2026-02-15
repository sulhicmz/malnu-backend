<?php

declare(strict_types=1);

namespace Tests\Traits;

trait TestAssertionsTrait
{
    protected function assertApiSuccessResponse(array $response, string $message = null): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        
        if ($message) {
            $this->assertArrayHasKey('message', $response);
            $this->assertEquals($message, $response['message']);
        }
    }

    protected function assertApiErrorResponse(array $response, int $expectedCode = 400, string $expectedMessage = null): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        
        if ($expectedMessage) {
            $this->assertArrayHasKey('message', $response);
            $this->assertStringContainsString($expectedMessage, $response['message']);
        }
    }

    protected function assertResponseHasPaginatedData(array $response): void
    {
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('current_page', $response['meta']);
        $this->assertArrayHasKey('total', $response['meta']);
        $this->assertArrayHasKey('per_page', $response['meta']);
    }

    protected function assertValidationError(array $response, string $field): void
    {
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey($field, $response['errors']);
    }

    protected function assertModelExists(string $modelClass, string $id): void
    {
        $model = $modelClass::find($id);
        $this->assertNotNull($model, "Model {$modelClass} with ID {$id} does not exist");
    }

    protected function assertModelMissing(string $modelClass, string $id): void
    {
        $model = $modelClass::find($id);
        $this->assertNull($model, "Model {$modelClass} with ID {$id} should not exist");
    }

    protected function assertDatabaseHasModel(string $modelClass, array $attributes): void
    {
        $this->assertDatabaseHas(
            (new $modelClass)->getTable(),
            $attributes
        );
    }

    protected function assertDatabaseMissingModel(string $modelClass, array $attributes): void
    {
        $this->assertDatabaseMissing(
            (new $modelClass)->getTable(),
            $attributes
        );
    }
}
