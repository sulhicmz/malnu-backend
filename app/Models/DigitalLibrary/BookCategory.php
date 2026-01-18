<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;

class BookCategory extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(BookCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BookCategory::class, 'parent_id');
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_category_mappings', 'category_id', 'book_id');
    }
}
