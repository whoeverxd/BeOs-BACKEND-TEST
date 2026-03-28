<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => 'USD', 'exchange_rate' => 1.000000],
            ['name' => 'Euro', 'symbol' => 'EUR', 'exchange_rate' => 0.920000],
            ['name' => 'Colombian Peso', 'symbol' => 'COP', 'exchange_rate' => 3915.000000],
            ['name' => 'Mexican Peso', 'symbol' => 'MXN', 'exchange_rate' => 16.870000],
            ['name' => 'Argentine Peso', 'symbol' => 'ARS', 'exchange_rate' => 1072.500000],
            ['name' => 'Brazilian Real', 'symbol' => 'BRL', 'exchange_rate' => 5.020000],
            ['name' => 'Chilean Peso', 'symbol' => 'CLP', 'exchange_rate' => 952.300000],
            ['name' => 'Peruvian Sol', 'symbol' => 'PEN', 'exchange_rate' => 3.730000],
            ['name' => 'British Pound', 'symbol' => 'GBP', 'exchange_rate' => 0.790000],
            ['name' => 'Canadian Dollar', 'symbol' => 'CAD', 'exchange_rate' => 1.350000],
            ['name' => 'Japanese Yen', 'symbol' => 'JPY', 'exchange_rate' => 151.200000],
            ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'exchange_rate' => 0.900000],
            ['name' => 'Chinese Yuan', 'symbol' => 'CNY', 'exchange_rate' => 7.230000],
            ['name' => 'Australian Dollar', 'symbol' => 'AUD', 'exchange_rate' => 1.530000],
            ['name' => 'New Zealand Dollar', 'symbol' => 'NZD', 'exchange_rate' => 1.670000],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['symbol' => $currency['symbol']],
                $currency,
            );
        }
    }
}
