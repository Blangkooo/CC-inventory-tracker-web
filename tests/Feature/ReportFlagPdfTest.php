<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFlagPdfTest extends TestCase
{
    use RefreshDatabase;

    private function makeAlert(int $branchId): DiscrepancyAlert
    {
        $ingredient = Ingredient::create(['name' => 'Whole Milk', 'unit' => 'l']);

        return DiscrepancyAlert::create([
            'branch_id' => $branchId,
            'type' => 'stock_mismatch',
            'severity' => 'high',
            'ingredient_id' => $ingredient->id,
            'expected_value' => 10,
            'actual_value' => 6,
            'variance' => -4,
            'details' => 'Counted short during closing shift.',
            'status' => 'pending',
        ]);
    }

    public function test_super_admin_can_download_flag_pdf(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $alert = $this->makeAlert($branch->id);

        $response = $this->actingAs($admin)->get("/reports/flags/{$alert->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_can_download_own_branchs_flag_pdf(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $alert = $this->makeAlert($branch->id);

        $response = $this->actingAs($manager)->get("/reports/flags/{$alert->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_cannot_download_another_branchs_flag_pdf(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $alert = $this->makeAlert($branchB->id);

        $response = $this->actingAs($managerA)->get("/reports/flags/{$alert->id}/pdf");

        $response->assertForbidden();
    }
}
