<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'category' => $this->category,
            'published_year' => $this->published_year,
            'copies' => $this->copies,
            'available_copies' => $this->available_copies,
            'description' => $this->description,
            'cover_image' => $this->cover_image ? url($this->cover_image) : null,
            'status' => $this->available_copies > 0 ? 'Available' : 'Borrowed',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
