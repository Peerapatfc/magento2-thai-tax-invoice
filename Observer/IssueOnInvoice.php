<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Peerapatfc\ThaiTaxInvoice\Model\Config;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceFinder;
use Peerapatfc\ThaiTaxInvoice\Model\InvoiceIssuer;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * With "Issue when the order is invoiced" on, issues the tax invoice when Magento invoices an order that asked for
 * one. A problem here is logged and never stops the order from being invoiced: the invoice can be issued by hand.
 */
class IssueOnInvoice implements ObserverInterface
{
    /**
     * @param Config $config
     * @param InvoiceIssuer $issuer
     * @param InvoiceFinder $finder
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly InvoiceIssuer $issuer,
        private readonly InvoiceFinder $finder,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        if (!$this->config->isAutoIssue($order->getStoreId())
            || !$this->issuer->isRequested($order)
            || $this->finder->byOrderId((int)$order->getEntityId())
        ) {
            return;
        }

        try {
            $this->issuer->issue($order);
        } catch (LocalizedException $e) {
            $this->logger->warning('Thai tax invoice not issued for order ' . $order->getIncrementId() . ': '
                . $e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error($e);
        }
    }
}
