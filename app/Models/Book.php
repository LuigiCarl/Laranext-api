<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class book extends Model
{
    protected $table = 'book';
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
