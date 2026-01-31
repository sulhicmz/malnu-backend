<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use Generator;

class DataExportService
{
    public function exportStudentsToCsv(string $outputPath, array $filters = []): array
    {
        $results = [
            'success' => true,
            'exported' => 0,
            'file_path' => $outputPath,
            'errors' => []
        ];

        try {
            $query = Student::query();

            if (!empty($filters['class_id'])) {
                $query->where('class_id', $filters['class_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('enrollment_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('enrollment_date', '<=', $filters['date_to']);
            }

            $students = $query->get();

            $handle = fopen($outputPath, 'w');
            if ($handle === false) {
                throw new \RuntimeException('Failed to create CSV file: ' . $outputPath);
            }

            $headers = ['id', 'nisn', 'class_id', 'birth_date', 'birth_place', 'address', 'parent_id', 'enrollment_date', 'status'];
            fputcsv($handle, $headers);

            foreach ($students as $student) {
                $row = [
                    'id' => $student->id,
                    'nisn' => $student->nisn,
                    'class_id' => $student->class_id,
                    'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
                    'birth_place' => $student->birth_place,
                    'address' => $student->address,
                    'parent_id' => $student->parent_id,
                    'enrollment_date' => $student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : '',
                    'status' => $student->status
                ];
                fputcsv($handle, $row);
                $results['exported']++;
            }

            fclose($handle);

            $results['success'] = true;
        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];

            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
        }

        return $results;
    }

    public function exportTeachersToCsv(string $outputPath, array $filters = []): array
    {
        $results = [
            'success' => true,
            'exported' => 0,
            'file_path' => $outputPath,
            'errors' => []
        ];

        try {
            $query = Teacher::query();

            if (!empty($filters['department'])) {
                $query->where('department', $filters['department']);
            }

            if (!empty($filters['subject'])) {
                $query->where('subject', 'like', '%' . $filters['subject'] . '%');
            }

            $teachers = $query->get();

            $handle = fopen($outputPath, 'w');
            if ($handle === false) {
                throw new \RuntimeException('Failed to create CSV file: ' . $outputPath);
            }

            $headers = ['id', 'user_id', 'employee_id', 'department', 'subject', 'created_at'];
            fputcsv($handle, $headers);

            foreach ($teachers as $teacher) {
                $row = [
                    'id' => $teacher->id,
                    'user_id' => $teacher->user_id,
                    'employee_id' => $teacher->employee_id,
                    'department' => $teacher->department,
                    'subject' => $teacher->subject,
                    'created_at' => $teacher->created_at ? $teacher->created_at->format('Y-m-d H:i:s') : ''
                ];
                fputcsv($handle, $row);
                $results['exported']++;
            }

            fclose($handle);

            $results['success'] = true;
        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];

            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
        }

        return $results;
    }

    public function exportClassesToCsv(string $outputPath, array $filters = []): array
    {
        $results = [
            'success' => true,
            'exported' => 0,
            'file_path' => $outputPath,
            'errors' => []
        ];

        try {
            $query = ClassModel::query();

            if (!empty($filters['grade_level'])) {
                $query->where('grade_level', $filters['grade_level']);
            }

            if (!empty($filters['academic_year'])) {
                $query->where('academic_year', $filters['academic_year']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $classes = $query->get();

            $handle = fopen($outputPath, 'w');
            if ($handle === false) {
                throw new \RuntimeException('Failed to create CSV file: ' . $outputPath);
            }

            $headers = ['id', 'class_name', 'grade_level', 'academic_year', 'homeroom_teacher_id', 'status', 'created_at'];
            fputcsv($handle, $headers);

            foreach ($classes as $class) {
                $row = [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'grade_level' => $class->grade_level,
                    'academic_year' => $class->academic_year,
                    'homeroom_teacher_id' => $class->homeroom_teacher_id,
                    'status' => $class->status,
                    'created_at' => $class->created_at ? $class->created_at->format('Y-m-d H:i:s') : ''
                ];
                fputcsv($handle, $row);
                $results['exported']++;
            }

            fclose($handle);

            $results['success'] = true;
        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];

            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
        }

        return $results;
    }

    public function streamStudentsToGenerator(array $filters = []): Generator
    {
        $query = Student::query();

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->chunk(1000, function ($students) {
            foreach ($students as $student) {
                yield $student;
            }
        });
    }

    private function escapeCsv(string $value): string
    {
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
