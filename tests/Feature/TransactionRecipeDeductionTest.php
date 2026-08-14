<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRecipeDeductionTest extends TestCase
{
    use RefreshDatabase;

    private function makeProductWithRecipe(Branch $branch, float $stockQty, float $qtyPerUnit): array
    {
        $ingredient = Ingredient::create(['name' => 'Milk', 'unit' => 'ml']);
        $product = Product::create(['name' => 'Milk Tea', 'category' => 'drinks', 'price' => 100, 'is_active' => true]);
        Recipe::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => $qtyPerUnit,
        ]);
        BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'current_quantity' => $stockQty,
            'min_threshold' => 10,
        ]);

        return [$product, $ingredient];
    }

    public function test_sale_deducts_recipe_ingredients_correctly(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        [$product, $ingredient] = $this->makeProductWithRecipe($branch, stockQty: 1000, qtyPerUnit: 50);

        $response = $this->actingAs($staff, 'api')->postJson('/api/transactions', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 2,
            'client_uuid' => 'test-uuid-1',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('stock_warnings', []);

        $stock = BranchStock::where('branch_id', $branch->id)->where('ingredient_id', $ingredient->id)->first();
        $this->assertEquals(900.0, (float) $stock->current_quantity); // 1000 - (50 * 2)

        $this->assertDatabaseHas('transactions', [
            'client_uuid' => 'test-uuid-1',
            'product_id' => $product->id,
            'quantity' => 2,
            'total_amount' => 200,
        ]);
    }

    public function test_sale_that_would_go_negative_still_records_and_warns(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        // Only 30 ml in stock, but the sale needs 50 ml — should go negative, not block.
        [$product, $ingredient] = $this->makeProductWithRecipe($branch, stockQty: 30, qtyPerUnit: 50);

        $response = $this->actingAs($staff, 'api')->postJson('/api/transactions', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 1,
            'client_uuid' => 'test-uuid-2',
        ]);

        $response->assertCreated();
        $response->assertJsonCount(1, 'stock_warnings');
        $response->assertJsonPath('stock_warnings.0.ingredient_id', $ingredient->id);

        $stock = BranchStock::where('branch_id', $branch->id)->where('ingredient_id', $ingredient->id)->first();
        $this->assertEquals(-20.0, (float) $stock->current_quantity); // 30 - 50

        // The sale is still recorded — never blocked by low/negative stock.
        $this->assertDatabaseHas('transactions', [
            'client_uuid' => 'test-uuid-2',
            'product_id' => $product->id,
        ]);

        // And a discrepancy alert was raised for reconciliation.
        $this->assertDatabaseHas('discrepancy_alerts', [
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
        ]);
    }

    public function test_duplicate_client_uuid_is_rejected(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        [$product] = $this->makeProductWithRecipe($branch, stockQty: 1000, qtyPerUnit: 10);

        $this->actingAs($staff, 'api')->postJson('/api/transactions', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 1,
            'client_uuid' => 'duplicate-uuid',
        ])->assertCreated();

        $response = $this->actingAs($staff, 'api')->postJson('/api/transactions', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 1,
            'client_uuid' => 'duplicate-uuid',
        ]);

        $response->assertStatus(422);
    }
}
