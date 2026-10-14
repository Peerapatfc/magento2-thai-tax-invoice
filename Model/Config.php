<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const PATH_CHECKOUT_ENABLED = 'thai_tax_invoice/checkout/enabled';

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
}
