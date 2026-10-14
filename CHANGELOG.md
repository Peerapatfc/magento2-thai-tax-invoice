# Changelog

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
