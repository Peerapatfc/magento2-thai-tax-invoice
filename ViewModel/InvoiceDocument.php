<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\ViewModel;

use DateTimeImmutable;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Peerapatfc\ThaiTaxInvoice\Model\TaxIdentity;

/**
 * Small helpers for printing a tax invoice: money, Thai dates and the head office / branch wording.
 */
class InvoiceDocument implements ArgumentInterface
{
    private const THAI_MONTHS = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    /**
     * Money with two decimals and thousands separators.
     *
     * @param float|int|string $amount
     * @return string
     */
    public function money($amount): string
    {
        return number_format((float)$amount, 2);
    }

    /**
     * A quantity without trailing zeros.
     *
     * @param float|int|string $qty
     * @return string
     */
    public function qty($qty): string
    {
        return rtrim(rtrim(number_format((float)$qty, 4, '.', ''), '0'), '.');
    }

    /**
     * A date as Thai writes it, with the Buddhist year: 2026-10-14 is "14 ตุลาคม 2569".
     *
     * @param string $date Y-m-d
     * @return string
     */
    public function thaiDate(string $date): string
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed) {
            return $date;
        }

        return sprintf(
            '%d %s %d',
            (int)$parsed->format('j'),
            self::THAI_MONTHS[(int)$parsed->format('n')],
            (int)$parsed->format('Y') + 543
        );
    }

    /**
     * "สำนักงานใหญ่" for 00000, otherwise "สาขาที่ 00001".
     *
     * @param string $branch
     * @return string
     */
    public function branchLabel(string $branch): string
    {
        return $branch === '' || $branch === TaxIdentity::HEAD_OFFICE
            ? 'สำนักงานใหญ่ (Head office)'
            : 'สาขาที่ ' . $branch . ' (Branch ' . $branch . ')';
    }
}
