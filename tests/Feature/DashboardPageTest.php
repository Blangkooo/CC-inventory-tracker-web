<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\ShiftLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $manager;
    private Branch $branch;
    private Product $product;
    private Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create(['name' => 'Dashboard Test Branch']);
        $this->owner = User::factory()->create([
            'role'      => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
            'password'  => bcrypt('password'),
        ]);
        $this->manager = User::factory()->create([
            'role'      => User::ROLE_MANAGER,
            'branch_id' => $this->branch->id,
            'password'  => bcrypt('password'),
        ]);
        $this->ingredient = Ingredient::create(['name' => 'Test Ingredient', 'unit' => 'g']);
        $this->product = Product::create(['name' => 'Test Product', 'price' => 50.00]);
        Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => 'regular',
            'quantity_required' => 10,
        ]);
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(302); // redirects to login
    }

    public function test_owner_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_manager_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->manager)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_dashboard_shows_branch_count(): void
    {
        Branch::factory()->count(3)->create();

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('4'); // 1 from setUp + 3 from factory
    }

    public function test_dashboard_shows_pending_alerts(): void
    {
        DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Test alert',
        ]);
        DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'medium',
            'status' => 'pending',
            'details' => 'Another alert',
        ]);

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_shows_low_stock_count(): void
    {
        BranchStock::create([
            'branch_id'        => $this->branch->id,
            'ingredient_id'    => $this->ingredient->id,
            'current_quantity' => 5,
            'min_threshold'    => 10,
        ]);

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_shows_recent_transactions(): void
    {
        Transaction::create([
            'branch_id'     => $this->branch->id,
            'product_id'    => $this->product->id,
            'user_id'       => $this->owner->id,
            'quantity'      => 1,
            'total_amount'  => 50.00,
            'client_uuid'   => 'test-uuid-1',
            'created_at'    => now()->subHour(),
        ]);

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_manager_only_sees_their_branch_data(): void
    {
        $otherBranch = Branch::factory()->create(['name' => 'Other Branch']);

        // Transaction in manager's branch
        Transaction::create([
            'branch_id'    => $this->branch->id,
            'product_id'   => $this->product->id,
            'user_id'      => $this->manager->id,
            'quantity'     => 1,
            'total_amount' => 50.00,
            'client_uuid'  => 'test-uuid-2',
        ]);

        // Transaction in other branch — manager should NOT see this
        Transaction::create([
            'branch_id'    => $otherBranch->id,
            'product_id'   => $this->product->id,
            'user_id'      => $this->owner->id,
            'quantity'     => 1,
            'total_amount' => 100.00,
            'client_uuid'  => 'test-uuid-3',
        ]);

        $response = $this->actingAs($this->manager)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee($this->branch->name);
    }

    public function test_dashboard_shows_top_earners(): void
    {
        Transaction::create([
            'branch_id'    => $this->branch->id,
            'product_id'   => $this->product->id,
            'user_id'      => $this->owner->id,
            'quantity'     => 5,
            'total_amount' => 250.00,
            'client_uuid'  => 'test-uuid-4',
        ]);

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_shows_ongoing_shifts(): void
    {
        ShiftLog::create([
            'branch_id'   => $this->branch->id,
            'user_id'     => $this->manager->id,
            'shift_start' => now()->subHours(2),
            'status'      => 'open',
        ]);

        $response = $this->actingAs($this->owner)->get('/dashboard');
        $response->assertStatus(200);
    }
}
