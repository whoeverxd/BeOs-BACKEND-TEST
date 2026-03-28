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

        $this->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
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
            ->assertJsonCount(1)
            ->assertJsonPath('0.currency.symbol', 'USD');

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
}
