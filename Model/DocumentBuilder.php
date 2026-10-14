<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Sales\Model\Order;
use Peerapatfc\ThaiTaxInvoice\Api\Data\RequestInterface;

/**
 * Collects what a tax invoice says (seller, buyer, lines, VAT) from an order, as a plain array.
 *
 * The array is stored when the invoice is issued and is what gets printed, so the invoice does not change if the
 * order or the seller's settings do.
 */
class DocumentBuilder
{
    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * Builds the document for an order that asked for a full tax invoice.
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order): array
    {
        $taxPercents = [];
        $lines = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $net = (float)$item->getRowTotal() - (float)$item->getDiscountAmount();
            $lines[] = [
                'description' => (string)$item->getName(),
                'sku' => (string)$item->getSku(),
                'qty' => (float)$item->getQtyOrdered(),
                'unit_price' => (float)$item->getPrice(),
                'amount' => round($net, 2),
            ];
            if ((float)$item->getTaxPercent() > 0) {
                $taxPercents[] = (float)$item->getTaxPercent();
            }
        }
        if ((float)$order->getShippingAmount() > 0) {
            $lines[] = [
                'description' => (string)($order->getShippingDescription() ?: 'Shipping'),
                'sku' => '',
                'qty' => 1.0,
                'unit_price' => (float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount(),
                'amount' => round((float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount(), 2),
            ];
        }

        $tax = round((float)$order->getTaxAmount(), 2);
        $total = round((float)$order->getGrandTotal(), 2);

        return [
            'seller' => $this->config->getSeller($order->getStoreId()),
            'buyer' => $this->buyer($order),
            'order_increment_id' => (string)$order->getIncrementId(),
            'currency' => (string)$order->getOrderCurrencyCode(),
            'lines' => $lines,
            'vat_rate' => $taxPercents ? max($taxPercents) : null,
            'before_vat' => round($total - $tax, 2),
            'vat' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Who the invoice is made out to, as the buyer gave it at checkout.
     *
     * @param Order $order
     * @return array
     */
    private function buyer(Order $order): array
    {
        $taxId = (string)$order->getData('thai_tax_invoice_tax_id');
        $vat = $order->getData('thai_tax_invoice_vat_registered');
        $address = trim((string)$order->getData('thai_tax_invoice_address'));

        return [
            'type' => (string)$order->getData('thai_tax_invoice_type') ?: RequestInterface::TYPE_COMPANY,
            'name' => (string)$order->getData('thai_tax_invoice_name'),
            // Orders from before the flag existed: registered when a tax ID was given.
            'vat_registered' => $vat === null ? $taxId !== '' : (bool)$vat,
            'tax_id' => $taxId,
            'branch' => (string)$order->getData('thai_tax_invoice_branch'),
            'address' => $address !== '' ? $address : $this->billingAddress($order),
        ];
    }

    /**
     * The billing address of the order on one or two lines.
     *
     * @param Order $order
     * @return string
     */
    private function billingAddress(Order $order): string
    {
        $address = $order->getBillingAddress();
        if (!$address) {
            return '';
        }

        $street = array_filter(array_map('trim', (array)$address->getStreet()));
        $area = array_filter([
            (string)$address->getCity(),
            (string)$address->getRegion(),
            (string)$address->getPostcode(),
        ]);

        return trim(implode(' ', $street) . "\n" . implode(' ', $area));
    }
}
