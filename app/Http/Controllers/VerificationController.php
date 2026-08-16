<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LegalDocument;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))
            ->orderBy('name')
            ->get();

        $documents = LegalDocument::when($isManager, fn ($q) => $q->where(fn ($q2) => $q2->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->get()
            ->groupBy('type');

        $statusFor = function (?LegalDocument $doc): array {
            if (! $doc) {
                return ['Not on File', 'missing'];
            }
            if ($doc->isExpired()) {
                return ['Expired', 'missing'];
            }
            if ($doc->isExpiringSoon()) {
                return ['Expiring Soon', 'pending'];
            }

            return ['On File', 'verified'];
        };

        $docForBranchAndType = function (Branch $branch, string $type) use ($documents) {
            return ($documents->get($type) ?? collect())
                ->first(fn ($d) => $d->branch_id === $branch->id || $d->branch_id === null);
        };

        $permitRows = $branches->map(function ($branch) use ($docForBranchAndType, $statusFor) {
            [$status, $tone] = $statusFor($docForBranchAndType($branch, 'permit'));

            return [$branch->name, $status, $tone];
        })->all();

        $insuranceRows = $branches->map(function ($branch) use ($docForBranchAndType, $statusFor) {
            [$status, $tone] = $statusFor($docForBranchAndType($branch, 'insurance'));

            return [$branch->name, $status, $tone];
        })->all();

        $complianceTypes = [
            'license' => 'Business License',
            'tax' => 'Tax Registration',
            'contract' => 'Contracts on File',
        ];
        $complianceRows = collect($complianceTypes)->map(function ($label, $type) use ($documents, $statusFor) {
            $doc = ($documents->get($type) ?? collect())->sortByDesc('expires_at')->first();
            [$status, $tone] = $statusFor($doc);

            return [$label, $status, $tone];
        })->values()->all();

        return view('business.verification', [
            'branches' => $branches,
            'permitRows' => $permitRows,
            'complianceRows' => $complianceRows,
            'insuranceRows' => $insuranceRows,
        ]);
    }
}
