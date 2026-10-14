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
     * The request as type, name, tax ID and branch, or null when the buyer did not ask for a tax invoice.
     *
     * An order saved before the type existed has a tax ID and no type: it was for a company.
     *
     * @return array|null
     */
    public function getRequest(): ?array
    {
        $order = $this->registry->registry('current_order');
        if (!$order instanceof OrderInterface || !$order->getData('thai_tax_invoice_name')) {
            return null;
        }

        return [
            'type' => (string)$order->getData('thai_tax_invoice_type') ?: 'company',
            'name' => (string)$order->getData('thai_tax_invoice_name'),
            'tax_id' => (string)$order->getData('thai_tax_invoice_tax_id'),
            'branch' => (string)$order->getData('thai_tax_invoice_branch'),
        ];
    }
}
