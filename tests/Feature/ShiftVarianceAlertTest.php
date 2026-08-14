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

class ShiftVarianceAlertTest extends TestCase
{
    use RefreshDatabase;

    private function makeOpenShift(Branch $branch, User $worker, Ingredient $ingredient, float $opening): ShiftLog
    {
        $shift = ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
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

    public function test_variance_under_threshold_raises_no_alert(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100);

        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $shift = $this->makeOpenShift($branch, $manager, $ingredient, 100);

        $response = $this->actingAs($manager, 'api')->postJson('/api/shifts/close', [
            'shift_log_id' => $shift->id,
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 99], // 1% off, under 5% threshold
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(0, 'discrepancy_alerts');
        $this->assertDatabaseCount('discrepancy_alerts', 0);
    }

    public function test_variance_over_pct_threshold_raises_an_alert(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100000); // effectively disable the peso trigger

        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $shift = $this->makeOpenShift($branch, $manager, $ingredient, 100);

        $response = $this->actingAs($manager, 'api')->postJson('/api/shifts/close', [
            'shift_log_id' => $shift->id,
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 80], // 20% off, over 5% threshold
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'discrepancy_alerts');
        $this->assertDatabaseHas('discrepancy_alerts', [
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'type' => 'shift_variance',
            'severity' => 'high', // 20% >= 2x the 5% threshold
        ]);
    }

    public function test_zero_expected_stock_does_not_divide_by_zero(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100);

        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        // No BranchStock row and an opening count of 0 — expected resolves to 0.
        $shift = $this->makeOpenShift($branch, $manager, $ingredient, 0);

        $response = $this->actingAs($manager, 'api')->postJson('/api/shifts/close', [
            'shift_log_id' => $shift->id,
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 5],
            ],
        ]);

        // Must not 500 — the (float)$expected !== 0.0 branch falls back to pct=1.0.
        $response->assertOk();
        $response->assertJsonCount(1, 'discrepancy_alerts');
    }

    public function test_closing_a_shift_updates_branch_stock_to_the_physical_count(): void
    {
        AppSetting::set('variance_threshold_pct', 0.05);
        AppSetting::set('variance_threshold_php', 100);

        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);
        BranchStock::create(['branch_id' => $branch->id, 'ingredient_id' => $ingredient->id, 'current_quantity' => 100, 'min_threshold' => 10]);
        $shift = $this->makeOpenShift($branch, $manager, $ingredient, 100);

        $this->actingAs($manager, 'api')->postJson('/api/shifts/close', [
            'shift_log_id' => $shift->id,
            'closing_counts' => [
                ['ingredient_id' => $ingredient->id, 'closing_quantity_actual' => 80],
            ],
        ])->assertOk();

        $stock = BranchStock::where('branch_id', $branch->id)->where('ingredient_id', $ingredient->id)->first();
        $this->assertEquals(80.0, (float) $stock->current_quantity);
        $this->assertDatabaseHas('shift_logs', ['id' => $shift->id, 'status' => 'closed']);
    }
}
