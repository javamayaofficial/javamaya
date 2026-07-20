<?php

namespace App\Services\Invoice;

use App\Models\TaxSetting;

/**
 * Kalkulasi PPN. TIDAK PERNAH hardcode 11% — dibaca dari tax_settings.
 * Hasil di-snapshot ke order (tax_percent, tax_mode, tax_amount) agar
 * perubahan setting tidak mengubah order lama.
 */
class TaxCalculator
{
    /** @return array{percent: float, mode: string, tax_amount: int, total: int} */
    public function calculate(int $amountAfterDiscount): array
    {
        $tax = TaxSetting::current();
        $percent = (float) $tax->percentage;

        if ($percent <= 0) {
            return ['percent' => 0.0, 'mode' => $tax->mode, 'tax_amount' => 0, 'total' => $amountAfterDiscount];
        }

        if ($tax->mode === 'exclusive') {
            $taxAmount = (int) round($amountAfterDiscount * $percent / 100);
            return ['percent' => $percent, 'mode' => 'exclusive', 'tax_amount' => $taxAmount, 'total' => $amountAfterDiscount + $taxAmount];
        }

        // inclusive: total tidak berubah; PPN = porsi di dalam harga
        $taxAmount = (int) round($amountAfterDiscount - ($amountAfterDiscount / (1 + $percent / 100)));
        return ['percent' => $percent, 'mode' => 'inclusive', 'tax_amount' => $taxAmount, 'total' => $amountAfterDiscount];
    }
}
