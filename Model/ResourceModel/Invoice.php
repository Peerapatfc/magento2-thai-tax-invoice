<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Invoice extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('peerapatfc_tax_invoice', 'entity_id');
    }

    /**
     * The next running number for a prefix, given out inside the caller's transaction.
     *
     * The row is locked until the transaction ends, so two orders cannot get the same number and a number is not
     * used up when the invoice that took it is rolled back: the numbers have no gaps.
     *
     * @param string $prefix
     * @return int
     */
    public function nextNumber(string $prefix): int
    {
        $connection = $this->getConnection();
        $table = $this->getTable('peerapatfc_tax_invoice_sequence');
        $connection->insertOnDuplicate(
            $table,
            ['prefix' => $prefix, 'last_number' => 1],
            ['last_number' => new \Zend_Db_Expr('last_number + 1')]
        );

        return (int)$connection->fetchOne(
            $connection->select()->from($table, 'last_number')->where('prefix = ?', $prefix)
        );
    }

    /**
     * Loads the invoice of an order by the order's ID, or returns an empty array.
     *
     * @param int $orderId
     * @return array
     */
    public function fetchByOrderId(int $orderId): array
    {
        $connection = $this->getConnection();

        return $connection->fetchRow(
            $connection->select()->from($this->getMainTable())->where('order_id = ?', $orderId)
        ) ?: [];
    }
}
