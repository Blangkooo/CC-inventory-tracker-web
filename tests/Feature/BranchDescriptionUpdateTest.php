<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchDescriptionUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_any_branchs_description(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create(['description' => 'Old description']);

        $response = $this->actingAs($admin)
            ->putJson("/branches/{$branch->id}/description", ['description' => 'A real, owner-written description.']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertSame('A real, owner-written description.', $branch->fresh()->description);
    }

    public function test_manager_can_update_their_own_branchs_description(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($manager)
            ->putJson("/branches/{$branch->id}/description", ['description' => 'Managed by the branch manager.']);

        $response->assertOk();
        $this->assertSame('Managed by the branch manager.', $branch->fresh()->description);
    }

    public function test_manager_cannot_update_another_branchs_description(): void
    {
        $ownBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create(['description' => 'Untouched']);
        $manager = User::factory()->manager()->create(['branch_id' => $ownBranch->id]);

        $response = $this->actingAs($manager)
            ->putJson("/branches/{$otherBranch->id}/description", ['description' => 'Should not be allowed.']);

        $response->assertForbidden();
        $this->assertSame('Untouched', $otherBranch->fresh()->description);
    }

    public function test_description_can_be_cleared_to_null(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create(['description' => 'Something']);

        $response = $this->actingAs($admin)
            ->putJson("/branches/{$branch->id}/description", ['description' => '']);

        $response->assertOk();
        $this->assertNull($branch->fresh()->description);
    }
}
