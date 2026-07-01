<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'type',
    'severity',
    'ingredient_id',
    'shift_log_id',
    'expected_value',
    'actual_value',
    'variance',
    'details',
    'status',
])]
class DiscrepancyAlert extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'severity' => 'string',
            'expected_value' => 'decimal:3',
            'actual_value' => 'decimal:3',
            'variance' => 'decimal:3',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function shiftLog(): BelongsTo
    {
        return $this->belongsTo(ShiftLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
