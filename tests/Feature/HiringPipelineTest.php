<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiringPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_cards_reflect_real_pipeline_state(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();

        $openOpening = JobOpening::create(['branch_id' => $branch->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $admin->id]);
        JobOpening::create(['branch_id' => $branch->id, 'title' => 'Old Role', 'status' => 'closed', 'posted_by' => $admin->id]);

        $openOpening->applicants()->create(['name' => 'A', 'status' => 'applied']);
        $openOpening->applicants()->create(['name' => 'B', 'status' => 'interviewed']);
        $openOpening->applicants()->create(['name' => 'C', 'status' => 'interviewed']);
        $openOpening->applicants()->create(['name' => 'D', 'status' => 'hired']);

        $response = $this->actingAs($admin)->get('/hiring');

        $response->assertOk();
        $response->assertViewHas('kpi_open_positions', 1);
        $response->assertViewHas('kpi_applicants', 4);
        $response->assertViewHas('kpi_in_interview', 2);
        $response->assertViewHas('kpi_accepted', 1);
    }

    public function test_advancing_an_applicant_through_the_pipeline_uses_the_real_update_endpoint(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();
        $opening = JobOpening::create(['branch_id' => $branch->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $admin->id]);
        $applicant = $opening->applicants()->create(['name' => 'Test Applicant', 'status' => 'applied']);

        $this->actingAs($admin)->putJson("/hiring/applicants/{$applicant->id}/status", ['status' => 'shortlisted'])
            ->assertOk();

        $this->assertSame('shortlisted', $applicant->fresh()->status);
    }
}
