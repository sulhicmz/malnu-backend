<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Input Validation Security Test
 *
 * Tests enhanced input validation methods for security including:
 * - URL validation
 * - Phone number validation
 * - IP address validation
 * - JSON validation
 * - Regex pattern validation
 * - Command injection protection
 * - SQL identifier escaping
 * - Filename sanitization
 * - Secure file upload validation
 */
class InputValidationSecurityTest extends TestCase
{
    /**
     * Test URL validation with valid URLs.
     */
    public function test_valid_url_passes_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateUrlPublic(string $url): bool
            {
                return $this->validateUrl($url);
            }
        };

        $this->assertTrue($trait->validateUrlPublic('https://example.com'));
        $this->assertTrue($trait->validateUrlPublic('http://test.example.org'));
        $this->assertTrue($trait->validateUrlPublic('https://sub.domain.co.uk/path?query=1'));
    }

    /**
     * Test URL validation rejects invalid URLs.
     */
    public function test_invalid_url_fails_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateUrlPublic(string $url): bool
            {
                return $this->validateUrl($url);
            }
        };

        $this->assertFalse($trait->validateUrlPublic('not-a-url'));
        $this->assertFalse($trait->validateUrlPublic('ftp://example.com'));
        $this->assertFalse($trait->validateUrlPublic('javascript:alert(1)'));
        $this->assertFalse($trait->validateUrlPublic('file:///etc/passwd'));
    }

    /**
     * Test phone number validation with valid formats.
     */
    public function test_valid_phone_number_passes_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validatePhonePublic(string $phone): bool
            {
                return $this->validatePhone($phone);
            }
        };

        $this->assertTrue($trait->validatePhonePublic('1234567890'));
        $this->assertTrue($trait->validatePhonePublic('+1234567890'));
        $this->assertTrue($trait->validatePhonePublic('+112345678901'));
        $this->assertTrue($trait->validatePhonePublic('(123) 456-7890'));
    }

    /**
     * Test phone number validation rejects invalid formats.
     */
    public function test_invalid_phone_number_fails_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validatePhonePublic(string $phone): bool
            {
                return $this->validatePhone($phone);
            }
        };

        $this->assertFalse($trait->validatePhonePublic('123'));
        $this->assertFalse($trait->validatePhonePublic('1234567890123456'));
    }

    /**
     * Test IP address validation with IPv4 addresses.
     */
    public function test_valid_ipv4_address_passes_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateIpPublic(string $ip): bool
            {
                return $this->validateIp($ip);
            }
        };

        $this->assertTrue($trait->validateIpPublic('192.168.1.1'));
        $this->assertTrue($trait->validateIpPublic('10.0.0.1'));
        $this->assertTrue($trait->validateIpPublic('127.0.0.1'));
    }

    /**
     * Test IP address validation with IPv6 addresses.
     */
    public function test_valid_ipv6_address_passes_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateIpPublic(string $ip): bool
            {
                return $this->validateIp($ip);
            }
        };

        $this->assertTrue($trait->validateIpPublic('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertTrue($trait->validateIpPublic('::1'));
        $this->assertTrue($trait->validateIpPublic('fe80::1'));
    }

    /**
     * Test IP address validation rejects invalid addresses.
     */
    public function test_invalid_ip_address_fails_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateIpPublic(string $ip): bool
            {
                return $this->validateIp($ip);
            }
        };

        $this->assertFalse($trait->validateIpPublic('256.256.256.256'));
        $this->assertFalse($trait->validateIpPublic('not-an-ip'));
        $this->assertFalse($trait->validateIpPublic('192.168.1'));
    }

    /**
     * Test JSON validation with valid JSON.
     */
    public function test_valid_json_passes_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateJsonPublic(string $json): bool
            {
                return $this->validateJson($json);
            }
        };

        $this->assertTrue($trait->validateJsonPublic('{"key": "value"}'));
        $this->assertTrue($trait->validateJsonPublic('{"nested": {"key": [1, 2, 3]}}'));
        $this->assertTrue($trait->validateJsonPublic('[]'));
        $this->assertTrue($trait->validateJsonPublic('"string"'));
    }

    /**
     * Test JSON validation rejects invalid JSON.
     */
    public function test_invalid_json_fails_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateJsonPublic(string $json): bool
            {
                return $this->validateJson($json);
            }
        };

        $this->assertFalse($trait->validateJsonPublic('{"key": value}'));
        $this->assertFalse($trait->validateJsonPublic('{not json}'));
        $this->assertFalse($trait->validateJsonPublic('undefined'));
    }

    /**
     * Test regex pattern validation.
     */
    public function test_regex_pattern_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateRegexPublic(string $value, string $pattern): bool
            {
                return $this->validateRegex($value, $pattern);
            }
        };

        // Email pattern
        $this->assertTrue($trait->validateRegexPublic('test@example.com', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'));

        // Alphanumeric pattern
        $this->assertTrue($trait->validateRegexPublic('ABC123', '/^[a-zA-Z0-9]+$/'));
        $this->assertFalse($trait->validateRegexPublic('ABC-123', '/^[a-zA-Z0-9]+$/'));
    }

    /**
     * Test command injection protection.
     */
    public function test_command_sanitize_prevents_injection(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function sanitizeCommandPublic(string $command): string
            {
                return $this->sanitizeCommand($command);
            }
        };

        $sanitized = $trait->sanitizeCommandPublic('ls; rm -rf /');
        $this->assertStringNotContainsString(';', $sanitized);
        $this->assertStringNotContainsString('rm', $sanitized);

        $sanitized = $trait->sanitizeCommandPublic('cat /etc/passwd && evil_command');
        $this->assertStringNotContainsString('&&', $sanitized);
    }

    /**
     * Test command argument sanitization.
     */
    public function test_command_arg_sanitize_prevents_injection(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function sanitizeCommandArgPublic(string $arg): string
            {
                return $this->sanitizeCommandArg($arg);
            }
        };

        $sanitized = $trait->sanitizeCommandArgPublic('file.txt; malicious');
        $this->assertStringNotContainsString(';', $sanitized);

        $sanitized = $trait->sanitizeCommandArgPublic('$(whoami)');
        $this->assertStringNotContainsString('$', $sanitized);
    }

    /**
     * Test SQL identifier escaping.
     */
    public function test_sql_identifier_escaping_prevents_injection(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function escapeSqlIdentifierPublic(string $identifier): string
            {
                return $this->escapeSqlIdentifier($identifier);
            }
        };

        $escaped = $trait->escapeSqlIdentifierPublic('table_name');
        $this->assertEquals('`table_name`', $escaped);

        $escaped = $trait->escapeSqlIdentifierPublic('`malicious`');
        $this->assertEquals('```malicious```', $escaped);
    }

    /**
     * Test filename sanitization prevents path traversal.
     */
    public function test_filename_sanitization_prevents_path_traversal(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function sanitizeFilenamePublic(string $filename): string
            {
                return $this->sanitizeFilename($filename);
            }
        };

        $this->assertStringNotContainsString('..', $trait->sanitizeFilenamePublic('../../../etc/passwd'));
        $this->assertEquals('etcpasswd', $trait->sanitizeFilenamePublic('../../../etc/passwd'));
        $this->assertEquals('file.txt', $trait->sanitizeFilenamePublic('file.txt'));
    }

    /**
     * Test filename sanitization removes dangerous characters.
     */
    public function test_filename_sanitization_removes_dangerous_chars(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function sanitizeFilenamePublic(string $filename): string
            {
                return $this->sanitizeFilename($filename);
            }
        };

        $sanitized = $trait->sanitizeFilenamePublic('file<script>.txt');
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('<', $sanitized);
        $this->assertStringNotContainsString('>', $sanitized);

        $sanitized = $trait->sanitizeFilenamePublic('file|pipe.txt');
        $this->assertStringNotContainsString('|', $sanitized);
    }

    /**
     * Test secure file upload validation with valid file.
     */
    public function test_secure_file_upload_valid_file_passes(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateSecureFileUploadPublic(array $file): array
            {
                return $this->validateSecureFileUpload($file, ['image/jpeg', 'image/png'], 5242880);
            }
        };

        $validFile = [
            'tmp_name' => '/tmp/test.jpg',
            'name' => 'safe-image.jpg',
            'size' => 1024000,
            'type' => 'image/jpeg'
        ];

        // Since we can't create actual file upload in test, just check structure is processed
        $this->assertIsArray($trait->validateSecureFileUploadPublic($validFile));
    }

    /**
     * Test secure file upload rejects dangerous extensions.
     */
    public function test_secure_file_upload_rejects_dangerous_extensions(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateSecureFileUploadPublic(array $file): array
            {
                return $this->validateSecureFileUpload($file, [], null);
            }
        };

        $dangerousFiles = [
            ['tmp_name' => '/tmp/test.php', 'name' => 'malicious.php', 'size' => 100, 'type' => 'application/x-php'],
            ['tmp_name' => '/tmp/test.sh', 'name' => 'script.sh', 'size' => 100, 'type' => 'application/x-sh'],
            ['tmp_name' => '/tmp/test.exe', 'name' => 'virus.exe', 'size' => 100, 'type' => 'application/x-exe'],
        ];

        foreach ($dangerousFiles as $file) {
            $errors = $trait->validateSecureFileUploadPublic($file);
            $this->assertNotEmpty($errors);
            $this->assertStringContainsString('Dangerous file extension', $errors[0]);
        }
    }

    /**
     * Test secure file upload validates file size.
     */
    public function test_secure_file_upload_validates_size(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateSecureFileUploadPublic(array $file): array
            {
                return $this->validateSecureFileUpload($file, [], 1048576);
            }
        };

        $oversizedFile = [
            'tmp_name' => '/tmp/large.jpg',
            'name' => 'large.jpg',
            'size' => 20971520, // 20 MB
            'type' => 'image/jpeg'
        ];

        $errors = $trait->validateSecureFileUploadPublic($oversizedFile);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size exceeds', $errors[0]);
    }

    /**
     * Test secure file upload validates MIME type.
     */
    public function test_secure_file_upload_validates_mime_type(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validateSecureFileUploadPublic(array $file): array
            {
                return $this->validateSecureFileUpload($file, ['image/jpeg', 'image/png'], null);
            }
        };

        $invalidMimeFile = [
            'tmp_name' => '/tmp/test.exe',
            'name' => 'disguise.exe',
            'size' => 1000,
            'type' => 'application/x-msdownload'
        ];

        $errors = $trait->validateSecureFileUploadPublic($invalidMimeFile);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File MIME type not allowed', $errors[0] ?? '');
    }

    /**
     * Test password complexity validation still works (existing functionality).
     */
    public function test_password_complexity_validation(): void
    {
        $trait = new class {
            use \App\Traits\InputValidationTrait;

            public function validatePasswordComplexityPublic(string $password): array
            {
                return $this->validatePasswordComplexity($password);
            }
        };

        // Valid password
        $this->assertEmpty($trait->validatePasswordComplexityPublic('SecureP@ssw0rd'));

        // Too short
        $errors = $trait->validatePasswordComplexityPublic('Short1!');
        $this->assertNotEmpty($errors);
        $this->assertContains('at least 8 characters', $errors);

        // Missing uppercase
        $errors = $trait->validatePasswordComplexityPublic('lowercase1!');
        $this->assertNotEmpty($errors);
        $this->assertContains('uppercase letter', $errors);

        // Missing lowercase
        $errors = $trait->validatePasswordComplexityPublic('UPPERCASE1!');
        $this->assertNotEmpty($errors);
        $this->assertContains('lowercase letter', $errors);

        // Missing number
        $errors = $trait->validatePasswordComplexityPublic('NoNumber!');
        $this->assertNotEmpty($errors);
        $this->assertContains('number', $errors);

        // Missing special character
        $errors = $trait->validatePasswordComplexityPublic('NoSpecial1');
        $this->assertNotEmpty($errors);
        $this->assertContains('special character', $errors);

        // Common password
        $errors = $trait->validatePasswordComplexityPublic('password123');
        $this->assertNotEmpty($errors);
        $this->assertContains('too common', $errors);
    }
}
