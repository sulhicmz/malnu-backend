<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Traits\InputValidationTrait;

class InputValidationSecurityTest extends TestCase
{
    use InputValidationTrait;

    public function testValidateUrl()
    {
        $this->assertTrue($this->validateUrl('https://example.com'));
        $this->assertTrue($this->validateUrl('http://example.com/path'));
        $this->assertTrue($this->validateUrl('ftp://files.example.com'));
        $this->assertFalse($this->validateUrl('not-a-url'));
        $this->assertFalse($this->validateUrl('example'));
        $this->assertFalse($this->validateUrl(''));
    }

    public function testValidatePhone()
    {
        $this->assertTrue($this->validatePhone('1234567890'));
        $this->assertTrue($this->validatePhone('6281234567'));
        $this->assertTrue($this->validatePhone('+6281234567'));
        $this->assertTrue($this->validatePhone('08123456789'));
        $this->assertFalse($this->validatePhone('123'));
        $this->assertFalse($this->validatePhone('1234567890123456'));
        $this->assertFalse($this->validatePhone('abcdefghij'));
        $this->assertFalse($this->validatePhone(''));
    }

    public function testValidateIp()
    {
        $this->assertTrue($this->validateIp('192.168.1.1'));
        $this->assertTrue($this->validateIp('::1'));
        $this->assertTrue($this->validateIp('2001:0db8:85a3::8a2e:370:7344'));
        $this->assertTrue($this->validateIp('10.0.0.1'));
        $this->assertFalse($this->validateIp('256.256.256.256'));
        $this->assertFalse($this->validateIp('not-an-ip'));
        $this->assertFalse($this->validateIp(''));
    }

    public function testValidateJson()
    {
        $this->assertTrue($this->validateJson('{"name":"test"}'));
        $this->assertTrue($this->validateJson('[]'));
        $this->assertTrue($this->validateJson('{"key":"value","number":123}'));
        $this->assertFalse($this->validateJson('{"invalid":}'));
        $this->assertFalse($this->validateJson('{invalid json}'));
        $this->assertFalse($this->validateJson('not json'));
    }

