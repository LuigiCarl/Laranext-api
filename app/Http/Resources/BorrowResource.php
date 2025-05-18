<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book_title' => $this->whenLoaded('book', function () {
                return $this->book->title;
            }),
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'user_avatar' => $this->whenLoaded('user', function () {
                return $this->user->avatar ? url($this->user->avatar) : null;
            }),
            'user_initials' => $this->whenLoaded('user', function () {
                $name = $this->user->name;
                $words = explode(' ', $name);
                $initials = '';
                foreach ($words as $word) {
                    $initials .= strtoupper(substr($word, 0, 1));
                }
                return $initials;
            }),
            'borrow_date' => $this->borrow_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'return_date' => $this->return_date ? $this->return_date->format('Y-m-d') : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
