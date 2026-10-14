<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Peerapatfc\ThaiTaxInvoice\Model\TaxIdentity;

/**
 * Stores the seller's branch as 5 digits (00000 is the head office) and refuses anything else.
 */
class Branch extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param TaxIdentity $identity
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly TaxIdentity $identity,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Normalizes the branch code and rejects one that is not 5 digits.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->identity->normalize((string)$this->getValue());
        if ($value !== '' && !$this->identity->isValidBranch($value)) {
            throw new LocalizedException(
                __('"%1" is not a valid branch code. It must be 5 digits; 00000 is the head office.', $this->getValue())
            );
        }
        $this->setValue($value);

        return parent::beforeSave();
    }
}
