<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\PurchaseHistory;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Suppliers are company-wide (no branch_id column), so unlike most Api
 * controllers this one does not use AuthorizesBranchAccess — access is
 * gated purely by the role: middleware in routes/api.php.
 */
class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::withCount('ingredients')->orderBy('name')->get();

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('suppliers', 'public');
        }
        unset($validated['photo']);

        $supplier = Supplier::create($validated);

        return response()->json(['message' => 'Supplier created successfully.', 'supplier' => $supplier], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['ingredients', 'purchaseHistory' => fn ($q) => $q->latest('purchased_at')->limit(20)]);

        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($supplier->photo_path) {
                Storage::disk('public')->delete($supplier->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('suppliers', 'public');
        }
        unset($validated['photo']);

        $supplier->update($validated);

        return response()->json(['message' => 'Supplier updated successfully.', 'supplier' => $supplier->fresh()]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->photo_path) {
            Storage::disk('public')->delete($supplier->photo_path);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully.']);
    }

    public function linkIngredient(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_primary')) {
            DB::table('ingredient_supplier')
                ->where('ingredient_id', $validated['ingredient_id'])
                ->update(['is_primary' => false]);
        }

        $supplier->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => [
                'unit_cost' => $validated['unit_cost'] ?? null,
                'is_primary' => $request->boolean('is_primary'),
            ],
        ]);

        return response()->json(['message' => 'Ingredient linked successfully.']);
    }

    public function unlinkIngredient(Supplier $supplier, Ingredient $ingredient): JsonResponse
    {
        $supplier->ingredients()->detach($ingredient->id);

        return response()->json(['message' => 'Ingredient unlinked successfully.']);
    }

    public function addPurchase(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchase = PurchaseHistory::create([
            ...$validated,
            'supplier_id' => $supplier->id,
            'recorded_by' => $request->user()->id,
        ]);

        $supplier->ingredients()->syncWithoutDetaching([
            $validated['ingredient_id'] => ['unit_cost' => $validated['unit_price']],
        ]);

        return response()->json(['message' => 'Purchase recorded successfully.', 'purchase' => $purchase], 201);
    }
}
