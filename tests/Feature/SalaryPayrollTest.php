<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ShiftLog;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryPayrollTest extends TestCase
{
    use RefreshDatabase;

    private function makeWorker(Branch $branch, float $hourlyRate = 100): User
    {
        $worker = User::factory()->create(['branch_id' => $branch->id]);
        WorkerProfile::create(['user_id' => $worker->id, 'hourly_rate' => $hourlyRate]);

        return $worker;
    }

    public function test_generates_correct_pay_for_a_normal_shift(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $worker = $this->makeWorker($branch, hourlyRate: 100);

        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
            'shift_start' => '2026-08-03 08:00:00',
            'shift_end' => '2026-08-03 16:00:00', // 8 hours
            'status' => 'closed',
        ]);

        $response = $this->actingAs($owner)->postJson('/salary/payslips', [
            'user_id' => $worker->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-07',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payslip.total_hours', '8.00');
        $response->assertJsonPath('payslip.gross_pay', '800.00');
        $response->assertJsonPath('payslip.net_pay', '800.00');
    }

    public function test_shift_spanning_midnight_counts_full_duration(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $worker = $this->makeWorker($branch, hourlyRate: 50);

        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
            'shift_start' => '2026-08-03 22:00:00',
            'shift_end' => '2026-08-04 02:00:00', // 4 hours, spans midnight
            'status' => 'closed',
        ]);

        $response = $this->actingAs($owner)->postJson('/salary/payslips', [
            'user_id' => $worker->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-07',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payslip.total_hours', '4.00');
        $response->assertJsonPath('payslip.gross_pay', '200.00');
    }

    public function test_open_unclosed_shift_is_excluded_from_hours(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $worker = $this->makeWorker($branch, hourlyRate: 100);

        // A closed shift that should count...
        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
            'shift_start' => '2026-08-03 08:00:00',
            'shift_end' => '2026-08-03 12:00:00', // 4 hours
            'status' => 'closed',
        ]);

        // ...and a still-open shift (no shift_end) left clocked in for days,
        // which must NOT inflate the payslip.
        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
            'shift_start' => '2026-08-04 08:00:00',
            'shift_end' => null,
            'status' => 'open',
        ]);

        $response = $this->actingAs($owner)->postJson('/salary/payslips', [
            'user_id' => $worker->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-07',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payslip.total_hours', '4.00');
        $response->assertJsonPath('payslip.gross_pay', '400.00');
    }

    public function test_deductions_reduce_net_pay(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $worker = $this->makeWorker($branch, hourlyRate: 100);

        ShiftLog::create([
            'branch_id' => $branch->id,
            'user_id' => $worker->id,
            'shift_start' => '2026-08-03 08:00:00',
            'shift_end' => '2026-08-03 16:00:00', // 8 hours
            'status' => 'closed',
        ]);

        $response = $this->actingAs($owner)->postJson('/salary/payslips', [
            'user_id' => $worker->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-07',
            'deductions' => 50,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payslip.gross_pay', '800.00');
        $response->assertJsonPath('payslip.net_pay', '750.00');
    }

    public function test_generation_rejected_without_an_hourly_rate(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $worker = User::factory()->create(['branch_id' => $branch->id]);
        // No WorkerProfile / hourly_rate set at all.

        $response = $this->actingAs($owner)->postJson('/salary/payslips', [
            'user_id' => $worker->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-07',
        ]);

        $response->assertStatus(422);
    }
}
