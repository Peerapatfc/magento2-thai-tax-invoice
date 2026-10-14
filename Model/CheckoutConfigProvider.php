<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Tells the checkout whether to offer the tax invoice request.
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return ['thaiTaxInvoice' => ['enabled' => $this->config->isCheckoutEnabled()]];
    }
}
