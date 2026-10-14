<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\Model\AbstractModel;
use Peerapatfc\ThaiTaxInvoice\Model\ResourceModel\Invoice as InvoiceResource;

/**
 * A tax invoice that was issued. What it says is the snapshot taken at that moment: it does not follow later
 * changes to the order or to the seller's settings.
 *
 * @method int getOrderId()
 * @method string getInvoiceNumber()
 * @method string getIssuedAt()
 */
class Invoice extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(InvoiceResource::class);
    }

    /**
     * What the invoice said when it was issued.
     *
     * @return array
     */
    public function getDocument(): array
    {
        $document = json_decode((string)$this->getData('snapshot'), true);

        return is_array($document) ? $document : [];
    }
}
