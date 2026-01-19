<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use Hyperf\DbConnection\Db;
use RuntimeException;

class DataImportService
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function importStudentsFromCsv(string $filePath): array
    {
        $results = [
            'success' => true,
            'imported' => 0,
            'failed' => 0,
            'errors' => []
        ];

        if (!file_exists($filePath)) {
            throw new RuntimeException('CSV file not found: ' . $filePath);
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Failed to open CSV file: ' . $filePath);
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('CSV file is empty');
        }

        $expectedHeaders = ['nisn', 'class_id', 'birth_date', 'birth_place', 'address', 'parent_id'];
        $missingHeaders = array_diff($expectedHeaders, $headers);

        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('Missing required headers: ' . implode(', ', $missingHeaders));
        }

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);

            try {
                $this->db->beginTransaction();

                $student = new Student();
                $student['nisn'] = $data['nisn'];
                $student['class_id'] = $data['class_id'];
                $student['birth_date'] = $data['birth_date'];
                $student['birth_place'] = $data['birth_place'];
                $student['address'] = $data['address'];
                $student['parent_id'] = $data['parent_id'];
                $student['status'] = 'active';
                $student['enrollment_date'] = date('Y-m-d');

                $student->save();

                $this->db->commit();
                $results['imported']++;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'nisn' => $data['nisn'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }
        }

        fclose($handle);

        $results['success'] = $results['failed'] === 0;

        return $results;
    }

    public function importTeachersFromCsv(string $filePath): array
    {
        $results = [
            'success' => true,
            'imported' => 0,
            'failed' => 0,
            'errors' => []
        ];

        if (!file_exists($filePath)) {
            throw new RuntimeException('CSV file not found: ' . $filePath);
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Failed to open CSV file: ' . $filePath);
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('CSV file is empty');
        }

        $expectedHeaders = ['user_id', 'employee_id', 'department', 'subject'];
        $missingHeaders = array_diff($expectedHeaders, $headers);

        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('Missing required headers: ' . implode(', ', $missingHeaders));
        }

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);

            try {
                $this->db->beginTransaction();

                $teacher = new Teacher();
                $teacher->user_id = $data['user_id'];
                $teacher->employee_id = $data['employee_id'];
                $teacher->department = $data['department'];
                $teacher->subject = $data['subject'];

                $teacher->save();

                $this->db->commit();
                $results['imported']++;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'employee_id' => $data['employee_id'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }
        }

        fclose($handle);

        $results['success'] = $results['failed'] === 0;

        return $results;
    }

    public function importClassesFromCsv(string $filePath): array
    {
        $results = [
            'success' => true,
            'imported' => 0,
            'failed' => 0,
            'errors' => []
        ];

        if (!file_exists($filePath)) {
            throw new RuntimeException('CSV file not found: ' . $filePath);
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Failed to open CSV file: ' . $filePath);
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('CSV file is empty');
        }

        $expectedHeaders = ['class_name', 'grade_level', 'academic_year', 'homeroom_teacher_id'];
        $missingHeaders = array_diff($expectedHeaders, $headers);

        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('Missing required headers: ' . implode(', ', $missingHeaders));
        }

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);

            try {
                $this->db->beginTransaction();

                $class = new ClassModel();
                $class->class_name = $data['class_name'];
                $class->grade_level = $data['grade_level'];
                $class->academic_year = $data['academic_year'];
                $class->homeroom_teacher_id = $data['homeroom_teacher_id'];
                $class->status = 'active';

                $class->save();

                $this->db->commit();
                $results['imported']++;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'class_name' => $data['class_name'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }
        }

        fclose($handle);

        $results['success'] = $results['failed'] === 0;

        return $results;
    }

    private function validateDate(string $date): bool
    {
        return (bool) strtotime($date);
    }

    private function validateRequired(array $data, array $required): array
    {
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}
