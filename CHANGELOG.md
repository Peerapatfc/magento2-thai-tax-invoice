# Changelog

## 0.1.0

First version: the base, not yet a tax invoice.

- Checks a Thai tax ID (13 digits, check digit) and a branch code (5 digits, 00000 is the head office).
- Seller details in **Stores > Configuration > General > Thai Tax Invoice**: registered name, tax ID, branch code and
  address. The tax ID and the branch are checked when you save and stored as plain digits.
- `GET /rest/V1/thai-tax-invoice/tax-id/:taxId/valid` returns `true` or `false`.
