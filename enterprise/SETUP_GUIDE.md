# ERP System - Quick Setup Guide

This document provides step-by-step instructions to set up and run the NetSuite ERP Basics implementation.

## System Requirements

- **PHP**: 8.2 or higher
- **Database**: PostgreSQL 12+
- **Composer**: Latest version
- **Node.js**: Optional (for frontend assets)

## Installation Steps

### Step 1: Install PHP Dependencies

```bash
cd enterprise
composer install
```

### Step 2: Configure Environment

Copy the example environment file:
```bash
cp .env.example .env
```

Edit `.env` and update database credentials:
```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=enterprise
DB_USERNAME=postgres
DB_PASSWORD=root
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Create Database

```bash
createdb enterprise
```

Or if using a database GUI, create a database named `enterprise`.

### Step 5: Run Migrations

```bash
php artisan migrate
```

This creates all tables required for the ERP system.

### Step 6: Seed Demo Data

```bash
php artisan db:seed
```

This populates:
- Test users with different roles
- Subsidiaries and locations
- Chart of accounts
- Tax schedules
- Sample vendors and customers
- Sample items

### Step 7: Start Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## First Login

### Default Admin Account
- **Email**: admin@example.com
- **Password**: password

After login, you can switch roles using the dropdown in the sidebar.

### Other Test Accounts
- **Purchasing Manager**: purchasing@example.com
- **Inventory Manager**: inventory@example.com
- **A/P Analyst**: ap@example.com
- **Sales Representative**: sales@example.com
- **Sales Manager**: sales_manager@example.com

All accounts use password: `password`

## What's Included

### Database Schema (5 Migrations)

1. **add_roles_to_users_table**: Role support in user model
2. **create_master_tables**: Subsidiaries, departments, locations, accounts, tax schedules
3. **create_items_tables**: Items, inventory stock, adjustments, transfers
4. **create_ptp_tables**: Vendors, POs, receipts, bills, payments
5. **create_otc_tables**: Customers, SOs, fulfillment, invoices, payments

### Models Created

**Master Data Models:**
- Subsidiary, Department, Location, Account, TaxSchedule

**Items Management:**
- Item, ItemAccount, InventoryStock, InventoryAdjustment, InventoryTransfer

**Procurement (PTP):**
- Vendor, PurchaseOrder, PurchaseOrderItem, ItemReceipt, VendorBill, BillPayment

**Sales (OTC):**
- Customer, SalesOrder, SalesOrderItem, OrderPick, OrderPack, OrderShip, SalesInvoice, CustomerPayment

**Authentication:**
- User (enhanced with role switching)

### Controllers Created

- **DashboardController**: Dashboard and role switching
- **ItemController**: Item CRUD operations
- **PurchaseOrderController**: PO creation and management
- **ItemReceiptController**: Item receiving
- **VendorBillController**: Bill management and approval
- **SalesOrderController**: Sales order creation and approval
- **LoginController**: Authentication

### Routes

All routes are under `/` prefix and protected with authentication middleware.

**Key Route Groups:**
- `/items` - Item management
- `/purchase-orders` - Purchase orders
- `/item-receipts` - Item receiving
- `/bills` - Vendor bills
- `/sales-orders` - Sales orders

## How It Works

### Role-Based Access

1. **Admin**: Full access to all modules and can switch to any role
2. **Purchasing Manager**: Create and manage purchase orders and items
3. **Inventory Manager**: Receive items and manage stock
4. **A/P Analyst**: Create vendor bills
5. **Accounting Manager**: Approve vendor bills
6. **Sales Representative**: Create sales orders
7. **Sales Manager**: Approve sales orders
8. **A/R Analyst**: Process customer payments

### Workflows

#### Procure to Pay (PTP)
```
Purchase Order (Draft) 
    → Approve PO 
    → Receive Items 
    → Create Bill 
    → Approve Bill 
    → Pay Bill
```

#### Order to Cash (OTC)
```
Sales Order (Draft)
    → Submit for Approval
    → Approve SO
    → Pick Items
    → Pack Items
    → Ship Items
    → Create Invoice
    → Receive Payment
