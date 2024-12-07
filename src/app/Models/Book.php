<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'published_date',
        'author_id',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}