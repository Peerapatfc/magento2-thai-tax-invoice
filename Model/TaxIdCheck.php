<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Peerapatfc\ThaiTaxInvoice\Api\TaxIdCheckInterface;

class TaxIdCheck implements TaxIdCheckInterface
{
    /**
     * @param TaxIdentity $identity
     */
    public function __construct(private readonly TaxIdentity $identity)
    {
    }

    /**
     * @inheritdoc
     */
    public function isValid(string $taxId): bool
    {
        return $this->identity->isValidTaxId($taxId);
    }
}
