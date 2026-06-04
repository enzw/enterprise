# ERP System - NetSuite Basics Implementation

A comprehensive Laravel-based ERP system implementing Item Management, Procure to Pay (PTP), and Order to Cash (OTC) workflows with role-based access control.

## Features

### 1. Authentication & Role Management
- **Login as Admin**: Master account with access to all roles
- **Role Switching**: After login, users can switch between available roles
- **Role-Based Access**: Each role has specific permissions and dashboard views
- **Roles Included**:
  - Admin
  - Purchasing Manager
  - Inventory Manager
  - A/P Analyst
  - Accounting Manager
  - Sales Representative
  - Sales Manager
  - A/R Analyst

### 2. Item Management
- **Inventory Items**: Items purchased, stored, and sold
- **Non-Inventory Items**: Items purchased and sold, not stored
- **Service Items**: Services offered for sale
- **Item Accounting**: Automatic account mapping (COGS, Asset, Income accounts)
- **Inventory Stock Levels**: Track on-hand, on-order, and reserved quantities
- **Inventory Adjustments**: Manual quantity adjustments
- **Inventory Transfers**: Move stock between locations

### 3. Procure to Pay (PTP) Workflow
- **Purchase Orders**: Create, approve, and manage POs
- **Item Receipts**: Partial receipt support
- **Vendor Bills**: Create standalone or link to PO
- **Bill Approvals**: Accounting Manager approval workflow
- **Bill Payments**: Pay bills with A/P and Cash account entries

### 4. Order to Cash (OTC) Workflow
- **Sales Orders**: Create and manage sales orders
- **Order Approval**: Sales Manager approval workflow
- **3-Step Fulfillment**:
  - Pick: Reserve inventory
  - Pack: Prepare items
  - Ship: Send to customer
- **Sales Invoices**: Generate from shipped orders
- **Customer Payments**: Receive and allocate payments

## Setup Instructions

### Prerequisites
- PHP 8.2+
- PostgreSQL 12+
- Composer
- Node.js (optional, for frontend assets)

### Installation

1. **Install Dependencies**
```bash
cd enterprise
composer install
```

2. **Create Environment File**
```bash
cp .env.example .env
```

3. **Configure Database** (in .env)
```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=enterprise
DB_USERNAME=postgres
DB_PASSWORD=root
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Run Migrations**
```bash
php artisan migrate
```

6. **Seed Database**
```bash
php artisan db:seed
```

7. **Start Development Server**
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## Test Credentials

After seeding, you can login with:

### Admin User (All Roles)
- **Email**: admin@example.com
- **Password**: password

### Individual Role Users
- **Purchasing Manager**: purchasing@example.com
- **Inventory Manager**: inventory@example.com
- **A/P Analyst**: ap@example.com
- **Sales Representative**: sales@example.com

All use password: `password`

## Database Schema

### Master Data
- `subsidiaries` - Companies/branches
- `departments` - Organizational departments
- `locations` - Warehouses/facilities
- `accounts` - Chart of accounts
- `tax_schedules` - Tax rates

### Items
- `items` - All items (inventory, non-inventory, service)
- `item_accounts` - Account mappings per item
- `inventory_stock` - Stock levels by location
- `inventory_adjustments` - Manual adjustments
- `inventory_transfers` - Inter-location transfers

### Procurement (PTP)
- `vendors` - Vendor master
- `purchase_orders` - PO header
- `purchase_order_items` - PO line items
- `item_receipts` - Item receipt header
- `item_receipt_items` - Receipt line items
- `vendor_bills` - Vendor bill header
- `vendor_bill_items` - Bill line items
- `bill_payments` - Payment records

### Sales (OTC)
- `customers` - Customer master
- `sales_orders` - SO header
- `sales_order_items` - SO line items
- `order_picks` - Pick documents
- `pick_items` - Pick line items
- `order_packs` - Pack documents
- `pack_items` - Pack line items
- `order_ships` - Ship documents
- `ship_items` - Ship line items
- `sales_invoices` - Invoice header
- `sales_invoice_items` - Invoice line items
- `customer_payments` - Customer payment records
- `customer_payment_allocations` - Payment to invoice allocations

## Project Structure

```
enterprise/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── ItemController.php
│   │   │   ├── PurchaseOrderController.php
│   │   │   ├── ItemReceiptController.php
│   │   │   ├── VendorBillController.php
│   │   │   └── SalesOrderController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php
│   ├── Models/
│   │   ├── User.php (with role switching)
│   │   ├── Subsidiary.php
│   │   ├── Department.php, Location.php
│   │   ├── Item.php, ItemAccount.php, InventoryStock.php
│   │   ├── Vendor.php, PurchaseOrder.php, VendorBill.php
│   │   ├── Customer.php, SalesOrder.php, SalesInvoice.php
│   │   └── ...
├── database/
│   ├── migrations/
│   │   ├── 2025_01_01_000100_add_roles_to_users_table.php
│   │   ├── 2025_01_01_000200_create_master_tables.php
│   │   ├── 2025_01_01_000300_create_items_tables.php
│   │   ├── 2025_01_01_000400_create_ptp_tables.php
│   │   └── 2025_01_01_000500_create_otc_tables.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── dashboard/
│       ├── items/
│       ├── po/ (purchase orders)
│       ├── bills/ (vendor bills)
│       └── sales-orders/
├── routes/
│   └── web.php
└── bootstrap/
    └── app.php
