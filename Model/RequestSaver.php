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
 * A company must give a tax ID and a branch. An individual must give a name; their tax or national ID is optional
 * (but checked when given) and they have no branch.
 *
 * With no request (the buyer did not ask, or changed their mind) the quote is cleared.
 */
class RequestSaver
{
    public const FIELD_TYPE = 'thai_tax_invoice_type';
    public const FIELD_NAME = 'thai_tax_invoice_name';
    public const FIELD_TAX_ID = 'thai_tax_invoice_tax_id';
    public const FIELD_BRANCH = 'thai_tax_invoice_branch';

    private const FIELDS = [self::FIELD_TYPE, self::FIELD_NAME, self::FIELD_TAX_ID, self::FIELD_BRANCH];

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
     * @throws InputException When the buyer asked for an invoice with something missing or wrong
     */
    public function save(Quote $quote, ?RequestInterface $request): void
    {
        $values = $this->isEmpty($request) || !$this->config->isCheckoutEnabled($quote->getStoreId())
            ? [null, null, null, null]
            : $this->validated($request);

        $changed = false;
        foreach (self::FIELDS as $index => $field) {
            if ($quote->getData($field) !== $values[$index]) {
                $quote->setData($field, $values[$index]);
                $changed = true;
            }
        }

        if ($changed) {
            $this->cartRepository->save($quote);
        }
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
     * Returns the cleaned type, name, tax ID and branch, or throws for the first thing that is wrong.
     *
     * @param RequestInterface $request
     * @return array
     * @throws InputException
     */
    private function validated(RequestInterface $request): array
    {
        $type = trim((string)$request->getType()) ?: RequestInterface::TYPE_COMPANY;
        if (!in_array($type, [RequestInterface::TYPE_INDIVIDUAL, RequestInterface::TYPE_COMPANY], true)) {
            throw new InputException(__('Choose whether the tax invoice is for an individual or a company.'));
        }

        $name = trim((string)$request->getName());
        if ($name === '') {
            throw new InputException(__('Enter the name for the tax invoice.'));
        }

        $taxId = $this->identity->normalize((string)$request->getTaxId());
        $isCompany = $type === RequestInterface::TYPE_COMPANY;
        if (($isCompany || $taxId !== '') && !$this->identity->isValidTaxId($taxId)) {
            throw new InputException(
                __('"%1" is not a valid tax ID. It must be 13 digits with a correct check digit.', $request->getTaxId())
            );
        }

        return [$type, $name, $taxId === '' ? null : $taxId, $isCompany ? $this->branch($request) : null];
    }

    /**
     * Returns the cleaned branch code of a company: 00000 when it is left empty.
     *
     * @param RequestInterface $request
     * @return string
     * @throws InputException
     */
    private function branch(RequestInterface $request): string
    {
        $branch = $this->identity->normalize((string)$request->getBranch());
        if ($branch === '') {
            return TaxIdentity::HEAD_OFFICE;
        }
        if (!$this->identity->isValidBranch($branch)) {
            throw new InputException(
                __('"%1" is not a valid branch code. It must be 5 digits; 00000 is the head office.', $branch)
            );
        }

        return $branch;
    }
}
