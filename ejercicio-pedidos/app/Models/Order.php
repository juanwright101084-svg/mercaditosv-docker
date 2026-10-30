<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Order",
    required: ["client_id", "status", "items"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "client_id", type: "integer", example: 1),
        new OA\Property(property: "status", type: "string", enum: ["pending", "shipped", "delivered"], example: "pending"),
        new OA\Property(property: "total", type: "number", format: "float", example: 3.00),
        new OA\Property(
            property: "items",
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(property: "product_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity", type: "integer", example: 2),
                ]
            )
        ),
    ]
)]
class Order extends Model
{
    protected $fillable = ['client_id', 'user_id', 'total', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}