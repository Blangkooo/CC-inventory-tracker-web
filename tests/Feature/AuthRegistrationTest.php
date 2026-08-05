<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ── Step 1: Personal Information ─────────────────────────────────

    public function test_api_register_step_1_creates_user(): void
    {
        $payload = [
            'full_name'      => 'Juan Dela Cruz',
            'email'          => 'juan@example.com',
            'contact_number' => '+63 912 345 6789',
            'role'           => 'owner',
        ];

        $response = $this->postJson('/api/auth/register/step-1', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => true,
                'message' => 'Personal information saved. Proceed to step 2.',
                'data'    => [
                    'email' => 'juan@example.com',
                    'role'  => 'owner',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'name'  => 'Juan Dela Cruz',
            'role'  => User::ROLE_SUPER_ADMIN,
        ]);
    }

    public function test_api_register_step_1_creates_manager_role(): void
    {
        $payload = [
            'full_name'      => 'Maria Santos',
            'email'          => 'maria@example.com',
            'contact_number' => '+63 917 654 3210',
            'role'           => 'manager',
        ];

        $response = $this->postJson('/api/auth/register/step-1', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role'  => User::ROLE_MANAGER,
        ]);

        // Response should include the user_id
        $user = User::where('email', 'maria@example.com')->first();
        $response->assertJsonPath('data.user_id', $user->id);
    }

    public function test_api_register_step_1_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register/step-1', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'email', 'contact_number', 'role']);
    }

    public function test_api_register_step_1_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/auth/register/step-1', [
            'full_name'      => 'Test User',
            'email'          => 'existing@example.com',
            'contact_number' => '+63 912 345 6789',
            'role'           => 'owner',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_register_step_1_validates_role(): void
    {
        $response = $this->postJson('/api/auth/register/step-1', [
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'contact_number' => '+63 912 345 6789',
            'role'           => 'cashier', // invalid role
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    // ── Step 2 (Owner): Business Registration ────────────────────────

    public function test_api_register_step_2_creates_single_branch_array_format(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'branch_id' => null]);

        $payload = [
            'user_id' => $user->id,
            'businesses' => [
                [
                    'business_name'        => 'QC Main Branch',
                    'type_of_business'     => 'Coffee Shop',
                    'business_registration' => 'BRN-12345',
                    'business_permit'      => 'PER-67890',
                    'location'             => 'Quezon City',
                ],
            ],
        ];

        $response = $this->postJson('/api/auth/register/step-2', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => true,
                'message' => '1 branch(es) created.',
            ]);

        $this->assertDatabaseHas('branches', [
            'name'     => 'QC Main Branch',
            'location' => 'Quezon City',
            'status'   => 'active',
        ]);
    }

    public function test_api_register_step_2_creates_multiple_branches(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'branch_id' => null]);

        $payload = [
            'user_id' => $user->id,
            'businesses' => [
                [
                    'business_name' => 'Branch One',
                    'location'      => 'Location One',
                ],
                [
                    'business_name' => 'Branch Two',
                    'location'      => 'Location Two',
                ],
            ],
        ];

        $response = $this->postJson('/api/auth/register/step-2', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', '2 branch(es) created.');

        $this->assertDatabaseCount('branches', 2);
    }

    public function test_api_register_step_2_flat_format_via_email(): void
    {
        $user = User::factory()->create([
            'email'     => 'owner@example.com',
            'role'      => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
        ]);

        $payload = [
            'email'         => 'owner@example.com',
            'business_name' => 'New Branch via Email',
            'location'      => 'Some Location',
        ];

        $response = $this->postJson('/api/auth/register/step-2', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('branches', [
            'name' => 'New Branch via Email',
        ]);
    }

    public function test_api_register_step_2_validates_required(): void
    {
        $response = $this->postJson('/api/auth/register/step-2', []);

        $response->assertStatus(422);
    }

    // ── Step 2 (Manager): Branch + Manager Link ──────────────────────

    public function test_api_register_manager_step_2_creates_branch_and_links_user(): void
    {
        $user = User::factory()->create([
            'role'      => User::ROLE_MANAGER,
            'branch_id' => null,
        ]);

        $payload = [
            'user_id'         => $user->id,
            'business_name'   => 'Managed Branch',
            'branch_location' => 'Makati City',
            'business_owner'  => 'Owner Name',
        ];

        $response = $this->postJson('/api/auth/register/manager/step-2', $payload);

        $response->assertStatus(201)
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('branches', [
            'name'     => 'Managed Branch',
            'location' => 'Makati City',
        ]);

        // User should be linked to the new branch
        $branchId = \App\Models\Branch::where('name', 'Managed Branch')->first()->id;
        $this->assertDatabaseHas('users', [
            'id'        => $user->id,
            'branch_id' => $branchId,
        ]);
    }

    // ── Step 3: Confirm Registration ─────────────────────────────────

    public function test_api_register_confirm_with_user_id(): void
    {
        $user = User::factory()->create();

        $payload = [
            'user_id'                      => $user->id,
            'permit_validity'              => true,
            'terms_accepted'               => true,
            'legal_papers_submitted'       => true,
            'legal_papers_secondary_submitted' => true,
        ];

        $response = $this->postJson('/api/auth/register/confirm', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => 'Onboarding completed successfully.',
                'data'    => [
                    'user_id' => $user->id,
                    'trackers' => [
                        'permit_validity'    => 'valid',
                        'terms_of_service'   => 'accepted',
                        'legal_papers'       => 'submitted',
                        'legal_papers_secondary' => 'submitted',
                    ],
                ],
            ]);
    }

    public function test_api_register_confirm_with_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $payload = [
            'email'                        => 'test@example.com',
            'permit_validity'              => true,
            'terms_accepted'               => true,
            'legal_papers_submitted'       => false,
            'legal_papers_secondary_submitted' => false,
        ];

        $response = $this->postJson('/api/auth/register/confirm', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.trackers.legal_papers', 'pending');
    }

    public function test_api_register_confirm_validates_booleans(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/register/confirm', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'permit_validity',
                'terms_accepted',
                'legal_papers_submitted',
                'legal_papers_secondary_submitted',
            ]);
    }

    // ── Full Registration Flow (Integration) ─────────────────────────

    public function test_full_owner_registration_flow(): void
    {
        // Step 1: Create user
        $step1 = $this->postJson('/api/auth/register/step-1', [
            'full_name'      => 'Owner User',
            'email'          => 'owneruser@example.com',
            'contact_number' => '+63 900 000 0001',
            'role'           => 'owner',
        ]);

        $step1->assertStatus(201);
        $userId = $step1->json('data.user_id');

        // Step 2: Create branches
        $step2 = $this->postJson('/api/auth/register/step-2', [
            'user_id' => $userId,
            'businesses' => [
                [
                    'business_name' => 'Main Store',
                    'location'      => 'City Center',
                ],
                [
                    'business_name' => 'Second Store',
                    'location'      => 'Suburb',
                ],
            ],
        ]);

        $step2->assertStatus(201)
            ->assertJsonPath('message', '2 branch(es) created.');

        $this->assertDatabaseCount('branches', 2);

        // Step 3: Confirm
        $step3 = $this->postJson('/api/auth/register/confirm', [
            'user_id'                      => $userId,
            'permit_validity'              => true,
            'terms_accepted'               => true,
            'legal_papers_submitted'       => true,
            'legal_papers_secondary_submitted' => true,
        ]);

        $step3->assertStatus(200)
            ->assertJsonPath('data.user_id', $userId);
    }

    public function test_full_manager_registration_flow(): void
    {
        // Step 1: Create manager user
        $step1 = $this->postJson('/api/auth/register/step-1', [
            'full_name'      => 'Manager Person',
            'email'          => 'manager@example.com',
            'contact_number' => '+63 900 000 0002',
            'role'           => 'manager',
        ]);

        $step1->assertStatus(201);
        $userId = $step1->json('data.user_id');

        // Step 2: Create branch for manager
        $step2 = $this->postJson('/api/auth/register/manager/step-2', [
            'user_id'         => $userId,
            'business_name'   => 'Managed Branch',
            'branch_location' => 'Business District',
            'business_owner'  => 'Big Boss',
        ]);

        $step2->assertStatus(201);

        // Branch should exist and manager should be linked
        $this->assertDatabaseHas('branches', ['name' => 'Managed Branch']);
        $branchId = \App\Models\Branch::where('name', 'Managed Branch')->first()->id;
        $this->assertDatabaseHas('users', ['id' => $userId, 'branch_id' => $branchId]);
    }
}
