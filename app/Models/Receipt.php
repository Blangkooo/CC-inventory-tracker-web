<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'branch_id',
    'user_id',
    'image_path',
    'raw_ocr_text',
    'parsed_total_amount',
    'matched_transaction_id',
    'reconciliation_status',
    'scanned_at',
])]
class Receipt extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'parsed_total_amount' => 'decimal:2',
            'scanned_at' => 'datetime',
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

    public function matchedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'matched_transaction_id');
    }
}
