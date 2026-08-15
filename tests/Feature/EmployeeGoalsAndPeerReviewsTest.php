<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\EmployeeGoal;
use App\Models\PeerReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeGoalsAndPeerReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_add_peer_review_for_own_branch_worker(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $staff = User::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($manager)->postJson("/business/workers/{$staff->id}/peer-reviews", [
            'comment' => 'Great with customers.',
            'rating' => 4.5,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('peer_reviews', [
            'reviewee_id' => $staff->id,
            'reviewer_id' => $manager->id,
            'comment' => 'Great with customers.',
        ]);
    }

    public function test_manager_cannot_add_peer_review_for_another_branchs_worker(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $staffB = User::factory()->create(['branch_id' => $branchB->id]);

        $this->actingAs($managerA)->postJson("/business/workers/{$staffB->id}/peer-reviews", [
            'comment' => 'Should not be allowed.',
        ])->assertForbidden();
    }

    public function test_staff_cannot_add_peer_review(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        $coworker = User::factory()->create(['branch_id' => $branch->id]);

        $this->actingAs($staff)->postJson("/business/workers/{$coworker->id}/peer-reviews", [
            'comment' => 'Should not be allowed.',
        ])->assertForbidden();
    }

    public function test_manager_can_delete_own_branch_workers_peer_review(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $staff = User::factory()->create(['branch_id' => $branch->id]);
        $review = PeerReview::create([
            'reviewee_id' => $staff->id,
            'reviewer_id' => $manager->id,
            'comment' => 'To be deleted.',
        ]);

        $this->actingAs($manager)->deleteJson("/business/workers/peer-reviews/{$review->id}")
            ->assertOk();

        $this->assertDatabaseMissing('peer_reviews', ['id' => $review->id]);
    }

    public function test_manager_can_add_and_complete_a_goal_for_own_branch_worker(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $staff = User::factory()->create(['branch_id' => $branch->id]);

        $store = $this->actingAs($manager)->postJson("/business/workers/{$staff->id}/goals", [
            'title' => 'Finish barista certification',
        ]);
        $store->assertCreated();

        $goal = EmployeeGoal::where('user_id', $staff->id)->firstOrFail();
        $this->assertSame('pending', $goal->status);

        $this->actingAs($manager)->putJson("/business/workers/goals/{$goal->id}/status", [
            'status' => 'completed',
        ])->assertOk();

        $this->assertSame('completed', $goal->fresh()->status);
    }

    public function test_manager_cannot_manage_goals_for_another_branchs_worker(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $staffB = User::factory()->create(['branch_id' => $branchB->id]);

        $this->actingAs($managerA)->postJson("/business/workers/{$staffB->id}/goals", [
            'title' => 'Should not be allowed.',
        ])->assertForbidden();

        $goal = EmployeeGoal::create([
            'user_id' => $staffB->id,
            'title' => 'Existing goal',
        ]);

        $this->actingAs($managerA)->deleteJson("/business/workers/goals/{$goal->id}")
            ->assertForbidden();
        $this->assertDatabaseHas('employee_goals', ['id' => $goal->id]);
    }

    public function test_super_admin_can_manage_goals_and_reviews_across_branches(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $admin = User::factory()->superAdmin()->create(['branch_id' => $branchA->id]);
        $staffB = User::factory()->create(['branch_id' => $branchB->id]);

        $this->actingAs($admin)->postJson("/business/workers/{$staffB->id}/peer-reviews", [
            'comment' => 'Cross-branch review from admin.',
        ])->assertCreated();

        $this->actingAs($admin)->postJson("/business/workers/{$staffB->id}/goals", [
            'title' => 'Cross-branch goal from admin.',
        ])->assertCreated();
    }
}
