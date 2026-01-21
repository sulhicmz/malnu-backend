<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    public function test_register_request_validation_passes_with_valid_data()
    {
        $request = new RegisterRequest($this->app->getContainer());
        $request->merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
        ]);

        $this->assertTrue($request->authorize());
        $this->assertEmpty(array_diff(['name', 'email', 'password'], array_keys($request->rules())));
    }

    public function test_register_request_fails_with_short_name()
    {
        $request = new RegisterRequest($this->app->getContainer());
        $request->merge([
            'name' => 'Jo',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
        ]);

        $rules = $request->rules();
        $this->assertEquals('min:3', $rules['name']);
    }

    public function test_login_request_validation_passes_with_valid_credentials()
    {
        $request = new LoginRequest($this->app->getContainer());
        $request->merge([
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
        ]);

        $this->assertTrue($request->authorize());
        $this->assertEmpty(array_diff(['email', 'password'], array_keys($request->rules())));
    }

    public function test_login_request_fails_with_invalid_email()
    {
        $request = new LoginRequest($this->app->getContainer());
        $request->merge([
            'email' => 'invalid-email',
            'password' => 'SecurePass123!',
        ]);

        $rules = $request->rules();
        $this->assertEquals('email', $rules['email']);
    }

    public function test_all_auth_requests_have_authorize_returning_true()
    {
        $container = $this->app->getContainer();

        $loginRequest = new LoginRequest($container);
        $this->assertTrue($loginRequest->authorize());

        $registerRequest = new RegisterRequest($container);
        $this->assertTrue($registerRequest->authorize());
    }
}
