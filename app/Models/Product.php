<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'price', 'price_large', 'procedure', 'is_active'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_large' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The price for a given recipe size — falls back to the regular price
     * when no large-size price has been set yet, so existing products with
     * only one price keep working unchanged.
     */
    public function priceForSize(string $size): float
    {
        if ($size === Recipe::SIZE_LARGE && $this->price_large !== null) {
            return (float) $this->price_large;
        }

        return (float) $this->price;
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
