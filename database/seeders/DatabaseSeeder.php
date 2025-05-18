<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create librarian user
        User::create([
            'name' => 'Librarian User',
            'email' => 'librarian@example.com',
            'password' => Hash::make('password'),
            'role' => 'librarian',
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create some books
        $books = [
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'isbn' => '9780061120084',
                'category' => 'Fiction',
                'published_year' => 1960,
                'copies' => 3,
                'available_copies' => 2,
                'description' => 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.',
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '9780451524935',
                'category' => 'Science Fiction',
                'published_year' => 1949,
                'copies' => 5,
                'available_copies' => 3,
                'description' => 'A dystopian novel by English novelist George Orwell.',
            ],
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'category' => 'Fiction',
                'published_year' => 1925,
                'copies' => 4,
                'available_copies' => 4,
                'description' => 'A novel by American writer F. Scott Fitzgerald.',
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'isbn' => '9780141439518',
                'category' => 'Romance',
                'published_year' => 1813,
                'copies' => 2,
                'available_copies' => 1,
                'description' => 'A romantic novel of manners by Jane Austen.',
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J.D. Salinger',
                'isbn' => '9780316769488',
                'category' => 'Fiction',
                'published_year' => 1951,
                'copies' => 3,
                'available_copies' => 0,
                'description' => 'A novel by J. D. Salinger.',
            ],
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }

        // Create some borrows
        $borrows = [
            [
                'user_id' => 3, // Regular user
                'book_id' => 1, // To Kill a Mockingbird
                'borrow_date' => now()->subDays(10),
                'due_date' => now()->addDays(4),
                'status' => 'active',
            ],
            [
                'user_id' => 3, // Regular user
                'book_id' => 2, // 1984
                'borrow_date' => now()->subDays(15),
                'due_date' => now()->subDays(1),
                'status' => 'overdue',
            ],
            [
                'user_id' => 3, // Regular user
                'book_id' => 5, // The Catcher in the Rye
                'borrow_date' => now()->subDays(5),
                'due_date' => now()->addDays(9),
                'status' => 'active',
            ],
            [
                'user_id' => 2, // Librarian
                'book_id' => 4, // Pride and Prejudice
                'borrow_date' => now()->subDays(20),
                'due_date' => now()->subDays(6),
                'return_date' => now()->subDays(8),
                'status' => 'returned',
            ],
        ];

        foreach ($borrows as $borrowData) {
            Borrow::create($borrowData);
        }
    }
}
