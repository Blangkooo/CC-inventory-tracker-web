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

    // Looks for patterns like "TOTAL: 130.00", "Total P130.00", "AMOUNT DUE: 65.00"
    public function parseTotalAmount(string $ocrText): ?float
    {
        $patterns = [
            '/(?:total|amount due|grand total|subtotal)[^\d]*(\d+[\.,]\d{2})/i',
            '/(?:₱|php|p)\s*(\d+[\.,]\d{2})/i',
            '/(\d+[\.,]\d{2})\s*(?:total|due)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ocrText, $matches)) {
                return (float) str_replace(',', '', $matches[1]);
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
