<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Http\Controllers\Api\BaseController;
use DateTime;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ApiErrorResponseTest extends TestCase
{
    private BaseController $controller;

    private RequestInterface $request;

    private ResponseInterface $response;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container(new DefinitionSourceFactory());

        ApplicationContext::setContainer($container);

        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
    }

    public function testValidationErrorResponseFormat()
    {
        $errors = [
            'email' => ['The email must be a valid email address.'],
            'password' => ['The password must be at least 6 characters.'],
        ];

        $response = $this->controller->validationErrorResponse($errors);

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed. Please check your input.', $data['error']['message']);
        $this->assertEquals('VAL_001', $data['error']['code']);
        $this->assertEquals('validation', $data['error']['type']);
        $this->assertEquals($errors, $data['error']['details']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testNotFoundResponseFormat()
    {
        $response = $this->controller->notFoundResponse('User not found');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('User not found', $data['error']['message']);
        $this->assertEquals('RES_001', $data['error']['code']);
        $this->assertEquals('not_found', $data['error']['type']);
        $this->assertArrayNotHasKey('details', $data['error']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUnauthorizedResponseFormat()
    {
        $response = $this->controller->unauthorizedResponse('Token invalid');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Token invalid', $data['error']['message']);
        $this->assertEquals('AUTH_005', $data['error']['code']);
        $this->assertEquals('authentication', $data['error']['type']);
        $this->assertArrayNotHasKey('details', $data['error']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testForbiddenResponseFormat()
    {
        $response = $this->controller->forbiddenResponse('Access denied');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Access denied', $data['error']['message']);
        $this->assertEquals('AUTH_006', $data['error']['code']);
        $this->assertEquals('authorization', $data['error']['type']);
        $this->assertArrayNotHasKey('details', $data['error']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testServerErrorResponseFormat()
    {
        $response = $this->controller->serverErrorResponse('Database connection failed');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Database connection failed', $data['error']['message']);
        $this->assertEquals('SRV_001', $data['error']['code']);
        $this->assertEquals('server', $data['error']['type']);
        $this->assertArrayNotHasKey('details', $data['error']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testCustomErrorResponseFormat()
    {
        $details = ['field' => 'Custom error details'];

        $response = $this->controller->errorResponse(
            'Custom error message',
            'RES_006',
            $details,
            400
        );

        $data = json_decode((string) $response->getBody(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Custom error message', $data['error']['message']);
        $this->assertEquals('RES_006', $data['error']['code']);
        $this->assertEquals($details, $data['error']['details']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testSuccessResponseFormat()
    {
        $responseData = ['id' => '123', 'name' => 'Test User'];

        $response = $this->controller->successResponse($responseData, 'User created successfully', 201);

        $data = json_decode((string) $response->getBody(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals($responseData, $data['data']);
        $this->assertEquals('User created successfully', $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testSuccessResponseWithoutData()
    {
        $response = $this->controller->successResponse(null, 'Operation successful');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertTrue($data['success']);
        $this->assertArrayNotHasKey('data', $data);
        $this->assertEquals('Operation successful', $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTimestampFormat()
    {
        $response = $this->controller->successResponse(['test' => 'data'], 'Test');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('timestamp', $data);

        $timestamp = $data['timestamp'];
        $this->assertNotEmpty($timestamp);

        $dateTime = DateTime::createFromFormat(DateTime::ATOM, $timestamp);
        $this->assertInstanceOf(DateTime::class, $dateTime);
    }

    public function testErrorCodesAreFromConfig()
    {
        $expectedCode = 'RES_002';
        $configuredCode = config('error-codes.error_codes.RESOURCE.CREATION_FAILED');

        $this->assertEquals($expectedCode, $configuredCode);

        $response = $this->controller->errorResponse(
            'Test error',
            $configuredCode,
            null,
            400
        );

        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals($expectedCode, $data['error']['code']);
    }
}
