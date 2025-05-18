<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowResource;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BorrowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Borrow::with(['user', 'book']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by book
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('borrow_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('borrow_date', '<=', $request->to_date);
        }

        // Sort by field
        $sortField = $request->sort_by ?? 'borrow_date';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $borrows = $query->paginate($perPage);

        return BorrowResource::collection($borrows);
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
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'due_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if the user is blocked
        $user = User::findOrFail($request->user_id);
        if ($user->isBlocked()) {
            return response()->json([
                'message' => 'User is blocked and cannot borrow books',
            ], 403);
        }

        // Check if the book is available
        $book = Book::findOrFail($request->book_id);
        if (!$book->isAvailable()) {
            return response()->json([
                'message' => 'Book is not available for borrowing',
            ], 400);
        }

        // Check if the user has already borrowed this book
        $existingBorrow = Borrow::where('user_id', $request->user_id)
            ->where('book_id', $request->book_id)
            ->whereIn('status', ['active', 'overdue'])
            ->first();

        if ($existingBorrow) {
            return response()->json([
                'message' => 'User has already borrowed this book',
            ], 400);
        }

        // Create the borrow record
        $borrow = new Borrow([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrow_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'active',
        ]);

        $borrow->save();

        // Decrement the available copies of the book
        $book->borrow();

        return new BorrowResource($borrow->load(['user', 'book']));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $borrow = Borrow::with(['user', 'book'])->findOrFail($id);
        return new BorrowResource($borrow);
    }

    /**
     * Return a borrowed book.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnBook($id)
    {
        $borrow = Borrow::with(['user', 'book'])->findOrFail($id);

        if ($borrow->isReturned()) {
            return response()->json([
                'message' => 'Book has already been returned',
            ], 400);
        }

        $borrow->returnBook();

        return new BorrowResource($borrow->fresh(['user', 'book']));
    }

    /**
     * Get borrows for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserBorrows($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        $query = $user->borrows()->with(['book']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sort by field
        $sortField = $request->sort_by ?? 'borrow_date';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $borrows = $query->paginate($perPage);

        return BorrowResource::collection($borrows);
    }
}
