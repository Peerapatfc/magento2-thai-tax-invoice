<?php

declare(strict_types=1);

namespace Peerapatfc\ThaiTaxInvoice\Test\Unit\Model;

use Peerapatfc\ThaiTaxInvoice\Model\TaxIdentity;
use PHPUnit\Framework\TestCase;

class TaxIdentityTest extends TestCase
{
    private TaxIdentity $identity;

    protected function setUp(): void
    {
        $this->identity = new TaxIdentity();
    }

    /**
     * @dataProvider validTaxIds
     */
    public function testAcceptsATaxIdWithACorrectCheckDigit(string $taxId): void
    {
        $this->assertTrue($this->identity->isValidTaxId($taxId));
    }

    public static function validTaxIds(): array
    {
        return [
            'plain digits' => ['1101700230678'],
            'with dashes' => ['1-1017-00230-67-8'],
            'with spaces' => ['1 1017 00230 67 8'],
            'Thai digits' => ['๑๑๐๑๗๐๐๒๓๐๖๗๘'],
            'check digit is one' => ['1234567890121'],
        ];
    }

    /**
     * @dataProvider invalidTaxIds
     */
    public function testRejectsAWrongTaxId(string $taxId): void
    {
        $this->assertFalse($this->identity->isValidTaxId($taxId));
    }

    public static function invalidTaxIds(): array
    {
        return [
            'wrong check digit' => ['1101700230679'],
            'one digit off' => ['1101700230688'],
            'too short' => ['110170023067'],
            'too long' => ['11017002306788'],
            'letters' => ['11017002306AB'],
            'empty' => [''],
        ];
    }

    public function testNormalizeKeepsOnlyDigits(): void
    {
        $this->assertSame('1101700230678', $this->identity->normalize(' 1-1017 00230-67-8 '));
    }

    /**
     * @dataProvider branches
     */
    public function testBranchMustBeFiveDigits(string $branch, bool $valid): void
    {
        $this->assertSame($valid, $this->identity->isValidBranch($branch));
    }

    public static function branches(): array
    {
        return [
            'head office' => ['00000', true],
            'branch 1' => ['00001', true],
            'Thai digits' => ['๐๐๐๐๑', true],
            'four digits' => ['0001', false],
            'six digits' => ['000001', false],
            'letters' => ['HQ', false],
            'empty' => ['', false],
        ];
    }
}
