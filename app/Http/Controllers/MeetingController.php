<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    use AuthorizesBranchAccess;

    /**
     * Display a listing of meetings for the calendar.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->isManager();

        $query = Meeting::with(['branch', 'creator']);

        // Filter by branch for managers
        if ($isManager && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        }

        // Filter by meeting type if provided
        if ($request->has('type')) {
            $query->where('meeting_type', $request->type);
        }

        $meetings = $query->orderBy('date')->orderBy('start_time')->get();

        return response()->json([
            'status' => true,
            'data' => $meetings,
        ]);
    }

    /**
     * Store a newly created meeting.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'string', 'max:10'],
            'end_time' => ['required', 'string', 'max:10'],
            'meeting_type' => ['nullable', 'in:meeting,task,event'],
            'location' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'attendee_ids' => ['nullable', 'array'],
            'attendee_ids.*' => ['exists:users,id'],
        ]);

        if ($user->isManager()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $validated['created_by'] = $user->id;
        $validated['status'] = 'scheduled';

        $meeting = Meeting::create($validated);

        // Attach attendees if provided
        if ($request->has('attendee_ids')) {
            $meeting->attendees()->attach($request->attendee_ids);
        }

        return response()->json([
            'status' => true,
            'message' => 'Meeting created successfully.',
            'data' => $meeting->load(['branch', 'creator', 'attendees']),
        ], 201);
    }

    /**
     * Display the specified meeting.
     */
    public function show(Meeting $meeting)
    {
        $this->authorizeBranchOrCompanyWide($meeting->branch_id);

        $meeting->load(['branch', 'creator', 'attendees']);

        return response()->json([
            'status' => true,
            'data' => $meeting,
        ]);
    }

    /**
     * Update the specified meeting.
     */
    public function update(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorizeBranchOrCompanyWide($meeting->branch_id);

        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'string', 'max:10'],
            'end_time' => ['sometimes', 'string', 'max:10'],
            'meeting_type' => ['nullable', 'in:meeting,task,event'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:scheduled,in_progress,completed,cancelled'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'attendee_ids' => ['nullable', 'array'],
            'attendee_ids.*' => ['exists:users,id'],
        ]);

        if ($user->isManager()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $meeting->update($validated);

        // Sync attendees if provided
        if ($request->has('attendee_ids')) {
            $meeting->attendees()->sync($request->attendee_ids);
        }

        return response()->json([
            'status' => true,
            'message' => 'Meeting updated successfully.',
            'data' => $meeting->load(['branch', 'creator', 'attendees']),
        ]);
    }

    /**
     * Remove the specified meeting.
     */
    public function destroy(Meeting $meeting): JsonResponse
    {
        $this->authorizeBranchOrCompanyWide($meeting->branch_id);

        $meeting->delete();

        return response()->json([
            'status' => true,
            'message' => 'Meeting deleted successfully.',
        ]);
    }

    /**
     * Get meetings for a specific week.
     */
    public function week(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->isManager();

        $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->get('end_date', now()->endOfWeek()->toDateString());

        $query = Meeting::with(['branch', 'creator'])
            ->forDateRange($startDate, $endDate);

        if ($isManager && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $meetings = $query->orderBy('date')->orderBy('start_time')->get();

        return response()->json([
            'status' => true,
            'data' => $meetings,
        ]);
    }

    /**
     * Get upcoming meetings.
     */
    public function upcoming()
    {
        $user = Auth::user();
        $isManager = $user->isManager();

        $query = Meeting::with(['branch', 'creator'])
            ->upcoming()
            ->take(10);

        if ($isManager && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $meetings = $query->get();

        return response()->json([
            'status' => true,
            'data' => $meetings,
        ]);
    }
}
