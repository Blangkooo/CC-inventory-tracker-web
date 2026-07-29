<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LegalPapersController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $documents = LegalDocument::with('branch', 'uploader')
            ->when($isManager, fn ($q) => $q->where(fn ($q2) => $q2->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->latest()
            ->get();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        return view('legal-papers.index', compact('documents', 'branches'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:permit,license,contract,insurance,tax,other'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['file_path'] = $request->file('file')->store('legal-documents', 'public');
        $validated['uploaded_by'] = auth()->id();
        unset($validated['file']);

        $document = LegalDocument::create($validated);

        return response()->json(['success' => true, 'document' => $document->load('branch', 'uploader')], 201);
    }

    public function update(Request $request, LegalDocument $document): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:permit,license,contract,insurance,tax,other'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $document->update($validated);

        return response()->json(['success' => true, 'document' => $document->load('branch', 'uploader')]);
    }

    public function destroy(LegalDocument $document): JsonResponse
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function download(LegalDocument $document): RedirectResponse
    {
        return redirect(Storage::url($document->file_path));
    }
}
