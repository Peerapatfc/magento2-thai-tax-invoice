<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceIssuer;
use Psr\Log\LoggerInterface;

/**
 * "Issue Tax Invoice" on the order. Like Magento's own order actions it is a link that carries the admin's secret key.
 */
class Issue extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Peerapatfc_ThaiTaxInvoice::issue';

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceIssuer $issuer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceIssuer $issuer,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        try {
            $invoice = $this->issuer->issue($this->orderRepository->get($orderId));
            $this->messageManager->addSuccessMessage(
                __('Tax invoice %1 was issued.', $invoice->getInvoiceNumber())
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('The tax invoice could not be issued. See the log.'));
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
