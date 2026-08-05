<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'meeting_type',
        'location',
        'status',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the branch that owns the meeting.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the meeting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users attending this meeting.
     */
    public function attendees()
    {
        return $this->belongsToMany(User::class, 'meeting_attendees')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    /**
     * Scope to get meetings for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get meetings for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get upcoming meetings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->orderBy('date')
                     ->orderBy('start_time');
    }

    /**
     * Get formatted time range.
     */
    public function getTimeRangeAttribute(): string
    {
        return "{$this->start_time} - {$this->end_time}";
    }
}
