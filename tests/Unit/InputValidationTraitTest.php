<?php

declare(strict_types=1);

namespace Tests\Unit;

use Hyperf\Foundation\Testing\TestCase;
use App\Traits\InputValidationTrait;

class InputValidationTraitTest extends TestCase
{
    use InputValidationTrait;

    public function testValidateRequired()
    {
        $input = ['name' => 'John', 'email' => ''];
        $required = ['name', 'email'];

        $errors = $this->validateRequired($input, $required);

        $this->assertTrue(isset($errors['email']));
        $this->assertFalse(isset($errors['name']));
    }

    public function testSanitizeString()
    {
        $input = '  Test  ';
        $sanitized = $this->sanitizeString($input);

        $this->assertEquals('Test', $sanitized);
    }

    public function testValidateEmail()
    {
        $this->assertTrue($this->validateEmail('test@example.com'));
        $this->assertFalse($this->validateEmail('invalid-email'));
    }

    public function testValidateNumeric()
    {
        $this->assertTrue($this->validateNumeric('123'));
        $this->assertFalse($this->validateNumeric('abc'));
    }

    public function testValidateDate()
    {
        $this->assertTrue($this->validateDate('2024-01-15'));
        $this->assertFalse($this->validateDate('15-01-2024', 'Y-m-d'));
    }

    public function testValidateStringLength()
    {
        $this->assertTrue($this->validateStringLength('test', 2, 10));
        $this->assertFalse($this->validateStringLength('t', 2));
    }

    public function testValidateInteger()
    {
        $this->assertTrue($this->validateInteger(123));
        $this->assertTrue($this->validateInteger('123'));
        $this->assertFalse($this->validateInteger('abc'));
    }

    public function testValidateBoolean()
    {
        $this->assertTrue($this->validateBoolean(true));
        $this->assertTrue($this->validateBoolean(false));
        $this->assertFalse($this->validateBoolean('invalid'));
    }

    public function testValidatePasswordComplexity()
    {
        $validPassword = 'SecurePass123!';
        $errors = $this->validatePasswordComplexity($validPassword);

        $this->assertEmpty($errors);

        $shortPassword = 'pass';
        $errors = $this->validatePasswordComplexity($shortPassword);

        $this->assertNotEmpty($errors);
    }

    public function testValidateUrl()
    {
        $this->assertTrue($this->validateUrl('https://example.com'));
        $this->assertFalse($this->validateUrl('not-a-url'));
    }

    public function testValidatePhoneNumber()
    {
        $this->assertTrue($this->validatePhoneNumber('+1234567890'));
        $this->assertFalse($this->validatePhoneNumber('123'));
    }

    public function testValidateIn()
    {
        $this->assertTrue($this->validateIn('active', ['active', 'inactive']));
        $this->assertFalse($this->validateIn('deleted', ['active', 'inactive']));
    }

    public function testValidateAlphanumeric()
    {
        $this->assertTrue($this->validateAlphanumeric('abc123'));
        $this->assertFalse($this->validateAlphanumeric('abc-123'));
    }

    public function testValidateJson()
    {
        $this->assertTrue($this->validateJson('{"key": "value"}'));
        $this->assertFalse($this->validateJson('{invalid json}'));
    }

    public function testSanitizeFilename()
    {
        $filename = '../../../etc/passwd';
        $sanitized = $this->sanitizeFilename($filename);

        $this->assertFalse(strpos($sanitized, '..'));
    }

    public function testDetectSqlInjection()
    {
        $this->assertTrue($this->detectSqlInjection("'; DROP TABLE users; --"));
        $this->assertFalse($this->detectSqlInjection('normal text'));
    }

    public function testDetectXss()
    {
        $this->assertTrue($this->detectXss('<script>alert("xss")</script>'));
        $this->assertFalse($this->detectXss('normal text'));
    }

    public function testValidateUuid()
    {
        $this->assertTrue($this->validateUuid('550e8400-e29b-41d4-a716-4466554400000'));
        $this->assertFalse($this->validateUuid('not-a-uuid'));
    }

    public function testValidateIp()
    {
        $this->assertTrue($this->validateIp('192.168.1.1'));
        $this->assertFalse($this->validateIp('not-an-ip'));
    }

    public function testValidateIntegerRange()
    {
        $this->assertTrue($this->validateIntegerRange(5, 1, 10));
        $this->assertFalse($this->validateIntegerRange(15, 1, 10));
    }

    public function testSanitizeForCommand()
    {
        $input = 'test; rm -rf /';
        $sanitized = $this->sanitizeForCommand($input);

        $this->assertNotEquals($input, $sanitized);
        $this->assertTrue($sanitized !== '');
        $this->assertNull($this->sanitizeForCommand(null));
    }

    public function testDetectInjectionPatterns()
    {
        $this->assertTrue($this->detectInjectionPatterns("'; DROP TABLE users; --"));
        $this->assertTrue($this->detectInjectionPatterns('<script>alert("xss")</script>'));
        $this->assertTrue($this->detectInjectionPatterns('test; cat /etc/passwd'));
        $this->assertTrue($this->detectInjectionPatterns('../../../etc/passwd'));
        $this->assertFalse($this->detectInjectionPatterns('normal text'));
    }

    public function testValidateFileUploadEnhanced()
    {
        $mockFile = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'size' => 1024,
        ];

        $errors = $this->validateFileUploadEnhanced(null);
        $this->assertNotEmpty($errors);
        $this->assertContains('File is required', $errors);

        $errors = $this->validateFileUploadEnhanced($mockFile);
        $this->assertNotEmpty($errors);

        $mockFile['tmp_name'] = __FILE__;
        $errors = $this->validateFileUploadEnhanced($mockFile);
        $this->assertNotEmpty($errors);
    }
 }
