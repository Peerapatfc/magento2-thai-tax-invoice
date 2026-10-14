<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Peerapatfc\ThaiTaxInvoice\Model\ResourceModel\Invoice as InvoiceResource;
use Throwable;

/**
 * Issues the tax invoice for an order that asked for one: checks it can be issued, takes the next number and keeps
 * the document. An order gets one invoice; to issue it twice is refused.
 */
class InvoiceIssuer
{
    /**
     * @param Config $config
     * @param TaxIdentity $identity
     * @param NumberFormat $numberFormat
     * @param DocumentBuilder $documentBuilder
     * @param InvoiceFinder $finder
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceResource $resource
     * @param TimezoneInterface $timezone
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly Config $config,
        private readonly TaxIdentity $identity,
        private readonly NumberFormat $numberFormat,
        private readonly DocumentBuilder $documentBuilder,
        private readonly InvoiceFinder $finder,
        private readonly InvoiceFactory $invoiceFactory,
        private readonly InvoiceResource $resource,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Whether the order asked for a full tax invoice at checkout.
     *
     * @param Order $order
     * @return bool
     */
    public function isRequested(Order $order): bool
    {
        return trim((string)$order->getData('thai_tax_invoice_name')) !== '';
    }

    /**
     * Issues the invoice.
     *
     * @param Order $order
     * @return Invoice
     * @throws LocalizedException When it cannot be issued, or the order already has an invoice
     */
    public function issue(Order $order): Invoice
    {
        $this->assertCanIssue($order);

        $now = $this->timezone->date(null, null, true, false);
        $document = $this->documentBuilder->build($order);
        $prefixPattern = $this->config->getNumberPrefix($order->getStoreId());
        $padding = $this->config->getNumberPadding($order->getStoreId());

        $this->resource->beginTransaction();
        try {
            $prefix = $this->numberFormat->prefix($prefixPattern, $now);
            $number = $this->numberFormat->format($prefix, $this->resource->nextNumber($prefix), $padding);
            $document['number'] = $number;
            $document['date'] = $now->format('Y-m-d');

            $invoice = $this->invoiceFactory->create();
            $invoice->setData([
                'order_id' => (int)$order->getEntityId(),
                'invoice_number' => $number,
                'snapshot' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
            $this->resource->save($invoice);
            $this->resource->commit();
        } catch (Throwable $e) {
            $this->resource->rollBack();
            // The unique index on the order is what stops two people issuing the same invoice at the same time.
            if ($this->finder->byOrderId((int)$order->getEntityId())) {
                throw new LocalizedException(__('A tax invoice has already been issued for this order.'), $e);
            }
            throw $e;
        }

        return $invoice;
    }

    /**
     * Refuses an order that cannot get a tax invoice, and says why.
     *
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    private function assertCanIssue(Order $order): void
    {
        if (!$this->isRequested($order)) {
            throw new LocalizedException(__('The buyer did not ask for a full tax invoice on this order.'));
        }
        if ($order->isCanceled()) {
            throw new LocalizedException(__('A canceled order cannot get a tax invoice.'));
        }
        if ($this->finder->byOrderId((int)$order->getEntityId())) {
            throw new LocalizedException(__('A tax invoice has already been issued for this order.'));
        }

        $seller = $this->config->getSeller($order->getStoreId());
        if ($seller['name'] === '' || $seller['address'] === '' || !$this->identity->isValidTaxId($seller['tax_id'])) {
            throw new LocalizedException(
                __('Fill in the seller (registered name, tax ID and address) under Stores > Configuration > '
                    . 'General > Thai Tax Invoice before issuing a tax invoice.')
            );
        }
    }
}
