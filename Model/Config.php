<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const PATH_CHECKOUT_ENABLED = 'thai_tax_invoice/checkout/enabled';
    private const PATH_SELLER = 'thai_tax_invoice/seller/';
    private const PATH_PREFIX = 'thai_tax_invoice/numbering/prefix';
    private const PATH_PADDING = 'thai_tax_invoice/numbering/padding';
    private const PATH_AUTO_ISSUE = 'thai_tax_invoice/numbering/auto_issue';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * Whether the checkout offers "I want a full tax invoice".
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isCheckoutEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_CHECKOUT_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * The seller's registered name, tax ID, branch and address.
     *
     * @param int|string|null $storeId
     * @return string[]
     */
    public function getSeller($storeId = null): array
    {
        $seller = [];
        foreach (['name', 'tax_id', 'branch', 'address'] as $field) {
            $seller[$field] = trim((string)$this->scopeConfig->getValue(
                self::PATH_SELLER . $field,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ));
        }

        return $seller;
    }

    /**
     * The number prefix, with {Y} and {y} for the year.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getNumberPrefix($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PATH_PREFIX, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * How many digits the running number has, with leading zeros.
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getNumberPadding($storeId = null): int
    {
        $padding = (int)$this->scopeConfig->getValue(self::PATH_PADDING, ScopeInterface::SCOPE_STORE, $storeId);

        return max(1, min(12, $padding));
    }

    /**
     * Whether the invoice is issued as soon as Magento invoices the order.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isAutoIssue($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_AUTO_ISSUE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
