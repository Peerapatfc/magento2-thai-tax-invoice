<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Plugin\Adminhtml;

use Magento\Framework\AuthorizationInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceIssuer;

/**
 * Adds "Issue Tax Invoice" (or "Print Tax Invoice" once it exists) to the order page, for orders that asked for one.
 */
class AddInvoiceButton
{
    /**
     * @param InvoiceIssuer $issuer
     * @param InvoiceFinder $finder
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        private readonly InvoiceIssuer $issuer,
        private readonly InvoiceFinder $finder,
        private readonly AuthorizationInterface $authorization
    ) {
    }

    /**
     * Adds the button before the page is laid out.
     *
     * @param View $subject
     * @return void
     */
    public function beforeSetLayout(View $subject): void
    {
        $order = $subject->getOrder();
        if (!$order instanceof Order || !$order->getEntityId() || !$this->issuer->isRequested($order)) {
            return;
        }

        $orderId = (int)$order->getEntityId();
        if ($this->finder->byOrderId($orderId)) {
            $printUrl = $subject->getUrl('thai_tax_invoice/invoice/print', ['order_id' => $orderId]);
            $subject->addButton('peerapatfc_print_tax_invoice', [
                'label' => __('Print Tax Invoice'),
                'onclick' => "window.open('" . $printUrl . "', '_blank')",
            ]);

            return;
        }

        if ($this->authorization->isAllowed('Peerapatfc_ThaiTaxInvoice::issue') && !$order->isCanceled()) {
            $question = __('Issue the tax invoice for this order? It gets the next number and cannot be changed '
                . 'afterwards.');
            $issueUrl = $subject->getUrl('thai_tax_invoice/invoice/issue', ['order_id' => $orderId]);
            $subject->addButton('peerapatfc_issue_tax_invoice', [
                'label' => __('Issue Tax Invoice'),
                'class' => 'primary',
                'onclick' => "confirmSetLocation('" . $question . "', '" . $issueUrl . "')",
            ]);
        }
    }
}
