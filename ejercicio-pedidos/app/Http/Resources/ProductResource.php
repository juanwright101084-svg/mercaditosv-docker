<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_featured' => (bool) $this->is_featured,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'image' => $this->image,
        ];
    }
}