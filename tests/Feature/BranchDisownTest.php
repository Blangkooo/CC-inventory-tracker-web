<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchDisownTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_disown_a_branch(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create(['status' => 'active']);

        $response = $this->actingAs($admin)
            ->putJson("/branches/{$branch->id}/disown");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertSame('inactive', $branch->fresh()->status);
    }

    public function test_manager_cannot_disown_a_branch(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($manager)
            ->putJson("/branches/{$branch->id}/disown");

        $response->assertForbidden();
        $this->assertSame('active', $branch->fresh()->status);
    }

    public function test_disowned_branch_is_hidden_from_branches_index(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $activeBranch = Branch::factory()->create(['status' => 'active', 'name' => 'Active Branch']);
        $disownedBranch = Branch::factory()->create(['status' => 'inactive', 'name' => 'Disowned Branch']);

        $response = $this->actingAs($admin)->get('/branches');

        $response->assertOk();
        $response->assertSee('Active Branch');
        $response->assertDontSee('Disowned Branch');
    }
}
