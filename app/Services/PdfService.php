<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Utils\ApplicationContext;

class PdfService
{
    /**
     * Generate a PDF report card from report data
     */
    public function generateReportCardPdf(array $reportData): string
    {
        // Since the framework has missing dependencies, we'll create a basic HTML template
        // that can be converted to PDF using a service like wkhtmltopdf or similar
        $html = $this->buildReportCardHtml($reportData);
        
        // For now, return the HTML content
        // In a complete implementation, this would convert HTML to PDF
        return $html;
    }
    
    /**
     * Generate a PDF transcript from transcript data
     */
    public function generateTranscriptPdf(array $transcriptData): string
    {
        $html = $this->buildTranscriptHtml($transcriptData);
        
        // For now, return the HTML content
        return $html;
    }
    
    /**
     * Build HTML for report card
     */
    private function buildReportCardHtml(array $reportData): string
    {
        $student = $reportData['student'];
        $class = $reportData['class'] ?? null;
        $grades = $reportData['grades'] ?? [];
        $competencies = $reportData['competencies'] ?? [];
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Report Card - ' . htmlspecialchars($student->name ?? 'Student') . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .info-table th { background-color: #f2f2f2; }
                .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .grades-table th, .grades-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .grades-table th { background-color: #f2f2f2; }
                .competencies-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .competencies-table th, .competencies-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .competencies-table th { background-color: #f2f2f2; }
                .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Report Card</h1>
                <h2>' . htmlspecialchars($student->name ?? 'Student Name') . '</h2>
                <p>Class: ' . htmlspecialchars($class->name ?? 'N/A') . '</p>
                <p>Semester: ' . $reportData['semester'] . ' | Academic Year: ' . htmlspecialchars($reportData['academic_year']) . '</p>
                <p>Generated: ' . $reportData['generated_at'] . '</p>
            </div>
            
            <div class="info-table">
                <table>
                    <tr>
                        <th>Student Information</th>
                        <td>
                            <strong>Name:</strong> ' . htmlspecialchars($student->name ?? 'N/A') . '<br>
                            <strong>Email:</strong> ' . htmlspecialchars($student->email ?? 'N/A') . '<br>
                            <strong>Student ID:</strong> ' . htmlspecialchars($student->id ?? 'N/A') . '<br>
                            <strong>Average Grade:</strong> ' . $reportData['average_grade'] . '<br>
                            <strong>Rank in Class:</strong> ' . $reportData['rank_in_class'] . '
                        </td>
                    </tr>
                </table>
            </div>
            
            <h3>Grades</h3>
            <div class="grades-table">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Type</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($grades as $grade) {
            $subjectName = is_object($grade) && isset($grade->subject) ? $grade->subject->name : (is_array($grade) ? ($grade['subject']['name'] ?? 'N/A') : 'N/A');
            $gradeValue = is_object($grade) ? $grade->grade : (is_array($grade) ? $grade['grade'] : 'N/A');
            $gradeType = is_object($grade) ? $grade->grade_type : (is_array($grade) ? $grade['grade_type'] : 'N/A');
            $notes = is_object($grade) ? $grade->notes : (is_array($grade) ? ($grade['notes'] ?? '') : '');
            
            $html .= '
                        <tr>
                            <td>' . htmlspecialchars($subjectName) . '</td>
                            <td>' . htmlspecialchars($gradeValue) . '</td>
                            <td>' . htmlspecialchars($gradeType) . '</td>
                            <td>' . htmlspecialchars($notes) . '</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>
            </div>';
        
        if (count($competencies) > 0) {
            $html .= '
            <h3>Competencies</h3>
            <div class="competencies-table">
                <table>
                    <thead>
                        <tr>
                            <th>Competency Code</th>
                            <th>Competency Name</th>
                            <th>Achievement Level</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($competencies as $competency) {
                $subjectName = is_object($competency) && isset($competency->subject) ? $competency->subject->name : (is_array($competency) ? ($competency['subject']['name'] ?? 'N/A') : 'N/A');
                $code = is_object($competency) ? $competency->competency_code : (is_array($competency) ? $competency['competency_code'] : 'N/A');
                $name = is_object($competency) ? $competency->competency_name : (is_array($competency) ? $competency['competency_name'] : 'N/A');
                $level = is_object($competency) ? $competency->achievement_level : (is_array($competency) ? $competency['achievement_level'] : 'N/A');
                $notes = is_object($competency) ? $competency->notes : (is_array($competency) ? ($competency['notes'] ?? '') : '');
                
                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($code) . '</td>
                            <td>' . htmlspecialchars($name) . ' (' . htmlspecialchars($subjectName) . ')</td>
                            <td>' . htmlspecialchars($level) . '</td>
                            <td>' . htmlspecialchars($notes) . '</td>
                        </tr>';
            }
            
            $html .= '
                    </tbody>
                </table>
            </div>';
        }
        
        $html .= '
            <div class="footer">
                <p>Generated by School Management System | Report Card</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Build HTML for transcript
     */
    private function buildTranscriptHtml(array $transcriptData): string
    {
        $student = $transcriptData['student'];
        $gradesByYear = $transcriptData['grades_by_year'] ?? [];
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Academic Transcript - ' . htmlspecialchars($student->name ?? 'Student') . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .info-table th { background-color: #f2f2f2; }
                .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .grades-table th, .grades-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .grades-table th { background-color: #f2f2f2; }
                .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Academic Transcript</h1>
                <h2>' . htmlspecialchars($student->name ?? 'Student Name') . '</h2>
                <p>Student ID: ' . htmlspecialchars($student->id ?? 'N/A') . '</p>
                <p>Cumulative GPA: ' . $transcriptData['cumulative_gpa'] . ' | Total Credits: ' . $transcriptData['total_credits'] . '</p>
                <p>Generated: ' . $transcriptData['generated_at'] . '</p>
            </div>
            
            <div class="info-table">
                <table>
                    <tr>
                        <th>Student Information</th>
                        <td>
                            <strong>Name:</strong> ' . htmlspecialchars($student->name ?? 'N/A') . '<br>
                            <strong>Email:</strong> ' . htmlspecialchars($student->email ?? 'N/A') . '<br>
                            <strong>Enrollment Date:</strong> ' . htmlspecialchars($student->created_at ?? 'N/A') . '
                        </td>
                    </tr>
                </table>
            </div>';
        
        // Loop through years and semesters
        foreach ($gradesByYear as $year => $semesters) {
            $html .= '<h3>Academic Year: ' . htmlspecialchars($year) . '</h3>';
            
            foreach ($semesters as $semester => $semesterGrades) {
                $html .= '
                <h4>Semester ' . $semester . '</h4>
                <div class="grades-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Grade</th>
                                <th>Type</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                foreach ($semesterGrades as $grade) {
                    $subjectName = is_object($grade) && isset($grade->subject) ? $grade->subject->name : (is_array($grade) ? ($grade['subject']['name'] ?? 'N/A') : 'N/A');
                    $className = is_object($grade) && isset($grade->class) ? $grade->class->name : (is_array($grade) ? ($grade['class']['name'] ?? 'N/A') : 'N/A');
                    $gradeValue = is_object($grade) ? $grade->grade : (is_array($grade) ? $grade['grade'] : 'N/A');
                    $gradeType = is_object($grade) ? $grade->grade_type : (is_array($grade) ? $grade['grade_type'] : 'N/A');
                    $notes = is_object($grade) ? $grade->notes : (is_array($grade) ? ($grade['notes'] ?? '') : '');
                    
                    $html .= '
                        <tr>
                            <td>' . htmlspecialchars($subjectName) . '</td>
                            <td>' . htmlspecialchars($className) . '</td>
                            <td>' . htmlspecialchars($gradeValue) . '</td>
                            <td>' . htmlspecialchars($gradeType) . '</td>
                            <td>' . htmlspecialchars($notes) . '</td>
                        </tr>';
                }
                
                $html .= '
                        </tbody>
                    </table>
                </div>';
            }
        }
        
        $html .= '
            <div class="footer">
                <p>Generated by School Management System | Academic Transcript</p>
                <p>This is an official transcript generated on ' . $transcriptData['generated_at'] . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}