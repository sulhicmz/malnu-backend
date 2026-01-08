<?php

declare(strict_types=1);

namespace App\Contracts;

use Psr\Http\Message\UploadedFileInterface;

interface FileUploadServiceInterface
{
    public function validateFile(UploadedFileInterface $file): array;

    public function sanitizeFilename(string $filename): string;

    public function setMaxFileSize(int $maxSize): void;

    public function addAllowedMimeType(string $mimeType): void;

    public function removeAllowedMimeType(string $mimeType): void;
}
