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
