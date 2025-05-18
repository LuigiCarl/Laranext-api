<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category',
        'published_year',
        'copies',
        'available_copies',
        'description',
        'cover_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_year' => 'integer',
        'copies' => 'integer',
        'available_copies' => 'integer',
    ];

    /**
     * Get the borrows for the book.
     */
    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    /**
     * Get the active borrows for the book.
     */
    public function activeBorrows()
    {
        return $this->borrows()->whereIn('status', ['active', 'overdue']);
    }

    /**
     * Check if the book is available for borrowing.
     */
    public function isAvailable()
    {
        return $this->available_copies > 0;
    }

    /**
     * Decrement the available copies when a book is borrowed.
     */
    public function borrow()
    {
        if ($this->available_copies > 0) {
            $this->available_copies--;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Increment the available copies when a book is returned.
     */
    public function return()
    {
        if ($this->available_copies < $this->copies) {
            $this->available_copies++;
            $this->save();
            return true;
        }
        return false;
    }
}
