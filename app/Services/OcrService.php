<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extractText(string $imagePath): string
    {
        try {
            return (new TesseractOCR($imagePath))->run();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Looks for patterns like "TOTAL: 130.00", "Total P1,250.00", "AMOUNT DUE: 65.00"
     * Supports thousands-separated amounts (e.g. 1,250.00, PHP 1,500.50).
     */
    public function parseTotalAmount(string $ocrText): ?float
    {
        // Capture group:  1+ digits, optionally (separator + 1+ digits), then dot + exactly 2 digits
        // Handles: 130.00, 1,250.00, 12,345.67, 500.50
        $numberPattern = '\d+(?:[.,]\d+)*\.\d{2}';

        $patterns = [
            '/'.'(?:total|amount due|grand total|subtotal)'.'[^\d]*'.'('.$numberPattern.')'.'/i',
            '/'.'(?:₱|php|p)'.'\s*'.'('.$numberPattern.')'.'/i',
            '/'.'('.$numberPattern.')'.'\s*(?:total|due)'.'/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ocrText, $matches)) {
                // Strip any thousands separators (commas) before casting
                $cleaned = str_replace(',', '', $matches[1]);
                return (float) $cleaned;
            }
        }

        return null;
    }

    public function parseDate(string $ocrText): ?string
    {
        $patterns = [
            '/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/',
            '/(\d{4}[\/\-]\d{2}[\/\-]\d{2})/',
            '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\w*\s+\d{1,2},?\s+\d{4}/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ocrText, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }
}
