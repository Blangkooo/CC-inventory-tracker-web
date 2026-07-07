<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = \App\Models\Alert::with('branch', 'shiftLog')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        return response()->json($alerts);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:read,resolved',
        ]);

        $alert = \App\Models\Alert::findOrFail($id);
        $alert->update(['status' => $request->status]);

        return response()->json($alert);
    }
}
