<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['branch_id', 'shift_log_id', 'type', 'message', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shiftLog()
    {
        return $this->belongsTo(ShiftLog::class);
    }
}
