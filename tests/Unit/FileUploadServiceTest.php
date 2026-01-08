<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FileUploadService;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @internal
 * @coversNothing
 */
class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileUploadService();
    }

    public function testValidateValidJpegImageReturnsEmptyErrors()
    {
        $file = $this->createMockUploadedFile(
            'test.jpg',
            'image/jpeg',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testValidateValidPngImageReturnsEmptyErrors()
    {
        $file = $this->createMockUploadedFile(
            'test.png',
            'image/png',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateValidPdfDocumentReturnsEmptyErrors()
    {
        $file = $this->createMockUploadedFile(
            'document.pdf',
            'application/pdf',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateFileExceedingMaxSizeReturnsError()
    {
        $file = $this->createMockUploadedFile(
            'large.jpg',
            'image/jpeg',
            UPLOAD_ERR_OK,
            6000000
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File size', $errors[0]);
        $this->assertStringContainsString('5MB', $errors[0]);
    }

    public function testValidateDisallowedMimeTypeReturnsError()
    {
        $file = $this->createMockUploadedFile(
            'test.exe',
            'application/octet-stream',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateUploadErrorReturnsErrorMessage()
    {
        $file = $this->createMockUploadedFile(
            'test.jpg',
            'image/jpeg',
            UPLOAD_ERR_INI_SIZE,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('exceeds upload_max_filesize', $errors[0]);
    }

    public function testValidateNoFileUploadedReturnsError()
    {
        $file = $this->createMockUploadedFile(
            '',
            '',
            UPLOAD_ERR_NO_FILE,
            0
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('No file was uploaded', $errors[0]);
    }

    public function testValidateInvalidExtensionReturnsError()
    {
        $file = $this->createMockUploadedFile(
            'test.exe',
            'image/jpeg',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File extension not allowed', $errors[0]);
    }

    public function testSanitizeFilenameRemovesDangerousCharacters()
    {
        $filename = '../../../etc/passwd';
        $sanitized = $this->service->sanitizeFilename($filename);

        $this->assertEquals('etc_passwd', $sanitized);
    }

    public function testSanitizeFilenamePreservesAllowedCharacters()
    {
        $filename = 'my-document_file.jpg';
        $sanitized = $this->service->sanitizeFilename($filename);

        $this->assertEquals('my-document_file.jpg', $sanitized);
    }

    public function testSanitizeFilenameHandlesSpecialCharacters()
    {
        $filename = 'file@#$%^&*().jpg';
        $sanitized = $this->service->sanitizeFilename($filename);

        $this->assertEquals('file_______jpg', $sanitized);
    }

    public function testSanitizeFilenameReturnsDefaultForEmptyResult()
    {
        $filename = '...';
        $sanitized = $this->service->sanitizeFilename($filename);

        $this->assertEquals('unnamed_file', $sanitized);
    }

    public function testSanitizeFilenameHandlesOnlyDots()
    {
        $filename = '.....';
        $sanitized = $this->service->sanitizeFilename($filename);

        $this->assertEquals('unnamed_file', $sanitized);
    }

    public function testSetMaxSizeUpdatesLimit()
    {
        $this->service->setMaxFileSize(10000000);

        $file = $this->createMockUploadedFile(
            'large.jpg',
            'image/jpeg',
            UPLOAD_ERR_OK,
            6000000
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testAddAllowedMimeTypeEnablesNewType()
    {
        $this->service->addAllowedMimeType('application/epub+zip');

        $file = $this->createMockUploadedFile(
            'book.epub',
            'application/epub+zip',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testRemoveAllowedMimeTypeDisablesType()
    {
        $this->service->removeAllowedMimeType('application/pdf');

        $file = $this->createMockUploadedFile(
            'document.pdf',
            'application/pdf',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File type not allowed', $errors[0]);
    }

    public function testValidateWordDocumentSucceeds()
    {
        $file = $this->createMockUploadedFile(
            'document.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateExcelDocumentSucceeds()
    {
        $file = $this->createMockUploadedFile(
            'spreadsheet.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateTextFileSucceeds()
    {
        $file = $this->createMockUploadedFile(
            'notes.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateSvgImageSucceeds()
    {
        $file = $this->createMockUploadedFile(
            'image.svg',
            'image/svg+xml',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateZipFileSucceeds()
    {
        $file = $this->createMockUploadedFile(
            'archive.zip',
            'application/zip',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    public function testValidateExactlyMaxSizePasses()
    {
        $this->service->setMaxFileSize(1024);

        $file = $this->createMockUploadedFile(
            'exact.jpg',
            'image/jpeg',
            UPLOAD_ERR_OK,
            1024
        );

        $errors = $this->service->validateFile($file);

        $this->assertEmpty($errors);
    }

    private function createMockUploadedFile(
        string $clientFilename,
        string $clientMediaType,
        int $error,
        int $size
    ): UploadedFileInterface {
        $mock = Mockery::mock(UploadedFileInterface::class);
        $mock->shouldReceive('getClientFilename')->andReturn($clientFilename);
        $mock->shouldReceive('getClientMediaType')->andReturn($clientMediaType);
        $mock->shouldReceive('getError')->andReturn($error);
        $mock->shouldReceive('getSize')->andReturn($size);

        return $mock;
    }
}
