<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Peerapatfc\ThaiTaxInvoice\Model\RequestSaver;

/**
 * Keeps the tax invoice request of a guest before the payment is saved or the order is placed.
 */
class SaveOnGuestPayment
{
    /**
     * @param RequestSaver $saver
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        private readonly RequestSaver $saver,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
    }

    /**
     * Saves the request before the order is placed.
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): void {
        $this->save((string)$cartId, $paymentMethod);
    }

    /**
     * Saves the request when only the payment information is saved.
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): void {
        $this->save((string)$cartId, $paymentMethod);
    }

    /**
     * Loads the cart behind the masked id and saves the request that came with the payment.
     *
     * @param string $maskedCartId
     * @param PaymentInterface $paymentMethod
     * @return void
     */
    private function save(string $maskedCartId, PaymentInterface $paymentMethod): void
    {
        $quote = $this->cartRepository->getActive($this->maskedQuoteIdToQuoteId->execute($maskedCartId));
        if ($quote instanceof Quote) {
            $extension = $paymentMethod->getExtensionAttributes();
            $this->saver->save($quote, $extension ? $extension->getThaiTaxInvoice() : null);
        }
    }
}
