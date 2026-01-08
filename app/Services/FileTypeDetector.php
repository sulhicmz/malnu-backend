<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FileTypeDetectorInterface;

class FileTypeDetector implements FileTypeDetectorInterface
{
    private static array $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
    ];

    private static array $magicNumbers = [
        'ffd8ff' => ['jpeg', 'jpg'],
        '89504e47' => ['png'],
        '47494638' => ['gif'],
        '52494646' => ['webp'],
        '25504446' => ['pdf'],
        'd0cf11e0' => ['doc', 'xls', 'ppt'],
        '504b0304' => ['docx', 'xlsx', 'pptx', 'zip'],
        '48545450' => ['html', 'htm'],
        '3c3f786d' => ['xml'],
    ];

    public static function getMimeType(string $filePath): ?string
    {
        if (! file_exists($filePath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mimeType;
    }

    public static function getExtension(string $mimeType): ?string
    {
        foreach (self::$mimeTypes as $ext => $type) {
            if ($type === $mimeType) {
                return $ext;
            }
        }

        return null;
    }

    public static function detectByMagicNumber(string $filePath): ?string
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return null;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return null;
        }

        $bytes = fread($handle, 8);
        fclose($handle);

        if (strlen($bytes) < 8) {
            return null;
        }

        $hex = bin2hex($bytes);

        foreach (self::$magicNumbers as $magic => $extensions) {
            if (str_starts_with($hex, $magic)) {
                return $extensions[0];
            }
        }

        return null;
    }

    public static function isAllowedMimeType(string $mimeType, array $allowedTypes): bool
    {
        return in_array(strtolower($mimeType), array_map('strtolower', $allowedTypes), true);
    }

    public static function isImage(string $filePath): bool
    {
        $mime = self::getMimeType($filePath);

        return str_starts_with($mime, 'image/');
    }

    public static function isPdf(string $filePath): bool
    {
        $mime = self::getMimeType($filePath);

        return $mime === 'application/pdf';
    }

    public static function isDocument(string $filePath): bool
    {
        $mime = self::getMimeType($filePath);

        return in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ], true);
    }

    public static function validateExtension(string $filename, array $allowedExtensions): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, array_map('strtolower', $allowedExtensions), true);
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        $filename = trim($filename, '_');

        if (empty($filename)) {
            $filename = 'file_' . time();
        }

        return $filename;
    }

    public static function generateSafeFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(16));

        return $extension ? $safeName . '.' . $extension : $safeName;
    }
}
