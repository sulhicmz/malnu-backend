<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;

class BookSubject extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_subject_mappings', 'subject_id', 'book_id');
    }
}
