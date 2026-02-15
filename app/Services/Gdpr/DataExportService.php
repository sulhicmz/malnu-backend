<?php

declare(strict_types=1);

namespace App\Services\Gdpr;

use App\Models\User;
use App\Models\UserConsent;
use App\Models\Logs\AuditLog;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

/**
 * DataExportService
 *
 * Handles GDPR Article 20 - Right to data portability
 * Exports all user data in machine-readable format
 */
class DataExportService
{
    private StdoutLoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Export all user data
     *
     * @return array<string, mixed>
     */
    public function exportUserData(User $user): array
    {
        $this->logger->info("Exporting user data for GDPR request", ['user_id' => $user->id]);

        $export = [
            'export_metadata' => $this->getExportMetadata($user),
            'personal_data' => $this->getPersonalData($user),
            'consent_history' => $this->getConsentHistory($user),
            'activity_logs' => $this->getActivityLogs($user),
            'roles_permissions' => $this->getRolesAndPermissions($user),
        ];

        // Add related data based on user type
        if ($user->student) {
            $export['student_data'] = $this->getStudentData($user);
        }

        if ($user->teacher) {
            $export['teacher_data'] = $this->getTeacherData($user);
        }

        if ($user->parent) {
            $export['parent_data'] = $this->getParentData($user);
        }

        if ($user->staff) {
            $export['staff_data'] = $this->getStaffData($user);
        }

        // Log the export for audit trail
        $this->logExport($user);

        return $export;
    }

    /**
     * Get export metadata
     */
    private function getExportMetadata(User $user): array
    {
        return [
            'export_id' => uniqid('gdpr_export_', true),
            'user_id' => $user->id,
            'export_date' => now()->toIso8601String(),
            'format_version' => '1.0',
            'gdpr_article' => '20',
            'data_categories' => [
                'personal_data',
                'consent_history',
                'activity_logs',
                'roles_permissions',
            ],
        ];
    }

    /**
     * Get personal data from user model
     */
    private function getPersonalData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'last_login_time' => $user->last_login_time?->toIso8601String(),
            'last_login_ip' => $user->last_login_ip,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get consent history
     */
    private function getConsentHistory(User $user): array
    {
        return UserConsent::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($consent) => [
                'consent_type' => $consent->consent_type,
                'consent_given' => $consent->consent_given,
                'consent_version' => $consent->consent_version,
                'ip_address' => $consent->ip_address,
                'created_at' => $consent->created_at?->toIso8601String(),
                'withdrawn_at' => $consent->withdrawn_at?->toIso8601String(),
                'withdrawn_reason' => $consent->withdrawn_reason,
            ])
            ->toArray();
    }

    /**
     * Get activity logs
     */
    private function getActivityLogs(User $user): array
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(1000) // Limit to recent 1000 entries
            ->get()
            ->map(fn ($log) => [
                'action' => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->toArray();
    }

    /**
     * Get roles and permissions
     */
    private function getRolesAndPermissions(User $user): array
    {
        return [
            'roles' => $user->roles()->pluck('name')->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    /**
     * Get student-specific data
     */
    private function getStudentData(User $user): array
    {
        $student = $user->student;
        if (!$student) {
            return [];
        }

        return [
            'student_id' => $student->id,
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'birth_date' => $student->birth_date,
            'birth_place' => $student->birth_place,
            'gender' => $student->gender,
            'religion' => $student->religion,
            'address' => $student->address,
            'enrollment_date' => $student->enrollment_date,
        ];
    }

    /**
     * Get teacher-specific data
     */
    private function getTeacherData(User $user): array
    {
        $teacher = $user->teacher;
        if (!$teacher) {
            return [];
        }

        return [
            'teacher_id' => $teacher->id,
            'nip' => $teacher->nip,
            'nuptk' => $teacher->nuptk,
            'specialization' => $teacher->specialization,
            'employment_status' => $teacher->employment_status,
            'hire_date' => $teacher->hire_date,
        ];
    }

    /**
     * Get parent-specific data
     */
    private function getParentData(User $user): array
    {
        $parent = $user->parent;
        if (!$parent) {
            return [];
        }

        return [
            'parent_id' => $parent->id,
            'relationship' => $parent->relationship,
            'occupation' => $parent->occupation,
            'address' => $parent->address,
        ];
    }

    /**
     * Get staff-specific data
     */
    private function getStaffData(User $user): array
    {
        $staff = $user->staff;
        if (!$staff) {
            return [];
        }

        return [
            'staff_id' => $staff->id,
            'employee_id' => $staff->employee_id,
            'department' => $staff->department,
            'position' => $staff->position,
            'hire_date' => $staff->hire_date,
        ];
    }

    /**
     * Log the data export for audit trail
     */
    private function logExport(User $user): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'gdpr_data_export',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'User requested GDPR data export (Article 20)',
            'ip_address' => request()?->getHeaderLine('x-forwarded-for') ?? request()?->getServerParams()['remote_addr'] ?? null,
        ]);
    }

    /**
     * Export data as JSON string
     */
    public function exportToJson(User $user): string
    {
        return json_encode($this->exportUserData($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export data as CSV format (simplified)
     */
    public function exportToCsv(User $user): string
    {
        $data = $this->exportUserData($user);
        
        // Flatten personal data for CSV
        $csv = "GDPR Data Export for User: {$user->id}\n";
        $csv .= "Export Date: " . now()->toIso8601String() . "\n\n";
        
        $csv .= "Personal Data:\n";
        foreach ($data['personal_data'] as $key => $value) {
            $csv .= "{$key},\"{$value}\"\n";
        }

        return $csv;
    }
}