```

### Role Switching

The admin user can demonstrate the system with different roles:

1. Login as `admin@example.com`
2. In the sidebar, use the "Switch Role" dropdown
3. Select any available role
4. The interface updates to show only relevant menu items for that role

## Testing the System

### Quick Test Workflow

1. **Login as Purchasing Manager**
   - Create an item (Items → Create Item)
   - Fill in basic information
   - Select Income Account for accounting
   - Save

2. **Create a Purchase Order**
   - Purchase Orders → Create PO
   - Select a vendor (Apple Store is pre-seeded)
   - Add the item you created
   - Save

3. **Receive Items**
   - Go back to the PO
   - Click "Receive Items"
   - Specify quantity received
   - Save

4. **Create a Bill**
   - Bills → Create Bill
   - Link to the purchase order
   - Verify amounts
   - Save

5. **Approve the Bill**
   - Switch role to Accounting Manager
   - Bills → Find your bill
   - Click "Approve"

6. **Pay the Bill**
   - Switch role to A/P Analyst (if available)
   - Record payment against the bill

## Project Structure

```
enterprise/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   │   └── CheckRole.php
│   ├── Models/
│       ├── User.php
│       ├── Subsidiary.php, Department.php, Location.php, Account.php
│       ├── Item.php, ItemAccount.php, InventoryStock.php
│       ├── Vendor.php, PurchaseOrder.php, VendorBill.php
│       ├── Customer.php, SalesOrder.php, SalesInvoice.php
│       └── ...
├── bootstrap/
│   ├── app.php (middleware registration)
├── database/
│   ├── migrations/ (5 migration files)
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── items/
│       │   ├── index.blade.php
│       │   └── create.blade.php
│       ├── po/, bills/, sales-orders/ (views)
└── routes/
    └── web.php
```

## Troubleshooting

### Database Connection Error
```
Error: SQLSTATE[08006] could not connect to server
```
**Solution:**
- Ensure PostgreSQL is running
- Check database credentials in `.env`
- Run: `php artisan migrate` again

### Authentication Error
```
Error: Column not found "current_role"
```
**Solution:**
- Run migrations: `php artisan migrate`
- Clear cache: `php artisan cache:clear`

### View Not Found
```
View [dashboard.index] not found
```
**Solution:**
- Ensure all views exist in `resources/views/`
- Check view names match exactly (case-sensitive)

### Permission Denied
```
403 - Unauthorized access to this role
```
**Solution:**
- Verify user role in database: `SELECT current_role FROM users WHERE id = X;`
- Check that user has permission for the action
- Try switching roles if available

## Database Queries for Verification

### Check Users and Roles
```sql
SELECT id, email, current_role, available_roles FROM users;
```

### Check Items Created
```sql
SELECT id, sku, name, type FROM items;
```

### Check Purchase Orders
```sql
SELECT po.po_number, v.name as vendor, po.status 
FROM purchase_orders po 
JOIN vendors v ON po.vendor_id = v.id;
```

### Check Vendor Bills
```sql
SELECT bill_number, status, total FROM vendor_bills;
```

## Common Operations

### Create a New Item
1. Login as Purchasing Manager or Admin
2. Go to Items → Create Item
3. Fill in: Name, SKU, Type, Income Account
4. Save

### Create a Purchase Order
1. Go to Purchase Orders → Create PO
2. Select Vendor and Location
3. Add items with quantities
4. Save

### Receive Items
1. Find the PO
2. Click "Receive Items"
3. Enter quantities received
4. Save (updates inventory)

### Create and Approve a Bill
1. Go to Bills → Create Bill
2. Enter reference number and amounts
3. Save (bill is in Pending Approval status)
4. Switch to Accounting Manager
5. Approve the bill

## Performance Notes

- Database uses PostgreSQL for production-ready reliability
- Indexes are created on foreign keys and commonly searched fields
- Pagination limits listings to 20 items per page
- Role-based middleware prevents unauthorized access

## Next Steps

To extend the system:

1. **Add Email Notifications**: Create notification classes for approvals
2. **Generate Reports**: Add report generation views
3. **API Development**: Create REST API endpoints
4. **Mobile App**: Build mobile interface using the API
5. **Audit Logging**: Track all document changes
6. **Automated Workflows**: Implement scheduled tasks
7. **Multi-currency**: Add currency conversion support
8. **Advanced Inventory**: Add lot tracking, serial numbers

## Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- PostgreSQL Docs: https://www.postgresql.org/docs/
- Code comments in models and controllers

## License

This project is for educational purposes.
