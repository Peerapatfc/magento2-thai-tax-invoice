<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Api\Data;

/**
 * What a buyer gives when asking for a full tax invoice: who the invoice is made out to, their tax ID and branch.
 * The buyer's address on the invoice is the billing address of the order.
 *
 * @api
 */
interface RequestInterface
{
    public const NAME = 'name';
    public const TAX_ID = 'tax_id';
    public const BRANCH = 'branch';

    /**
     * Name the invoice is made out to (a person or a company).
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set the name the invoice is made out to.
     *
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self;

    /**
     * The buyer's 13-digit tax ID (or national ID).
     *
     * @return string|null
     */
    public function getTaxId(): ?string;

    /**
     * Set the buyer's tax ID.
     *
     * @param string|null $taxId
     * @return $this
     */
    public function setTaxId(?string $taxId): self;

    /**
     * The buyer's 5-digit branch code, 00000 for a head office.
     *
     * @return string|null
     */
    public function getBranch(): ?string;

    /**
     * Set the buyer's branch code.
     *
     * @param string|null $branch
     * @return $this
     */
    public function setBranch(?string $branch): self;
}
