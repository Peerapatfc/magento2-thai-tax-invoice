# magento2-thai-tax-invoice

Thai tax invoice (ใบกำกับภาษี) support for Magento 2. **Work in progress: version 0.1.0 is only the base.** It does
not issue an invoice yet.

## What it does now

- Checks a Thai **tax ID** (13 digits with a correct check digit) and a **branch code** (5 digits, `00000` is the
  head office). Thai digits, spaces and dashes are accepted and removed.
- Keeps the **seller's details** in **Stores > Configuration > General > Thai Tax Invoice**: registered name, tax ID,
  branch code and address. A wrong tax ID or branch is refused when you save, and both are stored as plain digits.
- `GET /rest/V1/thai-tax-invoice/tax-id/:taxId/valid` returns `true` or `false` (no login needed). It checks the format
  and check digit only.

A correct check digit does **not** mean the number is registered. The module never asks the Revenue Department.

## What it does not do yet

- No "I want a tax invoice" option at checkout, no buyer tax ID or branch on the order.
- No invoice number, no printable or PDF invoice, no e-Tax Invoice.
- Admin and storefront do not check the buyer's tax ID anywhere yet.

## Install

```bash
composer config repositories.thai-tax-invoice vcs https://github.com/Peerapatfc/magento2-thai-tax-invoice
composer require peerapatfc/magento2-thai-tax-invoice
bin/magento module:enable Peerapatfc_ThaiTaxInvoice
bin/magento setup:upgrade
```

Needs Magento 2.4.8 and PHP 8.2 to 8.5. Tested on Magento Open Source 2.4.8-p4 with PHP 8.3 (see the CI workflow).

## Tests

```bash
cd dev/tests/unit   # in a Magento checkout that has the module in vendor/
../../../vendor/bin/phpunit -c phpunit.xml.dist ../../../vendor/peerapatfc/magento2-thai-tax-invoice/Test/Unit
```

## License

MIT
