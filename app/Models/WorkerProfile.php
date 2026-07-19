<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'birthday',
        'senior_high',
        'college',
        'partner_contact',
        'mother_contact',
        'skills',
        'notes',
        'work_schedule',
        'performance_metrics',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'skills'              => 'array',
            'work_schedule'       => 'array',
            'performance_metrics' => 'array',
            'rating'              => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
