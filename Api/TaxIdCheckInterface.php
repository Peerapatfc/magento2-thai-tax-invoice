<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Api;

/**
 * Tells whether a Thai tax ID is well formed (13 digits and a correct check digit).
 *
 * @api
 */
interface TaxIdCheckInterface
{
    /**
     * Checks the format and check digit only. It does not say whether the number is registered.
     *
     * @param string $taxId
     * @return bool
     */
    public function isValid(string $taxId): bool;
}
