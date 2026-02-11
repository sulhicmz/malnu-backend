<?php

declare(strict_types=1);

use Hyperf\DbConnection\Db;

return [
    'up' => static function (Db $db): void {
        // Compliance policies table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_policies` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `content` LONGTEXT NOT NULL,
            `category` VARCHAR(100) NOT NULL COMMENT "FERPA, GDPR, CCPA, CIPA, IDEA, General",
            `version` INT NOT NULL DEFAULT 1,
            `effective_date` DATE NOT NULL,
            `expiry_date` DATE,
            `status` ENUM("active", "superseded", "retired") NOT NULL DEFAULT "active",
            `created_by` CHAR(36) NOT NULL,
            `superseded_by` CHAR(36),
            `superseded_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_category` (`category`),
            INDEX `idx_status` (`status`),
            INDEX `idx_effective_date` (`effective_date`),
            INDEX `idx_created_by` (`created_by`),
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`superseded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance policy acknowledgments table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_policy_acknowledgments` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `policy_id` CHAR(36) NOT NULL,
            `user_id` CHAR(36) NOT NULL,
            `acknowledged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `acknowledgment_ip` VARCHAR(45),
            `acknowledgment_device` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_policy` (`user_id`, `policy_id`),
            INDEX `idx_policy_id` (`policy_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_acknowledged_at` (`acknowledged_at`),
            FOREIGN KEY (`policy_id`) REFERENCES `compliance_policies`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance training table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_training` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `content` LONGTEXT NOT NULL,
            `training_type` VARCHAR(50) NOT NULL COMMENT "FERPA, GDPR, Security, Privacy, General",
            `duration_minutes` INT NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `required_for_roles` JSON COMMENT "Array of role IDs that require this training",
            `required_for_all` BOOLEAN NOT NULL DEFAULT FALSE,
            `valid_from` DATE NOT NULL,
            `valid_until` DATE,
            `status` ENUM("active", "inactive", "archived") NOT NULL DEFAULT "active",
            `created_by` CHAR(36) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_training_type` (`training_type`),
            INDEX `idx_status` (`status`),
            INDEX `idx_valid_from` (`valid_from`),
            INDEX `idx_created_by` (`created_by`),
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance training completions table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_training_completions` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `training_id` CHAR(36) NOT NULL,
            `user_id` CHAR(36) NOT NULL,
            `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `score` DECIMAL(5,2),
            `passed` BOOLEAN NOT NULL DEFAULT TRUE,
            `completion_ip` VARCHAR(45),
            `completion_device` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_training` (`user_id`, `training_id`),
            INDEX `idx_training_id` (`training_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_completed_at` (`completed_at`),
            FOREIGN KEY (`training_id`) REFERENCES `compliance_training`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance audits table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_audits` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `user_id` CHAR(36),
            `action_type` VARCHAR(100) NOT NULL COMMENT "login, logout, data_access, data_export, grade_modify, student_view, etc.",
            `entity_type` VARCHAR(100) COMMENT "user, student, grade, policy, training, etc.",
            `entity_id` CHAR(36),
            `description` TEXT,
            `old_values` JSON COMMENT "Previous values for modifications",
            `new_values` JSON COMMENT "New values for modifications",
            `ip_address` VARCHAR(45),
            `user_agent` VARCHAR(500),
            `request_method` VARCHAR(10),
            `request_path` VARCHAR(500),
            `compliance_tags` JSON COMMENT "Array of tags: FERPA, GDPR, security, privacy",
            `severity` ENUM("low", "medium", "high", "critical") NOT NULL DEFAULT "low",
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_action_type` (`action_type`),
            INDEX `idx_entity_type` (`entity_type`),
            INDEX `idx_entity_id` (`entity_id`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_severity` (`severity`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance reports table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_reports` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `report_type` VARCHAR(100) NOT NULL COMMENT "FERPA_access, GDPR_subject_rights, training_completion, audit_summary, etc.",
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `report_period_start` DATE,
            `report_period_end` DATE,
            `generated_by` CHAR(36) NOT NULL,
            `report_data` LONGTEXT COMMENT "JSON report data",
            `status` ENUM("draft", "generated", "submitted", "approved", "rejected") NOT NULL DEFAULT "draft",
            `submitted_at` TIMESTAMP NULL,
            `submitted_to` VARCHAR(255),
            `external_reference` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_report_type` (`report_type`),
            INDEX `idx_status` (`status`),
            INDEX `idx_report_period` (`report_period_start`, `report_period_end`),
            INDEX `idx_generated_by` (`generated_by`),
            INDEX `idx_submitted_at` (`submitted_at`),
            FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Data breach incidents table
        $db->statement('CREATE TABLE IF NOT EXISTS `data_breach_incidents` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `incident_type` VARCHAR(100) NOT NULL COMMENT "unauthorized_access, data_exposure, lost_device, phishing, etc.",
            `severity` ENUM("low", "medium", "high", "critical") NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `affected_records` INT,
            `data_types_affected` JSON COMMENT "Array: student_records, grades, personal_info, financial_data, etc.",
            `discovered_at` TIMESTAMP NOT NULL,
            `reported_at` TIMESTAMP NOT NULL,
            `reported_by` CHAR(36) NOT NULL,
            `assigned_to` CHAR(36),
            `status` ENUM("open", "investigating", "mitigating", "resolved", "closed") NOT NULL DEFAULT "open",
            `root_cause` TEXT,
            `mitigation_actions` TEXT,
            `notification_sent` BOOLEAN NOT NULL DEFAULT FALSE,
            `notification_sent_at` TIMESTAMP NULL,
            `regulatory_report_required` BOOLEAN NOT NULL DEFAULT FALSE,
            `regulatory_report_submitted` BOOLEAN NOT NULL DEFAULT FALSE,
            `regulatory_submission_date` DATE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_severity` (`severity`),
            INDEX `idx_status` (`status`),
            INDEX `idx_reported_by` (`reported_by`),
            INDEX `idx_assigned_to` (`assigned_to`),
            INDEX `idx_discovered_at` (`discovered_at`),
            INDEX `idx_regulatory_report_required` (`regulatory_report_required`),
            FOREIGN KEY (`reported_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Compliance risks table
        $db->statement('CREATE TABLE IF NOT EXISTS `compliance_risks` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `risk_title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `risk_category` VARCHAR(100) NOT NULL COMMENT "data_privacy, access_control, training_gap, regulatory_change, etc.",
            `likelihood` ENUM("rare", "unlikely", "possible", "likely", "almost_certain") NOT NULL,
            `impact` ENUM("negligible", "minor", "moderate", "major", "catastrophic") NOT NULL,
            `risk_score` INT NOT NULL COMMENT "Calculated: likelihood x impact",
            `affected_systems` JSON COMMENT "Array of systems/areas affected",
            `applicable_regulations` JSON COMMENT "Array: FERPA, GDPR, CCPA, etc.",
            `mitigation_plan` TEXT,
            `mitigation_status` ENUM("not_started", "in_progress", "completed", "deferred") NOT NULL DEFAULT "not_started",
            `mitigation_priority` ENUM("low", "medium", "high", "critical") NOT NULL DEFAULT "medium",
            `target_mitigation_date` DATE,
            `actual_mitigation_date` DATE,
            `identified_by` CHAR(36) NOT NULL,
            `assigned_to` CHAR(36),
            `status` ENUM("open", "in_review", "mitigating", "mitigated", "accepted") NOT NULL DEFAULT "open",
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_risk_category` (`risk_category`),
            INDEX `idx_risk_score` (`risk_score`),
            INDEX `idx_mitigation_status` (`mitigation_status`),
            INDEX `idx_mitigation_priority` (`mitigation_priority`),
            INDEX `idx_status` (`status`),
            INDEX `idx_identified_by` (`identified_by`),
            INDEX `idx_assigned_to` (`assigned_to`),
            FOREIGN KEY (`identified_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    },
    'down' => static function (Db $db): void {
        $db->statement('DROP TABLE IF EXISTS `compliance_risks`');
        $db->statement('DROP TABLE IF EXISTS `data_breach_incidents`');
        $db->statement('DROP TABLE IF EXISTS `compliance_reports`');
        $db->statement('DROP TABLE IF EXISTS `compliance_audits`');
        $db->statement('DROP TABLE IF EXISTS `compliance_training_completions`');
        $db->statement('DROP TABLE IF EXISTS `compliance_training`');
        $db->statement('DROP TABLE IF EXISTS `compliance_policy_acknowledgments`');
        $db->statement('DROP TABLE IF EXISTS `compliance_policies`');
    },
];
