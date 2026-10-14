<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceRenderer;

/**
 * Shows the issued tax invoice of an order, ready to print.
 */
class PrintAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @param Context $context
     * @param InvoiceFinder $finder
     * @param InvoiceRenderer $renderer
     * @param RawFactory $rawFactory
     */
    public function __construct(
        Context $context,
        private readonly InvoiceFinder $finder,
        private readonly InvoiceRenderer $renderer,
        private readonly RawFactory $rawFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $invoice = $this->finder->byOrderId($orderId);
        if (!$invoice) {
            $this->messageManager->addErrorMessage(__('No tax invoice has been issued for this order.'));

            return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
        }

        return $this->rawFactory->create()
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setContents($this->renderer->render($invoice));
    }
}
