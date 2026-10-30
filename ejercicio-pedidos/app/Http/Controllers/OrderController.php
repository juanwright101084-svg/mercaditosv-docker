<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
    #[OA\Get(
        path: "/orders",
        summary: "Listar las órdenes del usuario autenticado",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de órdenes",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Order")
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function index()
    {
        $orders = Order::where('user_id', Auth::guard('sanctum')->id())
            ->with(['client', 'items.product'])
            ->get();

        return OrderResource::collection($orders);
    }

    #[OA\Post(
        path: "/orders",
        summary: "Crear una nueva orden",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Order")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Orden creada exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Order")
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 422, description: "Error de validación o stock insuficiente"),
        ]
    )]
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $products = Product::whereIn('id', collect($request->items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $total = 0;

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "No hay suficiente stock de \"{$product->name}\". Disponible: {$product->stock}.",
                    ]);
                }

                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'client_id' => $request->client_id,
                'user_id' => Auth::guard('sanctum')->id(),
                'status' => $request->status,
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        return new OrderResource($order->load(['client', 'items.product']));
    }

    #[OA\Get(
        path: "/orders/{id}",
        summary: "Obtener una orden por ID (solo del propio usuario)",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Orden encontrada",
                content: new OA\JsonContent(ref: "#/components/schemas/Order")
            ),
            new OA\Response(response: 404, description: "Orden no encontrada"),
        ]
    )]
    public function show(Order $order)
    {
        $this->authorizeOwnership($order);

        return new OrderResource($order->load(['client', 'items.product']));
    }

    #[OA\Put(
        path: "/orders/{id}",
        summary: "Actualizar una orden (solo del propio usuario)",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Order")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Orden actualizada",
                content: new OA\JsonContent(ref: "#/components/schemas/Order")
            ),
            new OA\Response(response: 404, description: "Orden no encontrada"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function update(StoreOrderRequest $request, Order $order)
    {
        $this->authorizeOwnership($order);

        // Solo actualizamos lo que venga en el request
        $data = [];

        if ($request->has('status')) {
            $data['status'] = $request->validated('status');
        }

        if ($request->has('client_id')) {
            $data['client_id'] = $request->validated('client_id');
        }

        $order->update($data);

        return new OrderResource($order->load(['client', 'items.product']));
    }

    #[OA\Delete(
        path: "/orders/{id}",
        summary: "Eliminar una orden (solo del propio usuario)",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Orden eliminada"),
            new OA\Response(response: 404, description: "Orden no encontrada"),
        ]
    )]
    public function destroy(Order $order)
    {
        $this->authorizeOwnership($order);

        $order->delete();

        return response()->noContent();
    }

    /**
     * Verifica que la orden pertenezca al usuario autenticado.
     * Devuelve 404 (no 403) para no revelar que la orden existe.
     */
    private function authorizeOwnership(Order $order): void
    {
        if ($order->user_id !== Auth::guard('sanctum')->id()) {
            throw new NotFoundHttpException('No query results for model [App\\Models\\Order].');
        }
    }
}