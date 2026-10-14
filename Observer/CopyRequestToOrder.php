<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Peerapatfc\ThaiTaxInvoice\Model\RequestSaver;

/**
 * Copies the tax invoice request from the quote to the order.
 *
 * A quote-to-order fieldset is not enough: when the order is placed Magento merges the converted order through
 * OrderInterface, which keeps only the fields that interface declares and drops these columns.
 */
class CopyRequestToOrder implements ObserverInterface
{
    private const FIELDS = [
        RequestSaver::FIELD_NAME,
        RequestSaver::FIELD_TAX_ID,
        RequestSaver::FIELD_BRANCH,
    ];

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        foreach (self::FIELDS as $field) {
            $order->setData($field, $quote->getData($field));
        }
    }
}
