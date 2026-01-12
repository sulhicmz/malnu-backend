<?php

declare(strict_types=1);

namespace Tests\Unit;

use Hyperf\Foundation\Testing\TestCase;
use App\Traits\InputValidationTrait;

class InputValidationTraitTest extends TestCase
{
    use InputValidationTrait;

    public function testValidateRequiredWithAllFieldsPresent()
    {
        $input = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123'
        ];
        $requiredFields = ['name', 'email', 'password'];

        $errors = $this->validateRequired($input, $requiredFields);

        $this->assertEmpty($errors);
    }

    public function testValidateRequiredWithMissingFields()
    {
        $input = [
            'name' => 'John Doe'
        ];
        $requiredFields = ['name', 'email', 'password'];

        $errors = $this->validateRequired($input, $requiredFields);

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testSanitizeString()
    {
        $input = '  <script>alert("xss")</script>  ';
        $sanitized = $this->sanitizeString($input);

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('</script>', $sanitized);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
    }

    public function testSanitizeInputArray()
    {
        $input = [
            'name' => '  John Doe  ',
            'email' => 'john@example.com',
            'bio' => '<script>alert("xss")</script>',
            'nested' => [
                'title' => '  <h1>Title</h1>  '
            ]
        ];
        $sanitized = $this->sanitizeInput($input);

        $this->assertEquals('John Doe', $sanitized['name']);
        $this->assertEquals('john@example.com', $sanitized['email']);
        $this->assertStringNotContainsString('<script>', $sanitized['bio']);
        $this->assertStringNotContainsString('<h1>', $sanitized['nested']['title']);
    }

    public function testValidateEmailValid()
    {
        $this->assertTrue($this->validateEmail('user@example.com'));
        $this->assertTrue($this->validateEmail('test.user+tag@domain.co.uk'));
    }

    public function testValidateEmailInvalid()
    {
        $this->assertFalse($this->validateEmail('invalid-email'));
        $this->assertFalse($this->validateEmail('@example.com'));
        $this->assertFalse($this->validateEmail('user@'));
    }

    public function testValidateNumeric()
    {
        $this->assertTrue($this->validateNumeric(123));
        $this->assertTrue($this->validateNumeric('123.45'));
        $this->assertTrue($this->validateNumeric('123'));
        $this->assertFalse($this->validateNumeric('abc'));
    }

    public function testValidateDate()
    {
        $this->assertTrue($this->validateDate('2024-01-01'));
        $this->assertTrue($this->validateDate('2024-12-31'));
        $this->assertFalse($this->validateDate('2024-13-01'));
        $this->assertFalse($this->validateDate('not-a-date'));
    }

    public function testValidateStringLength()
    {
        $this->assertTrue($this->validateStringLength('test', 3));
        $this->assertTrue($this->validateStringLength('test', null, 10));
        $this->assertTrue($this->validateStringLength('test', 3, 10));
        $this->assertFalse($this->validateStringLength('test', 5));
        $this->assertFalse($this->validateStringLength('test', null, 3));
    }

    public function testValidateDateRange()
    {
        $this->assertTrue($this->validateDateRange('2024-01-01', '2024-12-31'));
        $this->assertTrue($this->validateDateRange('2024-01-01', '2024-01-01'));
        $this->assertFalse($this->validateDateRange('2024-12-31', '2024-01-01'));
    }

    public function testValidateFileUploadWithNoFile()
    {
        $errors = $this->validateFileUpload(null);
        $this->assertNotEmpty($errors);
        $this->assertContains('File is required', $errors);
    }

    public function testValidateFileUploadWithSizeExceeded()
    {
        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'size' => 10 * 1024 * 1024, // 10MB
            'tmp_name' => '/tmp/test'
        ];
        
        $errors = $this->validateFileUpload($file, [], 5 * 1024 * 1024); // 5MB max
        $this->assertNotEmpty($errors);
        $this->assertContains('File size exceeds maximum allowed size', $errors);
    }

    public function testValidateFileUploadWithInvalidType()
    {
        $file = [
            'name' => 'test.exe',
            'type' => 'application/octet-stream',
            'size' => 1024,
            'tmp_name' => '/tmp/test'
        ];
        
        $errors = $this->validateFileUpload($file, ['image/jpeg', 'image/png']);
        $this->assertNotEmpty($errors);
        $this->assertContains('File type not allowed', $errors);
    }

    public function testValidateArray()
    {
        $this->assertTrue($this->validateArray([1, 2, 3]));
        $this->assertTrue($this->validateArray([1, 2, 3], ['min' => 2]));
        $this->assertTrue($this->validateArray([1, 2, 3], ['max' => 5]));
        $this->assertTrue($this->validateArray([1, 2, 3], ['min' => 1, 'max' => 5]));
        $this->assertFalse($this->validateArray([1, 2, 3], ['min' => 5]));
        $this->assertFalse($this->validateArray([1, 2, 3], ['max' => 2]));
        $this->assertFalse($this->validateArray('not an array'));
    }

    public function testValidateInteger()
    {
        $this->assertTrue($this->validateInteger(123));
        $this->assertTrue($this->validateInteger('123'));
        $this->assertFalse($this->validateInteger(123.45));
        $this->assertFalse($this->validateInteger('abc'));
    }

    public function testValidateBoolean()
    {
        $this->assertTrue($this->validateBoolean(true));
        $this->assertTrue($this->validateBoolean(false));
        $this->assertTrue($this->validateBoolean('true'));
        $this->assertTrue($this->validateBoolean('false'));
        $this->assertTrue($this->validateBoolean(1));
        $this->assertTrue($this->validateBoolean(0));
        $this->assertFalse($this->validateBoolean('abc'));
        $this->assertFalse($this->validateBoolean(''));
    }

    public function testValidateUrlValid()
    {
        $this->assertTrue($this->validateUrl('http://example.com'));
        $this->assertTrue($this->validateUrl('https://example.com'));
        $this->assertTrue($this->validateUrl('https://example.com/path?query=value'));
        $this->assertTrue($this->validateUrl('https://sub.domain.example.com:8080/path'));
    }

    public function testValidateUrlInvalid()
    {
        $this->assertFalse($this->validateUrl('example.com')); // Missing protocol
        $this->assertFalse($this->validateUrl('ftp://example.com')); // Insecure protocol
        $this->assertFalse($this->validateUrl('javascript:void(0)')); // Dangerous protocol
        $this->assertFalse($this->validateUrl('not-a-url'));
    }

    public function testValidatePhoneNumberValid()
    {
        $this->assertTrue($this->validatePhoneNumber('+1234567890'));
        $this->assertTrue($this->validatePhoneNumber('(123) 456-7890'));
        $this->assertTrue($this->validatePhoneNumber('123-456-7890'));
        $this->assertTrue($this->validatePhoneNumber('1234567890'));
        $this->assertTrue($this->validatePhoneNumber('+1 800 555 1234'));
    }

    public function testValidatePhoneNumberInvalid()
    {
        $this->assertFalse($this->validatePhoneNumber('123')); // Too short
        $this->assertFalse($this->validatePhoneNumber('1234567890123456')); // Too long
        $this->assertFalse($this->validatePhoneNumber('abc'));
        $this->assertFalse($this->validatePhoneNumber(''));
    }

    public function testSanitizeForSql()
    {
        $input = "O'Reilly \"quotes\" \n newline";
        $sanitized = $this->sanitizeForSql($input);

        $this->assertStringContainsString('\\\'', $sanitized);
        $this->assertStringContainsString('\\"', $sanitized);
        $this->assertStringContainsString('\\n', $sanitized);
    }

    public function testSanitizeForCommand()
    {
        $input = 'test; rm -rf /';
        $sanitized = $this->sanitizeForCommand($input);

        // The result should be escaped as a shell argument
        $this->assertStringContainsString("'", $sanitized);
    }

    public function testDetectInjectionPatternsSql()
    {
        $input = "1' OR '1'='1";
        $detected = $this->detectInjectionPatterns($input);
        $this->assertContains('sql', $detected);
    }

    public function testDetectInjectionPatternsCommand()
    {
        $input = "file.txt; rm -rf /";
        $detected = $this->detectInjectionPatterns($input);
        $this->assertContains('command', $detected);
    }

    public function testDetectInjectionPatternsLdap()
    {
        $input = "user)(|(password=*))";
        $detected = $this->detectInjectionPatterns($input);
        $this->assertContains('ldap', $detected);
    }

    public function testDetectInjectionPatternsPath()
    {
        $input = "../../../etc/passwd";
        $detected = $this->detectInjectionPatterns($input);
        $this->assertContains('path', $detected);
    }

    public function testDetectInjectionPatternsNone()
    {
        $input = "This is a normal string";
        $detected = $this->detectInjectionPatterns($input);
        $this->assertEmpty($detected);
    }

    public function testSanitizeFileName()
    {
        $this->assertEquals('file.txt', $this->sanitizeFileName('file.txt'));
        $this->assertEquals('file_name.jpg', $this->sanitizeFileName('file name.jpg'));
        $this->assertEquals('file_.txt', $this->sanitizeFileName('.hidden.txt'));
        $this->assertEquals('file.txt', $this->sanitizeFileName('../../../file.txt'));
        $this->assertEquals('file.txt', $this->sanitizeFileName('file.php.txt'));
    }

    public function testValidateFileUploadEnhancedWithValidFile()
    {
        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'size' => 1024,
            'tmp_name' => '/tmp/test'
        ];
        
        $errors = $this->validateFileUploadEnhanced($file, ['txt', 'pdf']);
        $this->assertEmpty($errors);
    }

    public function testValidateFileUploadEnhancedWithInvalidExtension()
    {
        $file = [
            'name' => 'test.exe',
            'type' => 'application/octet-stream',
            'size' => 1024,
            'tmp_name' => '/tmp/test'
        ];
        
        $errors = $this->validateFileUploadEnhanced($file, ['txt', 'pdf']);
        $this->assertNotEmpty($errors);
        $this->assertContains('File extension not allowed', $errors);
    }
}
