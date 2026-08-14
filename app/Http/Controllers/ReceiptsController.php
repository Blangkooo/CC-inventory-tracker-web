<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Receipt;
use App\Services\OcrService;
use App\Services\ReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Session-authed web counterpart to Api\ReceiptController — same
 * OcrService/ReconciliationService, no HTTP hop, since the JWT-guarded
 * API can't be called directly from a session-authenticated page.
 */
class ReceiptsController extends Controller
{
    public function __construct(
        private OcrService $ocr,
        private ReconciliationService $reconciliation
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $receipts = Receipt::with('matchedTransaction.product', 'branch', 'user')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->paginate(20);

        $summaryQuery = fn ($status) => Receipt::when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('reconciliation_status', $status))
            ->count();

        $summary = [
            'total' => $summaryQuery(null),
            'matched' => $summaryQuery('matched'),
            'mismatched' => $summaryQuery('mismatched'),
            'pending' => $summaryQuery('pending'),
            'unreadable' => $summaryQuery('unreadable'),
        ];

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        return view('receipts.index', compact('receipts', 'summary', 'branches'));
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $isManager = $user->isManager();

        $validated = $request->validate([
            'branch_id' => [$isManager ? 'nullable' : 'required', 'exists:branches,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $branchId = $isManager ? $user->branch_id : $validated['branch_id'];

        $path = $request->file('image')->store('receipts', 'public');
        $fullPath = storage_path('app/public/'.$path);

        $rawText = $this->ocr->extractText($fullPath);
        $parsedAmount = $this->ocr->parseTotalAmount($rawText);

        $receipt = Receipt::create([
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'image_path' => $path,
            'raw_ocr_text' => $rawText,
            'parsed_total_amount' => $parsedAmount,
            'reconciliation_status' => 'pending',
            'scanned_at' => now(),
        ]);

        $receipt = $this->reconciliation->reconcile($receipt);

        return response()->json([
            'success' => true,
            'receipt' => $receipt->load('matchedTransaction.product', 'branch', 'user'),
        ], 201);
    }
}
