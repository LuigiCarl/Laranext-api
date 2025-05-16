<?php

namespace App\Http\Controllers\Api;

use App\Models\books;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\bookResource;
use Illuminate\Support\Facades\Validator;

class bookController extends Controller
{
    public function index()
    {
        $books = books::get();

        if ($books->count() > 0) {
            return bookResource::collection($books);
        } else {
            return response()->json(['message' => 'No books found'], 200);
        }
    }

    public function show($id)
    {
        $book = books::findOrFail($id);
        return new bookResource($book);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'isbn' => 'required|integer|unique:books,isbn',
            'category' => 'required|string|max:255',
            'copies' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->messages()], 422);
        }

        $book = books::create([
            'title' => $request->title,
            'author' => $request->author,
            'published_year' => $request->published_year,
            'isbn' => $request->isbn,
            'category' => $request->category,
            'copies' => $request->copies,
            'description' => $request->description,
            'cover_image_url' => $request->hasFile('cover_image') ? $request->file('cover_image')->store('images') : null,
        ]);

        return response()->json(['message' => 'Book created successfully', 'data' => new bookResource($book)], 200);
    }

    public function update(Request $request, books $book)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'isbn' => 'required|integer|unique:books,isbn,' . $book->id,
            'category' => 'required|string|max:255',
            'copies' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->messages()], 422);
        }

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'published_year' => $request->published_year,
            'isbn' => $request->isbn,
            'category' => $request->category,
            'copies' => $request->copies,
            'description' => $request->description,
            'cover_image_url' => $request->hasFile('cover_image') ? $request->file('cover_image')->store('images') : $book->cover_image_url,
        ]);

        return response()->json(['message' => 'Book updated successfully', 'data' => new bookResource($book)], 200);
    }

    public function destroy($id)
    {
        $book = books::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
