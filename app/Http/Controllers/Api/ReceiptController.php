<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Services\OcrService;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    use AuthorizesBranchAccess;

    public function __construct(
        private OcrService $ocr,
        private ReconciliationService $reconciliation
    ) {}

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $path = $request->file('image')->store('receipts', 'public');
        $fullPath = storage_path('app/public/'.$path);

        $rawText = $this->ocr->extractText($fullPath);
        $parsedAmount = $this->ocr->parseTotalAmount($rawText);

        $receipt = Receipt::create([
            'branch_id' => $validated['branch_id'],
            'user_id' => $request->user()?->id,
            'image_path' => $path,
            'raw_ocr_text' => $rawText,
            'parsed_total_amount' => $parsedAmount,
            'reconciliation_status' => 'pending',
            'scanned_at' => now(),
        ]);

        $receipt = $this->reconciliation->reconcile($receipt);

        return response()->json([
            'message' => 'Receipt scanned successfully',
            'receipt' => $receipt->load('matchedTransaction.product', 'branch', 'user'),
            'ocr_text' => $rawText,
            'parsed_amount' => $parsedAmount,
            'reconciliation_status' => $receipt->reconciliation_status,
            'matched_transaction' => $receipt->relationLoaded('matchedTransaction') ? $receipt->matchedTransaction : null,
        ], 201);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $receipts = Receipt::with('matchedTransaction.product', 'branch', 'user')
            ->where('branch_id', $validated['branch_id'])
            ->latest()
            ->paginate(20);

        return response()->json($receipts);
    }

    public function show(Receipt $receipt)
    {
        $this->authorizeBranch($receipt->branch_id);

        return response()->json($receipt->load('matchedTransaction.product', 'branch', 'user'));
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $branchId = $validated['branch_id'];

        return response()->json([
            'total_scanned' => Receipt::where('branch_id', $branchId)->count(),
            'matched' => Receipt::where('branch_id', $branchId)->where('reconciliation_status', 'matched')->count(),
            'mismatched' => Receipt::where('branch_id', $branchId)->where('reconciliation_status', 'mismatched')->count(),
            'pending' => Receipt::where('branch_id', $branchId)->where('reconciliation_status', 'pending')->count(),
            'unreadable' => Receipt::where('branch_id', $branchId)->where('reconciliation_status', 'unreadable')->count(),
        ]);
    }
}
