<?php

declare(strict_types=1);

namespace App\Services\Gdpr;

use App\Models\User;
use App\Models\UserConsent;
use App\Models\Logs\AuditLog;
use Psr\Log\LoggerInterface;
use Hypervel\Support\Facades\DB;
use Psr\Container\ContainerInterface;

/**
 * AccountDeletionService
 *
 * Handles GDPR Article 17 - Right to erasure (Right to be forgotten)
 * Anonymizes or deletes user data while maintaining data integrity
 */
class AccountDeletionService
{
    private StdoutLoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Anonymize user account instead of hard deletion
     * This preserves referential integrity while removing PII
     */
    public function anonymizeUser(User $user, string $reason = 'User request'): bool
    {
        $this->logger->info("Starting GDPR anonymization for user", ['user_id' => $user->id]);

        try {
            Db::beginTransaction();

            // 1. Log the deletion request
            $this->logDeletionRequest($user, $reason);

            // 2. Withdraw all consents
            UserConsent::withdrawAllForUser($user->id, 'Account anonymization');

            // 3. Anonymize user record
            $this->anonymizeUserRecord($user);

            // 4. Anonymize related records
            $this->anonymizeRelatedRecords($user);

            // 5. Delete sensitive relationships that can be safely removed
            $this->deleteSensitiveRelationships($user);

            Db::commit();

            $this->logger->info("Successfully anonymized user", ['user_id' => $user->id]);

            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->logger->error("Failed to anonymize user", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Anonymize the main user record
     */
    private function anonymizeUserRecord(User $user): void
    {
        $anonymousId = 'anonymized_' . substr($user->id, 0, 8);
        $timestamp = now()->timestamp;

        $user->update([
            'name' => 'Anonymous User ' . $timestamp,
            'username' => $anonymousId,
            'email' => 'anonymized+' . $timestamp . '@deleted.local',
            'password' => bcrypt(uniqid('deleted_', true)), // Random password
            'full_name' => 'Anonymous User',
            'phone' => null,
            'avatar_url' => null,
            'is_active' => false,
            'last_login_ip' => null,
            'remember_token' => null,
            'email_verified_at' => null,
            'slug' => 'anonymized-' . $timestamp,
        ]);

        $this->logger->info("Anonymized user record", ['user_id' => $user->id]);
    }

    /**
     * Anonymize related records based on user type
     */
    private function anonymizeRelatedRecords(User $user): void
    {
        // Anonymize student record if exists
        if ($user->student) {
            $this->anonymizeStudentRecord($user->student);
        }

        // Anonymize teacher record if exists
        if ($user->teacher) {
            $this->anonymizeTeacherRecord($user->teacher);
        }

        // Anonymize parent record if exists
        if ($user->parent) {
            $this->anonymizeParentRecord($user->parent);
        }

        // Anonymize staff record if exists
        if ($user->staff) {
            $this->anonymizeStaffRecord($user->staff);
        }
    }

    /**
     * Anonymize student record
     */
    private function anonymizeStudentRecord($student): void
    {
        $student->update([
            'birth_place' => 'Anonymous',
            'address' => 'Anonymous',
            'phone' => null,
            'emergency_contact_name' => 'Anonymous',
            'emergency_contact_phone' => null,
        ]);
    }

    /**
     * Anonymize teacher record
     */
    private function anonymizeTeacherRecord($teacher): void
    {
        $teacher->update([
            'address' => 'Anonymous',
            'phone' => null,
            'emergency_contact' => null,
        ]);
    }

    /**
     * Anonymize parent record
     */
    private function anonymizeParentRecord($parent): void
    {
        $parent->update([
            'address' => 'Anonymous',
            'occupation' => 'Anonymous',
            'work_address' => null,
            'phone' => null,
        ]);
    }

    /**
     * Anonymize staff record
     */
    private function anonymizeStaffRecord($staff): void
    {
        $staff->update([
            'address' => 'Anonymous',
            'phone' => null,
            'emergency_contact' => null,
        ]);
    }

    /**
     * Delete sensitive relationships that can be safely removed
     */
    private function deleteSensitiveRelationships(User $user): void
    {
        // Delete MFA settings
        $user->mfaSettings()?->delete();

        // Delete MFA backup codes
        \App\Models\MfaBackupCode::where('user_id', $user->id)->delete();

        // Delete password reset tokens
        \App\Models\PasswordResetToken::where('email', $user->email)->delete();

        // Delete old audit logs (keep recent ones for security)
        AuditLog::where('user_id', $user->id)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Log deletion request for audit trail
     */
    private function logDeletionRequest(User $user, string $reason): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'gdpr_account_deletion_request',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => "GDPR account deletion requested. Reason: {$reason}",
            'ip_address' => request()?->getHeaderLine('x-forwarded-for') ?? request()?->getServerParams()['remote_addr'] ?? null,
        ]);
    }

    /**
     * Validate if user can be deleted
     * Check for dependencies that would break referential integrity
     */
    public function validateDeletion(User $user): array
    {
        $issues = [];

        // Check if user has critical relationships that need attention
        $criticalRelations = [
            'gradesCreated' => 'Grade records created by this user',
            'reportsCreated' => 'Report records created by this user',
            'examsCreated' => 'Exam records created by this user',
        ];

        foreach ($criticalRelations as $relation => $description) {
            if ($user->$relation()->count() > 0) {
                $issues[] = [
                    'type' => 'warning',
                    'relation' => $relation,
                    'description' => $description,
                    'count' => $user->$relation()->count(),
                ];
            }
        }

        return [
            'can_delete' => true, // Always allow anonymization
            'warnings' => $issues,
            'recommendation' => 'User will be anonymized. Related records will preserve referential integrity.',
        ];
    }

    /**
     * Schedule delayed deletion (cooling-off period)
     */
    public function scheduleDeletion(User $user, int $delayDays = 30): array
    {
        $scheduledDate = now()->addDays($delayDays);

        // Mark user for deletion
        $user->update([
            'key_status' => 'scheduled_deletion',
        ]);

        // Log scheduling
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'gdpr_deletion_scheduled',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => "Account deletion scheduled for {$scheduledDate->toIso8601String()}",
        ]);

        return [
            'scheduled_date' => $scheduledDate->toIso8601String(),
            'user_id' => $user->id,
            'status' => 'pending',
        ];
    }

    /**
     * Cancel scheduled deletion
     */
    public function cancelScheduledDeletion(User $user): bool
    {
        if ($user->key_status !== 'scheduled_deletion') {
            return false;
        }

        $user->update([
            'key_status' => null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'gdpr_deletion_cancelled',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'Scheduled account deletion cancelled',
        ]);

        return true;
    }
}
