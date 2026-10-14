<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\ViewModel\Adminhtml;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;

/**
 * The tax invoice request of the order that is open in the admin.
 */
class OrderTaxInvoice implements ArgumentInterface
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
     * The tax invoice that was issued for the open order: its number and the link to print it. Null when none yet.
     *
     * @return array|null
     */
    public function getIssuedInvoice(): ?array
    {
        $order = $this->registry->registry('current_order');
        $invoice = $order instanceof OrderInterface ? $this->finder->byOrderId((int)$order->getEntityId()) : null;

        return $invoice ? [
            'number' => $invoice->getInvoiceNumber(),
            'issued_at' => $invoice->getIssuedAt(),
            'print_url' => $this->urlBuilder->getUrl(
                'thai_tax_invoice/invoice/print',
                ['order_id' => $invoice->getOrderId()]
            ),
        ] : null;
    }

    /**
     * Whether the open order carries no VAT, which is unusual for a VAT-registered seller's tax invoice.
     *
     * @return bool
     */
    public function hasNoVat(): bool
    {
        $order = $this->registry->registry('current_order');

        return $order instanceof OrderInterface && (float)$order->getTaxAmount() <= 0;
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

        $taxId = (string)$order->getData('thai_tax_invoice_tax_id');
        $vat = $order->getData('thai_tax_invoice_vat_registered');

        return [
            'type' => (string)$order->getData('thai_tax_invoice_type') ?: 'company',
            // Orders from before the flag existed: registered when a tax ID was given.
            'vat_registered' => $vat === null ? $taxId !== '' : (bool)$vat,
            'address' => (string)$order->getData('thai_tax_invoice_address'),
            'name' => (string)$order->getData('thai_tax_invoice_name'),
            'tax_id' => $taxId,
            'branch' => (string)$order->getData('thai_tax_invoice_branch'),
        ];
    }
}
