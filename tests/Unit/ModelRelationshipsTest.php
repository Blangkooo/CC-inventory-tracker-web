<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Receipt;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    // ═══════════════════════════════════════════════════════════════════
    //  USER MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_user_has_role_constants(): void
    {
        $this->assertEquals('super_admin', User::ROLE_SUPER_ADMIN);
        $this->assertEquals('manager', User::ROLE_MANAGER);
        $this->assertEquals('staff', User::ROLE_STAFF);
    }

    public function test_user_role_helpers(): void
    {
        $branch = Branch::factory()->create();

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'branch_id' => null]);
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER, 'branch_id' => $branch->id]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $branch->id]);

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->isOwner());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isManager());
        $this->assertFalse($admin->isStaff());

        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isSuperAdmin());
        $this->assertTrue($manager->isAdmin());

        $this->assertTrue($staff->isStaff());
        $this->assertFalse($staff->isSuperAdmin());
        $this->assertFalse($staff->isManager());
        $this->assertFalse($staff->isAdmin());

        $this->assertTrue($admin->hasRole(User::ROLE_SUPER_ADMIN));
        $this->assertTrue($admin->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_MANAGER));
        $this->assertFalse($staff->hasRole(User::ROLE_SUPER_ADMIN));
    }

    public function test_user_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $this->assertInstanceOf(Branch::class, $user->branch);
        $this->assertEquals($branch->id, $user->branch->id);
    }

    public function test_user_has_many_transactions(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::create([
            'branch_id' => Branch::factory()->create()->id,
            'user_id' => $user->id,
            'product_id' => Product::create(['name' => 'P', 'price' => 10])->id,
            'quantity' => 1,
            'total_amount' => 10.00,
            'client_uuid' => 'user-txn-test',
        ]);

        $this->assertCount(1, $user->transactions);
        $this->assertEquals($transaction->id, $user->transactions->first()->id);
    }

    public function test_user_has_many_shift_logs(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $shift = ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);

        $this->assertCount(1, $user->shiftLogs);
        $this->assertEquals($shift->id, $user->shiftLogs->first()->id);
    }

    public function test_user_has_one_profile(): void
    {
        $user = User::factory()->create();
        WorkerProfile::create(['user_id' => $user->id]);

        $this->assertInstanceOf(WorkerProfile::class, $user->profile);
        $this->assertEquals($user->id, $user->profile->user_id);
    }

    public function test_password_and_pin_are_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text',
            'pin' => '1234',
        ]);

        // The 'hashed' cast should have hashed these values
        $this->assertNotEquals('plain-text', $user->getRawOriginal('password'));
        $this->assertNotEquals('1234', $user->getRawOriginal('pin'));
    }

    public function test_user_implements_jwt_subject(): void
    {
        $user = User::factory()->create();
        $this->assertEquals($user->id, $user->getJWTIdentifier());
        $this->assertEquals([], $user->getJWTCustomClaims());
    }

    // ═══════════════════════════════════════════════════════════════════
    //  BRANCH MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_branch_has_many_users(): void
    {
        $branch = Branch::factory()->create();
        User::factory()->count(3)->create(['branch_id' => $branch->id]);

        $this->assertCount(3, $branch->users);
    }

    public function test_branch_has_many_stocks(): void
    {
        $branch = Branch::factory()->create();
        $ingredient = Ingredient::create(['name' => 'Test', 'unit' => 'g']);
        BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'current_quantity' => 100,
        ]);

        $this->assertCount(1, $branch->stocks);
    }

    public function test_branch_has_many_transactions(): void
    {
        $branch = Branch::factory()->create();
        Transaction::create([
            'branch_id' => $branch->id,
            'product_id' => Product::create(['name' => 'P', 'price' => 10])->id,
            'user_id' => User::factory()->create()->id,
            'quantity' => 1,
            'total_amount' => 10.00,
            'client_uuid' => 'branch-txn',
        ]);

        $this->assertCount(1, $branch->transactions);
    }

    public function test_branch_has_many_shift_logs(): void
    {
        $branch = Branch::factory()->create();
        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => User::factory()->create()->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);

        $this->assertCount(1, $branch->shiftLogs);
    }

    public function test_branch_has_many_discrepancy_alerts(): void
    {
        $branch = Branch::factory()->create();
        DiscrepancyAlert::create([
            'branch_id' => $branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Test',
        ]);

        $this->assertCount(1, $branch->discrepancyAlerts);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  BRANCH STOCK MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_branch_stock_belongs_to_branch_and_ingredient(): void
    {
        $branch = Branch::factory()->create();
        $ingredient = Ingredient::create(['name' => 'BS Ing', 'unit' => 'g']);
        $stock = BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'current_quantity' => 50,
        ]);

        $this->assertInstanceOf(Branch::class, $stock->branch);
        $this->assertInstanceOf(Ingredient::class, $stock->ingredient);
    }

    public function test_branch_stock_stock_status_accessor(): void
    {
        $branch = Branch::factory()->create();

        // quantity <= 0 → 'out'
        $ing1 = Ingredient::create(['name' => 'Out Ing', 'unit' => 'g']);
        $out = BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ing1->id,
            'current_quantity' => 0,
            'min_threshold' => 10,
        ]);
        $this->assertEquals('out', $out->stock_status);

        // quantity > 0 but <= threshold → 'low'
        $ing2 = Ingredient::create(['name' => 'Low Ing', 'unit' => 'g']);
        $low = BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ing2->id,
            'current_quantity' => 5,
            'min_threshold' => 10,
        ]);
        $this->assertEquals('low', $low->stock_status);

        // quantity > threshold → 'ok'
        $ing3 = Ingredient::create(['name' => 'Ok Ing', 'unit' => 'g']);
        $ok = BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ing3->id,
            'current_quantity' => 20,
            'min_threshold' => 10,
        ]);
        $this->assertEquals('ok', $ok->stock_status);
    }

    public function test_branch_stock_has_many_movements(): void
    {
        $stock = BranchStock::create([
            'branch_id' => Branch::factory()->create()->id,
            'ingredient_id' => Ingredient::create(['name' => 'Mov', 'unit' => 'g'])->id,
            'current_quantity' => 100,
        ]);
        StockMovement::create([
            'branch_stock_id' => $stock->id,
            'type' => StockMovement::TYPE_INITIAL,
            'quantity_change' => 100,
            'quantity_before' => 0,
            'quantity_after' => 100,
            'user_id' => User::factory()->create()->id,
        ]);

        $this->assertCount(1, $stock->movements);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  INGREDIENT MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_ingredient_has_many_recipes(): void
    {
        $product = Product::create(['name' => 'P', 'price' => 10]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'g']);
        Recipe::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 10,
        ]);

        $this->assertCount(1, $ingredient->recipes);
    }

    public function test_ingredient_has_many_branch_stocks(): void
    {
        $ingredient = Ingredient::create(['name' => 'BS Ing', 'unit' => 'g']);
        BranchStock::create([
            'branch_id' => Branch::factory()->create()->id,
            'ingredient_id' => $ingredient->id,
            'current_quantity' => 100,
        ]);

        $this->assertCount(1, $ingredient->branchStocks);
    }

    public function test_ingredient_has_many_shift_stock_counts(): void
    {
        $ingredient = Ingredient::create(['name' => 'SSC Ing', 'unit' => 'g']);
        $shiftLog = ShiftLog::create([
            'branch_id' => Branch::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);
        ShiftStockCount::create([
            'shift_log_id' => $shiftLog->id,
            'ingredient_id' => $ingredient->id,
            'opening_quantity' => 100,
        ]);

        $this->assertCount(1, $ingredient->shiftStockCounts);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PRODUCT MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_product_has_many_recipes(): void
    {
        $product = Product::create(['name' => 'Prod', 'price' => 50]);
        $ingredient = Ingredient::create(['name' => 'Ing', 'unit' => 'g']);
        Recipe::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 20,
        ]);

        $this->assertCount(1, $product->recipes);
    }

    public function test_product_price_and_is_active_casts(): void
    {
        $product = Product::create(['name' => 'Cast Test', 'price' => 99.99, 'is_active' => true]);

        // decimal:2 cast returns a string in Laravel to preserve precision
        $this->assertEquals('99.99', $product->price);
        $this->assertIsBool($product->is_active);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RECIPE MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_recipe_has_size_constants(): void
    {
        $this->assertEquals('regular', Recipe::SIZE_REGULAR);
        $this->assertEquals('large', Recipe::SIZE_LARGE);
    }

    public function test_recipe_belongs_to_product_and_ingredient(): void
    {
        $product = Product::create(['name' => 'Recipe Prod', 'price' => 30]);
        $ingredient = Ingredient::create(['name' => 'Recipe Ing', 'unit' => 'ml']);
        $recipe = Recipe::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'size' => Recipe::SIZE_REGULAR,
            'quantity_required' => 15,
        ]);

        $this->assertInstanceOf(Product::class, $recipe->product);
        $this->assertInstanceOf(Ingredient::class, $recipe->ingredient);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SHIFT LOG MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_shift_log_belongs_to_branch_and_user(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $shift = ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);

        $this->assertInstanceOf(Branch::class, $shift->branch);
        $this->assertInstanceOf(User::class, $shift->user);
    }

    public function test_shift_log_dates_are_carbon(): void
    {
        $shift = ShiftLog::create([
            'branch_id' => Branch::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $shift->shift_start);
    }

    public function test_shift_log_has_many_stock_counts(): void
    {
        $shift = ShiftLog::create([
            'branch_id' => Branch::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);
        ShiftStockCount::create([
            'shift_log_id' => $shift->id,
            'ingredient_id' => Ingredient::create(['name' => 'SC', 'unit' => 'g'])->id,
            'opening_quantity' => 50,
        ]);

        $this->assertCount(1, $shift->stockCounts);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SHIFT STOCK COUNT MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_shift_stock_count_belongs_to_shift_log_and_ingredient(): void
    {
        $shift = ShiftLog::create([
            'branch_id' => Branch::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'shift_start' => now(),
            'status' => 'open',
        ]);
        $ingredient = Ingredient::create(['name' => 'SS Ing', 'unit' => 'g']);
        $sc = ShiftStockCount::create([
            'shift_log_id' => $shift->id,
            'ingredient_id' => $ingredient->id,
            'opening_quantity' => 100,
        ]);

        $this->assertInstanceOf(ShiftLog::class, $sc->shiftLog);
        $this->assertInstanceOf(Ingredient::class, $sc->ingredient);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  STOCK MOVEMENT MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_stock_movement_has_type_constants(): void
    {
        $this->assertEquals('initial', StockMovement::TYPE_INITIAL);
        $this->assertEquals('restock', StockMovement::TYPE_RESTOCK);
        $this->assertEquals('sale', StockMovement::TYPE_SALE);
        $this->assertEquals('shift_correction', StockMovement::TYPE_SHIFT_CORRECTION);
    }

    public function test_stock_movement_belongs_to_branch_stock_and_user(): void
    {
        $stock = BranchStock::create([
            'branch_id' => Branch::factory()->create()->id,
            'ingredient_id' => Ingredient::create(['name' => 'SM Ing', 'unit' => 'g'])->id,
            'current_quantity' => 100,
        ]);
        $user = User::factory()->create();
        $movement = StockMovement::create([
            'branch_stock_id' => $stock->id,
            'type' => StockMovement::TYPE_INITIAL,
            'quantity_change' => 100,
            'quantity_before' => 0,
            'quantity_after' => 100,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(BranchStock::class, $movement->branchStock);
        $this->assertInstanceOf(User::class, $movement->user);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  TRANSACTION MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_transaction_belongs_to_branch_user_and_product(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Txn Prod', 'price' => 25]);
        $txn = Transaction::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_amount' => 50.00,
            'client_uuid' => 'txn-rel-test',
        ]);

        $this->assertInstanceOf(Branch::class, $txn->branch);
        $this->assertInstanceOf(User::class, $txn->user);
        $this->assertInstanceOf(Product::class, $txn->product);
    }

    public function test_transaction_has_one_receipt(): void
    {
        $txn = Transaction::create([
            'branch_id' => Branch::factory()->create()->id,
            'product_id' => Product::create(['name' => 'P', 'price' => 10])->id,
            'user_id' => User::factory()->create()->id,
            'quantity' => 1,
            'total_amount' => 10.00,
            'client_uuid' => 'txn-receipt',
        ]);
        Receipt::create([
            'branch_id' => $txn->branch_id,
            'image_path' => 'test.jpg',
            'reconciliation_status' => 'matched',
            'matched_transaction_id' => $txn->id,
        ]);

        $this->assertInstanceOf(Receipt::class, $txn->receipt);
        $this->assertEquals($txn->id, $txn->receipt->matched_transaction_id);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RECEIPT MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_receipt_belongs_to_matched_transaction(): void
    {
        $txn = Transaction::create([
            'branch_id' => Branch::factory()->create()->id,
            'product_id' => Product::create(['name' => 'P', 'price' => 10])->id,
            'user_id' => User::factory()->create()->id,
            'quantity' => 1,
            'total_amount' => 10.00,
            'client_uuid' => 'rec-txn',
        ]);
        $receipt = Receipt::create([
            'branch_id' => $txn->branch_id,
            'image_path' => 'test.jpg',
            'reconciliation_status' => 'matched',
            'matched_transaction_id' => $txn->id,
        ]);

        $this->assertInstanceOf(Transaction::class, $receipt->matchedTransaction);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  WORKER PROFILE MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_worker_profile_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $profile = WorkerProfile::create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $profile->user);
    }

    public function test_worker_profile_json_casts(): void
    {
        $user = User::factory()->create();
        $skills = ['barista', 'cashier', 'inventory'];
        $schedule = ['monday' => '9am-5pm'];
        $profile = WorkerProfile::create([
            'user_id' => $user->id,
            'skills' => $skills,
            'work_schedule' => $schedule,
            'rating' => 4.5,
        ]);

        $this->assertIsArray($profile->skills);
        $this->assertEquals($skills, $profile->skills);
        $this->assertIsArray($profile->work_schedule);
        $this->assertEquals($schedule, $profile->work_schedule);
        $this->assertEquals(4.5, $profile->rating);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  DISCREPANCY ALERT MODEL + OBSERVER
    // ═══════════════════════════════════════════════════════════════════

    public function test_discrepancy_alert_belongs_to_branch(): void
    {
        $branch = Branch::factory()->create();
        $alert = DiscrepancyAlert::create([
            'branch_id' => $branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Alert test',
        ]);

        $this->assertInstanceOf(Branch::class, $alert->branch);
        $this->assertEquals($branch->id, $alert->branch->id);
    }

    public function test_discrepancy_alert_observer_creates_notification_on_create(): void
    {
        $branch = Branch::factory()->create();
        User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'branch_id' => null]);

        $alert = DiscrepancyAlert::create([
            'branch_id' => $branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Observer test alert',
        ]);

        // Observer should have created a notification
        $this->assertDatabaseHas('notifications', [
            'discrepancy_alert_id' => $alert->id,
            'title' => 'Discrepancy alert: '.$branch->name,
        ]);
    }

    public function test_discrepancy_alert_observer_notifies_manager_of_branch(): void
    {
        $branch = Branch::factory()->create(['name' => 'Manager Notify Branch']);
        // Create a manager for this branch
        $manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'branch_id' => $branch->id,
        ]);

        $alert = DiscrepancyAlert::create([
            'branch_id' => $branch->id,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'status' => 'pending',
            'details' => 'Manager notification test',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'discrepancy_alert_id' => $alert->id,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  NOTIFICATION MODEL
    // ═══════════════════════════════════════════════════════════════════

    public function test_notification_mark_as_read_sets_read_at(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Read Test',
            'message' => 'Test message',
        ]);

        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->read_at);

        $notification->markAsRead();

        $this->assertTrue($notification->fresh()->isRead());
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
