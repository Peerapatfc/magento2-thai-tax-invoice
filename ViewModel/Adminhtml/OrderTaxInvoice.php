<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\ViewModel\Adminhtml;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * The tax invoice request of the order that is open in the admin.
 */
class OrderTaxInvoice implements ArgumentInterface
{
    /**
     * @param Registry $registry
     */
    public function __construct(private readonly Registry $registry)
    {
    }

    /**
     * The request as name, tax ID and branch, or null when the buyer did not ask for a tax invoice.
     *
     * @return array|null
     */
    public function getRequest(): ?array
    {
        $order = $this->registry->registry('current_order');
        if (!$order instanceof OrderInterface || !$order->getData('thai_tax_invoice_tax_id')) {
            return null;
        }

        return [
            'name' => (string)$order->getData('thai_tax_invoice_name'),
            'tax_id' => (string)$order->getData('thai_tax_invoice_tax_id'),
            'branch' => (string)$order->getData('thai_tax_invoice_branch'),
        ];
    }
}
