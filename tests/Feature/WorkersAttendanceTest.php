<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkersAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $manager;
    private User $staff;
    private Branch $branch;
    private Branch $otherBranch;
    private Product $product;
    private Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        $this->branch = Branch::factory()->create(['name' => 'Workers Branch']);
        $this->otherBranch = Branch::factory()->create(['name' => 'Other Workers Branch']);
        $this->owner = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
            'password' => bcrypt('password'),
        ]);
        $this->manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'branch_id' => $this->branch->id,
            'password' => bcrypt('password'),
        ]);
        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->branch->id,
        ]);
        $this->ingredient = Ingredient::create(['name' => 'AT Ingredient', 'unit' => 'g']);
        $this->product = Product::create(['name' => 'AT Product', 'price' => 50.00]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  WORKERS CONTROLLER (Web CRUD)
    // ═══════════════════════════════════════════════════════════════════

    public function test_workers_store_requires_auth(): void
    {
        $response = $this->post('/business/workers', [
            'name' => 'New Worker',
            'pin' => '1111',
            'branch_id' => $this->branch->id,
        ]);
        $response->assertStatus(302); // redirected to login
    }

    public function test_workers_store_creates_staff(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers', [
                'name' => 'New Worker',
                'email' => 'worker@test.com',
                'pin' => '1111',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('staff.name', 'New Worker');
        $this->assertDatabaseHas('users', ['email' => 'worker@test.com', 'role' => User::ROLE_STAFF]);
        // Profile should be auto-created
        $user = User::where('email', 'worker@test.com')->first();
        $this->assertNotNull($user->profile);
    }

    public function test_manager_can_add_worker_to_their_branch(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/business/workers', [
                'name' => 'Manager Added Worker',
                'pin' => '2222',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201);
    }

    public function test_manager_cannot_add_worker_to_other_branch(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/business/workers', [
                'name' => 'Cross Branch Worker',
                'pin' => '3333',
                'branch_id' => $this->otherBranch->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_staff_cannot_add_worker(): void
    {
        $response = $this->actingAs($this->staff)
            ->postJson('/business/workers', [
                'name' => 'Staff Attempt',
                'pin' => '4444',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_workers_update(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson('/business/workers/'.$this->staff->id, [
                'name' => 'Updated Staff Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('staff.name', 'Updated Staff Name');
        $this->assertDatabaseHas('users', ['id' => $this->staff->id, 'name' => 'Updated Staff Name']);
    }

    public function test_manager_can_update_their_worker(): void
    {
        $response = $this->actingAs($this->manager)
            ->putJson('/business/workers/'.$this->staff->id, [
                'name' => 'Manager Updated',
            ]);

        $response->assertStatus(200);
    }

    public function test_manager_cannot_update_other_branch_worker(): void
    {
        $otherStaff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->otherBranch->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->putJson('/business/workers/'.$otherStaff->id, [
                'name' => 'Should Not Work',
            ]);

        $response->assertStatus(403);
    }

    public function test_workers_update_profile(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson('/business/workers/'.$this->staff->id.'/profile', [
                'phone' => '09123456789',
                'address' => '123 Test St',
                'skills' => ['barista', 'cashier'],
                'rating' => 4.5,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('worker_profiles', [
            'user_id' => $this->staff->id,
            'phone' => '09123456789',
        ]);
    }

    public function test_workers_destroy(): void
    {
        $deleteStaff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$deleteStaff->id.'/delete');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $deleteStaff->id]);
    }

    public function test_workers_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$this->owner->id.'/delete');

        $response->assertStatus(404); // owners aren't in MANAGED_ROLES
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ATTENDANCE CONTROLLER
    // ═══════════════════════════════════════════════════════════════════

    public function test_clock_in_creates_open_shift(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$this->staff->id.'/clock-in');

        $response->assertStatus(201)
            ->assertJsonPath('shift.status', 'open');
        $this->assertDatabaseHas('shift_logs', [
            'user_id' => $this->staff->id,
            'status' => 'open',
        ]);
    }

    public function test_clock_in_fails_if_already_open(): void
    {
        ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$this->staff->id.'/clock-in');

        $response->assertStatus(422);
    }

    public function test_clock_out_closes_shift(): void
    {
        $shift = ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$this->staff->id.'/clock-out');

        $response->assertStatus(200)
            ->assertJsonPath('shift.status', 'closed');
        $this->assertDatabaseHas('shift_logs', [
            'id' => $shift->id,
            'status' => 'closed',
        ]);
    }

    public function test_clock_out_fails_with_no_open_shift(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/business/workers/'.$this->staff->id.'/clock-out');

        $response->assertStatus(404);
    }

    public function test_manager_can_clock_in_their_worker(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/business/workers/'.$this->staff->id.'/clock-in');

        $response->assertStatus(201);
    }

    public function test_manager_cannot_clock_in_other_branch_worker(): void
    {
        $otherStaff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'branch_id' => $this->otherBranch->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson('/business/workers/'.$otherStaff->id.'/clock-in');

        $response->assertStatus(403);
    }

    public function test_attendance_history(): void
    {
        ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subDays(1)->subHours(8),
            'shift_end' => now()->subDays(1),
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson('/business/workers/'.$this->staff->id.'/attendance');

        $response->assertStatus(200)
            ->assertJsonStructure(['shifts']);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ACTIVITY CONTROLLER
    // ═══════════════════════════════════════════════════════════════════

    public function test_activity_shows_transactions(): void
    {
        Transaction::create([
            'branch_id' => $this->branch->id,
            'product_id' => $this->product->id,
            'user_id' => $this->staff->id,
            'quantity' => 1,
            'total_amount' => 50.00,
            'client_uuid' => 'activity-txn',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson('/business/workers/'.$this->staff->id.'/activity');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_activity_shifts(): void
    {
        ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'shift_end' => now(),
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson('/business/workers/'.$this->staff->id.'/activity?type=shifts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_activity_discrepancies(): void
    {
        $shift = ShiftLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->staff->id,
            'shift_start' => now()->subHours(8),
            'status' => 'closed',
        ]);

        DiscrepancyAlert::create([
            'branch_id' => $this->branch->id,
            'shift_log_id' => $shift->id,
            'ingredient_id' => $this->ingredient->id,
            'type' => 'shift_variance',
            'severity' => 'medium',
            'status' => 'pending',
            'details' => 'Variance detected',
            'expected_value' => 100,
            'actual_value' => 80,
            'variance' => -20,
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson('/business/workers/'.$this->staff->id.'/activity?type=discrepancies');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_activity_forbidden_for_staff(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/business/workers/'.$this->staff->id.'/activity');

        $response->assertStatus(403);
    }

    public function test_activity_requires_auth(): void
    {
        $response = $this->get('/business/workers/'.$this->staff->id.'/activity');
        $response->assertStatus(302);
    }
}
