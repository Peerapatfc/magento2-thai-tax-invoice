<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Model;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Peerapatfc\ThaiTaxInvoice\Model\Config;
use Peerapatfc\ThaiTaxInvoice\Model\Request;
use Peerapatfc\ThaiTaxInvoice\Model\RequestSaver;
use Peerapatfc\ThaiTaxInvoice\Model\TaxIdentity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestSaverTest extends TestCase
{
    private const GOOD_TAX_ID = '1101700230678';

    private CartRepositoryInterface|MockObject $cartRepository;
    private Config|MockObject $config;
    private RequestSaver $saver;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->config->method('isCheckoutEnabled')->willReturn(true);
        $this->saver = new RequestSaver(new TaxIdentity(), $this->config, $this->cartRepository);
    }

    private function request(?string $name, ?string $taxId, ?string $branch): Request
    {
        return (new Request())->setName($name)->setTaxId($taxId)->setBranch($branch);
    }

    /**
     * A quote that keeps its data in an array, so the test can read back what was set.
     *
     * @param array $data
     * @return Quote|MockObject
     */
    private function quote(array &$data): Quote|MockObject
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getStoreId')->willReturn(1);
        $quote->method('getData')->willReturnCallback(static fn ($key) => $data[$key] ?? null);
        $quote->method('setData')->willReturnCallback(
            static function ($key, $value) use (&$data, $quote) {
                $data[$key] = $value;

                return $quote;
            }
        );

        return $quote;
    }

    public function testSavesACleanRequestOnTheQuote(): void
    {
        $data = [];
        $quote = $this->quote($data);
        $this->cartRepository->expects($this->once())->method('save')->with($quote);

        $this->saver->save($quote, $this->request('  Acme Co., Ltd. ', '1-1017-00230-67-8', ' 00001 '));

        $this->assertSame('Acme Co., Ltd.', $data[RequestSaver::FIELD_NAME]);
        $this->assertSame(self::GOOD_TAX_ID, $data[RequestSaver::FIELD_TAX_ID]);
        $this->assertSame('00001', $data[RequestSaver::FIELD_BRANCH]);
    }

    public function testAnEmptyBranchMeansTheHeadOffice(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, ''));

        $this->assertSame('00000', $data[RequestSaver::FIELD_BRANCH]);
    }

    /**
     * @dataProvider badRequests
     */
    public function testRefusesAnIncompleteOrWrongRequest(?string $name, ?string $taxId, ?string $branch): void
    {
        $data = [];
        $this->cartRepository->expects($this->never())->method('save');

        $this->expectException(InputException::class);
        $this->saver->save($this->quote($data), $this->request($name, $taxId, $branch));
    }

    public static function badRequests(): array
    {
        return [
            'no name' => ['', self::GOOD_TAX_ID, '00000'],
            'wrong check digit' => ['Acme', '1101700230679', '00000'],
            'no tax ID' => ['Acme', '', '00000'],
            'bad branch' => ['Acme', self::GOOD_TAX_ID, '12'],
        ];
    }

    public function testNoRequestClearsWhatWasSavedBefore(): void
    {
        $data = [
            RequestSaver::FIELD_NAME => 'Acme',
            RequestSaver::FIELD_TAX_ID => self::GOOD_TAX_ID,
            RequestSaver::FIELD_BRANCH => '00000',
        ];
        $quote = $this->quote($data);
        $this->cartRepository->expects($this->once())->method('save')->with($quote);

        $this->saver->save($quote, null);

        $this->assertNull($data[RequestSaver::FIELD_TAX_ID]);
        $this->assertNull($data[RequestSaver::FIELD_NAME]);
    }

    public function testNothingToChangeMeansNothingIsSaved(): void
    {
        $data = [];
        $this->cartRepository->expects($this->never())->method('save');

        $this->saver->save($this->quote($data), null);
    }

    public function testTheSameRequestIsNotSavedTwice(): void
    {
        $data = [
            RequestSaver::FIELD_NAME => 'Acme',
            RequestSaver::FIELD_TAX_ID => self::GOOD_TAX_ID,
            RequestSaver::FIELD_BRANCH => '00000',
        ];
        $this->cartRepository->expects($this->never())->method('save');

        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, '00000'));
    }

    public function testIsIgnoredAndClearedWhenTheCheckoutOptionIsOff(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isCheckoutEnabled')->willReturn(false);
        $saver = new RequestSaver(new TaxIdentity(), $config, $this->cartRepository);
        $data = [];
        $quote = $this->quote($data);

        // A wrong request is not even looked at: the option is off.
        $saver->save($quote, $this->request('', 'nonsense', 'x'));

        $this->assertArrayNotHasKey(RequestSaver::FIELD_TAX_ID, $data);
    }
}
