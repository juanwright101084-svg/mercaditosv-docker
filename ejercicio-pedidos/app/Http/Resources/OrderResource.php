<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'total' => $this->total,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'client_name' => $this->whenLoaded('client', fn () => $this->client->name),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->image,   // ← NUEVA LÍNEA
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->quantity * $item->unit_price,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}