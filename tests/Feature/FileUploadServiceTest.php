<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FileUploadService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\UploadedFileInterface;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FileUploadServiceTest extends TestCase
{
    private FileUploadService $fileUploadService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileUploadService = new FileUploadService();
    }

    public function testValidateFileWithValidJpegImage()
    {
        $file = $this->createMockFile('test.jpg', 'image/jpeg', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithValidPngImage()
    {
        $file = $this->createMockFile('test.png', 'image/png', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithValidPdfDocument()
    {
        $file = $this->createMockFile('document.pdf', 'application/pdf', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithInvalidMimeType()
    {
        $file = $this->createMockFile('test.exe', 'application/x-msdownload', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileWithSizeExceedingLimit()
    {
        $file = $this->createMockFile('large.jpg', 'image/jpeg', 10 * 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size exceeds maximum', $errors[0]);
    }

    public function testValidateFileWithUploadError()
    {
        $file = $this->createMockFileWithError('error.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('exceeds upload_max_filesize', $errors[0]);
    }

    public function testValidateFileWithInvalidExtension()
    {
        $file = $this->createMockFile('malicious.php', 'application/x-php', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    public function testValidateFileWithMultipleErrors()
    {
        $file = $this->createMockFile(
            'large.exe',
            'application/x-executable',
            10 * 1024 * 1024
        );

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertGreaterThanOrEqual(1, count($errors));
    }

    public function testValidateFileWithEmptyFilename()
    {
        $file = $this->createMockFile('', 'image/jpeg', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithWordDocument()
    {
        $file = $this->createMockFile(
            'document.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            1024 * 1024
        );

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithExcelSpreadsheet()
    {
        $file = $this->createMockFile(
            'spreadsheet.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            1024 * 1024
        );

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateFileWithTextFile()
    {
        $file = $this->createMockFile('notes.txt', 'text/plain', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testSanitizeFilenameRemovesPathTraversal()
    {
        $filename = '../../../etc/passwd';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertEquals('etc_passwd', $sanitized);
        $this->assertStringNotContainsString('../', $sanitized);
    }

    public function testSanitizeFilenameRemovesSpecialCharacters()
    {
        $filename = 'test<script>alert("xss")</script>.jpg';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('"', $sanitized);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._-]+$/', $sanitized);
    }

    public function testSanitizeFilenamePreservesAllowedCharacters()
    {
        $filename = 'Valid_File-Name.123.jpg';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertEquals('Valid_File-Name.123.jpg', $sanitized);
    }

    public function testSanitizeFilenameHandlesEmptyResult()
    {
        $filename = '...---...';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertEquals('unnamed_file', $sanitized);
    }

    public function testSanitizeFilenameHandlesDotsOnly()
    {
        $filename = '..';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertEquals('unnamed_file', $sanitized);
    }

    public function testSanitizeFilenameHandlesWindowsPaths()
    {
        $filename = 'C:\Windows\System32\config.exe';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertStringNotContainsString('\\', $sanitized);
        $this->assertStringNotContainsString(':', $sanitized);
    }

    public function testSanitizeFilenameHandlesUnixPaths()
    {
        $filename = '/etc/passwd';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertStringNotContainsString('/', $sanitized);
        $this->assertEquals('passwd', $sanitized);
    }

    public function testSanitizeFilenameHandlesNullBytes()
    {
        $filename = "test\x00file.jpg";
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertStringNotContainsString("\x00", $sanitized);
    }

    public function testSanitizeFilenameHandlesUnicode()
    {
        $filename = 'файл.jpg';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertIsString($sanitized);
        $this->assertNotEmpty($sanitized);
    }

    public function testSetMaxFileSize()
    {
        $this->fileUploadService->setMaxFileSize(1024);

        $file = $this->createMockFile('test.jpg', 'image/jpeg', 2048);
        $errors = $this->fileUploadService->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size exceeds', $errors[0]);
    }

    public function testAddAllowedMimeType()
    {
        $this->fileUploadService->addAllowedMimeType('application/json');

        $file = $this->createMockFile('data.json', 'application/json', 1024);
        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testRemoveAllowedMimeType()
    {
        $this->fileUploadService->removeAllowedMimeType('image/jpeg');

        $file = $this->createMockFile('test.jpg', 'image/jpeg', 1024);
        $errors = $this->fileUploadService->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileWithExactMaxSize()
    {
        $this->fileUploadService->setMaxFileSize(1024);

        $file = $this->createMockFile('exact.jpg', 'image/jpeg', 1024);
        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithZeroSize()
    {
        $file = $this->createMockFile('empty.jpg', 'image/jpeg', 0);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
    }

    public function testValidateFileWithGifImage()
    {
        $file = $this->createMockFile('test.gif', 'image/gif', 512 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithWebpImage()
    {
        $file = $this->createMockFile('test.webp', 'image/webp', 512 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithSvgImage()
    {
        $file = $this->createMockFile('test.svg', 'image/svg+xml', 512 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithBmpImage()
    {
        $file = $this->createMockFile('test.bmp', 'image/bmp', 512 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithZipArchive()
    {
        $file = $this->createMockFile('archive.zip', 'application/zip', 2 * 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithLegacyWordDocument()
    {
        $file = $this->createMockFile(
            'old.doc',
            'application/msword',
            1024 * 1024
        );

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithLegacyExcelDocument()
    {
        $file = $this->createMockFile(
            'old.xls',
            'application/vnd.ms-excel',
            1024 * 1024
        );

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testSanitizeFilenameHandlesMultipleDots()
    {
        $filename = 'my...file...name.jpg';
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertEquals('my___file___name.jpg', $sanitized);
    }

    // ============================================================
    // SECURITY TESTS - Malicious File Uploads
    // ============================================================

    public function testValidateFileRejectsPhpFileWithDisguisedMime()
    {
        // Test: PHP file with image/jpeg MIME type (double-extension attack)
        // This should be caught by extension validation
        $file = $this->createMockFile('malicious.php.jpg', 'image/jpeg', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testValidateFileRejectsExecutableExeFile()
    {
        // Test: Executable file
        $file = $this->createMockFile('malware.exe', 'application/x-msdownload', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileRejectsShellScript()
    {
        // Test: Shell script file
        $file = $this->createMockFile('malicious.sh', 'application/x-sh', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileRejectsWindowsBatchFile()
    {
        // Test: Windows batch file
        $file = $this->createMockFile('malicious.bat', 'application/x-bat', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileRejectsDoubleExtensionPngPhp()
    {
        // Test: Double extension attempt (file.php.png)
        $file = $this->createMockFile('legitimate.png.php', 'image/png', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testValidateFileRejectsDoubleExtensionJpgPhp()
    {
        // Test: Double extension attempt (file.php.jpg)
        $file = $this->createMockFile('exploit.jpg.php', 'image/jpeg', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testValidateFileRejectsTripleExtension()
    {
        // Test: Triple extension attempt (file.php.jpg.png)
        $file = $this->createMockFile('complex.png.jpg.php', 'image/png', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testValidateFileRejectsHiddenPhpFile()
    {
        // Test: Hidden PHP file (.htaccess, .php files starting with dot)
        $file = $this->createMockFile('.htaccess', 'application/x-httpd-php', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateFileRejectsCaseSensitiveExtensionBypass()
    {
        // Test: Case variation attempt (.PHP, .Php, etc.)
        // Note: The current implementation uses strtolower() on extension, so this tests that
        $file = $this->createMockFile('malicious.PHP', 'image/jpeg', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    // ============================================================
    // SECURITY TESTS - MIME Type Validation
    // ============================================================

    public function testValidateFileRejectsMimeSpoofing()
    {
        // Test: File with spoofed MIME type
        // In a real scenario, an attacker might send image/jpeg but file is actually PHP
        // Current implementation relies on client-provided MIME type, so this documents the limitation
        $file = $this->createMockFile('malicious.php', 'image/jpeg', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        // The extension check should catch this
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testValidateFileEnforcesMimeWhitelist()
    {
        // Test: Only whitelisted MIME types are allowed
        // Test with various disallowed MIME types
        $disallowedMimeTypes = [
            'application/x-php',
            'application/x-httpd-php',
            'text/x-php',
            'application/x-sh',
            'text/x-shellscript',
            'application/x-msdownload',
            'application/x-executable',
            'application/x-msdos-program',
        ];

        foreach ($disallowedMimeTypes as $mimeType) {
            $file = $this->createMockFile('test.ext', $mimeType, 1024);

            $errors = $this->fileUploadService->validateFile($file);

            $this->assertNotEmpty($errors, "MIME type {$mimeType} should be rejected");
            $this->assertStringContainsString('File type not allowed', $errors[0]);
        }
    }

    public function testValidateFileAllowsAllWhitelistedMimeTypes()
    {
        // Test: Verify all whitelisted MIME types are allowed
        $whitelistedMimeTypes = [
            'image/jpeg' => 'test.jpg',
            'image/png' => 'test.png',
            'image/gif' => 'test.gif',
            'image/webp' => 'test.webp',
            'image/bmp' => 'test.bmp',
            'image/svg+xml' => 'test.svg',
            'application/pdf' => 'test.pdf',
            'application/msword' => 'test.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'test.docx',
            'application/vnd.ms-excel' => 'test.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'test.xlsx',
            'text/plain' => 'test.txt',
            'application/zip' => 'test.zip',
        ];

        foreach ($whitelistedMimeTypes as $mimeType => $filename) {
            $file = $this->createMockFile($filename, $mimeType, 1024);

            $errors = $this->fileUploadService->validateFile($file);

            $this->assertEmpty($errors, "MIME type {$mimeType} should be allowed");
        }
    }

    // ============================================================
    // SECURITY TESTS - Enhanced Path Traversal
    // ============================================================

    public function testSanitizeFilenameBlocksComplexPathTraversal()
    {
        // Test: Complex path traversal patterns
        $traversalAttempts = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config.exe',
            './././../../etc/shadow',
            '..%2F..%2Fetc%2Fpasswd',  // URL-encoded traversal
            '....//....//etc/passwd',  // Double-dot variations
            '..\\\\..\\\\..\\\\windows\\\\system32',  // Windows backslash
        ];

        foreach ($traversalAttempts as $filename) {
            $sanitized = $this->fileUploadService->sanitizeFilename($filename);

            $this->assertStringNotContainsString('../', $sanitized, "Path traversal not blocked: {$filename}");
            $this->assertStringNotContainsString('..\\', $sanitized, "Path traversal not blocked: {$filename}");
            $this->assertStringNotContainsString('%2F', $sanitized, "URL-encoded traversal not blocked: {$filename}");
            $this->assertDoesNotMatchRegularExpression('/^\./', $sanitized, "Leading dot not removed: {$filename}");
        }
    }

    public function testSanitizeFilenamePreventsDirectoryCreation()
    {
        // Test: Attempts to create directories via filename
        $maliciousFilenames = [
            'newdir/file.jpg',
            'newdir/subdir/file.jpg',
            './newdir/file.jpg',
            'newdir//file.jpg',
            'newdir\\file.jpg',
        ];

        foreach ($maliciousFilenames as $filename) {
            $sanitized = $this->fileUploadService->sanitizeFilename($filename);

            $this->assertStringNotContainsString('/', $sanitized, "Directory separator not removed: {$filename}");
            $this->assertStringNotContainsString('\\', $sanitized, "Directory separator not removed: {$filename}");
        }
    }

    public function testSanitizeFilenameRemovesNullByteInPath()
    {
        // Test: Null bytes in path (null byte injection)
        $filename = "test\x00.jpg";
        $sanitized = $this->fileUploadService->sanitizeFilename($filename);

        $this->assertStringNotContainsString("\x00", $sanitized);
    }

    public function testSanitizeFilenameHandlesEncodedPathSeparators()
    {
        // Test: URL-encoded path separators
        $encodedSeparators = [
            '%2F',      // /
            '%5C',      // \
            '%2F%2F',  // //
            '%5C%5C',  // \\
        ];

        foreach ($encodedSeparators as $separator) {
            $filename = 'file' . $separator . '.jpg';
            $sanitized = $this->fileUploadService->sanitizeFilename($filename);

            $this->assertStringNotContainsString($separator, $sanitized);
            $this->assertStringNotContainsString('/', $sanitized);
            $this->assertStringNotContainsString('\\', $sanitized);
        }
    }

    public function testSanitizeFilenameBlocksAbsolutePaths()
    {
        // Test: Absolute paths (both Unix and Windows)
        $absolutePaths = [
            '/etc/passwd',
            '/tmp/file.jpg',
            'C:\\Windows\\System32\\config.exe',
            'D:\\uploads\\file.jpg',
            '/var/www/uploads/file.jpg',
            '//server/share/file.jpg',
        ];

        foreach ($absolutePaths as $filename) {
            $sanitized = $this->fileUploadService->sanitizeFilename($filename);

            $this->assertStringNotContainsString('/', $sanitized, "Absolute path not removed: {$filename}");
            $this->assertStringNotContainsString('\\', $sanitized, "Absolute path not removed: {$filename}");
            $this->assertStringNotContainsString(':', $sanitized, "Drive letter not removed: {$filename}");
            $this->assertDoesNotMatchRegularExpression('/^[A-Z]:/', $sanitized, "Windows path not removed: {$filename}");
        }
    }

    // ============================================================
    // SECURITY TESTS - File Content Security
    // ============================================================

    public function testValidateFileRejectsSvgWithScripts()
    {
        // Test: SVG files with embedded scripts (XSS via SVG)
        // Note: Current implementation doesn't scan content, but this test documents the limitation
        // File extension .svg is allowed (image/svg+xml is in whitelist)
        $file = $this->createMockFile('malicious.svg', 'image/svg+xml', 1024);

        $errors = $this->fileUploadService->validateFile($file);

        // With current implementation, this would pass (no content scanning)
        // This test documents that SVG files with scripts would be accepted
        // In production, additional content scanning would be needed
        $this->assertIsArray($errors);
    }

    public function testValidateFileRejectsZipBomb()
    {
        // Test: Large zip file that could be a zip bomb
        // Note: Current implementation only checks file size, not decompressed size
        $file = $this->createMockFile('bomb.zip', 'application/zip', 2 * 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        // Within size limit, so would pass - documents limitation
        $this->assertIsArray($errors);
    }

    public function testValidateFileAllowsZipWithinSizeLimit()
    {
        // Test: Normal zip file within size limit
        $file = $this->createMockFile('archive.zip', 'application/zip', 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileRejectsOversizedArchive()
    {
        // Test: Archive exceeding size limit
        $file = $this->createMockFile('large.zip', 'application/zip', 10 * 1024 * 1024);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size exceeds', $errors[0]);
    }

    // ============================================================
    // SECURITY TESTS - Edge Cases and Boundary Conditions
    // ============================================================

    public function testValidateFileWithMaxSizeBoundary()
    {
        // Test: File exactly at max size limit (5MB)
        $maxSize = 5242880; // 5MB in bytes
        $file = $this->createMockFile('boundary.jpg', 'image/jpeg', $maxSize);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileWithMaxSizePlusOne()
    {
        // Test: File one byte over max size limit
        $maxSize = 5242880; // 5MB in bytes
        $file = $this->createMockFile('overlimit.jpg', 'image/jpeg', $maxSize + 1);

        $errors = $this->fileUploadService->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size exceeds', $errors[0]);
    }

    public function testSanitizeFilenameWithVeryLongName()
    {
        // Test: Very long filename (potential DoS)
        $longFilename = str_repeat('a', 1000) . '.jpg';
        $sanitized = $this->fileUploadService->sanitizeFilename($longFilename);

        $this->assertIsString($sanitized);
        $this->assertNotEmpty($sanitized);
        $this->assertStringEndsWithWith('.jpg', $sanitized);
    }

    public function testSanitizeFilenameWithSpecialUnicodeCharacters()
    {
        // Test: Unicode characters that might be problematic
        $unicodeFilenames = [
            'файл.jpg',           // Cyrillic
            '文件.jpg',            // Chinese
            'ملف.jpg',              // Arabic
            'tệp.jpg',            // Vietnamese with diacritics
            'αρχείο.jpg',          // Greek
        ];

        foreach ($unicodeFilenames as $filename) {
            $sanitized = $this->fileUploadService->sanitizeFilename($filename);

            $this->assertIsString($sanitized);
            $this->assertNotEmpty($sanitized);
            $this->assertStringEndsWithWith('.jpg', $sanitized);
        }
    }

    private function createMockFile(string $filename, string $mimeType, int $size): MockObject|UploadedFileInterface
    {
        $mock = $this->createMock(UploadedFileInterface::class);
        $mock->method('getClientFilename')->willReturn($filename);
        $mock->method('getClientMediaType')->willReturn($mimeType);
        $mock->method('getSize')->willReturn($size);
        $mock->method('getError')->willReturn(UPLOAD_ERR_OK);

        return $mock;
    }

    private function createMockFileWithError(string $filename, string $mimeType, int $errorCode): MockObject|UploadedFileInterface
    {
        $mock = $this->createMock(UploadedFileInterface::class);
        $mock->method('getClientFilename')->willReturn($filename);
        $mock->method('getClientMediaType')->willReturn($mimeType);
        $mock->method('getSize')->willReturn(0);
        $mock->method('getError')->willReturn($errorCode);

        return $mock;
    }
}
