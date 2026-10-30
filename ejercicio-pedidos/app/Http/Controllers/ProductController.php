<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * Soporta filtros: ?search=, ?category_id=, ?featured=, ?limit=
     */
    #[OA\Get(
        path: "/products",
        summary: "Listar todos los productos (con búsqueda y filtros opcionales)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Buscar por nombre de producto",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "category_id",
                in: "query",
                required: false,
                description: "Filtrar por categoría",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "featured",
                in: "query",
                required: false,
                description: "Solo productos destacados (1 = sí)",
                schema: new OA\Schema(type: "boolean")
            ),
            new OA\Parameter(
                name: "limit",
                in: "query",
                required: false,
                description: "Limitar número de resultados",
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de productos",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Product")
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $query = Product::with('category');

        // 🔍 Búsqueda por nombre (case-insensitive)
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 🏷️ Filtro por categoría
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // ⭐ Solo productos destacados
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // 🔢 Límite opcional (útil para dropdown del navbar)
        if ($limit = $request->query('limit')) {
            $query->limit((int) $limit);
        }

        // Ordenar por los más recientes primero
        $query->latest();

        return ProductResource::collection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/products",
        summary: "Crear un nuevo producto",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Product")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto creado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Maneja la carga de imagen
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return (new ProductResource($product->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/products/{id}",
        summary: "Obtener un producto por ID",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/products/{id}",
        summary: "Actualizar un producto existente",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Product")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function update(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Maneja la carga de imagen
        if ($request->hasFile('image')) {
            // Elimina imagen anterior si existe
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return new ProductResource($product->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/products/{id}",
        summary: "Eliminar un producto",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: "Producto eliminado"),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function destroy(Product $product)
    {
        // Elimina la imagen si existe
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->noContent();
    }
}