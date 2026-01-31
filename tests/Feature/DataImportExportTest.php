<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\ExportController;
use App\Services\DataImportService;
use App\Services\DataExportService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Context\ApplicationContext;
use RuntimeException;

class DataImportExportTest extends TestCase
{
    private ImportController $importController;

    private ExportController $exportController;

    private DataImportService $importService;

    private DataExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();
        $container = ApplicationContext::getContainer();

        $request = $container->get(RequestInterface::class);
        $response = $container->get(ResponseInterface::class);
        $importService = $container->get(DataImportService::class);
        $exportService = $container->get(DataExportService::class);

        $this->importController = new ImportController($request, $response, $importService);
        $this->exportController = new ExportController($request, $response, $exportService);
        $this->importService = $importService;
        $this->exportService = $exportService;

        @mkdir(BASE_PATH . '/storage/app/imports', 0755, true);
        @mkdir(BASE_PATH . '/storage/exports', 0755, true);
    }

    public function test_student_import_with_valid_csv()
    {
        $csvContent = "nisn,class_id,birth_date,birth_place,address,parent_id\n";
        $csvContent .= "1234567,1,2010-05-15,Jakarta,Jl. Merdeka Barat 1,parent1\n";

        $csvPath = BASE_PATH . '/storage/app/imports/test_students_valid.csv';
        file_put_contents($csvPath, $csvContent);

        $result = $this->importService->importStudentsFromCsv($csvPath);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);

        @unlink($csvPath);
    }

    public function test_student_import_with_missing_headers()
    {
        $csvContent = "name,address\n";
        $csvContent .= "John Doe,123 Main St\n";

        $csvPath = BASE_PATH . '/storage/app/imports/test_students_missing_headers.csv';
        file_put_contents($csvPath, $csvContent);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required headers');

        try {
            $this->importService->importStudentsFromCsv($csvPath);
        } finally {
            @unlink($csvPath);
        }
    }

    public function test_student_import_with_empty_file()
    {
        $csvPath = BASE_PATH . '/storage/app/imports/test_students_empty.csv';
        file_put_contents($csvPath, '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CSV file is empty');

        try {
            $this->importService->importStudentsFromCsv($csvPath);
        } finally {
            @unlink($csvPath);
        }
    }

    public function test_teacher_import_with_valid_csv()
    {
        $csvContent = "user_id,employee_id,department,subject\n";
        $csvContent .= "user123,EMP001,Mathematics,Algebra\n";

        $csvPath = BASE_PATH . '/storage/app/imports/test_teachers_valid.csv';
        file_put_contents($csvPath, $csvContent);

        $result = $this->importService->importTeachersFromCsv($csvPath);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);

        @unlink($csvPath);
    }

    public function test_classes_import_with_valid_csv()
    {
        $csvContent = "class_name,grade_level,academic_year,homeroom_teacher_id\n";
        $csvContent .= "Class 10A,10,2024-2025,teacher123\n";

        $csvPath = BASE_PATH . '/storage/app/imports/test_classes_valid.csv';
        file_put_contents($csvPath, $csvContent);

        $result = $this->importService->importClassesFromCsv($csvPath);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);

        @unlink($csvPath);
    }

    public function test_student_export_to_csv()
    {
        $filename = 'test_export_students.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $result = $this->exportService->exportStudentsToCsv($outputPath, []);

        $this->assertTrue($result['success']);
        $this->assertEquals($outputPath, $result['file_path']);
        $this->assertFileExists($outputPath);

        @unlink($outputPath);
    }

    public function test_student_export_with_filters()
    {
        $filename = 'test_export_students_filtered.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $filters = [
            'status' => 'active',
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31'
        ];

        $result = $this->exportService->exportStudentsToCsv($outputPath, $filters);

        $this->assertTrue($result['success']);
        $this->assertFileExists($outputPath);

        @unlink($outputPath);
    }

    public function test_teacher_export_to_csv()
    {
        $filename = 'test_export_teachers.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $result = $this->exportService->exportTeachersToCsv($outputPath, []);

        $this->assertTrue($result['success']);
        $this->assertEquals($outputPath, $result['file_path']);
        $this->assertFileExists($outputPath);

        @unlink($outputPath);
    }

    public function test_classes_export_to_csv()
    {
        $filename = 'test_export_classes.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $result = $this->exportService->exportClassesToCsv($outputPath, []);

        $this->assertTrue($result['success']);
        $this->assertEquals($outputPath, $result['file_path']);
        $this->assertFileExists($outputPath);

        @unlink($outputPath);
    }

    public function test_export_with_department_filter()
    {
        $filename = 'test_export_teachers_filtered.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $filters = [
            'department' => 'Mathematics'
        ];

        $result = $this->exportService->exportTeachersToCsv($outputPath, $filters);

        $this->assertTrue($result['success']);
        $this->assertFileExists($outputPath);

        @unlink($outputPath);
    }

    public function test_import_service_handles_missing_file()
    {
        $nonExistentPath = BASE_PATH . '/storage/app/imports/non_existent.csv';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CSV file not found');

        $this->importService->importStudentsFromCsv($nonExistentPath);
    }

    public function test_export_service_creates_valid_csv_structure()
    {
        $filename = 'test_structure.csv';
        $outputPath = BASE_PATH . '/storage/exports/' . $filename;

        $result = $this->exportService->exportStudentsToCsv($outputPath, []);

        $this->assertTrue($result['success']);

        if (file_exists($outputPath)) {
            $content = file_get_contents($outputPath);
            $lines = explode("\n", trim($content));
            $this->assertGreaterThan(1, count($lines));

            $firstLine = str_getcsv($lines[0]);
            $expectedHeaders = ['id', 'nisn', 'class_id', 'birth_date', 'birth_place', 'address', 'parent_id', 'enrollment_date', 'status'];
            $this->assertEquals($expectedHeaders, $firstLine);

            @unlink($outputPath);
        }
    }

    protected function tearDown(): void
    {
        @unlink(BASE_PATH . '/storage/app/imports/test_students_valid.csv');
        @unlink(BASE_PATH . '/storage/app/imports/test_teachers_valid.csv');
        @unlink(BASE_PATH . '/storage/app/imports/test_classes_valid.csv');

        $exports = glob(BASE_PATH . '/storage/exports/test_*.csv');
        foreach ($exports as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }
}
