<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    #[OA\Post(
        path: "/payments",
        summary: "Crear un intento de pago para una orden",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_id"],
                properties: [
                    new OA\Property(property: "order_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Intento de pago creado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "payment", ref: "#/components/schemas/Payment"),
                        new OA\Property(property: "client_secret", type: "string"),
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Orden no encontrada"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 500, description: "Error al crear el intento de pago"),
        ]
    )]
    public function store(StorePaymentRequest $request)
    {
        // ✅ FIX: usar 'sanctum' en vez de 'api'
        $userId = Auth::guard('sanctum')->id();

        $order = Order::where('id', $request->validated('order_id'))
            ->where('user_id', $userId)
            ->firstOrFail();

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round((float) $order->total * 100),
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => "Pago de la orden #{$order->id}",
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $userId,
                ],
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'amount' => $order->total,
                'currency' => 'usd',
                'stripe_payment_intent_id' => $intent->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'payment' => $payment,
                'client_secret' => $intent->client_secret,
            ], 201);
        } catch (ApiErrorException $e) {
            report($e);

            return response()->json([
                'message' => 'No se pudo crear el intento de pago.',
            ], 500);
        }
    }

    #[OA\Get(
        path: "/my-orders",
        summary: "Historial de pagos del usuario autenticado",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de pagos del usuario",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Payment")
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function myOrders()
    {
        // ✅ FIX: usar 'sanctum' en vez de 'api'
        $userId = Auth::guard('sanctum')->id();

        $payments = Payment::where('user_id', $userId)
            ->with('order.items.product')
            ->latest()
            ->get();

        return response()->json($payments);
    }
}