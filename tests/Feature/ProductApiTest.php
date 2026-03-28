<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_listed(): void
    {
        $currency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        Product::create([
            'name' => 'Teclado',
            'description' => 'Teclado mecanico',
            'price' => 120000,
            'currency_id' => $currency->id,
            'tax_cost' => 22800,
            'manufacturing_cost' => 70000,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Teclado')
            ->assertJsonPath('0.currency.symbol', 'COP');
    }

    public function test_a_product_can_be_created(): void
    {
        $currency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $response = $this->postJson('/api/products', [
            'name' => 'Monitor',
            'description' => 'Monitor 27 pulgadas',
            'price' => 399.99,
            'currency_id' => $currency->id,
            'tax_cost' => 76,
            'manufacturing_cost' => 220,
        ]);

        $response
            ->assertCreated()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('name', 'Monitor')
            ->assertJsonPath('currency.id', $currency->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Monitor',
            'currency_id' => $currency->id,
        ]);

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $response->json('id'),
            'currency_id' => $currency->id,
            'price' => 399.99,
        ]);
    }

    public function test_decimal_product_fields_can_be_sent_as_numeric_strings(): void
    {
        $currency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $response = $this->postJson('/api/products', [
            'name' => 'Monitor String Price',
            'description' => 'Producto enviado con decimales como string.',
            'price' => '399.99',
            'currency_id' => $currency->id,
            'tax_cost' => '76.50',
            'manufacturing_cost' => '220.10',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('price', '399.99')
            ->assertJsonPath('tax_cost', '76.50')
            ->assertJsonPath('manufacturing_cost', '220.10');

        $this->assertDatabaseHas('products', [
            'name' => 'Monitor String Price',
            'currency_id' => $currency->id,
            'price' => 399.99,
            'tax_cost' => 76.50,
            'manufacturing_cost' => 220.10,
        ]);
    }

    public function test_a_product_can_be_retrieved_updated_and_deleted(): void
    {
        $currency = Currency::create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.92,
        ]);

        $product = Product::create([
            'name' => 'Mouse',
            'description' => 'Mouse inalambrico',
            'price' => 25.50,
            'currency_id' => $currency->id,
            'tax_cost' => 4.85,
            'manufacturing_cost' => 10,
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('id', $product->id);

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Mouse Pro',
            'description' => 'Mouse inalambrico con sensor mejorado',
            'price' => 30.00,
            'currency_id' => $currency->id,
            'tax_cost' => 5.70,
            'manufacturing_cost' => 12.30,
        ])
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('name', 'Mouse Pro');

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $currency->id,
            'price' => 30.00,
        ]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_deleting_a_product_cascades_its_product_prices(): void
    {
        $baseCurrency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $eurCurrency = Currency::create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.92,
        ]);

        $product = Product::create([
            'name' => 'Laptop Cascade',
            'description' => 'Producto para probar cascade delete.',
            'price' => 1000,
            'currency_id' => $baseCurrency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 500,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'currency_id' => $eurCurrency->id,
            'price' => 950,
        ]);

        $product->delete();

        $this->assertDatabaseMissing('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $baseCurrency->id,
        ]);

        $this->assertDatabaseMissing('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $eurCurrency->id,
        ]);
    }

    public function test_product_prices_can_be_listed_and_created(): void
    {
        $baseCurrency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        $usdCurrency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 0.00026,
        ]);

        $product = Product::create([
            'name' => 'Portatil',
            'description' => 'Portatil de trabajo',
            'price' => 3500000,
            'currency_id' => $baseCurrency->id,
            'tax_cost' => 665000,
            'manufacturing_cost' => 2100000,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'currency_id' => $usdCurrency->id,
            'price' => 910.25,
        ]);

        $this->getJson("/api/products/{$product->id}/prices")
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonCount(2)
            ->assertJsonPath('0.currency.symbol', 'COP')
            ->assertJsonPath('0.price', '3500000.00')
            ->assertJsonPath('1.currency.symbol', 'USD');

        $eurCurrency = Currency::create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.00024,
        ]);

        $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $eurCurrency->id,
            'price' => 845.10,
        ])
            ->assertCreated()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('product.id', $product->id)
            ->assertJsonPath('currency.symbol', 'EUR');

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $eurCurrency->id,
        ]);
    }

    public function test_base_price_is_recreated_when_product_base_currency_changes(): void
    {
        $usdCurrency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $eurCurrency = Currency::create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.92,
        ]);

        $gbpCurrency = Currency::create([
            'name' => 'Libra esterlina',
            'symbol' => 'GBP',
            'exchange_rate' => 0.85,
        ]);

        $product = Product::create([
            'name' => 'Laptop Base',
            'description' => 'Producto con precio base.',
            'price' => 1000,
            'currency_id' => $usdCurrency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 500,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'currency_id' => $gbpCurrency->id,
            'price' => 850,
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Laptop Base',
            'description' => 'Producto con precio base.',
            'price' => 950,
            'currency_id' => $eurCurrency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 500,
        ])
            ->assertOk();

        $this->assertDatabaseMissing('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $usdCurrency->id,
            'price' => 1000,
        ]);

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $eurCurrency->id,
            'price' => 950,
        ]);
    }

    public function test_deleting_a_currency_cascades_related_products_and_product_prices(): void
    {
        $usdCurrency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $eurCurrency = Currency::create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.92,
        ]);

        $productWithUsdBase = Product::create([
            'name' => 'Laptop USD',
            'description' => 'Producto base USD.',
            'price' => 1000,
            'currency_id' => $usdCurrency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 500,
        ]);

        $productWithEurBase = Product::create([
            'name' => 'Laptop EUR',
            'description' => 'Producto base EUR.',
            'price' => 900,
            'currency_id' => $eurCurrency->id,
            'tax_cost' => 100,
            'manufacturing_cost' => 450,
        ]);

        ProductPrice::create([
            'product_id' => $productWithUsdBase->id,
            'currency_id' => $eurCurrency->id,
            'price' => 950,
        ]);

        $eurCurrency->delete();

        $this->assertDatabaseHas('products', [
            'id' => $productWithUsdBase->id,
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $productWithEurBase->id,
        ]);

        $this->assertDatabaseMissing('product_prices', [
            'product_id' => $productWithUsdBase->id,
            'currency_id' => $eurCurrency->id,
        ]);

        $this->assertDatabaseMissing('product_prices', [
            'product_id' => $productWithEurBase->id,
            'currency_id' => $eurCurrency->id,
        ]);
    }

    public function test_product_price_currency_must_be_unique_per_product(): void
    {
        $baseCurrency = Currency::create([
            'name' => 'Dolar estadounidense',
            'symbol' => 'USD',
            'exchange_rate' => 1,
        ]);

        $product = Product::create([
            'name' => 'Laptop Base',
            'description' => 'Producto con precio base.',
            'price' => 1000,
            'currency_id' => $baseCurrency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 500,
        ]);

        $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $baseCurrency->id,
            'price' => 1000,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Los datos enviados no son validos.',
                'errors' => [
                    'currency_id' => ['Ya existe un precio registrado para esta moneda en este producto.'],
                ],
            ]);
    }

    public function test_validation_errors_are_returned_as_json(): void
    {
        $response = $this->postJson('/api/products', []);

        $response
            ->assertUnprocessable()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonValidationErrors([
                'name',
                'description',
                'price',
                'currency_id',
                'tax_cost',
                'manufacturing_cost',
            ]);
    }

    public function test_missing_product_is_returned_as_json_even_when_html_is_requested(): void
    {
        $this->get('/api/products/999999', [
            'Accept' => 'text/html',
        ])
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'message' => 'Recurso no encontrado',
            ]);
    }

    public function test_update_validation_errors_are_returned_as_json_even_when_html_is_requested(): void
    {
        $currency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        $product = Product::create([
            'name' => 'Laptop Pro 14',
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => 1499,
            'currency_id' => $currency->id,
            'tax_cost' => 284.81,
            'manufacturing_cost' => 890,
        ]);

        $this->put("/api/products/{$product->id}", [
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => 1499,
            'currency_id' => $currency->id,
            'tax_cost' => 284.81,
            'manufacturing_cost' => 890,
        ], [
            'Accept' => 'text/html',
        ])
            ->assertUnprocessable()
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'message' => 'Los datos enviados no son validos.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ]);
    }

    public function test_store_rejects_negative_price_and_costs_greater_than_price(): void
    {
        $currency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        $this->postJson('/api/products', [
            'name' => 'Laptop Pro 14',
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => -1499,
            'currency_id' => $currency->id,
            'tax_cost' => 284.81,
            'manufacturing_cost' => 890,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Los datos enviados no son validos.',
                'errors' => [
                    'price' => ['El precio no puede ser negativo.'],
                    'tax_cost' => ['El impuesto no puede ser mayor al precio.'],
                    'manufacturing_cost' => ['El costo de fabricacion no puede exceder el precio de venta.'],
                ],
            ]);
    }

    public function test_update_rejects_costs_greater_than_price(): void
    {
        $currency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        $product = Product::create([
            'name' => 'Laptop Pro 14',
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => 1499,
            'currency_id' => $currency->id,
            'tax_cost' => 284.81,
            'manufacturing_cost' => 890,
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Laptop Pro 14',
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => 100,
            'currency_id' => $currency->id,
            'tax_cost' => 120,
            'manufacturing_cost' => 110,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Los datos enviados no son validos.',
                'errors' => [
                    'tax_cost' => ['El impuesto no puede ser mayor al precio.'],
                    'manufacturing_cost' => ['El costo de fabricacion no puede exceder el precio de venta.'],
                ],
            ]);
    }

    public function test_store_rejects_negative_tax_and_manufacturing_costs(): void
    {
        $currency = Currency::create([
            'name' => 'Peso colombiano',
            'symbol' => 'COP',
            'exchange_rate' => 1,
        ]);

        $this->postJson('/api/products', [
            'name' => 'baby dont say no',
            'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
            'price' => 1499,
            'currency_id' => $currency->id,
            'tax_cost' => -284.81,
            'manufacturing_cost' => -890,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Los datos enviados no son validos.',
                'errors' => [
                    'tax_cost' => ['El impuesto no puede ser negativo.'],
                    'manufacturing_cost' => ['El costo de fabricacion no puede ser negativo.'],
                ],
            ]);
    }
}
