<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeStaleReceiptsTest extends TestCase
{
    use RefreshDatabase;

    private function makeReceipt(Branch $branch, User $user, string $path, string $createdAt): Receipt
    {
        $receipt = Receipt::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'image_path' => $path,
            'reconciliation_status' => 'pending',
        ]);
        $receipt->created_at = $createdAt;
        $receipt->saveQuietly();

        return $receipt;
    }

    public function test_purges_a_receipt_file_past_the_retention_cutoff(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', 24);

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('receipts/old.jpg', 'fake image bytes');
        $receipt = $this->makeReceipt($branch, $user, 'receipts/old.jpg', now()->subMonths(30)->toDateTimeString());

        $this->artisan('app:purge-stale-receipts')->assertExitCode(0);

        Storage::disk('public')->assertMissing('receipts/old.jpg');
        $this->assertNull($receipt->fresh()->image_path);
    }

    public function test_keeps_a_receipt_file_still_within_retention(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', 24);

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('receipts/recent.jpg', 'fake image bytes');
        $receipt = $this->makeReceipt($branch, $user, 'receipts/recent.jpg', now()->subMonths(2)->toDateTimeString());

        $this->artisan('app:purge-stale-receipts')->assertExitCode(0);

        Storage::disk('public')->assertExists('receipts/recent.jpg');
        $this->assertSame('receipts/recent.jpg', $receipt->fresh()->image_path);
    }

    public function test_the_underlying_record_survives_the_purge(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', 24);

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('receipts/old.jpg', 'fake image bytes');
        $receipt = $this->makeReceipt($branch, $user, 'receipts/old.jpg', now()->subMonths(30)->toDateTimeString());

        $this->artisan('app:purge-stale-receipts');

        $this->assertDatabaseHas('receipts', ['id' => $receipt->id]);
    }

    public function test_dry_run_deletes_nothing(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', 24);

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('receipts/old.jpg', 'fake image bytes');
        $receipt = $this->makeReceipt($branch, $user, 'receipts/old.jpg', now()->subMonths(30)->toDateTimeString());

        $this->artisan('app:purge-stale-receipts --dry-run')->assertExitCode(0);

        Storage::disk('public')->assertExists('receipts/old.jpg');
        $this->assertSame('receipts/old.jpg', $receipt->fresh()->image_path);
    }

    public function test_a_malformed_retention_setting_does_not_purge_everything(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', '0'); // malformed/zero setting

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('receipts/fresh.jpg', 'fake image bytes');
        // Created moments ago — must survive even a "0 months" setting thanks to the floor guard.
        $receipt = Receipt::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'image_path' => 'receipts/fresh.jpg',
            'reconciliation_status' => 'pending',
        ]);

        $this->artisan('app:purge-stale-receipts')->assertExitCode(0);

        Storage::disk('public')->assertExists('receipts/fresh.jpg');
        $this->assertSame('receipts/fresh.jpg', $receipt->fresh()->image_path);
    }

    public function test_payment_receipt_photos_are_also_purged(): void
    {
        Storage::fake('public');
        AppSetting::set('receipt_retention_months', 24);

        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        Storage::disk('public')->put('payments/old.jpg', 'fake image bytes');
        $payment = Payment::create([
            'branch_id' => $branch->id,
            'recorded_by' => $user->id,
            'category' => 'rent',
            'payee' => 'Landlord',
            'amount' => 500,
            'method' => 'cash',
            'status' => 'paid',
            'receipt_photo' => 'payments/old.jpg',
        ]);
        $payment->created_at = now()->subMonths(30);
        $payment->saveQuietly();

        $this->artisan('app:purge-stale-receipts');

        Storage::disk('public')->assertMissing('payments/old.jpg');
        $this->assertNull($payment->fresh()->receipt_photo);
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }
}
