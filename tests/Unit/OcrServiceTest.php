<?php

namespace Tests\Unit;

use App\Services\OcrService;
use PHPUnit\Framework\TestCase;

class OcrServiceTest extends TestCase
{
    private OcrService $ocr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ocr = new OcrService;
    }

    // ── parseTotalAmount ─────────────────────────────────────────────

    public function test_parse_total_amount_standard_total(): void
    {
        $amount = $this->ocr->parseTotalAmount('TOTAL: 130.00');
        $this->assertSame(130.00, $amount);
    }

    public function test_parse_total_amount_lowercase_total(): void
    {
        $amount = $this->ocr->parseTotalAmount('total: 75.50');
        $this->assertSame(75.50, $amount);
    }

    public function test_parse_total_amount_amount_due(): void
    {
        $amount = $this->ocr->parseTotalAmount('AMOUNT DUE: 65.00');
        $this->assertSame(65.00, $amount);
    }

    public function test_parse_total_amount_grand_total(): void
    {
        $amount = $this->ocr->parseTotalAmount('Grand Total ₱120.75');
        $this->assertSame(120.75, $amount);
    }

    public function test_parse_total_amount_subtotal(): void
    {
        $amount = $this->ocr->parseTotalAmount('Subtotal: 240.00');
        $this->assertSame(240.00, $amount);
    }

    public function test_parse_total_amount_with_peso_sign(): void
    {
        $amount = $this->ocr->parseTotalAmount('₱1,500.50');
        $this->assertSame(1500.50, $amount);
    }

    public function test_parse_total_amount_with_php_prefix(): void
    {
        $amount = $this->ocr->parseTotalAmount('PHP 99.99');
        $this->assertSame(99.99, $amount);
    }

    public function test_parse_total_amount_with_p_prefix(): void
    {
        $amount = $this->ocr->parseTotalAmount('Total: P45.00');
        $this->assertSame(45.00, $amount);
    }

    public function test_parse_total_amount_number_before_label(): void
    {
        $amount = $this->ocr->parseTotalAmount('350.00 total');
        $this->assertSame(350.00, $amount);
    }

    public function test_parse_total_amount_number_before_due(): void
    {
        $amount = $this->ocr->parseTotalAmount('1200.00 due');
        $this->assertSame(1200.00, $amount);
    }

    public function test_parse_total_amount_with_thousands_separator(): void
    {
        $amount = $this->ocr->parseTotalAmount('TOTAL: 1,250.00');
        $this->assertSame(1250.00, $amount);
    }

    public function test_parse_total_amount_no_match_returns_null(): void
    {
        $amount = $this->ocr->parseTotalAmount('Just some random text without any numbers');
        $this->assertNull($amount);
    }

    public function test_parse_total_amount_empty_string_returns_null(): void
    {
        $amount = $this->ocr->parseTotalAmount('');
        $this->assertNull($amount);
    }

    public function test_parse_total_amount_receipt_block(): void
    {
        $receiptText = "Store: Coffee Shop\nDate: 2025-03-15\nItem: Milk Tea x2\nTOTAL: 130.00\nCash: 200.00\nChange: 70.00";
        $amount = $this->ocr->parseTotalAmount($receiptText);
        $this->assertSame(130.00, $amount);
    }

    // ── parseDate ────────────────────────────────────────────────────

    public function test_parse_date_mm_dd_yyyy_with_slashes(): void
    {
        $date = $this->ocr->parseDate('Date: 03/15/2025');
        $this->assertSame('03/15/2025', $date);
    }

    public function test_parse_date_mm_dd_yyyy_with_dashes(): void
    {
        $date = $this->ocr->parseDate('03-15-2025');
        $this->assertSame('03-15-2025', $date);
    }

    public function test_parse_date_yyyy_mm_dd_with_slashes(): void
    {
        $date = $this->ocr->parseDate('2025/03/15');
        $this->assertSame('2025/03/15', $date);
    }

    public function test_parse_date_yyyy_mm_dd_with_dashes(): void
    {
        $date = $this->ocr->parseDate('Issued: 2025-03-15');
        $this->assertSame('2025-03-15', $date);
    }

    public function test_parse_date_full_month_name(): void
    {
        $date = $this->ocr->parseDate('March 15, 2025');
        $this->assertStringContainsString('March', $date);
        $this->assertStringContainsString('2025', $date);
    }

    public function test_parse_date_abbreviated_month(): void
    {
        $date = $this->ocr->parseDate('Jan 5, 2025');
        $this->assertStringContainsString('Jan', $date);
    }

    public function test_parse_date_from_receipt(): void
    {
        $receiptText = "Store: Coffee Shop\nDate: Dec 25, 2025\nItem: Latte\nTotal: 180.00";
        $date = $this->ocr->parseDate($receiptText);
        $this->assertStringContainsString('Dec', $date);
        $this->assertStringContainsString('2025', $date);
    }

    public function test_parse_date_no_match_returns_null(): void
    {
        $date = $this->ocr->parseDate('No date here, just text');
        $this->assertNull($date);
    }

    public function test_parse_date_empty_string_returns_null(): void
    {
        $date = $this->ocr->parseDate('');
        $this->assertNull($date);
    }
}
