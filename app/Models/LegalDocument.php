<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'uploaded_by', 'title', 'type', 'file_path', 'issued_at', 'expires_at', 'notes'])]
class LegalDocument extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at !== null
            && ! $this->expires_at->isPast()
            // Carbon 3's diffInDays() is signed by default (negative when
            // $this is in the future), so it must be wrapped in abs() —
            // unwrapped, this check was true for every future date, no
            // matter how far out, not just ones inside the window.
            && abs($this->expires_at->diffInDays(now())) <= $days;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
