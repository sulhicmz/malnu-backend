<?php

declare(strict_types=1);

namespace App\Traits;

use Hypervel\Support\Facades\DB;

trait UsesUuid
{
    /**
     * Initialize UUID for model.
     *
     * Uses MySQL 8.0+ UUID() function for optimized database-level UUID generation.
     * This is more performant than PHP-level Str::uuid() because:
     * - UUID is generated natively by MySQL
     * - Better randomization distribution
     * - Optimized indexing performance
     * - Reduced PHP overhead
     */
    public function initializeUsesUuid(): void
    {
        if (empty($this->{$this->getKeyName()})) {
            $this->{$this->getKeyName()} = (string) Db::raw('(UUID())');
        }
    }

    /**
     * Validate if a string is a valid UUID v4 format.
     *
     * Validates RFC 4122 UUID format (8-4-4-4-12 pattern).
     *
     * @param string $uuid The UUID string to validate
     * @return bool True if valid UUID v4, false otherwise
     */
    public static function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    /**
     * Normalize UUID to lowercase for consistent storage and comparison.
     *
     * @param string $uuid The UUID string to normalize
     * @return string The normalized UUID (lowercase)
     */
    public static function normalizeUuid(string $uuid): string
    {
        return strtolower($uuid);
    }

    /**
     * Validate and normalize a UUID.
     *
     * Combines validation and normalization for convenience.
     *
     * @param string $uuid The UUID string to validate and normalize
     * @return null|string Normalized UUID if valid, null otherwise
     */
    public static function validateAndNormalizeUuid(string $uuid): ?string
    {
        if (! self::isValidUuid($uuid)) {
            return null;
        }
        return self::normalizeUuid($uuid);
    }

    /**
     * Override create method to ensure UUID is set.
     */
    public static function create(array $attributes = []): static
    {
        $model = new static($attributes);
        $model->initializeUsesUuid();
        $model->save();
        return $model;
    }
}
