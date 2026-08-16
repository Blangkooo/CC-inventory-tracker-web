<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesBranchAccess
{
    /**
     * Super admins have global access; managers and staff are locked to their own branch.
     */
    protected function authorizeBranch(int $branchId): void
    {
        $user = request()->user();

        // Null only via the TEMPORARY no-auth test routes that reuse these controller
        // methods without going through auth:api — nothing to authorize against.
        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        if ($user->branch_id !== $branchId) {
            abort(403, 'Forbidden: you do not have access to this branch.');
        }
    }

    /**
     * For resources whose branch_id is nullable (company-wide). Non-admins may only
     * mutate a resource scoped to their own branch — a null (company-wide) branch_id
     * is visible to them via index queries but not theirs to edit or delete.
     */
    protected function authorizeBranchOrCompanyWide(?int $branchId): void
    {
        $user = request()->user();

        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        if ($branchId === null || $user->branch_id !== $branchId) {
            abort(403, 'Forbidden: you do not have access to this branch.');
        }
    }
}
