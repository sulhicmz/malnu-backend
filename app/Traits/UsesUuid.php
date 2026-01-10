<?php

declare(strict_types=1);

namespace App\Traits;

trait UsesUuid
{
    /**
     * Initialize UUID for model.
     */
    public function initializeUsesUuid(): void
    {
        if (empty($this->{$this->getKeyName()})) {
            $this->{$this->getKeyName()} = $this->generateUuid();
        }
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

    /**
     * Generate UUID v4.
     */
    protected function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
