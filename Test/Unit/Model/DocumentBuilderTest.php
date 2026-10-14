<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Peerapatfc\ThaiTaxInvoice\Model\Config;
use Peerapatfc\ThaiTaxInvoice\Model\DocumentBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DocumentBuilderTest extends TestCase
{
    private Config|MockObject $config;
    private DocumentBuilder $builder;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->config->method('getSeller')->willReturn(
            ['name' => 'Seller Co.', 'tax_id' => '0105556012341', 'branch' => '00000', 'address' => '1 Road']
        );
        $this->builder = new DocumentBuilder($this->config);
    }

    private function item(string $name, float $qty, float $price, float $rowTotal, float $discount, float $taxPercent): Item
    {
        $item = $this->createMock(Item::class);
        $item->method('getName')->willReturn($name);
        $item->method('getSku')->willReturn('sku-' . $name);
        $item->method('getQtyOrdered')->willReturn($qty);
        $item->method('getPrice')->willReturn($price);
        $item->method('getRowTotal')->willReturn($rowTotal);
        $item->method('getDiscountAmount')->willReturn($discount);
        $item->method('getTaxPercent')->willReturn($taxPercent);

        return $item;
    }

    private function order(array $data, array $items, ?Address $billing = null): Order|MockObject
    {
        $order = $this->createMock(Order::class);
        $order->method('getAllVisibleItems')->willReturn($items);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getIncrementId')->willReturn('000000042');
        $order->method('getOrderCurrencyCode')->willReturn('THB');
        $order->method('getShippingAmount')->willReturn($data['shipping'] ?? 0.0);
        $order->method('getShippingDiscountAmount')->willReturn(0.0);
        $order->method('getShippingDescription')->willReturn('Flat Rate');
        $order->method('getTaxAmount')->willReturn($data['tax'] ?? 0.0);
        $order->method('getGrandTotal')->willReturn($data['total'] ?? 0.0);
        $order->method('getBillingAddress')->willReturn($billing);
        $order->method('getData')->willReturnCallback(static fn ($key) => $data['request'][$key] ?? null);

        return $order;
    }

    public function testLinesVatAndTotalsComeFromTheOrder(): void
    {
        $order = $this->order(
            ['shipping' => 15.0, 'tax' => 61.95, 'total' => 946.95, 'request' => [
                'thai_tax_invoice_name' => 'Buyer Co.',
                'thai_tax_invoice_tax_id' => '1101700230678',
                'thai_tax_invoice_branch' => '00004',
                'thai_tax_invoice_vat_registered' => '1',
                'thai_tax_invoice_address' => '99 Silom Rd',
            ]],
            [$this->item('Mug', 3.0, 290.0, 870.0, 0.0, 7.0)]
        );

        $doc = $this->builder->build($order);

        $this->assertSame(885.0, $doc['before_vat']);
        $this->assertSame(61.95, $doc['vat']);
        $this->assertSame(946.95, $doc['total']);
        $this->assertSame(7.0, $doc['vat_rate']);
        $this->assertSame('THB', $doc['currency']);
        $this->assertSame('000000042', $doc['order_increment_id']);
        $this->assertCount(2, $doc['lines']);
        $this->assertSame(870.0, $doc['lines'][0]['amount']);
        $this->assertSame(15.0, $doc['lines'][1]['amount']);
        $this->assertSame('Seller Co.', $doc['seller']['name']);
        $this->assertTrue($doc['buyer']['vat_registered']);
        $this->assertSame('99 Silom Rd', $doc['buyer']['address']);
        $this->assertSame('00004', $doc['buyer']['branch']);
    }

    public function testALineIsShownAfterItsDiscount(): void
    {
        $order = $this->order(['total' => 90.0, 'request' => ['thai_tax_invoice_name' => 'B']], [
            $this->item('Mug', 1.0, 100.0, 100.0, 10.0, 0.0),
        ]);

        $this->assertSame(90.0, $this->builder->build($order)['lines'][0]['amount']);
    }

    public function testNoAddressTypedMeansTheBillingAddress(): void
    {
        $billing = $this->createMock(Address::class);
        $billing->method('getStreet')->willReturn(['99 Silom Rd', ' ', 'Si Lom']);
        $billing->method('getCity')->willReturn('Bang Rak');
        $billing->method('getRegion')->willReturn('Bangkok');
        $billing->method('getPostcode')->willReturn('10500');
        $order = $this->order(['request' => ['thai_tax_invoice_name' => 'B']], [], $billing);

        $this->assertSame(
            "99 Silom Rd Si Lom\nBang Rak Bangkok 10500",
            $this->builder->build($order)['buyer']['address']
        );
    }

    public function testAnOrderFromBeforeTheVatFlagCountsAsRegisteredWhenItHasATaxId(): void
    {
        $withId = $this->order(['request' => ['thai_tax_invoice_name' => 'B', 'thai_tax_invoice_tax_id' => '1101700230678']], []);
        $withoutId = $this->order(['request' => ['thai_tax_invoice_name' => 'B']], []);

        $this->assertTrue($this->builder->build($withId)['buyer']['vat_registered']);
        $this->assertFalse($this->builder->build($withoutId)['buyer']['vat_registered']);
        $this->assertSame('company', $this->builder->build($withoutId)['buyer']['type']);
    }

    public function testAnOrderWithoutTaxHasNoRate(): void
    {
        $order = $this->order(['total' => 100.0, 'request' => ['thai_tax_invoice_name' => 'B']], [
            $this->item('Mug', 1.0, 100.0, 100.0, 0.0, 0.0),
        ]);

        $doc = $this->builder->build($order);

        $this->assertNull($doc['vat_rate']);
        $this->assertSame(100.0, $doc['before_vat']);
    }
}
