<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->has('available') && $request->available === 'true') {
            $query->where('available_copies', '>', 0);
        }

        // Search by title, author, or ISBN
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Sort by field
        $sortField = $request->sort_by ?? 'title';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:20|unique:books',
            'category' => 'required|string|max:100',
            'published_year' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'copies' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $book = new Book($request->except('cover_image'));
        $book->available_copies = $request->copies; // Initially, all copies are available

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $book->cover_image = 'storage/' . $path;
        }

        $book->save();

        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|required|string|max:20|unique:books,isbn,' . $id,
            'category' => 'sometimes|required|string|max:100',
            'published_year' => 'sometimes|required|integer|min:1000|max:' . (date('Y') + 1),
            'copies' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update book details
        $book->fill($request->except(['cover_image', 'available_copies']));

        // If copies is being updated, adjust available_copies accordingly
        if ($request->has('copies')) {
            $difference = $request->copies - $book->copies;
            $book->available_copies = max(0, $book->available_copies + $difference);
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                $oldPath = str_replace('storage/', '', $book->cover_image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('cover_image')->store('covers', 'public');
            $book->cover_image = 'storage/' . $path;
        }

        $book->save();

        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Check if the book has active borrows
        if ($book->activeBorrows()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete book with active borrows',
            ], 400);
        }

        // Delete cover image if exists
        if ($book->cover_image) {
            $path = str_replace('storage/', '', $book->cover_image);
            Storage::disk('public')->delete($path);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }

    /**
     * Upload a cover image for the book.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadCover(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'cover' => 'required|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Delete old image if exists
        if ($book->cover_image) {
            $oldPath = str_replace('storage/', '', $book->cover_image);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('cover')->store('covers', 'public');
        $book->cover_image = 'storage/' . $path;
        $book->save();

        return response()->json([
            'message' => 'Cover image uploaded successfully',
            'url' => url($book->cover_image),
        ]);
    }
}
