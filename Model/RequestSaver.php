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
 * The rule that matters is whether the buyer is registered for VAT, not whether they are a person or a company:
 * a buyer registered for VAT must give a tax ID and a branch (00000 is the head office) so the seller can put them
 * on the invoice. Anyone else only needs a name; a tax ID is optional, but checked when given.
 *
 * The address on the invoice is the billing address unless the buyer types another one (the registered address).
 *
 * With no request (the buyer did not ask, or changed their mind) the quote is cleared.
 */
class RequestSaver
{
    public const FIELD_TYPE = 'thai_tax_invoice_type';
    public const FIELD_VAT_REGISTERED = 'thai_tax_invoice_vat_registered';
    public const FIELD_NAME = 'thai_tax_invoice_name';
    public const FIELD_TAX_ID = 'thai_tax_invoice_tax_id';
    public const FIELD_BRANCH = 'thai_tax_invoice_branch';
    public const FIELD_ADDRESS = 'thai_tax_invoice_address';

    private const MAX_ADDRESS_LENGTH = 500;

    private const FIELDS = [
        self::FIELD_TYPE,
        self::FIELD_VAT_REGISTERED,
        self::FIELD_NAME,
        self::FIELD_TAX_ID,
        self::FIELD_BRANCH,
        self::FIELD_ADDRESS,
    ];

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
            ? array_fill(0, count(self::FIELDS), null)
            : $this->validated($request);

        $changed = false;
        foreach (self::FIELDS as $index => $field) {
            if (!$this->same($quote->getData($field), $values[$index])) {
                $quote->setData($field, $values[$index]);
                $changed = true;
            }
        }

        if ($changed) {
            $this->cartRepository->save($quote);
        }
    }

    /**
     * Whether a stored value (a string from the database) is the one about to be saved.
     *
     * @param mixed $stored
     * @param mixed $new
     * @return bool
     */
    private function same(mixed $stored, mixed $new): bool
    {
        return $stored === null || $new === null ? $stored === $new : (string)$stored === (string)$new;
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
            && trim((string)$request->getBranch()) === ''
            && trim((string)$request->getAddress()) === '';
    }

    /**
     * Returns the cleaned values in the order of FIELDS, or throws for the first thing that is wrong.
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
        $vatRegistered = $request->getVatRegistered() ?? $taxId !== '';
        if (($vatRegistered || $taxId !== '') && !$this->identity->isValidTaxId($taxId)) {
            throw new InputException(
                __('"%1" is not a valid tax ID. It must be 13 digits with a correct check digit.', $request->getTaxId())
            );
        }

        return [
            $type,
            $vatRegistered ? 1 : 0,
            $name,
            $taxId === '' ? null : $taxId,
            $vatRegistered ? $this->branch($request) : null,
            $this->address($request),
        ];
    }

    /**
     * Returns the cleaned branch code of a VAT-registered buyer: 00000 when it is left empty.
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

    /**
     * Returns the typed address, or null for "the billing address".
     *
     * @param RequestInterface $request
     * @return string|null
     * @throws InputException
     */
    private function address(RequestInterface $request): ?string
    {
        $address = trim((string)preg_replace('/[ \t]+/u', ' ', (string)$request->getAddress()));
        if (mb_strlen($address) > self::MAX_ADDRESS_LENGTH) {
            throw new InputException(
                __('The address for the tax invoice must be at most %1 characters.', self::MAX_ADDRESS_LENGTH)
            );
        }

        return $address === '' ? null : $address;
    }
}
