<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Peerapatfc\ThaiTaxInvoice\Model\ResourceModel\Invoice as InvoiceResource;

class InvoiceFinder
{
    /**
     * @param InvoiceResource $resource
     * @param InvoiceFactory $invoiceFactory
     */
    public function __construct(
        private readonly InvoiceResource $resource,
        private readonly InvoiceFactory $invoiceFactory
    ) {
    }

    /**
     * The tax invoice that was issued for an order, or null when there is none.
     *
     * @param int $orderId
     * @return Invoice|null
     */
    public function byOrderId(int $orderId): ?Invoice
    {
        $row = $this->resource->fetchByOrderId($orderId);
        if (!$row) {
            return null;
        }

        return $this->invoiceFactory->create()->setData($row)->setOrigData();
    }
}
