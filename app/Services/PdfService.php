<?php

declare (strict_types = 1);

namespace App\Services;

class PdfService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = BASE_PATH . '/storage/reports/';
    }

    public function generateFromHtml(string $html, string $outputPath): bool
    {
        try {
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($outputPath . '.html', $html);

            return true;
        } catch (\Exception $e) {
            throw new \Exception("PDF generation failed: " . $e->getMessage());
        }
    }

    public function generateReportHtml(array $data, string $template): string
    {
        $html = $template;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->formatArrayValue($value);
            }
            $html = str_replace('{' . $key . '}', $value, $html);
        }

        return $html;
    }

    private function formatArrayValue(array $value): string
    {
        $output = '';
        foreach ($value as $item) {
            if (is_array($item)) {
                $output .= '<tr>';
                foreach ($item as $subValue) {
                    $output .= '<td>' . htmlspecialchars($subValue ?? '') . '</td>';
                }
                $output .= '</tr>';
            } else {
                $output .= '<li>' . htmlspecialchars($item ?? '') . '</li>';
            }
        }
        return $output;
    }

    public function getFilePath(string $filename): string
    {
        return $this->storagePath . $filename;
    }

    public function getPublicPath(string $filename): string
    {
        return '/storage/reports/' . $filename;
    }

    public function deleteFile(string $filepath): bool
    {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }

    public function getFileSize(string $filepath): ?int
    {
        if (file_exists($filepath)) {
            return filesize($filepath);
        }
        return null;
    }
}