    public function testValidateRegex()
    {
        $this->assertTrue($this->validateRegex('test@example.com', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+$/'));
        $this->assertTrue($this->validateRegex('123-456-7890', '/^\d{3}-\d{4}$/'));
        $this->assertTrue($this->validateRegex('ABC123', '/^[A-Z]{3}\d{3}$/'));
        $this->assertFalse($this->validateRegex('test', '/^[a-z]+$/'));
        $this->assertFalse($this->validateRegex('', '/./'));
    }

    public function testSanitizeCommand()
    {
        $command = 'ls -la';
        $sanitized = $this->sanitizeCommand($command);
        $this->assertEquals("ls -la", $sanitized);

        $commandWithArg = 'rm -rf /tmp/test';
        $sanitized = $this->sanitizeCommand($commandWithArg);
        $this->assertEquals("rm -rf /tmp/test", $sanitized);

        $commandWithQuotes = 'echo "test; ls"';
        $sanitized = $this->sanitizeCommand($commandWithQuotes);
        $this->assertEquals("echo \"test; ls\"", $sanitized);
    }

    public function testSanitizeCommandArg()
    {
        $arg = 'test file.txt';
        $sanitized = $this->sanitizeCommandArg($arg);
        $this->assertEquals("test file.txt", $sanitized);

        $argWithSpaces = 'file with spaces.txt';
        $sanitized = $this->sanitizeCommandArg($argWithSpaces);
        $this->assertEquals("'file with spaces.txt'", $sanitized);

        $argWithSpecial = 'file$(whoami).txt';
        $sanitized = $this->sanitizeCommandArg($argWithSpecial);
        $this->assertEquals("'file\$(whoami).txt'", $sanitized);
    }

    public function testEscapeSqlIdentifier()
    {
        $this->assertEquals('``table_name``', $this->escapeSqlIdentifier('table_name'));
        $this->assertEquals('``column_name``', $this->escapeSqlIdentifier('column_name'));
        $this->assertEquals('``table```name``', $this->escapeSqlIdentifier('table`name'));
        $this->assertNotEquals('table_name', $this->escapeSqlIdentifier('table_name'));
        $this->assertNotEquals('column`name', $this->escapeSqlIdentifier('column`name'));
    }

    public function testSanitizeFilename()
    {
        $this->assertEquals('document.pdf', $this->sanitizeFilename('document.pdf'));
        $this->assertEquals('image.jpg', $this->sanitizeFilename('image.jpg'));
        $this->assertEquals('data_file.csv', $this->sanitizeFilename('data_file.csv'));
        $this->assertEquals('myfile.txt', $this->sanitizeFilename('../myfile.txt'));
        $this->assertEquals('my-file.txt', $this->sanitizeFilename('my--file.txt'));
        $this->assertEquals('file.txt', $this->sanitizeFilename('.../file.txt'));
        $this->assertEquals('file', $this->sanitizeFilename('.../file'));
        $this->assertEquals('file', $this->sanitizeFilename('..\\..\\file'));
        $this->assertEquals('file.txt', $this->sanitizeFilename('../../../etc/passwd'));
    }

    public function testValidateSecureFileUpload()
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSizeBytes = 5 * 1024 * 1024;

        $validFile = [
            'tmp_name' => '/tmp/test_upload.jpg',
            'name' => 'test_upload.jpg',
            'size' => 1024 * 1024,
            'type' => 'image/jpeg'
        ];

        $errors = $this->validateSecureFileUpload($validFile, $allowedMimes, $maxSizeBytes);
        $this->assertEmpty($errors);

        $oversizedFile = [
            'tmp_name' => '/tmp/oversized.jpg',
            'name' => 'oversized.jpg',
            'size' => 10 * 1024 * 1024,
            'type' => 'image/jpeg'
        ];

        $errors = $this->validateSecureFileUpload($oversizedFile, $allowedMimes, $maxSizeBytes);
        $this->assertContains('File size exceeds maximum allowed size', $errors);

        $wrongTypeFile = [
            'tmp_name' => '/tmp/test.exe',
            'name' => 'test.exe',
            'size' => 1024,
            'type' => 'application/x-msdownload'
        ];

        $errors = $this->validateSecureFileUpload($wrongTypeFile, $allowedMimes, $maxSizeBytes);
        $this->assertContains('File type not allowed', $errors);

        $nullFile = null;
        $errors = $this->validateSecureFileUpload($nullFile, $allowedMimes, $maxSizeBytes);
        $this->assertContains('File is required', $errors);

        $maliciousNameFile = [
            'tmp_name' => '/tmp/test.jpg',
            'name' => '../../../etc/passwd',
            'size' => 1024,
            'type' => 'image/jpeg'
        ];

        $errors = $this->validateSecureFileUpload($maliciousNameFile, $allowedMimes, $maxSizeBytes);
        $this->assertContains('Filename contains invalid characters', $errors);

        $phpFile = [
            'tmp_name' => '/tmp/test.php',
            'name' => 'malicious.php',
            'size' => 1024,
            'type' => 'application/x-httpd-php'
        ];

        $errors = $this->validateSecureFileUpload($phpFile, $allowedMimes, $maxSizeBytes);
        $this->assertContains('File extension is not allowed', $errors);
    }

    public function testXssProtectionRemains()
    {
        $xss = '<script>alert("xss")</script>';
        $sanitized = $this->sanitizeString($xss);
        $this->assertEquals('&lt;script&gt;alert("xss")&lt;/script&gt;', $sanitized);

        $xssWithQuotes = '<img src=x onerror="alert(1)">';
        $sanitized = $this->sanitizeString($xssWithQuotes);
        $this->assertStringStartsWith('<img', $sanitized);

        $xssWithJs = '<javascript>alert(1)</javascript>';
        $sanitized = $this->sanitizeString($xssWithJs);
        $this->assertStringNotContains('<script', $sanitized);
    }
}
