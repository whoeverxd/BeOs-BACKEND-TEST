<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductPriceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! Schema::hasTable('product_prices')) {
            return;
        }

        $currencies = Currency::query()
            ->whereIn('symbol', ['USD', 'EUR', 'COP', 'GBP', 'MXN'])
            ->get()
            ->keyBy('symbol');

        $products = Product::query()
            ->whereIn('name', [
                'Laptop Pro 14',
                'Mechanical Keyboard TKL',
                'Noise Cancelling Headphones',
                '4K Monitor 27',
                'Ergonomic Mouse',
                'USB-C Docking Station',
            ])
            ->get()
            ->keyBy('name');

        $prices = [
            ['product' => 'Laptop Pro 14', 'currency' => 'EUR', 'price' => 1379.00],
            ['product' => 'Laptop Pro 14', 'currency' => 'COP', 'price' => 5868585.00],
            ['product' => 'Mechanical Keyboard TKL', 'currency' => 'EUR', 'price' => 119.50],
            ['product' => 'Mechanical Keyboard TKL', 'currency' => 'MXN', 'price' => 2191.00],
            ['product' => 'Noise Cancelling Headphones', 'currency' => 'USD', 'price' => 271.20],
            ['product' => 'Noise Cancelling Headphones', 'currency' => 'GBP', 'price' => 197.10],
            ['product' => '4K Monitor 27', 'currency' => 'USD', 'price' => 485.00],
            ['product' => '4K Monitor 27', 'currency' => 'EUR', 'price' => 446.20],
            ['product' => 'Ergonomic Mouse', 'currency' => 'EUR', 'price' => 73.60],
            ['product' => 'Ergonomic Mouse', 'currency' => 'COP', 'price' => 313160.85],
            ['product' => 'USB-C Docking Station', 'currency' => 'USD', 'price' => 183.70],
            ['product' => 'USB-C Docking Station', 'currency' => 'MXN', 'price' => 2849.00],
        ];

        foreach ($prices as $item) {
            ProductPrice::query()->updateOrCreate(
                [
                    'product_id' => $products[$item['product']]->id,
                    'currency_id' => $currencies[$item['currency']]->id,
                ],
                [
                    'price' => $item['price'],
                ],
            );
        }
    }
}
