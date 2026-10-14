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
    public const TYPE = 'type';
    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_COMPANY = 'company';
    public const VAT_REGISTERED = 'vat_registered';
    public const ADDRESS = 'address';
    public const NAME = 'name';
    public const TAX_ID = 'tax_id';
    public const BRANCH = 'branch';

    /**
     * Whether the invoice is for a person ("individual") or a juristic person ("company").
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set whether the invoice is for an individual or a company.
     *
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): self;

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
     * The buyer's 13-digit tax ID (an individual may give their national ID, or nothing).
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
     * The buyer's 5-digit branch code, 00000 for a head office. Only a company has one.
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

    /**
     * Whether the buyer is registered for VAT.
     *
     * Then the tax ID and the branch are required on the invoice. Left out, it is taken as true when a tax ID is
     * given.
     *
     * @return bool|null
     */
    public function getVatRegistered(): ?bool;

    /**
     * Set whether the buyer is registered for VAT.
     *
     * @param bool|null $vatRegistered
     * @return $this
     */
    public function setVatRegistered(?bool $vatRegistered): self;

    /**
     * The address for the invoice, as registered. Empty means the billing address of the order.
     *
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * Set the address for the invoice.
     *
     * @param string|null $address
     * @return $this
     */
    public function setAddress(?string $address): self;
}
