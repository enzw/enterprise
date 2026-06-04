# Quick Reference - ERP System Commands

## Environment Setup

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Generate key
php artisan key:generate

# Create database (PostgreSQL)
createdb enterprise

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed
```

## Running the Application

```bash
# Start development server
php artisan serve

# Visit http://localhost:8000
```

## Test Credentials

```
Email: admin@example.com
Password: password

(All test users use 'password')
```

## Common Database Checks

```sql
-- View all users and roles
SELECT id, email, current_role, available_roles FROM users;

-- View items created
SELECT id, sku, name, type, is_active FROM items;

-- View purchase orders
SELECT po_number, status, total FROM purchase_orders;

-- View vendor bills
SELECT bill_number, status, total FROM vendor_bills;

-- View sales orders
SELECT so_number, status, total FROM sales_orders;

-- Check item accounting setup
SELECT i.name, ia.income_account_id, ia.tax_schedule_id 
FROM items i 
LEFT JOIN item_accounts ia ON i.id = ia.item_id;
```

## Useful Laravel Commands

```bash
# Clear cache
php artisan cache:clear

# Rebuild class loader
php artisan dump-autoload

# View all routes
php artisan route:list

# Fresh migration (warning: drops all data)
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName

# Create new controller
php artisan make:controller ControllerName

# Run specific seeder
php artisan db:seed --class=DatabaseSeeder

# Tinker (interactive shell)
php artisan tinker
```

## Troubleshooting Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:cache --force
php artisan route:cache

# Verify database connection
php artisan db:show

# Migrate specific migration
php artisan migrate --path=database/migrations/specific_file.php

# Rollback last migration
php artisan migrate:rollback

# Create new test user (in tinker)
php artisan tinker
>>> App\Models\User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')])
>>> exit
```

## File Locations

```
Views:              resources/views/
Models:             app/Models/
Controllers:        app/Http/Controllers/
Migrations:         database/migrations/
Seeders:            database/seeders/
Routes:             routes/web.php
Middleware:         app/Http/Middleware/
```

## Configuration Files

```
Environment:        .env
App Config:         config/app.php
Database:           config/database.php
Authentication:     config/auth.php
Bootstrap:          bootstrap/app.php (middleware)
```

## API Testing with curl

```bash
# Login
curl -X POST http://localhost:8000/login \
  -d "email=admin@example.com&password=password"

# Access dashboard
curl -X GET http://localhost:8000/dashboard \
  -H "Cookie: LARAVEL_SESSION=..."

# List items
curl -X GET http://localhost:8000/items \
  -H "Cookie: LARAVEL_SESSION=..."
```

## Database Backup/Restore

```bash
# Backup
pg_dump enterprise > backup.sql

# Restore
psql enterprise < backup.sql

# Backup specific tables
pg_dump enterprise --table=users > users_backup.sql
```

## Performance Monitoring

```sql
-- View slow queries
SELECT mean_exec_time, calls, query 
FROM pg_stat_statements 
ORDER BY mean_exec_time DESC LIMIT 10;

-- Check table sizes
SELECT 
  schemaname,
  tablename,
  pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname != 'information_schema'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

## Development Tips

1. **Use Tinker for quick testing:**
   ```
   php artisan tinker
   >>> User::find(1)->switchRole('purchasing_manager')
   ```

2. **Debug with dd():**
   ```php
   dd($purchaseOrder); // dump and die
   dump($items); // dump only
   ```

3. **Use Laravel Log:**
   ```php
   \Log::info('Message', ['context' => $data]);
   tail -f storage/logs/laravel.log
   ```

4. **Enable query logging:**
   ```php
   DB::enableQueryLog();
   // ... do queries
   dd(DB::getQueryLog());
   ```

## Module Navigation Map

```
Dashboard (/)
├── Item Management
│   ├── List Items (/items)
│   └── Create Item (/items/create)
├── Procurement (PTP)
│   ├── Purchase Orders (/purchase-orders)
│   ├── Create PO (/purchase-orders/create)
│   └── Item Receipts (/item-receipts/po/{id}/create)
├── Bills & Payments
│   ├── Vendor Bills (/bills)
│   ├── Create Bill (/bills/create)
│   └── Approve Bills (/bills/{id}/approve)
└── Sales (OTC)
    ├── Sales Orders (/sales-orders)
    ├── Create SO (/sales-orders/create)
    └── Approve SO (/sales-orders/{id}/approve)
```

## Role Capabilities Matrix

| Role | Items | PO | Receive | Bill | Approve Bill | SO | Approve SO |
|------|-------|----|---------|----- |-------------|-------|------------|
| Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Purchasing Mgr | ✓ | ✓ | - | - | - | - | - |
| Inventory Mgr | - | ✓ | ✓ | - | - | - | - |
| A/P Analyst | - | - | - | ✓ | - | - | - |
| Accounting Mgr | - | - | - | - | ✓ | - | - |
| Sales Rep | - | - | - | - | - | ✓ | - |
| Sales Manager | - | - | - | - | - | ✓ | ✓ |
| A/R Analyst | - | - | - | - | - | - | - |

---

**For full documentation, see:** README_ERP.md and SETUP_GUIDE.md
