<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'ingredient_id', 'current_quantity', 'min_threshold', 'last_updated_at'])]
class BranchStock extends Model
{
    use HasFactory;

    protected $table = 'branch_stock';

    protected function casts(): array
    {
        return [
            'current_quantity' => 'decimal:3',
            'min_threshold' => 'decimal:3',
            'last_updated_at' => 'datetime',
        ];
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_quantity <= 0) {
            return 'out';
        }

        if ($this->current_quantity <= $this->min_threshold) {
            return 'low';
        }

        return 'ok';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
