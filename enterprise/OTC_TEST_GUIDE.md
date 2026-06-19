# Order-to-Cash Manual Test Guide

This guide tests the complete workflow:

`Sales Order -> Approval -> Pick -> Pack -> Ship -> Sales Invoice -> Invoice Approval -> Customer Payment`

## Start and login

```powershell
cd F:\Kuliah\Project\enterprise\enterprise
php artisan serve
```

Open `http://127.0.0.1:8000`.

Login:

- Email: `admin@example.com`
- Password: `password`

Use the **Active Role** selector to change roles during the workflow.

The current demo database contains `10` units of `EXE-INV-001` at `US Warehouse 1`. If another test has already consumed the stock, replenish it through the Procure-to-Pay flow first.

## 1. Create the Sales Order

1. Switch to **Sales Representative**.
2. Open **Sales (OTC) -> Create SO**.
3. Enter:
   - Customer: `ABC Corporation`
   - Fulfillment Warehouse: `US Warehouse 1`
   - Currency: `USD`
   - Customer PO Reference: `CUSTOMER-PO-001`
   - Item: `EXE-INV-001 - Exercise Inventory Item`
   - Quantity: `5`
   - Unit Price: `200`
4. Click **Create Sales Order**.

Expected:

- A Sales Order number such as `SO-000001` is generated.
- Status is **Draft**.
- Total is `$1,000.00`.
- The order can still be edited.

## 2. Submit and approve the Sales Order

1. On the Sales Order page, click **Submit for Approval**.
2. Switch to **Sales Manager**.
3. Open the same Sales Order.
4. Click **Approve SO**.

Expected:

- Status changes from **Pending Approval** to **Approved**.
- Customer credit is checked.
- Available warehouse stock is checked.
- The order becomes available to the Inventory Manager.

## 3. Perform a partial pick

1. Switch to **Inventory Manager**.
2. Open the Sales Order.
3. Click **Pick Items**.
4. Enter `2` as **Pick Now**.
5. Click **Complete Pick**.

Expected:

- Status becomes **Partial**.
- Picked quantity is `2`.
- Remaining quantity is `3`.
- Reserved warehouse stock increases by `2`.
- On-hand stock has not decreased yet.

## 4. Pack and ship the partial quantity

1. Click **Pack Items**.
2. Pack quantity `2`.
3. Click **Complete Pack**.
4. Click **Ship Items**.
5. Enter:
   - Ship quantity: `2`
   - Carrier: `DHL`
   - Tracking Number: `DHL-OTC-001`
6. Click **Complete Shipment**.

Expected:

- Pick, pack, and shipment history appear on the Sales Order.
- Shipped quantity is `2`.
- On-hand stock decreases by `2`.
- Reserved stock returns to `0` for those units.
- Inventory cost is moved from Inventory Asset to COGS.
- Status remains **Partial** because `3` units are still unfulfilled.

## 5. Create the first Sales Invoice

1. Switch to **AR Analyst**.
2. Open the Sales Order.
3. Click **Create Invoice**.
4. Invoice the available quantity `2`.
5. Click **Create Draft Invoice**.

Expected:

- An invoice number such as `INV-000001` is generated.
- Invoice total is `$400.00`.
- Invoice status is **Draft**.
- The same shipped quantity cannot be invoiced twice.

## 6. Submit the Sales Invoice for approval

1. While still using **AR Analyst**, open the draft invoice.
2. Review the invoice lines and total.
3. Click **Submit for Approval**.

Expected:

- Invoice status becomes **Pending Approval**.
- The invoice is now available for Accounting Manager review.
- The AR Analyst cannot approve the invoice.

## 7. Approve the Sales Invoice

1. Switch to **Accounting Manager**.
2. Open **Sales Invoices**.
3. Open the invoice with status **Pending Approval**.
4. Click **Approve Invoice**.

The Accounting Manager can click **Reject Invoice** instead. A rejected invoice returns to **Draft**, allowing the AR Analyst to review, cancel, or resubmit it.

Expected:

- Invoice status becomes **Approved**.
- Accounts Receivable increases by `$400.00`.
- Sales Revenue increases by `$400.00`.
- Customer credit used increases by `$400.00`.

## 8. Record a partial customer payment

1. Switch to **AR Analyst**.
2. Open the approved invoice.
3. Click **Record Payment**.
4. Enter:
   - Payment Amount: `200`
   - Payment Method: `Bank Transfer`
   - Cash Account: `1000 - Cash - Checking`
   - Reference: `PAY-OTC-001`
5. In **Allocate to Invoices**, click **Add Invoice Allocation**.
6. Select the invoice and allocate `200`.
7. Click **Record & Post Payment**.

Expected:

- Payment is created and linked to the invoice.
- Invoice status becomes **Partial**.
- Amount paid is `$200.00`.
- Balance due is `$200.00`.
- Cash increases by `$200.00`.
- Accounts Receivable and customer credit used decrease by `$200.00`.

## 9. Complete the first invoice payment

1. Create another customer payment for `200`.
2. Allocate it to the same invoice.

Expected:

- Invoice status becomes **Paid**.
- Invoice balance becomes `$0.00`.
- Both payment allocations appear on the invoice.

## 10. Fulfill the remaining quantity

1. Switch to **Inventory Manager**.
2. Open the same Sales Order.
3. Pick the remaining `3`.
4. Pack the remaining `3`.
5. Ship the remaining `3` with a new tracking number.

Expected:

- Total picked, packed, and shipped quantities each become `5`.
- Sales Order status becomes **Shipped**.
- Warehouse on-hand stock has decreased by a total of `5`.

## 11. Invoice and receive payment for the remainder

1. Switch to **AR Analyst**.
2. Create another invoice from the Sales Order for quantity `3`.
3. Click **Submit for Approval**.
4. Switch to **Accounting Manager** and approve it.
5. Switch to **AR Analyst** and record a `$600.00` payment allocated to the second invoice.

Expected final state:

- Both invoices are **Paid**.
- Sales Order status is **Invoiced**.
- Total Sales Revenue increased by `$1,000.00`.
- Total Cash increased by `$1,000.00`.
- Accounts Receivable returns to its original balance.
- Customer credit used returns to its original balance.
- Five units remain from the original ten-unit demo stock.

## Negative validation checks

Test these safeguards if desired:

1. Approve an order larger than the customer credit limit.
   - Expected: approval is rejected.
2. Approve an order with quantity above available warehouse stock.
   - Expected: approval is rejected.
3. Pick more than the remaining Sales Order quantity.
   - Expected: validation error and no stock reservation.
4. Pack more than the picked quantity.
   - Expected: validation error.
5. Ship more than the packed quantity.
   - Expected: validation error and no stock reduction.
6. Invoice more than the shipped quantity.
   - Expected: validation error.
7. Allocate payment above the invoice balance or payment amount.
   - Expected: validation error and no accounting changes.

## Automated verification

Run:

```powershell
php artisan test --filter=OrderToCashWorkflowTest
```

The test covers the complete workflow, partial picking, stock reservation, shipment inventory accounting, invoice accounting, partial payment, final payment, credit-limit validation, stock validation, and over-picking prevention.
