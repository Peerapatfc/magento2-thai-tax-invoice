<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Peerapatfc\ThaiTaxInvoice\Api\Data\RequestInterface;

class Request extends AbstractSimpleObject implements RequestInterface
{
    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->_get(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType(?string $type): RequestInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->_get(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(?string $name): RequestInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getTaxId(): ?string
    {
        return $this->_get(self::TAX_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTaxId(?string $taxId): RequestInterface
    {
        return $this->setData(self::TAX_ID, $taxId);
    }

    /**
     * @inheritdoc
     */
    public function getBranch(): ?string
    {
        return $this->_get(self::BRANCH);
    }

    /**
     * @inheritdoc
     */
    public function setBranch(?string $branch): RequestInterface
    {
        return $this->setData(self::BRANCH, $branch);
    }

    /**
     * @inheritdoc
     */
    public function getVatRegistered(): ?bool
    {
        $value = $this->_get(self::VAT_REGISTERED);

        return $value === null ? null : (bool)$value;
    }

    /**
     * @inheritdoc
     */
    public function setVatRegistered(?bool $vatRegistered): RequestInterface
    {
        return $this->setData(self::VAT_REGISTERED, $vatRegistered);
    }

    /**
     * @inheritdoc
     */
    public function getAddress(): ?string
    {
        return $this->_get(self::ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setAddress(?string $address): RequestInterface
    {
        return $this->setData(self::ADDRESS, $address);
    }
}
