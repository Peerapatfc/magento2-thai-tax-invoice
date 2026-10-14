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

    private function request(
        ?string $name,
        ?string $taxId = '',
        ?string $branch = '',
        ?string $type = null,
        ?bool $vat = null,
        ?string $address = null
    ): Request {
        return (new Request())->setType($type)->setVatRegistered($vat)->setName($name)->setTaxId($taxId)
            ->setBranch($branch)->setAddress($address);
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

        $this->saver->save(
            $quote,
            $this->request('  Acme Co., Ltd. ', '1-1017-00230-67-8', ' 00001 ', 'company', true, "  99  Silom Rd\nBangkok ")
        );

        $this->assertSame('company', $data[RequestSaver::FIELD_TYPE]);
        $this->assertSame(1, $data[RequestSaver::FIELD_VAT_REGISTERED]);
        $this->assertSame('Acme Co., Ltd.', $data[RequestSaver::FIELD_NAME]);
        $this->assertSame(self::GOOD_TAX_ID, $data[RequestSaver::FIELD_TAX_ID]);
        $this->assertSame('00001', $data[RequestSaver::FIELD_BRANCH]);
        $this->assertSame("99 Silom Rd\nBangkok", $data[RequestSaver::FIELD_ADDRESS]);
    }

    public function testNoTypeMeansACompany(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, '00001'));

        $this->assertSame('company', $data[RequestSaver::FIELD_TYPE]);
    }

    public function testNoVatFlagMeansRegisteredWhenATaxIdIsGiven(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, ''));

        $this->assertSame(1, $data[RequestSaver::FIELD_VAT_REGISTERED]);
        $this->assertSame('00000', $data[RequestSaver::FIELD_BRANCH]);
    }

    public function testAnIndividualRegisteredForVatNeedsATaxIdAndHasABranch(): void
    {
        $data = [];
        $this->saver->save(
            $this->quote($data),
            $this->request('สมชาย ใจดี', self::GOOD_TAX_ID, '', 'individual', true)
        );

        $this->assertSame('individual', $data[RequestSaver::FIELD_TYPE]);
        $this->assertSame(1, $data[RequestSaver::FIELD_VAT_REGISTERED]);
        $this->assertSame('00000', $data[RequestSaver::FIELD_BRANCH]);
    }

    public function testBuyersRegisteredForVatMustGiveATaxId(): void
    {
        $data = [];
        $this->cartRepository->expects($this->never())->method('save');

        $this->expectException(InputException::class);
        $this->saver->save($this->quote($data), $this->request('สมชาย', '', '', 'individual', true));
    }

    public function testAnIndividualNotRegisteredNeedsOnlyAName(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('สมชาย ใจดี', '', '', 'individual', false));

        $this->assertSame(0, $data[RequestSaver::FIELD_VAT_REGISTERED]);
        $this->assertSame('สมชาย ใจดี', $data[RequestSaver::FIELD_NAME]);
        $this->assertNull($data[RequestSaver::FIELD_TAX_ID] ?? null);
        $this->assertNull($data[RequestSaver::FIELD_BRANCH] ?? null);
    }

    public function testACompanyNotRegisteredForVatNeedsNoTaxId(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', '', '', 'company', false));

        $this->assertSame(0, $data[RequestSaver::FIELD_VAT_REGISTERED]);
        $this->assertNull($data[RequestSaver::FIELD_TAX_ID] ?? null);
    }

    public function testABuyerNotRegisteredHasNoBranchEvenIfOneIsSent(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, '00005', 'company', false));

        $this->assertSame(self::GOOD_TAX_ID, $data[RequestSaver::FIELD_TAX_ID]);
        $this->assertNull($data[RequestSaver::FIELD_BRANCH] ?? null);
    }

    public function testATaxIdIsCheckedWheneverItIsGiven(): void
    {
        $data = [];
        $this->expectException(InputException::class);

        $this->saver->save($this->quote($data), $this->request('สมชาย', '1101700230679', '', 'individual', false));
    }

    public function testNoAddressMeansTheBillingAddress(): void
    {
        $data = [];
        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, '', 'company', true, '  '));

        $this->assertNull($data[RequestSaver::FIELD_ADDRESS] ?? null);
    }

    public function testRefusesAnAddressThatIsTooLong(): void
    {
        $data = [];
        $this->expectException(InputException::class);

        $this->saver->save(
            $this->quote($data),
            $this->request('Acme', self::GOOD_TAX_ID, '', 'company', true, str_repeat('ก', 501))
        );
    }

    /**
     * @dataProvider badRequests
     */
    public function testRefusesAnIncompleteOrWrongRequest(
        ?string $name,
        ?string $taxId,
        ?string $branch,
        ?string $type
    ): void {
        $data = [];
        $this->cartRepository->expects($this->never())->method('save');

        $this->expectException(InputException::class);
        $this->saver->save($this->quote($data), $this->request($name, $taxId, $branch, $type, true));
    }

    public static function badRequests(): array
    {
        return [
            'no name' => ['', self::GOOD_TAX_ID, '00000', 'company'],
            'wrong check digit' => ['Acme', '1101700230679', '00000', 'company'],
            'no tax ID' => ['Acme', '', '00000', 'company'],
            'bad branch' => ['Acme', self::GOOD_TAX_ID, '12', 'company'],
            'unknown type' => ['Acme', self::GOOD_TAX_ID, '00000', 'partnership'],
        ];
    }

    public function testNoRequestClearsWhatWasSavedBefore(): void
    {
        $data = [
            RequestSaver::FIELD_TYPE => 'company',
            RequestSaver::FIELD_VAT_REGISTERED => '1',
            RequestSaver::FIELD_NAME => 'Acme',
            RequestSaver::FIELD_TAX_ID => self::GOOD_TAX_ID,
            RequestSaver::FIELD_BRANCH => '00000',
            RequestSaver::FIELD_ADDRESS => '1 Road',
        ];
        $quote = $this->quote($data);
        $this->cartRepository->expects($this->once())->method('save')->with($quote);

        $this->saver->save($quote, null);

        foreach ($data as $value) {
            $this->assertNull($value);
        }
    }

    public function testNothingToChangeMeansNothingIsSaved(): void
    {
        $data = [];
        $this->cartRepository->expects($this->never())->method('save');

        $this->saver->save($this->quote($data), null);
    }

    public function testTheSameRequestIsNotSavedTwice(): void
    {
        // From the database the numbers come back as strings.
        $data = [
            RequestSaver::FIELD_TYPE => 'company',
            RequestSaver::FIELD_VAT_REGISTERED => '1',
            RequestSaver::FIELD_NAME => 'Acme',
            RequestSaver::FIELD_TAX_ID => self::GOOD_TAX_ID,
            RequestSaver::FIELD_BRANCH => '00000',
            RequestSaver::FIELD_ADDRESS => null,
        ];
        $this->cartRepository->expects($this->never())->method('save');

        $this->saver->save($this->quote($data), $this->request('Acme', self::GOOD_TAX_ID, '00000', 'company', true));
    }

    public function testIsIgnoredWhenTheCheckoutOptionIsOff(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isCheckoutEnabled')->willReturn(false);
        $saver = new RequestSaver(new TaxIdentity(), $config, $this->cartRepository);
        $data = [];

        // A wrong request is not even looked at: the option is off.
        $saver->save($this->quote($data), $this->request('', 'nonsense', 'x'));

        $this->assertArrayNotHasKey(RequestSaver::FIELD_TAX_ID, $data);
    }
}
