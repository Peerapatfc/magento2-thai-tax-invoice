<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Controller\Invoice;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceRenderer;

/**
 * A signed-in customer's own tax invoice, ready to print. Anyone else gets "not found".
 */
class PrintAction implements HttpGetActionInterface
{
    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceFinder $finder
     * @param InvoiceRenderer $renderer
     * @param RawFactory $rawFactory
     * @param ForwardFactory $forwardFactory
     * @param RedirectFactory $redirectFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly Session $customerSession,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceFinder $finder,
        private readonly InvoiceRenderer $renderer,
        private readonly RawFactory $rawFactory,
        private readonly ForwardFactory $forwardFactory,
        private readonly RedirectFactory $redirectFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->redirectFactory->create()->setPath('customer/account/login');
        }

        $orderId = (int)$this->request->getParam('order_id');
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return $this->notFound();
        }

        $invoice = (int)$order->getCustomerId() === (int)$this->customerSession->getCustomerId()
            ? $this->finder->byOrderId($orderId)
            : null;
        if (!$invoice) {
            return $this->notFound();
        }

        return $this->rawFactory->create()
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Cache-Control', 'private, no-store', true)
            ->setContents($this->renderer->render($invoice));
    }

    /**
     * The 404 page.
     *
     * @return ResultInterface
     */
    private function notFound(): ResultInterface
    {
        return $this->forwardFactory->create()->setController('index')->forward('noroute');
    }
}
