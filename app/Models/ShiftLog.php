<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftLog extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'opening_stock', 'closing_stock',
        'time_in', 'time_out', 'variance', 'flagged', 'status',
    ];

    protected $casts = ['flagged' => 'boolean'];
}
