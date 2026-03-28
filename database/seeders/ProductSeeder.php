<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $currencies = Currency::query()
            ->whereIn('symbol', ['USD', 'EUR', 'COP'])
            ->get()
            ->keyBy('symbol');

        $products = [
            [
                'name' => 'Laptop Pro 14',
                'description' => 'Laptop profesional de 14 pulgadas para trabajo y desarrollo.',
                'price' => 1499.00,
                'currency_id' => $currencies['USD']->id,
                'tax_cost' => 284.81,
                'manufacturing_cost' => 890.00,
            ],
            [
                'name' => 'Mechanical Keyboard TKL',
                'description' => 'Teclado mecanico compacto con switches tactiles.',
                'price' => 129.90,
                'currency_id' => $currencies['USD']->id,
                'tax_cost' => 24.68,
                'manufacturing_cost' => 58.50,
            ],
            [
                'name' => 'Noise Cancelling Headphones',
                'description' => 'Audifonos inalambricos con cancelacion activa de ruido.',
                'price' => 249.50,
                'currency_id' => $currencies['EUR']->id,
                'tax_cost' => 47.41,
                'manufacturing_cost' => 121.00,
            ],
            [
                'name' => '4K Monitor 27',
                'description' => 'Monitor 4K de 27 pulgadas orientado a productividad y diseno.',
                'price' => 1899000.00,
                'currency_id' => $currencies['COP']->id,
                'tax_cost' => 360810.00,
                'manufacturing_cost' => 1045000.00,
            ],
            [
                'name' => 'Ergonomic Mouse',
                'description' => 'Mouse ergonomico para jornadas prolongadas de trabajo.',
                'price' => 79.99,
                'currency_id' => $currencies['USD']->id,
                'tax_cost' => 15.20,
                'manufacturing_cost' => 31.00,
            ],
            [
                'name' => 'USB-C Docking Station',
                'description' => 'Dock multipuerto con salida HDMI, Ethernet y carga PD.',
                'price' => 169.00,
                'currency_id' => $currencies['EUR']->id,
                'tax_cost' => 32.11,
                'manufacturing_cost' => 76.00,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                $product,
            );
        }
    }
}
