<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertsKpiTest extends TestCase
{
    use RefreshDatabase;

    private function makeAlert(array $overrides = []): DiscrepancyAlert
    {
        $branch = $overrides['branch_id'] ?? Branch::factory()->create()->id;
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $alert = DiscrepancyAlert::create(array_merge([
            'branch_id' => $branch,
            'type' => 'stock_mismatch',
            'severity' => 'medium',
            'status' => 'pending',
            'variance' => -1,
            'details' => 'Test alert.',
        ], $overrides));

        if ($createdAt !== null) {
            $alert->forceFill(['created_at' => $createdAt])->save();
        }

        return $alert;
    }

    public function test_kpi_counts_reflect_seeded_alerts(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->makeAlert(['status' => 'pending', 'severity' => 'high']);
        $this->makeAlert(['status' => 'pending', 'severity' => 'medium']);
        $this->makeAlert(['status' => 'reviewed', 'created_at' => now()]);
        $this->makeAlert(['status' => 'dismissed', 'created_at' => now()->subMonths(2)]);

        $response = $this->actingAs($admin)->get('/alerts');

        $response->assertOk();
        $response->assertViewHas('kpi_active', 2);
        $response->assertViewHas('kpi_high_severity', 1);
        $response->assertViewHas('kpi_resolved_this_month', 1);
    }

    public function test_value_recovered_uses_the_shared_calculator(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();
        $ingredient = Ingredient::create(['name' => 'Coffee Beans', 'unit' => 'g']);

        $this->makeAlert([
            'branch_id' => $branch->id,
            'status' => 'reviewed',
            'ingredient_id' => $ingredient->id,
            'variance' => -5,
        ]);

        $response = $this->actingAs($admin)->get('/alerts');

        $response->assertOk();
        $response->assertViewHas('kpi_value_recovered');
    }

    public function test_manager_only_sees_their_branchs_alerts_and_kpis(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);

        $this->makeAlert(['branch_id' => $branchA->id, 'status' => 'pending']);
        $this->makeAlert(['branch_id' => $branchB->id, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get('/alerts');

        $response->assertOk();
        $response->assertViewHas('kpi_active', 1);
    }
}
