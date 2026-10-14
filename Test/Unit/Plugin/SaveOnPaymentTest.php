<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentExtensionInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Peerapatfc\ThaiTaxInvoice\Api\Data\RequestInterface;
use Peerapatfc\ThaiTaxInvoice\Model\RequestSaver;
use Peerapatfc\ThaiTaxInvoice\Plugin\SaveOnCustomerPayment;
use Peerapatfc\ThaiTaxInvoice\Plugin\SaveOnGuestPayment;
use PHPUnit\Framework\TestCase;

class SaveOnPaymentTest extends TestCase
{
    private function payment(?RequestInterface $request): PaymentInterface
    {
        // Magento generates this interface from extension_attributes.xml, so the method is there only after
        // setup:di:compile has run.
        $declared = method_exists(PaymentExtensionInterface::class, 'getThaiTaxInvoice');
        $builder = $this->getMockBuilder(PaymentExtensionInterface::class);
        $extension = ($declared ? $builder->onlyMethods(['getThaiTaxInvoice']) : $builder->addMethods(['getThaiTaxInvoice']))
            ->getMockForAbstractClass();
        $extension->method('getThaiTaxInvoice')->willReturn($request);
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getExtensionAttributes')->willReturn($extension);

        return $payment;
    }

    public function testACustomersRequestIsSavedOnTheirCart(): void
    {
        $quote = $this->createMock(Quote::class);
        $request = $this->createMock(RequestInterface::class);
        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository->expects($this->once())->method('getActive')->with(42)->willReturn($quote);
        $saver = $this->createMock(RequestSaver::class);
        $saver->expects($this->once())->method('save')->with($quote, $request);

        (new SaveOnCustomerPayment($saver, $cartRepository))->beforeSavePaymentInformationAndPlaceOrder(
            $this->createMock(PaymentInformationManagementInterface::class),
            42,
            $this->payment($request)
        );
    }

    public function testAGuestsMaskedCartIdIsResolvedFirst(): void
    {
        $quote = $this->createMock(Quote::class);
        $request = $this->createMock(RequestInterface::class);
        $mask = $this->createMock(MaskedQuoteIdToQuoteIdInterface::class);
        $mask->expects($this->once())->method('execute')->with('abc123')->willReturn(7);
        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository->expects($this->once())->method('getActive')->with(7)->willReturn($quote);
        $saver = $this->createMock(RequestSaver::class);
        $saver->expects($this->once())->method('save')->with($quote, $request);

        (new SaveOnGuestPayment($saver, $cartRepository, $mask))->beforeSavePaymentInformationAndPlaceOrder(
            $this->createMock(GuestPaymentInformationManagementInterface::class),
            'abc123',
            'a@b.co',
            $this->payment($request)
        );
    }

    public function testPaymentWithoutExtensionAttributesClearsTheRequest(): void
    {
        $quote = $this->createMock(Quote::class);
        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository->method('getActive')->willReturn($quote);
        $saver = $this->createMock(RequestSaver::class);
        $saver->expects($this->once())->method('save')->with($quote, null);
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getExtensionAttributes')->willReturn(null);

        (new SaveOnCustomerPayment($saver, $cartRepository))->beforeSavePaymentInformation(
            $this->createMock(PaymentInformationManagementInterface::class),
            1,
            $payment
        );
    }
}
