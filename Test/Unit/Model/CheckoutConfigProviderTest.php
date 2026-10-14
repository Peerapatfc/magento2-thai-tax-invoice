<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Model;

use Peerapatfc\ThaiTaxInvoice\Model\CheckoutConfigProvider;
use Peerapatfc\ThaiTaxInvoice\Model\Config;
use PHPUnit\Framework\TestCase;

class CheckoutConfigProviderTest extends TestCase
{
    /**
     * @dataProvider flags
     */
    public function testTellsTheCheckoutWhetherToOfferTheInvoice(bool $enabled): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isCheckoutEnabled')->willReturn($enabled);

        $this->assertSame(
            ['thaiTaxInvoice' => ['enabled' => $enabled]],
            (new CheckoutConfigProvider($config))->getConfig()
        );
    }

    public static function flags(): array
    {
        return ['on' => [true], 'off' => [false]];
    }
}