```

## Key Features

### Role Switching
After login, admin users can switch between roles in the sidebar dropdown. The interface automatically updates to show only relevant menu items for the current role.

### Access Control
All routes are protected with role middleware. Unauthorized access returns a 403 Forbidden error.

### Workflow Support
- **PTP**: PO → Receipt → Bill → Payment
- **OTC**: SO (approval) → Pick → Pack → Ship → Invoice → Payment

## API Routes

All routes require authentication and are prefixed with role checks.

### Item Management
- `GET /items` - List items
- `GET /items/create` - Create form
- `POST /items` - Store item
- `GET /items/{item}/edit` - Edit form
- `PUT /items/{item}` - Update item
- `DELETE /items/{item}` - Delete item

### Purchase Orders
- `GET /purchase-orders` - List POs
- `GET /purchase-orders/create` - Create form
- `POST /purchase-orders` - Store PO
- `GET /purchase-orders/{po}` - View PO
- `POST /purchase-orders/{po}/approve` - Approve PO

### Item Receipts
- `GET /item-receipts/po/{po}/create` - Receive items
- `POST /item-receipts/po/{po}` - Store receipt

### Vendor Bills
- `GET /bills` - List bills
- `GET /bills/create` - Create form
- `POST /bills` - Store bill
- `POST /bills/{bill}/approve` - Approve bill
- `POST /bills/{bill}/reject` - Reject bill

### Sales Orders
- `GET /sales-orders` - List SOs
- `GET /sales-orders/create` - Create form
- `POST /sales-orders` - Store SO
- `POST /sales-orders/{so}/request-approval` - Submit for approval
- `POST /sales-orders/{so}/approve` - Approve SO
- `POST /sales-orders/{so}/reject` - Reject SO

## Development Notes

### Adding New Items
Use the Item Creation screen to add:
1. **Inventory Items**: Require COGS, Asset, and Income accounts
2. **Non-Inventory Items**: Require Income and Expense accounts
3. **Service Items**: Require Income account only

### Creating Purchase Orders
1. Select vendor and location
2. Add items with quantities and prices
3. Submit for approval (if required by role)
4. Receive items (creates inventory stock)
5. Create bill from received PO
6. Approve bill
7. Pay bill

### Creating Sales Orders
1. Select customer
2. Add items with quantities and prices
3. Submit for approval (sales rep) or approve directly (sales manager)
4. Pick, Pack, and Ship
5. Generate invoice from shipped order
6. Receive payment

## Customization

### Adding New Roles
1. Add role to seeder in `available_roles` JSON
2. Create middleware checks in controllers
3. Add sidebar menu items in layout
4. Create specific views if needed

### Adding New Modules
1. Create migrations for new tables
2. Create corresponding models
3. Create controllers with role checks
4. Add routes
5. Create views

## Troubleshooting

### Database Connection Errors
- Ensure PostgreSQL is running
- Check .env database credentials
- Verify database exists: `createdb enterprise`

### Authentication Errors
- Run migrations: `php artisan migrate`
- Seed database: `php artisan db:seed`
- Clear cache: `php artisan cache:clear`

### Permission Errors
- Check role-middleware configuration in `bootstrap/app.php`
- Verify user roles in database: `SELECT id, email, current_role, available_roles FROM users;`

## Future Enhancements

- [ ] PDF report generation
- [ ] Email notifications for approvals
- [ ] Inventory reorder automation
- [ ] Batch operations
- [ ] Multi-currency support
- [ ] Audit logging
- [ ] API for mobile app
- [ ] Real-time dashboard updates
- [ ] Advanced financial reporting
- [ ] Inventory forecasting

## License

This project is proprietary and for educational purposes.

## Support

For issues or questions, please contact the development team.
