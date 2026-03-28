<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class ProductController extends Controller
{
    #[OAT\Get(
        path: '/api/products',
        summary: 'Obtener lista de productos',
        description: 'Retorna todos los productos con su divisa asociada',
        operationId: 'getProducts',
        tags: ['Products'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Lista de productos en formato JSON',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        type: 'object',
                        properties: [
                            new OAT\Property(property: 'id', type: 'integer', example: 1),
                            new OAT\Property(property: 'name', type: 'string', example: 'Laptop Pro 14'),
                            new OAT\Property(property: 'description', type: 'string', example: 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.'),
                            new OAT\Property(property: 'price', type: 'number', format: 'float', example: 1499.00),
                            new OAT\Property(property: 'currency_id', type: 'integer', example: 1),
                            new OAT\Property(property: 'tax_cost', type: 'number', format: 'float', example: 284.81),
                            new OAT\Property(property: 'manufacturing_cost', type: 'number', format: 'float', example: 890.00),
                        ]
                    )
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with('currency')
            ->orderBy('id')
            ->get();

        return response()->json($products);
    }

    #[OAT\Post(
        path: '/api/products',
        summary: 'Crear un producto',
        description: 'Crea un nuevo producto en la base de datos',
        operationId: 'storeProduct',
        tags: ['Products'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['name', 'description', 'price', 'currency_id', 'tax_cost', 'manufacturing_cost'],
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Laptop Pro 14'),
                    new OAT\Property(property: 'description', type: 'string', example: 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.'),
                    new OAT\Property(property: 'price', type: 'number', format: 'float', example: 1499.00),
                    new OAT\Property(property: 'currency_id', type: 'integer', example: 1),
                    new OAT\Property(property: 'tax_cost', type: 'number', format: 'float', example: 284.81),
                    new OAT\Property(property: 'manufacturing_cost', type: 'number', format: 'float', example: 890.00),
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 201, description: 'Producto creado correctamente'),
            new OAT\Response(response: 422, description: 'Error de validacion'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(
            $this->productValidationRules(),
            $this->productValidationMessages(),
        );

        $product = Product::create($validated)->load('currency');

        return response()->json($product, 201);
    }

    #[OAT\Get(
        path: '/api/products/{id}',
        summary: 'Obtener un producto por ID',
        description: 'Retorna un producto con su divisa asociada',
        operationId: 'showProduct',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, description: 'ID del producto', schema: new OAT\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Producto encontrado'),
            new OAT\Response(response: 404, description: 'Producto no encontrado'),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        $product->load('currency');

        return response()->json($product);
    }

    #[OAT\Put(
        path: '/api/products/{id}',
        summary: 'Actualizar un producto',
        description: 'Actualiza un producto existente',
        operationId: 'updateProduct',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, description: 'ID del producto', schema: new OAT\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['name', 'description', 'price', 'currency_id', 'tax_cost', 'manufacturing_cost'],
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Laptop Pro 14'),
                    new OAT\Property(property: 'description', type: 'string', example: 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.'),
                    new OAT\Property(property: 'price', type: 'number', format: 'float', example: 1499.00),
                    new OAT\Property(property: 'currency_id', type: 'integer', example: 1),
                    new OAT\Property(property: 'tax_cost', type: 'number', format: 'float', example: 284.81),
                    new OAT\Property(property: 'manufacturing_cost', type: 'number', format: 'float', example: 890.00),
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 200, description: 'Producto actualizado correctamente'),
            new OAT\Response(response: 404, description: 'Producto no encontrado'),
            new OAT\Response(response: 422, description: 'Error de validacion'),
        ]
    )]
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate(
            $this->productValidationRules(),
            $this->productValidationMessages(),
        );

        $product->update($validated);
        $product->load('currency');

        return response()->json($product);
    }

    private function productValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'gte:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'tax_cost' => ['required', 'numeric', 'gte:0', 'lte:price'],
            'manufacturing_cost' => ['required', 'numeric', 'gte:0', 'lte:price'],
        ];
    }

    private function productValidationMessages(): array
    {
        return [
            'price.gte' => 'El precio no puede ser negativo.',
            'tax_cost.gte' => 'El impuesto no puede ser negativo.',
            'tax_cost.lte' => 'El impuesto no puede ser mayor al precio.',
            'manufacturing_cost.gte' => 'El costo de fabricacion no puede ser negativo.',
            'manufacturing_cost.lte' => 'El costo de fabricacion no puede exceder el precio de venta.',
        ];
    }

    #[OAT\Delete(
        path: '/api/products/{id}',
        summary: 'Eliminar un producto',
        description: 'Elimina un producto existente',
        operationId: 'destroyProduct',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, description: 'ID del producto', schema: new OAT\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OAT\Response(response: 204, description: 'Producto eliminado correctamente'),
            new OAT\Response(response: 404, description: 'Producto no encontrado'),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(status: 204);
    }
}
