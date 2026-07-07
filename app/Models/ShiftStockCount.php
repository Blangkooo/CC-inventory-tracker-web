<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'shift_log_id',
    'ingredient_id',
    'opening_quantity',
    'closing_quantity_expected',
    'closing_quantity_actual',
    'variance',
])]
class ShiftStockCount extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opening_quantity' => 'decimal:3',
            'closing_quantity_expected' => 'decimal:3',
            'closing_quantity_actual' => 'decimal:3',
            'variance' => 'decimal:3',
        ];
    }

    public function shiftLog(): BelongsTo
    {
        return $this->belongsTo(ShiftLog::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
