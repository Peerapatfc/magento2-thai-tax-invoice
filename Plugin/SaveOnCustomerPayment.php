<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Peerapatfc\ThaiTaxInvoice\Model\RequestSaver;

/**
 * Keeps the tax invoice request of a signed-in customer before the payment is saved or the order is placed.
 */
class SaveOnCustomerPayment
{
    /**
     * @param RequestSaver $saver
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        private readonly RequestSaver $saver,
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * Saves the request before the order is placed.
     *
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): void {
        $this->save((int)$cartId, $paymentMethod);
    }

    /**
     * Saves the request when only the payment information is saved.
     *
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): void {
        $this->save((int)$cartId, $paymentMethod);
    }

    /**
     * Loads the cart and saves the request that came with the payment.
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @return void
     */
    private function save(int $cartId, PaymentInterface $paymentMethod): void
    {
        $quote = $this->cartRepository->getActive($cartId);
        if ($quote instanceof Quote) {
            $extension = $paymentMethod->getExtensionAttributes();
            $this->saver->save($quote, $extension ? $extension->getThaiTaxInvoice() : null);
        }
    }
}
