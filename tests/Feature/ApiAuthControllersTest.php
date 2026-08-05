<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\ShiftLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthControllersTest extends TestCase
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

        $this->branch = Branch::factory()->create(['name' => 'API Gaps Branch']);
        $this->otherBranch = Branch::factory()->create(['name' => 'API Gaps Other']);
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
            'password' => bcrypt('admin123'),
            'email' => 'admin@gaps.test',
        ]);
        $this->manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'branch_id' => $this->branch->id,
            'password' => bcrypt('mgr123'),
            'email' => 'mgr@gaps.test',
        ]);
        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->branch->id,
            'pin' => '1234',
        ]);
        $this->product = Product::create(['name' => 'Gaps Product', 'price' => 100.00]);
        $this->ingredient = Ingredient::create(['name' => 'Gaps Ingredient', 'unit' => 'g']);
        $this->ingredient2 = Ingredient::create(['name' => 'Second Ingredient', 'unit' => 'ml']);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  AUTH CONTROLLER (JWT login/logout/me)
    // ═══════════════════════════════════════════════════════════════════

    public function test_admin_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/admin-login', [
            'email' => 'admin@gaps.test',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_admin_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/admin-login', [
            'email' => 'admin@gaps.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_staff_login_with_valid_pin(): void
    {
        $response = $this->postJson('/api/auth/staff-login', [
            'pin' => '1234',
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user', 'branch']);
    }

    public function test_staff_login_with_invalid_pin(): void
    {
        $response = $this->postJson('/api/auth/staff-login', [
            'pin' => '0000',
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_unified_login_endpoint(): void
    {
        $response = $this->postJson('/api/login', [
            'pin' => '1234',
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    public function test_logout(): void
    {
        // Login first to get a real JWT token
        $login = $this->postJson('/api/auth/admin-login', [
            'email' => 'admin@gaps.test',
            'password' => 'admin123',
        ]);
        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    public function test_me_endpoint(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('email', 'admin@gaps.test');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  BRANCH CONTROLLER (API CRUD)
    // ═══════════════════════════════════════════════════════════════════

    public function test_branch_index(): void
    {
        $response = $this->actingAs($this->admin, 'api')->getJson('/api/branches');
        $response->assertStatus(200);
    }

    public function test_branch_show(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/branches/'.$this->branch->id);
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'API Gaps Branch');
    }

    public function test_branch_store(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/branches', [
                'name' => 'New API Branch',
                'location' => 'Test City',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('branch.name', 'New API Branch');
        $this->assertDatabaseHas('branches', ['name' => 'New API Branch']);
    }

    public function test_branch_store_validates_required(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/branches', []);
        $response->assertStatus(422);
    }

    public function test_branch_update(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/branches/'.$this->branch->id, [
                'name' => 'Updated Branch Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('branch.name', 'Updated Branch Name');
    }

    public function test_branch_destroy(): void
    {
        $newBranch = Branch::factory()->create(['name' => 'Delete Me']);
        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson('/api/branches/'.$newBranch->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('branches', ['id' => $newBranch->id]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ALERT CONTROLLER (API)
    // ═══════════════════════════════════════════════════════════════════

    public function test_alert_index(): void
    {
        DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'API alert test',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/alerts?branch_id='.$this->branch->id);
        $response->assertStatus(200);
    }

    public function test_alert_show(): void
    {
        $alert = DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Show me',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/alerts/'.$alert->id);
        $response->assertStatus(200);
    }

    public function test_alert_review(): void
    {
        $alert = DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'medium',
            'status' => 'pending',
            'details' => 'Review me',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/alerts/'.$alert->id.'/review');
        $response->assertStatus(200);

        $this->assertDatabaseHas('discrepancy_alerts', [
            'id' => $alert->id,
            'status' => 'reviewed',
        ]);
    }

    public function test_alert_dismiss(): void
    {
        $alert = DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'low',
            'status' => 'pending',
            'details' => 'Dismiss me',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/alerts/'.$alert->id.'/dismiss');
        $response->assertStatus(200);

        $this->assertDatabaseHas('discrepancy_alerts', [
            'id' => $alert->id,
            'status' => 'dismissed',
        ]);
    }

    public function test_alert_requires_auth(): void
    {
        $this->getJson('/api/alerts?branch_id='.$this->branch->id)->assertStatus(401);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  NOTIFICATION CONTROLLER
    // ═══════════════════════════════════════════════════════════════════

    public function test_notification_index(): void
    {
        Notification::create([
            'user_id' => $this->admin->id,
            'title' => 'Test Notif',
            'message' => 'Test notification',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/notifications');
        $response->assertStatus(200);
    }

    public function test_notification_mark_read(): void
    {
        $notification = Notification::create([
            'user_id' => $this->admin->id,
            'title' => 'Mark Read Test',
            'message' => 'Mark as read',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/notifications/'.$notification->id.'/read');
        $response->assertStatus(200);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  OVERVIEW CONTROLLER
    // ═══════════════════════════════════════════════════════════════════

    public function test_overview_requires_super_admin(): void
    {
        $response = $this->actingAs($this->manager, 'api')
            ->getJson('/api/dashboard');
        $response->assertStatus(403);
    }

    public function test_overview_returns_counts(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/dashboard');
        $response->assertStatus(200)
            ->assertJsonStructure(['total_branches', 'total_products', 'total_ingredients', 'pending_alerts']);
    }

    public function test_branch_stock_overview(): void
    {
        BranchStock::create([
            'branch_id' => $this->branch->id,
            'ingredient_id' => $this->ingredient->id,
            'current_quantity' => 200,
            'min_threshold' => 20,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/branches/'.$this->branch->id.'/stock');
        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  REPORT CONTROLLER
    // ═══════════════════════════════════════════════════════════════════

    public function test_sales_report(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/reports/sales?branch_id='.$this->branch->id.'&period=daily');
        $response->assertStatus(200)
            ->assertJsonStructure(['branch_id', 'period', 'total_transactions', 'total_sales', 'average_order_value', 'by_product']);
    }

    public function test_sales_report_validates_period(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/reports/sales?branch_id='.$this->branch->id.'&period=yearly');
        $response->assertStatus(422);
    }

    public function test_inventory_report(): void
    {
        BranchStock::create([
            'branch_id' => $this->branch->id,
            'ingredient_id' => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/reports/inventory?branch_id='.$this->branch->id);
        $response->assertStatus(200)
            ->assertJsonStructure(['branch_id', 'total_ingredients', 'low_stock_count', 'out_of_stock_count', 'items']);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SHIFT LOG CONTROLLER (thin start/end)
    // ═══════════════════════════════════════════════════════════════════

    public function test_shift_log_start(): void
    {
        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shift-logs/start', [
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('shift_logs', [
            'user_id' => $this->staff->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
        ]);
    }

    public function test_shift_log_end(): void
    {
        $shiftLog = ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shift-logs/'.$shiftLog->id.'/end');

        $response->assertStatus(200);
        $this->assertDatabaseHas('shift_logs', [
            'id' => $shiftLog->id,
            'status' => 'closed',
        ]);
    }

    public function test_shift_log_end_already_closed(): void
    {
        $shiftLog = ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'shift_end' => now()->subHours(1),
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->staff, 'api')
            ->postJson('/api/shift-logs/'.$shiftLog->id.'/end');

        $response->assertStatus(409);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  STAFF CONTROLLER (API CRUD)
    // ═══════════════════════════════════════════════════════════════════

    public function test_staff_index(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/staff?branch_id='.$this->branch->id);
        $response->assertStatus(200);
    }

    public function test_staff_store(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/staff', [
                'name' => 'New Staff Member',
                'email' => 'newstaff@test.com',
                'pin' => '5678',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('staff.name', 'New Staff Member');
        $this->assertDatabaseHas('users', ['email' => 'newstaff@test.com', 'role' => User::ROLE_STAFF]);
    }

    public function test_staff_show(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/staff/'.$this->staff->id);
        $response->assertStatus(200);
    }

    public function test_staff_update(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/staff/'.$this->staff->id, [
                'name' => 'Updated Staff Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->staff->id, 'name' => 'Updated Staff Name']);
    }

    public function test_staff_destroy(): void
    {
        $deleteStaff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson('/api/staff/'.$deleteStaff->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $deleteStaff->id]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PRODUCT API CRUD
    // ═══════════════════════════════════════════════════════════════════

    public function test_api_product_index(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/products');
        $response->assertStatus(200);
    }

    public function test_api_product_show(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/products/'.$this->product->id);
        $response->assertStatus(200)
            ->assertJsonPath('name', 'Gaps Product');
    }

    public function test_api_product_store(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/products', [
                'name' => 'API Created Product',
                'price' => 75.00,
                'category' => 'Beverage',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'API Created Product']);
    }

    public function test_api_product_update(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/products/'.$this->product->id, [
                'price' => 150.00,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'price' => 150.00]);
    }

    public function test_api_product_destroy(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson('/api/products/'.$this->product->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  INGREDIENT API CRUD
    // ═══════════════════════════════════════════════════════════════════

    public function test_api_ingredient_index(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/ingredients');
        $response->assertStatus(200);
    }

    public function test_api_ingredient_show(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/ingredients/'.$this->ingredient->id);
        $response->assertStatus(200)
            ->assertJsonPath('name', 'Gaps Ingredient');
    }

    public function test_api_ingredient_store(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/ingredients', [
                'name' => 'New Ingredient',
                'unit' => 'ml',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ingredients', ['name' => 'New Ingredient']);
    }

    public function test_api_ingredient_store_validates_unit(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/ingredients', [
                'name' => 'Bad Unit',
                'unit' => 'cups',
            ]);

        $response->assertStatus(422);
    }

    public function test_api_ingredient_update(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/ingredients/'.$this->ingredient->id, [
                'name' => 'Updated Ingredient',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ingredients', ['id' => $this->ingredient->id, 'name' => 'Updated Ingredient']);
    }

    public function test_api_ingredient_destroy(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson('/api/ingredients/'.$this->ingredient->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('ingredients', ['id' => $this->ingredient->id]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RECIPE API CRUD
    // ═══════════════════════════════════════════════════════════════════

    public function test_api_recipe_index(): void
    {
        Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/recipes?product_id='.$this->product->id);
        $response->assertStatus(200);
    }

    public function test_api_recipe_show(): void
    {
        $recipe = Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 15,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/recipes/'.$recipe->id);
        $response->assertStatus(200)
            ->assertJsonPath('ingredient.name', 'Gaps Ingredient');
    }

    public function test_api_recipe_store(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/recipes', [
                'product_id' => $this->product->id,
                'ingredient_id' => $this->ingredient->id,
                'size' => Recipe::SIZE_LARGE,
                'quantity_required' => 25,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('recipes', [
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_LARGE,
        ]);
    }

    public function test_api_recipe_store_rejects_duplicate(): void
    {
        Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/recipes', [
                'product_id' => $this->product->id,
                'ingredient_id' => $this->ingredient->id,
                'size' => Recipe::SIZE_REGULAR,
                'quantity_required' => 20,
            ]);

        $response->assertStatus(422);
    }

    public function test_api_recipe_update(): void
    {
        $recipe = Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/recipes/'.$recipe->id, [
                'quantity_required' => 50,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id, 'quantity_required' => 50]);
    }

    public function test_api_recipe_destroy(): void
    {
        $recipe = Recipe::create([
            'product_id' => $this->product->id,
            'ingredient_id' => $this->ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson('/api/recipes/'.$recipe->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  STOCK MOVEMENTS
    // ═══════════════════════════════════════════════════════════════════

    public function test_stock_movements(): void
    {
        $stock = BranchStock::create([
            'branch_id' => $this->branch->id,
            'ingredient_id' => $this->ingredient->id,
            'current_quantity' => 100,
            'min_threshold' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/stock/'.$stock->id.'/movements');
        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RECEIPT SHOW
    // ═══════════════════════════════════════════════════════════════════

    public function test_receipt_show(): void
    {
        $receipt = \App\Models\Receipt::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'image_path' => 'receipts/test.jpg',
            'raw_ocr_text' => 'Test OCR',
            'parsed_total_amount' => 100.00,
            'reconciliation_status' => 'pending',
            'scanned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/receipts/'.$receipt->id);
        $response->assertStatus(200);
    }
}
