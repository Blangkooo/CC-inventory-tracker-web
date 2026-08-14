<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Ingredient;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffShiftCloseTest extends TestCase
{
    use RefreshDatabase;

    private function makeOpenShift(Branch $branch, User $staff, Ingredient $ingredient, float $opening): ShiftLog
    {
        $shift = ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
            'shift_start' => now()->subHours(4),
            'status' => 'open',
        ]);

        ShiftStockCount::create([
            'shift_log_id' => $shift->id,
            'ingredient_id' => $ingredient->id,
            'opening_quantity' => $opening,
        ]);

        return $shift;
    }

    public function test_closing_with_no_variance_closes_the_shift_and_raises_no_alert(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100);

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $this->makeOpenShift($branch, $staff, $ingredient, 100);

        $response = $this->actingAs($staff)->postJson('/staff/close-shift', [
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 100],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(0, 'discrepancy_alerts');
        $this->assertDatabaseHas('shift_logs', ['user_id' => $staff->id, 'status' => 'closed']);
        $this->assertDatabaseCount('discrepancy_alerts', 0);
    }

    public function test_closing_with_variance_over_threshold_raises_an_alert(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100000);

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $this->makeOpenShift($branch, $staff, $ingredient, 100);

        $response = $this->actingAs($staff)->postJson('/staff/close-shift', [
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 80], // 20% short
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'discrepancy_alerts');
        $this->assertDatabaseHas('discrepancy_alerts', [
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'type' => 'shift_variance',
            'severity' => 'high',
        ]);

        $stock = BranchStock::where('branch_id', $branch->id)->where('ingredient_id', $ingredient->id)->first();
        $this->assertEquals(80.0, (float) $stock->current_quantity);
    }

    public function test_cannot_close_without_an_open_shift(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);

        $response = $this->actingAs($staff)->postJson('/staff/close-shift', [
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 50],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_a_staff_member_cannot_close_another_staff_members_shift(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100);

        $branch = Branch::factory()->create();
        $staffA = User::factory()->create(['branch_id' => $branch->id]);
        $staffB = User::factory()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $shiftA = $this->makeOpenShift($branch, $staffA, $ingredient, 100);

        // staffB has no open shift of their own — closing must act on staffB's
        // own shift only (there is none), never staffA's, regardless of branch.
        $response = $this->actingAs($staffB)->postJson('/staff/close-shift', [
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('shift_logs', ['id' => $shiftA->id, 'status' => 'open']);
    }
}
