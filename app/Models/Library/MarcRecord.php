<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\DigitalLibrary\Book;

class MarcRecord extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'leader',
        'control_number',
        'fields',
        'record_type',
        'bibliographic_level',
        'cataloging_notes',
    ];

    protected $casts = [
        'fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function marcFields()
    {
        return $this->hasMany(MarcField::class, 'marc_record_id');
    }

    public function scopeByRecordType($query, $type)
    {
        return $query->where('record_type', $type);
    }

    public function getLanguageMaterialAttribute()
    {
        return $this->record_type === 'language_material';
    }
}
