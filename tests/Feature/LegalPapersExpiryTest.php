<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPapersExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expiry_bucket_counts_are_correct(): void
    {
        $admin = User::factory()->superAdmin()->create();

        LegalDocument::create(['title' => 'Expiring in 10 days', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $admin->id, 'expires_at' => now()->addDays(10)]);
        LegalDocument::create(['title' => 'Expiring in 45 days', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $admin->id, 'expires_at' => now()->addDays(45)]);
        LegalDocument::create(['title' => 'Expired last week', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $admin->id, 'expires_at' => now()->subDays(7)]);
        LegalDocument::create(['title' => 'Valid for a year', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $admin->id, 'expires_at' => now()->addYear()]);
        LegalDocument::create(['title' => 'No expiry set', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $admin->id]);

        $response = $this->actingAs($admin)->get('/legal-papers');

        $response->assertOk();
        $response->assertViewHas('expiring_30_count', 1);
        $response->assertViewHas('expiring_60_count', 1);
        $response->assertViewHas('expired_count', 1);
        $response->assertViewHas('active_count', 4);
    }

    public function test_manager_only_sees_their_branch_and_company_wide_documents(): void
    {
        $branchA = \App\Models\Branch::factory()->create();
        $branchB = \App\Models\Branch::factory()->create();
        $manager = User::factory()->manager()->create(['branch_id' => $branchA->id]);

        LegalDocument::create(['title' => 'Branch A doc', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $manager->id, 'branch_id' => $branchA->id]);
        LegalDocument::create(['title' => 'Branch B doc', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $manager->id, 'branch_id' => $branchB->id]);
        LegalDocument::create(['title' => 'Company-wide doc', 'type' => 'permit', 'file_path' => 'x', 'uploaded_by' => $manager->id, 'branch_id' => null]);

        $response = $this->actingAs($manager)->get('/legal-papers');

        $response->assertOk();
        $response->assertViewHas('active_count', 2);
    }
}
