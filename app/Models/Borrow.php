<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Get the user that owns the borrow.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that is borrowed.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Check if the borrow is overdue.
     */
    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    /**
     * Check if the borrow is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the borrow is returned.
     */
    public function isReturned()
    {
        return in_array($this->status, ['returned', 'returned-late']);
    }

    /**
     * Update the status based on the due date.
     */
    public function updateStatus()
    {
        if ($this->isReturned()) {
            return;
        }

        if ($this->due_date < now() && $this->status !== 'overdue') {
            $this->status = 'overdue';
            $this->save();
        }
    }

    /**
     * Return the book.
     */
    public function returnBook()
    {
        if ($this->isReturned()) {
            return false;
        }

        $this->return_date = now();
        $this->status = $this->due_date < now() ? 'returned-late' : 'returned';
        $this->save();

        // Increment the available copies of the book
        $this->book->return();

        return true;
    }
}
