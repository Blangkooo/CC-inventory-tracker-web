<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use App\Models\LegalDocument;
use App\Models\Meeting;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossBranchAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // ─── Hiring: Job Openings ───────────────────────────────────────────

    public function test_manager_cannot_update_another_branchs_job_opening(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchB->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $managerA->id]);

        $response = $this->actingAs($managerA)->putJson("/hiring/openings/{$opening->id}", [
            'title' => 'Hacked Title',
            'status' => 'open',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('job_openings', ['id' => $opening->id, 'title' => 'Barista']);
    }

    public function test_manager_cannot_delete_another_branchs_job_opening(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchB->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $managerA->id]);

        $response = $this->actingAs($managerA)->deleteJson("/hiring/openings/{$opening->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('job_openings', ['id' => $opening->id]);
    }

    public function test_manager_cannot_add_applicant_to_another_branchs_opening(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchB->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $managerA->id]);

        $response = $this->actingAs($managerA)->postJson("/hiring/openings/{$opening->id}/applicants", [
            'name' => 'Sneaky Applicant',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('job_applicants', ['name' => 'Sneaky Applicant']);
    }

    public function test_manager_cannot_update_status_of_another_branchs_applicant(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchB->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $managerA->id]);
        $applicant = JobApplicant::create(['job_opening_id' => $opening->id, 'name' => 'Applicant', 'status' => 'applied']);

        $response = $this->actingAs($managerA)->putJson("/hiring/applicants/{$applicant->id}/status", [
            'status' => 'hired',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'applied']);
    }

    public function test_manager_cannot_delete_another_branchs_applicant(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchB->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $managerA->id]);
        $applicant = JobApplicant::create(['job_opening_id' => $opening->id, 'name' => 'Applicant', 'status' => 'applied']);

        $response = $this->actingAs($managerA)->deleteJson("/hiring/applicants/{$applicant->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id]);
    }

    public function test_manager_can_update_own_branchs_job_opening(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $opening = JobOpening::create(['branch_id' => $branch->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $manager->id]);

        $response = $this->actingAs($manager)->putJson("/hiring/openings/{$opening->id}", [
            'title' => 'Senior Barista',
            'status' => 'open',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('job_openings', ['id' => $opening->id, 'title' => 'Senior Barista']);
    }

    public function test_manager_cannot_reassign_job_opening_to_another_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $opening = JobOpening::create(['branch_id' => $branchA->id, 'title' => 'Barista', 'status' => 'open', 'posted_by' => $manager->id]);

        $response = $this->actingAs($manager)->putJson("/hiring/openings/{$opening->id}", [
            'branch_id' => $branchB->id,
            'title' => 'Barista',
            'status' => 'open',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('job_openings', ['id' => $opening->id, 'branch_id' => $branchA->id]);
    }

    // ─── Legal Papers ───────────────────────────────────────────────────

    public function test_manager_cannot_download_another_branchs_legal_document(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $managerA = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $document = LegalDocument::create([
            'branch_id' => $branchB->id,
            'uploaded_by' => $managerA->id,
            'title' => 'Permit',
            'type' => 'permit',
            'file_path' => 'legal-documents/test.pdf',
        ]);

        $response = $this->actingAs($managerA)->get("/legal-papers/{$document->id}/download");

        $response->assertForbidden();
    }

    public function test_manager_can_download_own_branchs_legal_document(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $document = LegalDocument::create([
            'branch_id' => $branch->id,
            'uploaded_by' => $manager->id,
            'title' => 'Permit',
            'type' => 'permit',
            'file_path' => 'legal-documents/test.pdf',
        ]);

        $response = $this->actingAs($manager)->get("/legal-papers/{$document->id}/download");

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    // ─── Notices (Mail) ─────────────────────────────────────────────────

    public function test_staff_cannot_delete_another_branchs_notice(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $staffA = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $branchA->id]);
        $notice = Notice::create(['branch_id' => $branchB->id, 'posted_by' => $staffA->id, 'title' => 'Notice', 'body' => 'Body']);

        $response = $this->actingAs($staffA)->deleteJson("/mail/{$notice->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('notices', ['id' => $notice->id]);
    }

    public function test_manager_cannot_delete_a_company_wide_notice(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $admin = User::factory()->superAdmin()->create();
        $notice = Notice::create(['branch_id' => null, 'posted_by' => $admin->id, 'title' => 'Company Notice', 'body' => 'Body']);

        $response = $this->actingAs($manager)->deleteJson("/mail/{$notice->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('notices', ['id' => $notice->id]);
    }

    public function test_staff_can_delete_a_notice_posted_under_their_own_branch(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $branch->id]);
        $notice = Notice::create(['branch_id' => $branch->id, 'posted_by' => $staff->id, 'title' => 'Notice', 'body' => 'Body']);

        $response = $this->actingAs($staff)->deleteJson("/mail/{$notice->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('notices', ['id' => $notice->id]);
    }

    public function test_staff_cannot_post_a_notice_under_another_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $staffA = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $branchA->id]);

        $response = $this->actingAs($staffA)->postJson('/mail', [
            'branch_id' => $branchB->id,
            'title' => 'Fake Notice',
            'body' => 'Body',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('notices', ['title' => 'Fake Notice', 'branch_id' => $branchA->id]);
    }

    // ─── Calendar Meetings (route + ownership) ────────────────────────

    public function test_staff_cannot_reach_calendar_routes(): void
    {
        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $branch->id]);

        $this->actingAs($staff)->get('/calendar')->assertForbidden();
        $this->actingAs($staff)->postJson('/calendar/meetings', [
            'title' => 'Sneaky Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ])->assertForbidden();
    }

    public function test_manager_cannot_create_meeting_for_another_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);

        $response = $this->actingAs($manager)->postJson('/calendar/meetings', [
            'title' => 'Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'branch_id' => $branchB->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('meetings', ['title' => 'Meeting', 'branch_id' => $branchA->id]);
    }

    public function test_manager_cannot_delete_another_branchs_meeting(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);
        $meeting = Meeting::create([
            'branch_id' => $branchB->id,
            'created_by' => $manager->id,
            'title' => 'Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($manager)->deleteJson("/calendar/meetings/{$meeting->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id]);
    }

    public function test_manager_can_delete_own_branchs_meeting(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branch->id]);
        $meeting = Meeting::create([
            'branch_id' => $branch->id,
            'created_by' => $manager->id,
            'title' => 'Meeting',
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($manager)->deleteJson("/calendar/meetings/{$meeting->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
    }
}
