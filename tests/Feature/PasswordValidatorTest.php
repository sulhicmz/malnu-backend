<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PasswordValidator;

class PasswordValidatorTest extends TestCase
{
    private PasswordValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new PasswordValidator();
    }

    public function test_valid_password_passes_all_validation_rules()
    {
        $password = 'SecurePass123!';
        $errors = $this->validator->validate($password);

        $this->assertEmpty($errors);
        $this->assertTrue($this->validator->isValid($password));
    }

    public function test_password_with_less_than_8_characters_fails()
    {
        $password = 'Pass1!';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password must be at least 8 characters', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_password_without_uppercase_fails()
    {
        $password = 'securepass123!';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one uppercase letter', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_password_without_lowercase_fails()
    {
        $password = 'SECUREPASS123!';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one lowercase letter', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_password_without_numbers_fails()
    {
        $password = 'SecurePass!!';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one number', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_password_without_special_characters_fails()
    {
        $password = 'SecurePass123';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one special character', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_common_password_fails_validation()
    {
        $password = 'password123!';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertContains('Password is too common. Please choose a stronger password.', $errors);
        $this->assertFalse($this->validator->isValid($password));
    }

    public function test_case_insensitive_common_password_detection()
    {
        $password1 = 'Password123!';
        $password2 = 'PASSWORD123!';
        $password3 = 'PaSsWoRd123!';

        foreach ([$password1, $password2, $password3] as $password) {
            $errors = $this->validator->validate($password);
            $this->assertContains('Password is too common. Please choose a stronger password.', $errors);
        }
    }

    public function test_all_validation_errors_are_returned()
    {
        $password = 'pass';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertGreaterThanOrEqual(3, count($errors));
    }

    public function test_password_with_special_characters_only()
    {
        $password = '!@#$%^&*()';
        $errors = $this->validator->validate($password);

        $this->assertNotEmpty($errors);
        $this->assertNotContains('Password must contain at least one special character', $errors);
    }

    public function test_password_with_mixed_special_characters()
    {
        $password = 'MySecure#Pass123';
        $errors = $this->validator->validate($password);

        $this->assertEmpty($errors);
        $this->assertTrue($this->validator->isValid($password));
    }

    public function test_password_exactly_8_characters_passes()
    {
        $password = 'Secur1@';
        $errors = $this->validator->validate($password);

        $this->assertEmpty($errors);
        $this->assertTrue($this->validator->isValid($password));
    }
}
