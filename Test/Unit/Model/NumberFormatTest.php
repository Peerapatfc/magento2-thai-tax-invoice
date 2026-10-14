<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Model;

use DateTimeImmutable;
use Peerapatfc\ThaiTaxInvoice\Model\NumberFormat;
use PHPUnit\Framework\TestCase;

class NumberFormatTest extends TestCase
{
    private NumberFormat $format;

    protected function setUp(): void
    {
        $this->format = new NumberFormat();
    }

    public function testTheYearTokensAreResolved(): void
    {
        $date = new DateTimeImmutable('2026-10-14');

        $this->assertSame('INV2026-', $this->format->prefix('INV{Y}-', $date));
        $this->assertSame('TX26/', $this->format->prefix('TX{y}/', $date));
        $this->assertSame('INV-', $this->format->prefix('INV-', $date));
    }

    public function testANewYearGivesANewPrefixSoTheSeriesStartsAgain(): void
    {
        $this->assertNotSame(
            $this->format->prefix('INV{Y}-', new DateTimeImmutable('2026-12-31')),
            $this->format->prefix('INV{Y}-', new DateTimeImmutable('2027-01-01'))
        );
    }

    /**
     * @dataProvider numbers
     */
    public function testTheRunningNumberIsPaddedWithZeros(int $number, int $padding, string $expected): void
    {
        $this->assertSame($expected, $this->format->format('INV2026-', $number, $padding));
    }

    public static function numbers(): array
    {
        return [
            'first' => [1, 6, 'INV2026-000001'],
            'wider than the padding' => [1234567, 6, 'INV2026-1234567'],
            'short padding' => [7, 2, 'INV2026-07'],
            'no padding needed' => [12, 1, 'INV2026-12'],
        ];
    }
}
