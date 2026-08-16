<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Models\Branch;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LegalPapersController extends Controller
{
    use AuthorizesBranchAccess;

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

        $expired = $documents->filter(fn ($d) => $d->isExpired());
        $expiring30 = $documents->filter(fn ($d) => $d->isExpiringSoon(30));
        $expiring60 = $documents->filter(fn ($d) => $d->isExpiringSoon(60) && ! $d->isExpiringSoon(30));

        return view('legal-papers.index', [
            'documents' => $documents,
            'branches' => $branches,
            'expiring_30_count' => $expiring30->count(),
            'expiring_60_count' => $expiring60->count(),
            'expired_count' => $expired->count(),
            'active_count' => $documents->count() - $expired->count(),
        ]);
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
        $this->authorizeBranchOrCompanyWide($document->branch_id);

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
        $this->authorizeBranchOrCompanyWide($document->branch_id);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function download(LegalDocument $document): RedirectResponse
    {
        $this->authorizeBranchOrCompanyWide($document->branch_id);

        return redirect(Storage::url($document->file_path));
    }
}
