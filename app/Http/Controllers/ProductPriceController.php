<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

class ProductPriceController extends Controller
{
    #[OAT\Get(
        path: '/api/products/{product}/prices',
        summary: 'Obtener lista de precios de un producto',
        description: 'Retorna todos los precios de un producto en sus diferentes divisas',
        operationId: 'getProductPrices',
        tags: ['Product Prices'],
        parameters: [
            new OAT\Parameter(
                name: 'product',
                in: 'path',
                required: true,
                description: 'ID del producto',
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Lista de precios del producto en formato JSON',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        type: 'object',
                        properties: [
                            new OAT\Property(property: 'id', type: 'integer', example: 1),
                            new OAT\Property(property: 'product_id', type: 'integer', example: 1),
                            new OAT\Property(property: 'currency_id', type: 'integer', example: 2),
                            new OAT\Property(property: 'price', type: 'number', format: 'float', example: 1379.00),
                            new OAT\Property(
                                property: 'currency',
                                properties: [
                                    new OAT\Property(property: 'id', type: 'integer', example: 2),
                                    new OAT\Property(property: 'name', type: 'string', example: 'Euro'),
                                    new OAT\Property(property: 'symbol', type: 'string', example: 'EUR'),
                                    new OAT\Property(property: 'exchange_rate', type: 'number', format: 'float', example: 0.92),
                                ],
                                type: 'object'
                            ),
                        ]
                    )
                )
            ),
            new OAT\Response(response: 404, description: 'Producto no encontrado'),
        ]
    )]
    public function index(Product $product): JsonResponse
    {
        $prices = $product->prices()
            ->with('currency')
            ->orderBy('id')
            ->get();

        return response()->json($prices);
    }

    #[OAT\Post(
        path: '/api/products/{product}/prices',
        summary: 'Crear un nuevo precio para un producto',
        description: 'Crea un precio adicional para un producto en una divisa especifica',
        operationId: 'storeProductPrice',
        tags: ['Product Prices'],
        parameters: [
            new OAT\Parameter(
                name: 'product',
                in: 'path',
                required: true,
                description: 'ID del producto',
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['currency_id', 'price'],
                properties: [
                    new OAT\Property(property: 'currency_id', type: 'integer', example: 2),
                    new OAT\Property(property: 'price', type: 'number', format: 'float', example: 1379.00),
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 201, description: 'Precio creado correctamente'),
            new OAT\Response(response: 404, description: 'Producto no encontrado'),
            new OAT\Response(response: 422, description: 'Error de validacion'),
        ]
    )]
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
                Rule::unique('product_prices', 'currency_id')->where(
                    fn ($query) => $query->where('product_id', $product->id)
                ),
            ],
            'price' => ['required', 'numeric'],
        ], [
            'currency_id.unique' => 'Ya existe un precio registrado para esta moneda en este producto.',
        ]);

        $price = $product->prices()->create($validated)->load(['product', 'currency']);

        return response()->json($price, 201);
    }
}
