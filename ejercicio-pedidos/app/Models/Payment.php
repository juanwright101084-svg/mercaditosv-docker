<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Payment",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "order_id", type: "integer", example: 1),
        new OA\Property(property: "amount", type: "number", format: "float", example: 4.50),
        new OA\Property(property: "currency", type: "string", example: "usd"),
        new OA\Property(property: "status", type: "string", example: "succeeded"),
        new OA\Property(property: "stripe_payment_intent_id", type: "string", example: "pi_3Q...abc"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
    ]
)]
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'amount', 'currency', 'stripe_payment_intent_id', 'status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}