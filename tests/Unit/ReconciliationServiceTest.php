<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReconciliationService $service;
    private Branch $branch;
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ReconciliationService;
        $this->branch = Branch::factory()->create(['name' => 'Reconciliation Test Branch']);
        $this->user = User::factory()->create(['role' => User::ROLE_STAFF, 'branch_id' => $this->branch->id]);
        $this->product = Product::create(['name' => 'Test Product', 'price' => 100.00]);
        $this->receiptDefaults = ['branch_id' => $this->branch->id, 'image_path' => 'test.jpg', 'reconciliation_status' => 'pending'];
        $this->transactionDefaults = ['branch_id' => $this->branch->id, 'product_id' => $this->product->id, 'user_id' => $this->user->id, 'quantity' => 1, 'client_uuid' => 'test-uuid'];
    }

    public function test_unreadable_receipt_returns_unreadable_status(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => null,
        ]));

        $result = $this->service->reconcile($receipt);

        $this->assertEquals('unreadable', $result->reconciliation_status);
        $this->assertDatabaseHas('receipts', [
            'id'                    => $receipt->id,
            'reconciliation_status' => 'unreadable',
        ]);
    }

    public function test_matched_receipt_links_to_transaction(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 100.00,
        ]));

        // Create a matching transaction (same branch, same amount, recent)
        $transaction = Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $this->branch->id,
            'total_amount' => 100.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
            'created_at'   => now()->subHour(),
        ]));

        $result = $this->service->reconcile($receipt);

        $this->assertEquals('matched', $result->reconciliation_status);
        $this->assertEquals($transaction->id, $result->matched_transaction_id);

        $this->assertDatabaseHas('receipts', [
            'id'                      => $receipt->id,
            'matched_transaction_id'  => $transaction->id,
            'reconciliation_status'   => 'matched',
        ]);
    }

    public function test_matched_receipt_within_one_peso_tolerance(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 100.50,
        ]));

        // 0.50 difference — within 1 peso tolerance
        Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $this->branch->id,
            'total_amount' => 100.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
            'created_at'   => now()->subHour(),
        ]));

        $result = $this->service->reconcile($receipt);

        $this->assertEquals('matched', $result->reconciliation_status);
    }

    public function test_outside_tolerance_creates_mismatch(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 200.00,
        ]));

        // Create a transaction far off in amount — outside 1 peso tolerance
        Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $this->branch->id,
            'total_amount' => 100.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
            'created_at'   => now()->subHour(),
        ]));

        $result = $this->service->reconcile($receipt);

        $this->assertEquals('mismatched', $result->reconciliation_status);
        $this->assertNull($result->matched_transaction_id);
    }

    public function test_mismatched_receipt_creates_discrepancy_alert(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 200.00,
        ]));

        $this->service->reconcile($receipt);

        $this->assertDatabaseHas('discrepancy_alerts', [
            'branch_id'      => $this->branch->id,
            'type'           => 'stock_mismatch',
            'severity'       => 'high',
            'expected_value' => 200.00,
            'actual_value'   => 0,
            'variance'       => 200.00,
            'status'         => 'pending',
        ]);
    }

    public function test_mismatch_alert_contains_receipt_details(): void
    {
        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 85.50,
        ]));

        $this->service->reconcile($receipt);

        $this->assertDatabaseHas('discrepancy_alerts', [
            'branch_id' => $this->branch->id,
            'variance'  => 85.50,
        ]);

        // Verify the details message mentions the amount
        $alert = \App\Models\DiscrepancyAlert::where('branch_id', $this->branch->id)->first();
        $this->assertStringContainsString('85.50', $alert->details);
        $this->assertStringContainsString('no matching POS transaction', $alert->details);
    }

    public function test_multiple_receipts_each_tracked_independently(): void
    {
        // Two receipts, same branch, same amount — each should have its own reconciliation result
        $receipt1 = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 150.00,
        ]));

        $receipt2 = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 150.00,
        ]));

        // One matching transaction
        Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $this->branch->id,
            'total_amount' => 150.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
        ]));

        // First receipt should match
        $result1 = $this->service->reconcile($receipt1);
        $this->assertEquals('matched', $result1->reconciliation_status);

        // Second receipt should mismatch (no more available transactions)
        $result2 = $this->service->reconcile($receipt2);
        $this->assertEquals('mismatched', $result2->reconciliation_status);
    }

    public function test_receipt_does_not_match_already_linked_transaction(): void
    {
        $transaction = Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $this->branch->id,
            'total_amount' => 100.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
            'created_at'   => now()->subHour(),
        ]));

        // First receipt matches and claims the transaction
        $receipt1 = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 100.00,
        ]));
        $this->service->reconcile($receipt1);

        // Second receipt with same amount should not match — transaction already taken
        $receipt2 = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 100.00,
        ]));
        $result = $this->service->reconcile($receipt2);

        $this->assertEquals('mismatched', $result->reconciliation_status);
    }

    public function test_receipt_only_matches_same_branch(): void
    {
        $otherBranch = Branch::factory()->create(['name' => 'Other Branch']);

        $receipt = Receipt::create(array_merge($this->receiptDefaults, [
            'parsed_total_amount' => 100.00,
        ]));

        // Transaction in a different branch — should not match
        Transaction::create(array_merge($this->transactionDefaults, [
            'branch_id'    => $otherBranch->id,
            'total_amount' => 100.00,
            'product_id'   => $this->product->id,
            'user_id'      => $this->user->id,
            'created_at'   => now()->subHour(),
        ]));

        $result = $this->service->reconcile($receipt);

        $this->assertEquals('mismatched', $result->reconciliation_status);
    }
}
