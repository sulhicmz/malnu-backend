<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use function putenv;

class InputValidationSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_validates_url_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testUrl(string $url): bool
            {
                return $this->validateUrl($url);
            }
        };

        $this->assertTrue($trait->testUrl('https://example.com'));
        $this->assertTrue($trait->testUrl('http://example.com'));
        $this->assertTrue($trait->testUrl('https://example.com/path'));
        $this->assertFalse($trait->testUrl('not-a-url'));
        $this->assertFalse($trait->testUrl('htp://invalid'));
        $this->assertFalse($trait->testUrl('javascript:alert(1)'));
    }

    public function test_validates_phone_numbers_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testPhone(string $phone): bool
            {
                return $this->validatePhone($phone);
            }
        };

        $this->assertTrue($trait->testPhone('1234567890'));
        $this->assertTrue($trait->testPhone('+1234567890'));
        $this->assertTrue($trait->testPhone('123-456-7890'));
        $this->assertTrue($trait->testPhone('1234567890123'));
        $this->assertFalse($trait->testPhone('123'));
        $this->assertFalse($trait->testPhone('1234567890123456'));
    }

    public function test_validates_ip_addresses_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testIp(string $ip): bool
            {
                return $this->validateIp($ip);
            }
        };

        $this->assertTrue($trait->testIp('192.168.1.1'));
        $this->assertTrue($trait->testIp('8.8.8.8'));
        $this->assertTrue($trait->testIp('::1'));
        $this->assertTrue($trait->testIp('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse($trait->testIp('256.256.256.256'));
        $this->assertFalse($trait->testIp('not-an-ip'));
    }

    public function test_validates_json_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testJson(string $json): bool
            {
                return $this->validateJson($json);
            }
        };

        $this->assertTrue($trait->testJson('{"key":"value"}'));
        $this->assertTrue($trait->testJson('["item1","item2"]'));
        $this->assertTrue($trait->testJson('123'));
        $this->assertTrue($trait->testJson('"string"'));
        $this->assertFalse($trait->testJson('{"key":value}'));
        $this->assertFalse($trait->testJson('{invalid}'));
    }

    public function test_validates_regex_patterns_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testRegex(string $value, string $pattern): bool
            {
                return $this->validateRegex($value, $pattern);
            }
        };

        $this->assertTrue($trait->testRegex('abc123', '/^[a-z0-9]+$/'));
        $this->assertTrue($trait->testRegex('user@example.com', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
        $this->assertFalse($trait->testRegex('ABC123', '/^[a-z0-9]+$/'));
        $this->assertFalse($trait->testRegex('user@example', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
    }

    public function test_sanitizes_command_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testCommand(string $command): string
            {
                return $this->sanitizeCommand($command);
            }
        };

        $result = $trait->testCommand('ls -la');
        $this->assertIsString($result);
        $this->assertStringNotContainsString('; rm -rf', $trait->testCommand('ls; rm -rf'));
    }

    public function test_sanitizes_command_argument_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testCommandArg(string $arg): string
            {
                return $this->sanitizeCommandArg($arg);
            }
        };

        $result = $trait->testCommandArg('filename.txt');
        $this->assertIsString($result);
        $this->assertStringStartsWith("'", $result);
        $this->assertStringEndsWith("'", $result);
    }

    public function test_escapes_sql_identifier_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testSqlIdentifier(string $identifier): string
            {
                return $this->escapeSqlIdentifier($identifier);
            }
        };

        $result = $trait->testSqlIdentifier('table_name');
        $this->assertEquals('table_name', $result);

        $result = $trait->testSqlIdentifier('table`name');
        $this->assertEquals('table``name', $result);
    }

    public function test_sanitizes_filename_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testFilename(string $filename): string
            {
                return $this->sanitizeFilename($filename);
            }
        };

        $this->assertEquals('file.txt', $trait->testFilename('file.txt'));
        $this->assertEquals('file.txt', $trait->testFilename('../../../file.txt'));
        $this->assertEquals('file.txt', $trait->testFilename('confile.txt'));
        $this->assertEquals('file.txt', $trait->testFilename('file;.txt'));
        $this->assertEquals('file', $trait->testFilename('...file...'));
    }

    public function test_validates_secure_file_upload_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testSecureFileUpload(mixed $file, array $allowedMimes = [], ?int $maxSize = null): array
            {
                return $this->validateSecureFileUpload($file, $allowedMimes, $maxSize);
            }
        };

        $this->assertNotEmpty($trait->testSecureFileUpload(null));

        $mockFile = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test',
            'size' => 1024
        ];
        $this->assertContains('Invalid file upload', $trait->testSecureFileUpload($mockFile));

        $mockFile = [
            'name' => 'test.php',
            'type' => 'application/x-php',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
            'size' => 1024
        ];
        $errors = $trait->testSecureFileUpload($mockFile);
        $this->assertContains('File extension is not allowed', $errors);

        $mockFile = [
            'name' => '../../../test.txt',
            'type' => 'text/plain',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
            'size' => 1024
        ];
        $errors = $trait->testSecureFileUpload($mockFile);
        $this->assertContains('Filename contains invalid characters', $errors);

        $mockFile = [
            'name' => 'test.exe',
            'type' => 'application/x-msdownload',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
            'size' => 1024
        ];
        $errors = $trait->testSecureFileUpload($mockFile);
        $this->assertContains('File extension is not allowed', $errors);

        $mockFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
            'size' => 10485760
        ];
        $errors = $trait->testSecureFileUpload($mockFile, ['text/plain'], 1048576);
        $this->assertContains('File size exceeds maximum allowed size', $errors);
    }

    public function test_sanitize_string_prevents_xss()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testString(?string $value): ?string
            {
                return $this->sanitizeString($value);
            }
        };

        $xss = '<script>alert("xss")</script>';
        $result = $trait->testString($xss);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_validates_email_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testEmail(string $email): bool
            {
                return $this->validateEmail($email);
            }
        };

        $this->assertTrue($trait->testEmail('user@example.com'));
        $this->assertTrue($trait->testEmail('user.name@example.co.uk'));
        $this->assertFalse($trait->testEmail('invalid'));
        $this->assertFalse($trait->testEmail('@example.com'));
        $this->assertFalse($trait->testEmail('user@'));
    }

    public function test_validates_date_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testDate(string $date, string $format = 'Y-m-d'): bool
            {
                return $this->validateDate($date, $format);
            }
        };

        $this->assertTrue($trait->testDate('2024-01-15'));
        $this->assertTrue($trait->testDate('15/01/2024', 'd/m/Y'));
        $this->assertFalse($trait->testDate('2024-13-01'));
        $this->assertFalse($trait->testDate('not-a-date'));
    }

    public function test_validates_string_length_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testStringLength(string $value, ?int $min = null, ?int $max = null): bool
            {
                return $this->validateStringLength($value, $min, $max);
            }
        };

        $this->assertTrue($trait->testStringLength('hello', 3, 10));
        $this->assertFalse($trait->testStringLength('hi', 3, 10));
        $this->assertFalse($trait->testStringLength('hello world', 3, 10));
    }

    public function test_validates_date_range_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testDateRange(string $startDate, string $endDate): bool
            {
                return $this->validateDateRange($startDate, $endDate);
            }
        };

        $this->assertTrue($trait->testDateRange('2024-01-01', '2024-12-31'));
        $this->assertTrue($trait->testDateRange('2024-01-01', '2024-01-01'));
        $this->assertFalse($trait->testDateRange('2024-12-31', '2024-01-01'));
    }

    public function test_validates_integer_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testInteger(mixed $value): bool
            {
                return $this->validateInteger($value);
            }
        };

        $this->assertTrue($trait->testInteger(123));
        $this->assertTrue($trait->testInteger('123'));
        $this->assertFalse($trait->testInteger('not-a-number'));
        $this->assertFalse($trait->testInteger(12.3));
    }

    public function test_validates_boolean_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testBoolean(mixed $value): bool
            {
                return $this->validateBoolean($value);
            }
        };

        $this->assertTrue($trait->testBoolean(true));
        $this->assertTrue($trait->testBoolean(false));
        $this->assertTrue($trait->testBoolean('true'));
        $this->assertTrue($trait->testBoolean('false'));
        $this->assertTrue($trait->testBoolean('1'));
        $this->assertTrue($trait->testBoolean('0'));
        $this->assertFalse($trait->testBoolean('not-a-boolean'));
    }

    public function test_validates_password_complexity_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testPasswordComplexity(string $password): array
            {
                return $this->validatePasswordComplexity($password);
            }
        };

        $this->assertEmpty($trait->testPasswordComplexity('SecurePass123!'));

        $errors = $trait->testPasswordComplexity('short');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must be at least 8 characters long.', $errors);

        $errors = $trait->testPasswordComplexity('nouppercase123!');
        $this->assertContains('Password must contain at least 1 uppercase letter.', $errors);

        $errors = $trait->testPasswordComplexity('NOLOWERCASE123!');
        $this->assertContains('Password must contain at least 1 lowercase letter.', $errors);

        $errors = $trait->testPasswordComplexity('NoNumbersHere!');
        $this->assertContains('Password must contain at least 1 number.', $errors);

        $errors = $trait->testPasswordComplexity('NoSpecialChars123');
        $this->assertContains('Password must contain at least 1 special character', $errors);

        $errors = $trait->testPasswordComplexity('password');
        $this->assertContains('Password is too common.', $errors);
    }

    public function test_validates_array_correctly()
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function testArray(mixed $value, array $rules = []): bool
            {
                return $this->validateArray($value, $rules);
            }
        };

        $this->assertTrue($trait->testArray([1, 2, 3]));
        $this->assertFalse($trait->testArray('not-an-array'));

        $this->assertTrue($trait->testArray([1, 2, 3], ['min' => 2, 'max' => 5]));
        $this->assertFalse($trait->testArray([1], ['min' => 2, 'max' => 5]));
        $this->assertFalse($trait->testArray([1, 2, 3, 4, 5, 6], ['min' => 2, 'max' => 5]));
    }
}
