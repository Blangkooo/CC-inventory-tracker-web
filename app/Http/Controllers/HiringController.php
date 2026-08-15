<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HiringController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $openings = JobOpening::with('branch', 'applicants')
            ->when($isManager, fn ($q) => $q->where(fn ($q2) => $q2->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->latest()
            ->get();

        $applicants = JobApplicant::with('opening.branch')
            ->when($isManager, fn ($q) => $q->whereHas('opening', fn ($q2) => $q2->where(fn ($q3) => $q3->where('branch_id', $branchId)->orWhereNull('branch_id'))))
            ->latest()
            ->get();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        return view('hiring.index', [
            'openings' => $openings,
            'applicants' => $applicants,
            'branches' => $branches,
            'kpi_open_positions' => $openings->where('status', 'open')->count(),
            'kpi_applicants' => $applicants->count(),
            'kpi_in_interview' => $applicants->where('status', 'interviewed')->count(),
            'kpi_accepted' => $applicants->where('status', 'hired')->count(),
            'pipeline_stages' => [
                'applied' => 'Applied',
                'shortlisted' => 'Shortlisted',
                'interviewed' => 'In Interview',
                'hired' => 'Hired',
                'rejected' => 'Rejected',
            ],
        ]);
    }

    public function storeOpening(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'open';
        $validated['posted_by'] = auth()->id();

        $opening = JobOpening::create($validated);

        return response()->json(['success' => true, 'opening' => $opening->load('branch')], 201);
    }

    public function updateOpening(Request $request, JobOpening $opening): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
        ]);

        $opening->update($validated);

        return response()->json(['success' => true, 'opening' => $opening->load('branch')]);
    }

    public function destroyOpening(JobOpening $opening): JsonResponse
    {
        $opening->delete();

        return response()->json(['success' => true]);
    }

    public function storeApplicant(Request $request, JobOpening $opening): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('resume')) {
            $validated['resume_path'] = $request->file('resume')->store('resumes', 'public');
        }
        unset($validated['resume']);

        $applicant = $opening->applicants()->create($validated);

        return response()->json(['success' => true, 'applicant' => $applicant->load('opening')], 201);
    }

    public function updateApplicantStatus(Request $request, JobApplicant $applicant): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:applied,shortlisted,interviewed,hired,rejected'],
        ]);

        $applicant->update($validated);

        return response()->json(['success' => true, 'applicant' => $applicant->load('opening')]);
    }

    public function destroyApplicant(JobApplicant $applicant): JsonResponse
    {
        $applicant->delete();

        return response()->json(['success' => true]);
    }
}
