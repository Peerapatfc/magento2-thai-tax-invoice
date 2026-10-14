<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Peerapatfc\ThaiTaxInvoice\Observer\CopyRequestToOrder;
use PHPUnit\Framework\TestCase;

class CopyRequestToOrderTest extends TestCase
{
    private function dispatch(DataObject $quote, DataObject $order): void
    {
        $observer = new Observer(['event' => new Event(['quote' => $quote, 'order' => $order])]);
        (new CopyRequestToOrder())->execute($observer);
    }

    public function testTheRequestMovesFromTheQuoteToTheOrder(): void
    {
        $quote = new DataObject([
            'thai_tax_invoice_type' => 'company',
            'thai_tax_invoice_name' => 'Acme',
            'thai_tax_invoice_tax_id' => '1101700230678',
            'thai_tax_invoice_branch' => '00001',
        ]);
        $order = new DataObject();

        $this->dispatch($quote, $order);

        $this->assertSame('company', $order->getData('thai_tax_invoice_type'));
        $this->assertSame('Acme', $order->getData('thai_tax_invoice_name'));
        $this->assertSame('1101700230678', $order->getData('thai_tax_invoice_tax_id'));
        $this->assertSame('00001', $order->getData('thai_tax_invoice_branch'));
    }

    public function testAnOrderWithoutARequestStaysEmpty(): void
    {
        $order = new DataObject();

        $this->dispatch(new DataObject(), $order);

        $this->assertNull($order->getData('thai_tax_invoice_tax_id'));
    }
}
