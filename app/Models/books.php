<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class books extends Model
{
    protected $table = 'books';
    protected $fillable = [
        'title',
        'author',
        'published_year',
        'isbn',
        'category',
        'copies',
        'description',
        'cover_image',
    ];

    public function getCoverImageUrlAttribute()
    {
        return asset('storage/' . $this->cover_image);
    }
}
