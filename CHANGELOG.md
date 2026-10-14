# Changelog

## 0.4.0

The form follows what the Revenue Department asks for (Revenue Code section 86/4 and Notification No. 199), which
depends on whether the buyer is registered for VAT, not on whether they are a person or a company.

- **Registered for VAT** (a box, ticked for a company and not for an individual until the buyer changes it): the tax ID
  and the branch are required, for a person as well as a company. This is what the buyer needs to claim input tax.
- **Not registered:** only the name is required. A tax ID is optional but checked when given, and there is no branch.
- **Address on the invoice:** by default the billing address. The buyer can untick "Use my billing address" and type
  the registered address instead (up to 500 characters). The law asks for the address of the establishment as
  registered, which can differ from the billing address.
- Name label: "Company name, as registered" or "First and last name".
- New columns `thai_tax_invoice_vat_registered` and `thai_tax_invoice_address` on `quote` and `sales_order`. Orders
  from before have neither: they count as registered when they have a tax ID, and as using the billing address.

## 0.3.0

The buyer says whether the invoice is for an individual or a company.

- **Company:** name, tax ID and branch are required (the branch starts at 00000).
- **Individual:** only the name is required. A national or tax ID is optional, checked if given, and there is no
  branch. Not asking for the ID of a person keeps personal data out of the shop.
- New column `sales_order.thai_tax_invoice_type` (`quote` too). Orders and API calls from 0.2.0, which have no type,
  count as a company.

## 0.2.0

The buyer can ask for a full tax invoice at checkout.

- Luma checkout, payment step: "I want a full tax invoice" with the name, tax ID and branch of the buyer (the name
  is filled from the billing address, the branch starts at 00000). The invoice is made out to the billing address.
- Checked in the browser and again on the server when the order is placed: a wrong tax ID or branch is refused.
  Works for guests and for signed-in customers.
- The request is kept on the quote and copied to the order (`thai_tax_invoice_name`, `_tax_id`, `_branch` on
  `sales_order`), and shown in the admin order view.
- **Stores > Configuration > General > Thai Tax Invoice > Checkout** turns it off.

## 0.1.0

First version: the base, not yet a tax invoice.

- Checks a Thai tax ID (13 digits, check digit) and a branch code (5 digits, 00000 is the head office).
- Seller details in **Stores > Configuration > General > Thai Tax Invoice**: registered name, tax ID, branch code and
  address. The tax ID and the branch are checked when you save and stored as plain digits.
- `GET /rest/V1/thai-tax-invoice/tax-id/:taxId/valid` returns `true` or `false`.
