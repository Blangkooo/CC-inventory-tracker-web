<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReceiptScanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create(['name' => 'Test Branch']);

        $this->user = User::factory()->create([
            'role'      => User::ROLE_SUPER_ADMIN,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_scan_requires_authentication(): void
    {
        $response = $this->postJson('/api/receipts/scan', [
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_scan_validates_image_required(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/receipts/scan', [
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_scan_validates_branch_id(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/receipts/scan', [
                'image'     => UploadedFile::fake()->image('receipt.jpg'),
                'branch_id' => 9999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_scan_accepts_only_image_files(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/receipts/scan', [
                'image'     => UploadedFile::fake()->create('document.pdf', 100),
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/receipts?branch_id='.$this->branch->id);

        $response->assertStatus(401);
    }

    public function test_index_returns_receipts(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/receipts?branch_id='.$this->branch->id);

        $response->assertStatus(200);
    }

    public function test_index_validates_branch_id(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/receipts');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_summary_returns_stats(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/receipts/summary?branch_id='.$this->branch->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_scanned',
                'matched',
                'mismatched',
                'pending',
                'unreadable',
            ]);
    }

    public function test_scan_creates_receipt_record(): void
    {
        $file = UploadedFile::fake()->image('receipt.jpg', 400, 200);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/receipts/scan', [
                'image'     => $file,
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'receipt' => [
                    'id', 'branch_id', 'user_id', 'image_path',
                    'raw_ocr_text', 'parsed_total_amount',
                    'reconciliation_status', 'scanned_at',
                ],
                'ocr_text',
                'parsed_amount',
                'reconciliation_status',
                'matched_transaction',
            ]);

        $this->assertDatabaseHas('receipts', [
            'branch_id' => $this->branch->id,
            'user_id'   => $this->user->id,
        ]);
    }
}
