<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\ViewModel;

use Peerapatfc\ThaiTaxInvoice\ViewModel\InvoiceDocument;
use PHPUnit\Framework\TestCase;

class InvoiceDocumentTest extends TestCase
{
    private InvoiceDocument $document;

    protected function setUp(): void
    {
        $this->document = new InvoiceDocument();
    }

    public function testDatesUseTheBuddhistYear(): void
    {
        $this->assertSame('14 ตุลาคม 2569', $this->document->thaiDate('2026-10-14'));
        $this->assertSame('1 มกราคม 2570', $this->document->thaiDate('2027-01-01'));
    }

    public function testADateItCannotReadIsLeftAlone(): void
    {
        $this->assertSame('not a date', $this->document->thaiDate('not a date'));
    }

    public function testMoneyHasTwoDecimalsAndThousandsSeparators(): void
    {
        $this->assertSame('1,234.50', $this->document->money(1234.5));
        $this->assertSame('0.00', $this->document->money('0'));
    }

    public function testQuantitiesLoseTrailingZeros(): void
    {
        $this->assertSame('3', $this->document->qty(3.0));
        $this->assertSame('2.5', $this->document->qty('2.5000'));
    }

    public function testBranchWording(): void
    {
        $this->assertStringStartsWith('สำนักงานใหญ่', $this->document->branchLabel('00000'));
        $this->assertStringStartsWith('สำนักงานใหญ่', $this->document->branchLabel(''));
        $this->assertStringStartsWith('สาขาที่ 00004', $this->document->branchLabel('00004'));
    }
}
