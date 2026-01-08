<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FileUploadServiceInterface;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

class FileUploadService implements FileUploadServiceInterface
{
    private array $allowedMimeTypes = [
        // Images
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/svg+xml',
        
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'application/zip',
    ];
    
    private int $maxFileSize = 5242880; // 5MB in bytes

    /**
     * Validate an uploaded file.
     */
    public function validateFile(UploadedFileInterface $file): array
    {
        $errors = [];

        // Check for upload errors
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errors[] = $this->getUploadErrorMessage($file->getError());
            return $errors;
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . ($this->maxFileSize / 1024 / 1024) . 'MB';
        }

        // Check file type
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->getAllowedExtensions());
        }

        // Check file extension based on MIME type
        $clientFilename = $file->getClientFilename();
        if ($clientFilename) {
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = $this->getAllowedExtensions();
            
            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = 'File extension not allowed. Allowed extensions: ' . implode(', ', $allowedExtensions);
            }
        }

        return $errors;
    }

    /**
     * Get allowed file extensions based on MIME types.
     */
    private function getAllowedExtensions(): array
    {
        $extensions = [];
        
        foreach ($this->allowedMimeTypes as $mimeType) {
            switch ($mimeType) {
                case 'image/jpeg':
                    $extensions[] = 'jpg';
                    $extensions[] = 'jpeg';
                    break;
                case 'image/png':
                    $extensions[] = 'png';
                    break;
                case 'image/gif':
                    $extensions[] = 'gif';
                    break;
                case 'image/webp':
                    $extensions[] = 'webp';
                    break;
                case 'image/bmp':
                    $extensions[] = 'bmp';
                    break;
                case 'image/svg+xml':
                    $extensions[] = 'svg';
                    break;
                case 'application/pdf':
                    $extensions[] = 'pdf';
                    break;
                case 'application/msword':
                    $extensions[] = 'doc';
                    break;
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    $extensions[] = 'docx';
                    break;
                case 'application/vnd.ms-excel':
                    $extensions[] = 'xls';
                    break;
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    $extensions[] = 'xlsx';
                    break;
                case 'text/plain':
                    $extensions[] = 'txt';
                    break;
                case 'application/zip':
                    $extensions[] = 'zip';
                    break;
            }
        }
        
        return array_unique($extensions);
    }

    /**
     * Get human-readable error message for upload error code.
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive in form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Sanitize filename to prevent directory traversal and other attacks.
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove path information to prevent directory traversal
        $filename = basename($filename);
        
        // Remove potentially dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Ensure the filename is not empty after sanitization
        if (empty($filename) || $filename === '.') {
            $filename = 'unnamed_file';
        }
        
        return $filename;
    }

    /**
     * Set maximum file size in bytes.
     */
    public function setMaxFileSize(int $maxSize): void
    {
        $this->maxFileSize = $maxSize;
    }

    /**
     * Add allowed MIME type.
     */
    public function addAllowedMimeType(string $mimeType): void
    {
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $this->allowedMimeTypes[] = $mimeType;
        }
    }

    /**
     * Remove allowed MIME type.
     */
    public function removeAllowedMimeType(string $mimeType): void
    {
        $key = array_search($mimeType, $this->allowedMimeTypes);
        if ($key !== false) {
            unset($this->allowedMimeTypes[$key]);
        }
    }
}