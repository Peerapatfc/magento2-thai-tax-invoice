<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use DateTimeInterface;

/**
 * Turns a prefix pattern and a running number into a tax invoice number.
 *
 * {Y} is the year as 2026 and {y} as 26 (Gregorian). The running number restarts at 1 for every different prefix,
 * so a pattern with the year in it starts again each January.
 */
class NumberFormat
{
    /**
     * Resolves the year tokens in the pattern.
     *
     * @param string $pattern
     * @param DateTimeInterface $date
     * @return string
     */
    public function prefix(string $pattern, DateTimeInterface $date): string
    {
        return strtr($pattern, ['{Y}' => $date->format('Y'), '{y}' => $date->format('y')]);
    }

    /**
     * The number as it is printed: prefix, then the running number padded with zeros.
     *
     * @param string $prefix
     * @param int $number
     * @param int $padding
     * @return string
     */
    public function format(string $prefix, int $number, int $padding): string
    {
        return $prefix . str_pad((string)$number, $padding, '0', STR_PAD_LEFT);
    }
}
