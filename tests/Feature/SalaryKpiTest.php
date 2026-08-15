<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Payslip;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_cards_reflect_this_months_payslips_and_configured_rates(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();

        $workerWithRate = User::factory()->create(['branch_id' => $branch->id]);
        WorkerProfile::create(['user_id' => $workerWithRate->id, 'hourly_rate' => 100]);

        $workerNoRate = User::factory()->create(['branch_id' => $branch->id]);
        WorkerProfile::create(['user_id' => $workerNoRate->id, 'hourly_rate' => null]);

        Payslip::create([
            'user_id' => $workerWithRate->id, 'branch_id' => $branch->id, 'generated_by' => $admin->id,
            'period_start' => now()->startOfMonth(), 'period_end' => now(),
            'hourly_rate' => 100, 'total_hours' => 40, 'gross_pay' => 4000, 'deductions' => 0, 'net_pay' => 4000,
            'status' => 'paid', 'paid_at' => now(),
        ]);
        Payslip::create([
            'user_id' => $workerWithRate->id, 'branch_id' => $branch->id, 'generated_by' => $admin->id,
            'period_start' => now()->startOfMonth(), 'period_end' => now(),
            'hourly_rate' => 100, 'total_hours' => 20, 'gross_pay' => 2000, 'deductions' => 0, 'net_pay' => 2000,
            'status' => 'draft',
        ]);
        // Last month — should not count toward this month's KPIs.
        $old = Payslip::create([
            'user_id' => $workerWithRate->id, 'branch_id' => $branch->id, 'generated_by' => $admin->id,
            'period_start' => now()->subMonth()->startOfMonth(), 'period_end' => now()->subMonth(),
            'hourly_rate' => 100, 'total_hours' => 10, 'gross_pay' => 1000, 'deductions' => 0, 'net_pay' => 1000,
            'status' => 'paid', 'paid_at' => now()->subMonth(),
        ]);
        $old->forceFill(['created_at' => now()->subMonth()])->save();

        $response = $this->actingAs($admin)->get('/salary');

        $response->assertOk();
        $response->assertViewHas('kpi_monthly_payroll', '6000.00');
        $response->assertViewHas('kpi_pending_payslips', 1);
        $response->assertViewHas('kpi_paid_this_month', 1);
        $response->assertViewHas('kpi_average_rate', 100.0);
    }

    public function test_average_rate_is_null_when_no_worker_has_a_configured_rate(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();
        $worker = User::factory()->create(['branch_id' => $branch->id]);
        WorkerProfile::create(['user_id' => $worker->id, 'hourly_rate' => null]);

        $response = $this->actingAs($admin)->get('/salary');

        $response->assertOk();
        $response->assertViewHas('kpi_average_rate', null);
    }
}
