# Procure-to-Pay Manual Test Guide

This guide tests the complete workflow:

`Purchase Order -> Approval -> Item Receipt -> Vendor Bill -> Bill Approval -> Vendor Payment`

## Start the application

```powershell
cd F:\Kuliah\Project\enterprise\enterprise
php artisan serve
```

Open `http://127.0.0.1:8000`.

Login with:

- Email: `admin@example.com`
- Password: `password`

The admin account can switch roles from the **Active Role** selector.

## 1. Create a purchase order

1. Switch to **Purchasing Manager**.
2. Open **Procurement (PTP) -> Create PO**.
3. Select:
   - Vendor: `Apple Store`
   - Ship To Location: `US Warehouse 1`
   - Item: `Exercise Inventory Item`
   - Department: `Purchasing`
   - Quantity: `10`
   - Unit Price: `125`
4. Select an expected delivery date that is the same as or later than the order date.
5. Click **Create Purchase Order**.
6. Record the generated PO number.

Expected result:

- PO status is **Draft**.
- Total is `$1,250.00`.
- The PO can still be edited, including its line items.

## 2. Approve the purchase order

1. On the PO detail page, click **Approve PO**.
2. Confirm the action.

Expected result:

- PO status becomes **Approved**.
- The inventory quantity on order increases by `10`.
- The **Receive Items** button becomes available to the Inventory Manager.

## 3. Test a partial item receipt

1. Switch to **Inventory Manager**.
2. Open **Purchase Orders**, then open the PO created above.
3. Click **Receive Items**.
4. Enter `4` in **Receive Now**.
5. Click **Post Item Receipt**.

Expected result:

- PO status becomes **Partially Received**.
- Quantity received is `4`.
- Remaining quantity is `6`.
- Warehouse on-hand stock increases by `4`.
- Quantity on order decreases to `6`.

## 4. Complete the item receipt

1. Click **Receive Items** again.
2. Enter `6`.
3. Click **Post Item Receipt**.

Expected result:

- PO status becomes **Received**.
- Total quantity received is `10`.
- Quantity on order becomes `0`.
- Two receipt records appear on the PO detail page.

## 5. Create the vendor bill

1. Switch to **AP Analyst**.
2. Open **Bills & Payments -> Create Bill**.
3. Select the received PO.
4. Verify that:
   - Vendor is selected automatically.
   - Available quantity is `10`.
   - Unit price is locked at `125`.
5. Enter unique values, for example:
   - Internal Bill Number: `BILL-PTP-001`
   - Vendor Invoice Reference: `INV-APPLE-001`
6. Leave Bill Qty as `10`.
7. Click **Create & Submit Bill**.

Expected result:

- Bill total is `$1,250.00`.
- Bill status is **Pending Approval**.
- PO line quantity billed becomes `10`.
- The same received quantity cannot be billed twice.

## 6. Approve the vendor bill

1. Switch to **Accounting Manager**.
2. Open **Vendor Bills** and select the new bill.
3. Click **Approve Bill**.

Expected result:

- Bill status becomes **Approved**.
- Accounts Payable increases by `$1,250.00`.
- Inventory Asset increases by `$1,250.00`.
- Vendor credit used increases by `$1,250.00`.
- The bill becomes available for payment.

## 7. Record a partial payment

1. Switch to **AP Analyst**.
2. Open the approved bill.
3. Click **Record Payment**.
4. Enter:
   - Amount: `500`
   - Method: `Bank Transfer`
   - Pay From Account: `1000 - Cash - Checking` or `1010 - US Checking Account`
   - Reference: `TRF-PTP-001`
5. Click **Post Vendor Payment**.

Expected result:

- Bill status becomes **Partially Paid**.
- Amount paid is `$500.00`.
- Balance due is `$750.00`.
- Cash, Accounts Payable, and vendor credit used each decrease by `$500.00`.

## 8. Complete the payment

1. Return to the bill.
2. Click **Record Payment**.
3. Pay the remaining `$750.00`.
4. Use a different reference, such as `TRF-PTP-002`.

Expected result:

- Bill status becomes **Paid**.
- Amount paid is `$1,250.00`.
- Balance due is `$0.00`.
- Both payments appear in **Payment History**.
- Both records appear under **Vendor Payments** in the sidebar.

## Negative validation checks

Optionally verify these safeguards:

1. Try receiving more than the remaining PO quantity.
   - Expected: validation error, no stock change.
2. Try billing more than the received and unbilled quantity.
   - Expected: validation error, no bill created.
3. Try paying more than the bill balance.
   - Expected: validation error, no payment created.
4. Try paying from an account with insufficient cash.
   - Expected: validation error, no account balance changes.

## Automated verification

Run:

```powershell
php artisan test --filter=ProcureToPayWorkflowTest
```

The test covers partial receipt, full receipt, bill matching, approval accounting, partial payment, final payment, over-receipt prevention, and over-billing prevention.
