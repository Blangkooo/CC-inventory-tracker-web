<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'unit'])]
class Ingredient extends Model
{
    use HasFactory;

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function shiftStockCounts(): HasMany
    {
        return $this->hasMany(ShiftStockCount::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot('unit_cost', 'is_primary')
            ->withTimestamps();
    }

    public function primarySupplier(): ?Supplier
    {
        return $this->suppliers()->wherePivot('is_primary', true)->first();
    }

    public function purchaseHistory(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }
}
