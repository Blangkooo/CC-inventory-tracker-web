<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['branch_id', 'user_id', 'shift_start', 'shift_end', 'status'])]
class ShiftLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'shift_start' => 'datetime',
            'shift_end' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(ShiftStockCount::class);
    }

    public function discrepancyAlerts(): HasMany
    {
        return $this->hasMany(DiscrepancyAlert::class);
    }
}
