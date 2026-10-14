# magento2-thai-tax-invoice

Thai tax invoice (ใบกำกับภาษี) support for Magento 2. **Work in progress: it collects the buyer's request, it does not
issue an invoice yet.**

## What it does now

- **Checkout (Luma):** on the payment step the buyer can tick "I want a full tax invoice" and give the name, tax ID
  and branch. They are checked in the browser and again on the server (guests and signed-in customers), kept on the
  order and shown in the admin order view. The invoice address is the billing address. Turn it off under
  **Stores > Configuration > General > Thai Tax Invoice > Checkout**.
- Checks a Thai **tax ID** (13 digits with a correct check digit) and a **branch code** (5 digits, `00000` is the
  head office). Thai digits, spaces and dashes are accepted and removed.
- Keeps the **seller's details** in **Stores > Configuration > General > Thai Tax Invoice**: registered name, tax ID,
  branch code and address. A wrong tax ID or branch is refused when you save, and both are stored as plain digits.
- `GET /rest/V1/thai-tax-invoice/tax-id/:taxId/valid` returns `true` or `false` (no login needed). It checks the format
  and check digit only.

A correct check digit does **not** mean the number is registered. The module never asks the Revenue Department.

## What it does not do yet

- No invoice number, no printable or PDF invoice, no e-Tax Invoice.
- The buyer cannot see or change the request after the order is placed (the customer's order page does not show it).
- Luma checkout only: Hyvä and other checkouts are not done. The request is sent with the payment, so a payment
  method that does not use Magento's standard place-order action may not carry it.
- The admin order creation screen and the API outside the checkout do not take the request.
- The browser checks have no automated test (the PHP is unit tested; the checkout was tried by hand).

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
