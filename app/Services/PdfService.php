<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class PdfService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = BASE_PATH . '/storage/app/reports';
        $this->ensureDirectoryExists();
    }

    public function generateAndStore(string $htmlContent, string $subDirectory, string $fileName): string
    {
        $directory = $this->storagePath . '/' . $subDirectory;
        $this->ensureDirectoryExists($directory);

        $filePath = $directory . '/' . $fileName . '.html';

        $result = file_put_contents($filePath, $htmlContent);

        if ($result === false) {
            throw new Exception('Failed to write report file: ' . $filePath);
        }

        return $filePath;
    }

    public function generateFromTemplate(
        string $headerTemplate,
        string $contentTemplate,
        string $footerTemplate,
        array $placeholders,
        ?string $cssStyles = null
    ): string {
        $header = $this->replacePlaceholders($headerTemplate, $placeholders);
        $content = $this->replacePlaceholders($contentTemplate, $placeholders);
        $footer = $this->replacePlaceholders($footerTemplate, $placeholders);

        $css = $cssStyles ?? $this->getDefaultStyles();

        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<style>' . $css . '</style>';
        $html .= '</head><body>';
        $html .= $header;
        $html .= $content;
        $html .= $footer;
        $html .= '</body></html>';

        return $html;
    }

    public function getReportContent(string $filePath): ?string
    {
        if (! file_exists($filePath)) {
            return null;
        }

        return file_get_contents($filePath);
    }

    public function deleteReport(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    public function getAvailablePlaceholders(): array
    {
        return [
            'student' => [
                '{{student_name}}' => 'Student full name',
                '{{student_nisn}}' => 'Student NISN number',
                '{{student_nis}}' => 'Student NIS number',
                '{{date_of_birth}}' => 'Date of birth',
                '{{place_of_birth}}' => 'Place of birth',
                '{{enrollment_date}}' => 'Enrollment date',
                '{{graduation_date}}' => 'Graduation date',
            ],
            'class' => [
                '{{class_name}}' => 'Class name',
                '{{class_grade_level}}' => 'Grade level',
            ],
            'academic' => [
                '{{semester}}' => 'Semester number',
                '{{academic_year}}' => 'Academic year',
                '{{average_grade}}' => 'Average grade',
                '{{rank_in_class}}' => 'Class rank',
                '{{cumulative_gpa}}' => 'Cumulative GPA',
                '{{total_credits}}' => 'Total credits',
            ],
            'notes' => [
                '{{homeroom_notes}}' => 'Homeroom teacher notes',
                '{{principal_notes}}' => 'Principal notes',
            ],
            'tables' => [
                '{{grades_table}}' => 'Subject grades table HTML',
                '{{competencies_table}}' => 'Competencies table HTML',
                '{{academic_history}}' => 'Academic history HTML',
                '{{improvement_trends}}' => 'Improvement trends HTML',
            ],
            'meta' => [
                '{{generation_date}}' => 'Report generation date',
            ],
        ];
    }

    private function replacePlaceholders(string $template, array $placeholders): string
    {
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );
    }

    private function ensureDirectoryExists(?string $directory = null): void
    {
        $dir = $directory ?? $this->storagePath;

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function getDefaultStyles(): string
    {
        return '
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .report-header {
                text-align: center;
                border-bottom: 2px solid #333;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .report-header h1 {
                margin: 0;
                color: #2c5282;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f5f5f5;
                font-weight: bold;
            }
            .report-footer {
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                color: #666;
            }
        ';
    }
}
