<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(int $branchId, int $recordedBy): Payment
    {
        return Payment::create([
            'branch_id' => $branchId,
            'recorded_by' => $recordedBy,
            'category' => 'rent',
            'payee' => 'Landlord',
            'amount' => 1000,
            'method' => 'cash',
            'status' => 'pending',
        ]);
    }

    public function test_manager_cannot_update_another_branchs_payment(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $payment = $this->makePayment($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA)->putJson("/payments/{$payment->id}", [
            'category' => 'rent',
            'payee' => 'Hacked Landlord',
            'amount' => 1,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payee' => 'Landlord']);
    }

    public function test_manager_cannot_mark_another_branchs_payment_paid(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $payment = $this->makePayment($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA)->postJson("/payments/{$payment->id}/mark-paid");

        $response->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
    }

    public function test_manager_cannot_delete_another_branchs_payment(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $payment = $this->makePayment($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA)->deleteJson("/payments/{$payment->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_super_admin_can_update_any_branchs_payment(): void
    {
        $branch = Branch::factory()->create();
        $owner = User::factory()->superAdmin()->create();
        $payment = $this->makePayment($branch->id, $owner->id);

        $response = $this->actingAs($owner)->putJson("/payments/{$payment->id}", [
            'category' => 'rent',
            'payee' => 'Updated Landlord',
            'amount' => 1200,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payee' => 'Updated Landlord']);
    }

    public function test_manager_can_update_own_branchs_payment(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $payment = $this->makePayment($branch->id, $manager->id);

        $response = $this->actingAs($manager)->putJson("/payments/{$payment->id}", [
            'category' => 'rent',
            'payee' => 'Updated Landlord',
            'amount' => 1200,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payee' => 'Updated Landlord']);
    }
}
