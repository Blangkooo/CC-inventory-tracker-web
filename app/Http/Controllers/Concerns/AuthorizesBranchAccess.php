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
}
