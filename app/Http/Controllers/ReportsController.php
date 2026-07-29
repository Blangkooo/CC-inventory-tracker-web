<?php

namespace App\Http\Controllers;

use App\Models\BranchStock;
use App\Models\PurchaseHistory;
use App\Models\ShiftLog;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private const TYPES = [
        'sales' => ['label' => 'Sales', 'description' => 'Every recorded transaction — date, branch, product, cashier, amount.'],
        'inventory' => ['label' => 'Inventory', 'description' => 'Current stock levels per branch against the minimum threshold.'],
        'payroll' => ['label' => 'Payroll Preview', 'description' => 'Closed-shift hours per worker for the current month.'],
        'purchases' => ['label' => 'Purchases', 'description' => 'Ingredient restocking cost recorded from suppliers.'],
    ];

    public function index(): View
    {
        return view('reports.index', ['types' => self::TYPES]);
    }

    public function show(string $type): View
    {
        $data = $this->reportData($type);

        return view('reports.show', [
            'type' => $type,
            'label' => self::TYPES[$type]['label'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        $data = $this->reportData($type);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $data['columns']);
            foreach ($data['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, "{$type}-report-".now()->format('Y-m-d').'.csv');
    }

    public function exportPdf(string $type): Response
    {
        $data = $this->reportData($type);
        $user = auth()->user();

        // dompdf's bundled fonts have no ₱ glyph, so it renders as tofu.
        // Spell the currency out for the PDF only; CSV keeps the symbol.
        $peso = fn (string $s) => str_replace('₱', 'PHP', $s);

        $pdf = Pdf::loadView('reports.pdf', [
            'label' => self::TYPES[$type]['label'],
            'description' => self::TYPES[$type]['description'],
            'columns' => array_map($peso, $data['columns']),
            'rows' => array_map(fn ($row) => array_map($peso, $row), $data['rows']),
            'generatedBy' => $user->name,
            'scope' => $user->isManager() ? ($user->branch?->name ?? 'Assigned branch') : 'All branches',
            'generatedAt' => now()->format('M d, Y g:i A'),
        ])->setPaper('a4', count($data['columns']) > 5 ? 'landscape' : 'portrait');

        return $pdf->download("{$type}-report-".now()->format('Y-m-d').'.pdf');
    }

    /**
     * @return array{columns: array<string>, rows: array<array<string>>}
     */
    private function reportData(string $type): array
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        return match ($type) {
            'sales' => $this->salesData($isManager, $branchId),
            'inventory' => $this->inventoryData($isManager, $branchId),
            'payroll' => $this->payrollData($isManager, $branchId),
            'purchases' => $this->purchasesData(),
            default => abort(404, 'Unknown report type.'),
        };
    }

    private function salesData(bool $isManager, ?int $branchId): array
    {
        $rows = Transaction::with('branch', 'product', 'user')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(200)
            ->get()
            ->map(fn ($t) => [
                $t->created_at->format('Y-m-d H:i'),
                $t->branch?->name ?? '—',
                $t->product?->name ?? '—',
                $t->user?->name ?? '—',
                number_format((float) $t->total_amount, 2),
            ])->all();

        return ['columns' => ['Date', 'Branch', 'Product', 'Cashier', 'Amount (₱)'], 'rows' => $rows];
    }

    private function inventoryData(bool $isManager, ?int $branchId): array
    {
        $rows = BranchStock::with('branch', 'ingredient')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(fn ($s) => [
                $s->branch?->name ?? '—',
                $s->ingredient?->name ?? '—',
                number_format((float) $s->current_quantity, 3),
                number_format((float) $s->min_threshold, 3),
                ucfirst($s->stock_status),
            ])->all();

        return ['columns' => ['Branch', 'Ingredient', 'Current Qty', 'Min Threshold', 'Status'], 'rows' => $rows];
    }

    private function payrollData(bool $isManager, ?int $branchId): array
    {
        $rows = ShiftLog::with('user', 'branch')
            ->where('status', 'closed')
            ->whereNotNull('shift_end')
            ->where('shift_start', '>=', now()->startOfMonth())
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->groupBy('user_id')
            ->map(fn ($logs) => [
                $logs->first()->user?->name ?? '—',
                $logs->first()->branch?->name ?? '—',
                number_format($logs->sum(fn ($l) => $l->shift_start->diffInMinutes($l->shift_end) / 60), 1),
                $logs->count(),
            ])->values()->all();

        return ['columns' => ['Worker', 'Branch', 'Hours (this month)', 'Shifts'], 'rows' => $rows];
    }

    private function purchasesData(): array
    {
        $rows = PurchaseHistory::with('ingredient', 'supplier')
            ->latest('purchased_at')
            ->take(200)
            ->get()
            ->map(fn ($p) => [
                $p->purchased_at?->format('Y-m-d') ?? '—',
                $p->supplier?->name ?? '—',
                $p->ingredient?->name ?? '—',
                number_format((float) $p->quantity, 3),
                number_format((float) $p->unit_price, 2),
                number_format((float) $p->total_cost, 2),
            ])->all();

        return ['columns' => ['Date', 'Supplier', 'Ingredient', 'Quantity', 'Unit Price (₱)', 'Total Cost (₱)'], 'rows' => $rows];
    }
}
