<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $month = $request->query('month')
            ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
            : now()->startOfMonth();

        $events = CalendarEvent::with('branch', 'creator')
            ->whereBetween('starts_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()->endOfDay()])
            ->when($isManager, fn ($q) => $q->where(fn ($q2) => $q2->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($e) => $e->starts_at->format('Y-m-d'));

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        return view('calendar.index', [
            'month' => $month,
            'events' => $events,
            'branches' => $branches,
            'prevMonth' => $month->copy()->subMonthNoOverflow()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonthNoOverflow()->format('Y-m'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:shift,meeting,delivery,maintenance,other'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['nullable', 'boolean'],
        ]);

        $validated['created_by'] = auth()->id();

        $event = CalendarEvent::create($validated);

        return response()->json(['success' => true, 'event' => $event->load('branch', 'creator')], 201);
    }

    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:shift,meeting,delivery,maintenance,other'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['nullable', 'boolean'],
        ]);

        $event->update($validated);

        return response()->json(['success' => true, 'event' => $event->load('branch', 'creator')]);
    }

    public function destroy(CalendarEvent $event): JsonResponse
    {
        $event->delete();

        return response()->json(['success' => true]);
    }
}
