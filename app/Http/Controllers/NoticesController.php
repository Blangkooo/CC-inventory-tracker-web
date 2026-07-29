<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticesController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $notices = Notice::with('branch', 'poster')
            ->when($isManager, fn ($q) => $q->where(fn ($q2) => $q2->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->latest()
            ->get();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        return view('notices.index', compact('notices', 'branches'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $validated['posted_by'] = auth()->id();

        $notice = Notice::create($validated);

        return response()->json(['success' => true, 'notice' => $notice->load('branch', 'poster')], 201);
    }

    public function destroy(Notice $notice): JsonResponse
    {
        $notice->delete();

        return response()->json(['success' => true]);
    }
}
