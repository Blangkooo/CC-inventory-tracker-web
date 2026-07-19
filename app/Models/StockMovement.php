<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'branch_stock_id',
    'type',
    'quantity_change',
    'quantity_before',
    'quantity_after',
    'user_id',
    'notes',
])]
class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_INITIAL = 'initial';

    public const TYPE_RESTOCK = 'restock';

    public const TYPE_SALE = 'sale';

    public const TYPE_SHIFT_CORRECTION = 'shift_correction';

    protected function casts(): array
    {
        return [
            'quantity_change' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
        ];
    }

    public function branchStock(): BelongsTo
    {
        return $this->belongsTo(BranchStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // reference() MorphTo removed — not currently used
}
