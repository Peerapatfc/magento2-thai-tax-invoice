<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

/**
 * Checks Thai tax identifiers: the 13-digit tax ID (or national ID) and the 5-digit branch code.
 *
 * The 13th digit of a tax ID is a check digit, so a mistyped number is caught without asking the Revenue Department.
 * This does not tell you the number is registered, only that it is well formed.
 */
class TaxIdentity
{
    public const HEAD_OFFICE = '00000';

    /**
     * Removes spaces and dashes and turns Thai digits into Arabic ones, so "1-1017-00230-67-8" and
     * "1101700230678" compare equal.
     *
     * @param string $value
     * @return string
     */
    public function normalize(string $value): string
    {
        $value = strtr($value, ['๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
            '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9']);

        return preg_replace('/[\s\-]/u', '', $value) ?? '';
    }

    /**
     * Whether the value is 13 digits with a correct check digit.
     *
     * @param string $taxId
     * @return bool
     */
    public function isValidTaxId(string $taxId): bool
    {
        $taxId = $this->normalize($taxId);
        if (!preg_match('/^\d{13}$/', $taxId)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$taxId[$i] * (13 - $i);
        }

        return (11 - $sum % 11) % 10 === (int)$taxId[12];
    }

    /**
     * Whether the value is a 5-digit branch code (00000 is the head office).
     *
     * @param string $branch
     * @return bool
     */
    public function isValidBranch(string $branch): bool
    {
        return (bool)preg_match('/^\d{5}$/', $this->normalize($branch));
    }
}
