<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;

/**
 * The "Print Tax Invoice" link on the customer's order page, shown once the invoice has been issued.
 */
class OrderInvoiceLink implements ArgumentInterface
{
    /**
     * @param Registry $registry
     * @param InvoiceFinder $finder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly InvoiceFinder $finder,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Where to print the invoice, or null when none has been issued for the order on this page.
     *
     * @return string|null
     */
    public function getPrintUrl(): ?string
    {
        $order = $this->registry->registry('current_order');
        if (!$order instanceof OrderInterface || !$this->finder->byOrderId((int)$order->getEntityId())) {
            return null;
        }

        return $this->urlBuilder->getUrl('thai_tax_invoice/invoice/print', ['order_id' => $order->getEntityId()]);
    }
}
