# magento2-thai-tax-invoice

Thai tax invoice (ใบกำกับภาษี) support for Magento 2. **Work in progress: it takes the buyer's request, issues a numbered
tax invoice and prints it. Read "What it does not do yet" before you rely on it.**

## What it does now

- **Checkout (Luma):** on the payment step the buyer can tick "I want a full tax invoice" and give the name, say
  whether they are **registered for VAT**, and give the address for the invoice.
  - Registered for VAT: tax ID and branch are required (a person as well as a company), because the seller has to
    print them and the buyer needs them to claim input tax.
  - Not registered: only the name is required; a tax ID is optional.
  - The address is the billing address unless the buyer types the registered address.

  Everything is checked in the browser and again on the server (guests and signed-in customers), kept on the order
  and shown in the admin order view. Turn it off under **Stores > Configuration > General > Thai Tax Invoice >
  Checkout**.
- **Issue Tax Invoice** (admin order page, or automatically when Magento invoices the order, if you turn that on):
  the order gets the next number (`INV2026-000001`, no gaps, a new series each year if the prefix has `{Y}`) and the
  invoice is kept exactly as it was issued. **Print Tax Invoice** opens a page in Thai and English that the browser
  can print or save as a PDF; a signed-in customer can open their own from their order page.
- Checks a Thai **tax ID** (13 digits with a correct check digit) and a **branch code** (5 digits, `00000` is the
  head office). Thai digits, spaces and dashes are accepted and removed.
- Keeps the **seller's details** in **Stores > Configuration > General > Thai Tax Invoice**: registered name, tax ID,
  branch code and address. A wrong tax ID or branch is refused when you save, and both are stored as plain digits.
- `GET /rest/V1/thai-tax-invoice/tax-id/:taxId/valid` returns `true` or `false` (no login needed). It checks the format
  and check digit only.

A correct check digit does **not** mean the number is registered. The module never asks the Revenue Department.

The buyer rules are taken from the Revenue Department's pages on [section 86/4](https://www.rd.go.th/5208.html),
[Notification No. 199](https://www.rd.go.th/27982.html) and [its explanation](https://rd.go.th/49981.html). This is
software, not tax advice. What a full tax invoice must show about the buyer, and when, is set by the Revenue
Department: check it with your accountant before you rely on it.

## What it does not do yet

- **Nothing here decides when a tax invoice has to be issued.** That depends on the kind of sale and is set by the
  Revenue Department. You issue it by hand, or when Magento invoices the order. Ask your accountant which fits.
- **No credit note or debit note, and no way to cancel an issued invoice.** A refund or a correction does not change
  the invoice that was issued.
- **No real PDF file and no e-mail attachment.** The print page is HTML; "save as PDF" is the browser's. No e-Tax
  Invoice.
- No "original / copy" marking and no multi-copy sets.
- The invoice shows the order's own amounts: lines and shipping before VAT, the VAT the order carries and the total.
  I tried it with 7% VAT added to the price. I could not make a "catalog prices include tax" order in the test shop,
  so that case is not checked. An order with no VAT prints VAT 0.00 and the admin page warns about it.
- Only the order's currency code is printed; nothing here checks that a foreign currency is allowed on a tax invoice.
- A guest cannot print the invoice from the storefront (there is no sign-in to check); the admin can.
- The seller has one branch code per store view: it is what is printed, there is no choice per order.
- Luma only: Hyvä and other checkouts are not done. The request is sent with the payment, so a payment method that
  does not use Magento's standard place-order action may not carry it.
- The admin order creation screen and the API outside the checkout do not take the request.
- The browser checks have no automated test (the PHP is unit tested; the checkout and the invoice were tried by hand
  in Magento 2.4.8-p4).

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
