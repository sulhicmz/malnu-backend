<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Traits\UsesUuid;

class MessageCategory extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function messages()
    {
        return $this->hasMany(Message::class, 'message_category_id');
    }
}