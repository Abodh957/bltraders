<?php

namespace App\Support;

/**
 * Rupee amounts in words using the Indian numbering system
 * (thousand, lakh, crore), as printed on GST invoices.
 *
 *   AmountInWords::rupees(1800)     => "Rupees One Thousand Eight Hundred Only"
 *   AmountInWords::rupees(123456.5) => "Rupees One Lakh Twenty Three Thousand Four Hundred Fifty Six and Fifty Paise Only"
 */
class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
        'Eighteen', 'Nineteen'];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function rupees(float $amount): string
    {
        $amount = round(abs($amount), 2);
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $words = $rupees === 0 ? 'Zero' : self::number($rupees);
        $out   = 'Rupees ' . $words;

        if ($paise > 0) {
            $out .= ' and ' . self::number($paise) . ' Paise';
        }

        return $out . ' Only';
    }

    private static function number(int $n): string
    {
        $parts = [];

        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$div, $label]) {
            if ($n >= $div) {
                $chunk = intdiv($n, $div);
                // crores can exceed 99 — recurse so "120 crore" still reads right
                $parts[] = ($div === 10000000 ? self::number($chunk) : self::belowHundred($chunk)) . ' ' . $label;
                $n %= $div;
            }
        }

        if ($n > 0) {
            $parts[] = self::belowHundred($n);
        }

        return implode(' ', $parts);
    }

    private static function belowHundred(int $n): string
    {
        if ($n < 20) {
            return self::ONES[$n];
        }

        return trim(self::TENS[intdiv($n, 10)] . ' ' . self::ONES[$n % 10]);
    }
}
