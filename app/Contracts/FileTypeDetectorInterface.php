<?php

declare(strict_types=1);

namespace App\Contracts;

interface FileTypeDetectorInterface
{
    public static function getMimeType(string $filePath): ?string;

    public static function getExtension(string $mimeType): ?string;

    public static function detectByMagicNumber(string $filePath): ?string;

    public static function isAllowedMimeType(string $mimeType, array $allowedTypes): bool;

    public static function isImage(string $filePath): bool;

    public static function isPdf(string $filePath): bool;

    public static function isDocument(string $filePath): bool;

    public static function validateExtension(string $filename, array $allowedExtensions): bool;

    public static function sanitizeFilename(string $filename): string;

    public static function generateSafeFilename(string $originalName): string;
}
