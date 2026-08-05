<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private Branch $branch;
    private Branch $otherBranch;
    private Product $product;
    private Ingredient $ingredient;
    private Ingredient $ingredient2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create(['name' => 'API Test Branch']);
        $this->otherBranch = Branch::factory()->create(['name' => 'Other Branch']);
        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'branch_id' => null]);
        $this->manager = User::factory()->create(['role' => User::ROLE_MANAGER, 'branch_id' => $this->branch->id]);
        $this->staff = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $this->branch->id]);
        $this->product = Product::create(['name' => 'Test Item', 'price' => 50.00]);
        $this->ingredient = Ingredient::create(['name' => 'Base', 'unit' => 'g']);
        $this->ingredient2 = Ingredient::create(['name' => 'Flavor', 'unit' => 'ml']);

        Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);
        Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient2->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 20,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  STOCK API
    // ═══════════════════════════════════════════════════════════════════

    public function test_stock_store_requires_auth(): void
    {
        $response = $this->postJson('/api/stock', []);
        $response->assertStatus(401);
    }

    public function test_staff_cannot_create_stock(): void
    {
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/stock', [
                'branch_id'     => $this->branch->id,
                'ingredient_id' => $this->ingredient->id,
                'current_quantity' => 100,
            ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_create_stock(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/stock', [
                'branch_id'        => $this->branch->id,
                'ingredient_id'    => $this->ingredient->id,
                'current_quantity' => 500,
                'min_threshold'    => 50,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('ingredient.name', 'Base');

        $this->assertDatabaseHas('branch_stock', [
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 500,
        ]);
        // Should have created a stock movement
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'initial',
            'quantity_change' => 500,
        ]);
    }

    public function test_stock_restock(): void
    {
        $stock = BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 20,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/stock/restock', [
                'branch_id'     => $this->branch->id,
                'ingredient_id' => $this->ingredient->id,
                'quantity'      => 50,
                'notes'         => 'Weekly restock',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('branch_stock', [
            'id' => $stock->id,
            'current_quantity' => 150,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'restock',
            'quantity_change' => 50,
        ]);
    }

    public function test_low_stock(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 10,
            'min_threshold'    => 20,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/stock/low-stock?branch_id='.$this->branch->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    // ═══════════════════════════════════════════════════════════════════
    //  TRANSACTION API
    // ═══════════════════════════════════════════════════════════════════

    public function test_transaction_store_requires_auth(): void
    {
        $response = $this->postJson('/api/transactions', []);
        $response->assertStatus(401);
    }

    public function test_transaction_store_with_sufficient_stock(): void
    {
        // Stock up
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient2->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/transactions', [
                'branch_id'   => $this->branch->id,
                'product_id'  => $this->product->id,
                'quantity'    => 2,
                'client_uuid' => 'unique-txn-001',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('transaction.product.name', 'Test Item');

        $this->assertDatabaseHas('transactions', ['client_uuid' => 'unique-txn-001']);

        // Stock should be deducted: 100 - (10 * 2) for ingredient, 100 - (20 * 2) for ingredient2
        $this->assertDatabaseHas('branch_stock', [
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 80,
        ]);
        $this->assertDatabaseHas('branch_stock', [
            'ingredient_id'    => $this->ingredient2->id,
            'current_quantity' => 60,
        ]);
    }

    public function test_transaction_store_insufficient_stock(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 5,
            'min_threshold'    => 10,
        ]);
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient2->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/transactions', [
                'branch_id'   => $this->branch->id,
                'product_id'  => $this->product->id,
                'quantity'    => 1,
                'client_uuid' => 'insufficient-stock-txn',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'needed', 'available']);
    }

    public function test_transaction_store_unique_client_uuid(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient2->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        // First transaction
        $this->actingAs($this->staff, 'api')
            ->postJson('/api/transactions', [
                'branch_id'   => $this->branch->id,
                'product_id'  => $this->product->id,
                'quantity'    => 1,
                'client_uuid' => 'duplicate-uuid',
            ]);

        // Second with same UUID should fail
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/transactions', [
                'branch_id'   => $this->branch->id,
                'product_id'  => $this->product->id,
                'quantity'    => 1,
                'client_uuid' => 'duplicate-uuid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_uuid']);
    }

    public function test_transaction_list(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/transactions?branch_id='.$this->branch->id);

        $response->assertStatus(200);
    }

    public function test_transaction_list_requires_manager_or_admin(): void
    {
        $response = $this->actingAs($this->staff, 'api')
            ->getJson('/api/transactions?branch_id='.$this->branch->id);

        $response->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SHIFT API
    // ═══════════════════════════════════════════════════════════════════

    public function test_shift_open_requires_auth(): void
    {
        $response = $this->postJson('/api/shifts/open', []);
        $response->assertStatus(401);
    }

    public function test_shift_open_creates_shift(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shifts/open', [
                'opening_counts' => [
                    [
                        'ingredient_id'    => $this->ingredient->id,
                        'opening_quantity' => 100,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Shift opened successfully.');

        $this->assertDatabaseHas('shift_logs', [
            'user_id'   => $this->staff->id,
            'branch_id' => $this->branch->id,
            'status'    => 'open',
        ]);
    }

    public function test_shift_open_fails_if_already_open(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        // Open first shift
        $this->actingAs($this->staff, 'api')
            ->postJson('/api/shifts/open', [
                'opening_counts' => [
                    ['ingredient_id' => $this->ingredient->id, 'opening_quantity' => 100],
                ],
            ]);

        // Try opening another
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shifts/open', [
                'opening_counts' => [
                    ['ingredient_id' => $this->ingredient->id, 'opening_quantity' => 100],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_shift_close(): void
    {
        $stock = BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        $shiftLog = ShiftLog::create([
            'branch_id'   => $this->branch->id,
            'user_id'     => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'status'      => 'open',
        ]);

        ShiftStockCount::create([
            'shift_log_id'     => $shiftLog->id,
            'ingredient_id'    => $this->ingredient->id,
            'opening_quantity' => 100,
        ]);

        // Close with same count — no variance
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shifts/close', [
                'shift_log_id' => $shiftLog->id,
                'closing_counts' => [
                    [
                        'ingredient_id'            => $this->ingredient->id,
                        'closing_quantity_actual'  => 100,
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Shift closed successfully.');

        $this->assertDatabaseHas('shift_logs', [
            'id'     => $shiftLog->id,
            'status' => 'closed',
        ]);
    }

    public function test_shift_close_detects_variance(): void
    {
        $stock = BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold'    => 10,
        ]);

        $shiftLog = ShiftLog::create([
            'branch_id'   => $this->branch->id,
            'user_id'     => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'status'      => 'open',
        ]);

        ShiftStockCount::create([
            'shift_log_id'     => $shiftLog->id,
            'ingredient_id'    => $this->ingredient->id,
            'opening_quantity' => 100,
        ]);

        // Close with different count — variance detected
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shifts/close', [
                'shift_log_id' => $shiftLog->id,
                'closing_counts' => [
                    [
                        'ingredient_id'            => $this->ingredient->id,
                        'closing_quantity_actual'  => 80,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('discrepancy_alerts', [
            'shift_log_id'   => $shiftLog->id,
            'type'           => 'shift_variance',
            'expected_value' => 100,
            'actual_value'   => 80,
            'variance'       => -20,
        ]);
    }

    public function test_shift_list(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/shifts?branch_id='.$this->branch->id);

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  API DASHBOARD
    // ═══════════════════════════════════════════════════════════════════

    public function test_api_dashboard_kpis(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/dashboard/kpis?branch_id='.$this->branch->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date', 'total_sales', 'total_revenue', 'flagged_shifts', 'open_alerts',
            ]);
    }

    public function test_api_dashboard_sales_summary(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/dashboard/sales-summary?branch_id='.$this->branch->id);

        $response->assertStatus(200);
    }

    public function test_api_dashboard_top_products(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/dashboard/top-products?branch_id='.$this->branch->id);

        $response->assertStatus(200);
    }
}
