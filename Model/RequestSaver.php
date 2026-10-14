<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Peerapatfc\ThaiTaxInvoice\Api\Data\RequestInterface;

/**
 * Checks a tax invoice request and keeps it on the quote, from where it is copied to the order.
 *
 * With no request (the buyer did not ask, or changed their mind) the quote is cleared.
 */
class RequestSaver
{
    public const FIELD_NAME = 'thai_tax_invoice_name';
    public const FIELD_TAX_ID = 'thai_tax_invoice_tax_id';
    public const FIELD_BRANCH = 'thai_tax_invoice_branch';

    /**
     * @param TaxIdentity $identity
     * @param Config $config
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        private readonly TaxIdentity $identity,
        private readonly Config $config,
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * Validates the request and saves it on the quote, or clears it when there is none.
     *
     * @param Quote $quote
     * @param RequestInterface|null $request
     * @return void
     * @throws InputException When the buyer asked for an invoice with a missing or wrong name, tax ID or branch
     */
    public function save(Quote $quote, ?RequestInterface $request): void
    {
        $values = $this->isEmpty($request) || !$this->config->isCheckoutEnabled($quote->getStoreId())
            ? [null, null, null]
            : $this->validated($request);

        [$name, $taxId, $branch] = $values;
        if ($quote->getData(self::FIELD_NAME) === $name
            && $quote->getData(self::FIELD_TAX_ID) === $taxId
            && $quote->getData(self::FIELD_BRANCH) === $branch
        ) {
            return;
        }

        $quote->setData(self::FIELD_NAME, $name);
        $quote->setData(self::FIELD_TAX_ID, $taxId);
        $quote->setData(self::FIELD_BRANCH, $branch);
        $this->cartRepository->save($quote);
    }

    /**
     * Whether nothing was filled in.
     *
     * @param RequestInterface|null $request
     * @return bool
     */
    private function isEmpty(?RequestInterface $request): bool
    {
        return $request === null
            || trim((string)$request->getName()) === ''
            && trim((string)$request->getTaxId()) === ''
            && trim((string)$request->getBranch()) === '';
    }

    /**
     * Returns the cleaned name, tax ID and branch, or throws for the first thing that is wrong.
     *
     * @param RequestInterface $request
     * @return string[]
     * @throws InputException
     */
    private function validated(RequestInterface $request): array
    {
        $name = trim((string)$request->getName());
        if ($name === '') {
            throw new InputException(__('Enter the name for the tax invoice.'));
        }

        $taxId = $this->identity->normalize((string)$request->getTaxId());
        if (!$this->identity->isValidTaxId($taxId)) {
            throw new InputException(
                __('"%1" is not a valid tax ID. It must be 13 digits with a correct check digit.', $request->getTaxId())
            );
        }

        $branch = $this->identity->normalize((string)$request->getBranch());
        if ($branch === '') {
            $branch = TaxIdentity::HEAD_OFFICE;
        }
        if (!$this->identity->isValidBranch($branch)) {
            throw new InputException(
                __('"%1" is not a valid branch code. It must be 5 digits; 00000 is the head office.', $branch)
            );
        }

        return [$name, $taxId, $branch];
    }
}
