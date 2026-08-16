<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMeetingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeMeeting(?int $branchId, int $createdBy): Meeting
    {
        return Meeting::create([
            'branch_id' => $branchId,
            'created_by' => $createdBy,
            'title' => 'Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'scheduled',
        ]);
    }

    public function test_manager_cannot_view_another_branchs_meeting(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $meeting = $this->makeMeeting($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA, 'api')->getJson("/api/meetings/{$meeting->id}");

        $response->assertForbidden();
    }

    public function test_manager_cannot_update_another_branchs_meeting(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $meeting = $this->makeMeeting($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA, 'api')->putJson("/api/meetings/{$meeting->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id, 'title' => 'Meeting']);
    }

    public function test_manager_cannot_delete_another_branchs_meeting(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $meeting = $this->makeMeeting($branchB->id, $managerA->id);

        $response = $this->actingAs($managerA, 'api')->deleteJson("/api/meetings/{$meeting->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id]);
    }

    public function test_manager_cannot_create_meeting_for_another_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);

        $response = $this->actingAs($manager, 'api')->postJson('/api/meetings', [
            'title' => 'Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'branch_id' => $branchB->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('meetings', ['title' => 'Meeting', 'branch_id' => $branchA->id]);
    }

    public function test_manager_cannot_reassign_meeting_to_another_branch_on_update(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $meeting = $this->makeMeeting($branchA->id, $manager->id);

        $response = $this->actingAs($manager, 'api')->putJson("/api/meetings/{$meeting->id}", [
            'branch_id' => $branchB->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id, 'branch_id' => $branchA->id]);
    }

    public function test_manager_can_manage_own_branchs_meeting(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $meeting = $this->makeMeeting($branch->id, $manager->id);

        $this->actingAs($manager, 'api')->getJson("/api/meetings/{$meeting->id}")->assertOk();
        $this->actingAs($manager, 'api')->putJson("/api/meetings/{$meeting->id}", ['title' => 'Updated'])->assertOk();
        $this->actingAs($manager, 'api')->deleteJson("/api/meetings/{$meeting->id}")->assertOk();
        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
    }

    public function test_super_admin_can_manage_any_branchs_meeting(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->superAdmin()->create();
        $meeting = $this->makeMeeting($branch->id, $admin->id);

        $this->actingAs($admin, 'api')->putJson("/api/meetings/{$meeting->id}", ['title' => 'Updated'])->assertOk();
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id, 'title' => 'Updated']);
    }
}
