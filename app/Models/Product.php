<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (self $product): void {
            $product->syncBasePrice();
        });

        static::updated(function (self $product): void {
            if (! $product->wasChanged(['price', 'currency_id'])) {
                return;
            }

            DB::transaction(function () use ($product): void {
                if ($product->wasChanged('currency_id')) {
                    $product->prices()
                        ->where('currency_id', $product->getOriginal('currency_id'))
                        ->delete();
                }

                $product->syncBasePrice();
            });
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency_id',
        'tax_cost',
        'manufacturing_cost',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'currency_id' => 'integer',
            'tax_cost' => 'decimal:2',
            'manufacturing_cost' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function syncBasePrice(): void
    {
        $this->prices()->updateOrCreate(
            ['currency_id' => $this->currency_id],
            ['price' => $this->price],
        );
    }
}
