<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductPriceController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $prices = $product->prices()
            ->with('currency')
            ->orderBy('id')
            ->get();

        return response()->json($prices);
    }

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
        ]);

        $price = $product->prices()->create($validated)->load(['product', 'currency']);

        return response()->json($price, 201);
    }
}
