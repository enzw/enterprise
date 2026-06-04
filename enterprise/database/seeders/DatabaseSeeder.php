<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subsidiary;
use App\Models\Department;
use App\Models\Location;
use App\Models\Account;
use App\Models\TaxSchedule;
use App\Models\Item;
use App\Models\ItemAccount;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create subsidiaries
        $subsidiary = Subsidiary::create([
            'name' => 'United States - West',
            'code' => 'US-WEST',
            'country' => 'United States',
            'address' => '123 Main Street, Los Angeles, CA',
            'is_active' => true,
        ]);

        // Create departments
        Department::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'Purchasing',
            'code' => 'PURCH',
            'is_active' => true,
        ]);

        Department::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'Inventory',
            'code' => 'INV',
            'is_active' => true,
        ]);

        // Create locations
        Location::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'US Warehouse 1',
            'code' => 'US-1',
            'address' => '100 Warehouse Blvd, Los Angeles, CA',
            'is_warehouse' => true,
            'is_active' => true,
        ]);

        Location::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'US Warehouse 2',
            'code' => 'US-2',
            'address' => '200 Warehouse Blvd, San Francisco, CA',
            'is_warehouse' => true,
            'is_active' => true,
        ]);

        // Create accounts
        $account1000 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '1000',
            'name' => 'Cash - Checking',
            'type' => 'asset',
            'balance' => 50000,
            'is_active' => true,
        ]);

        $account1010 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '1010',
            'name' => 'US Checking Account',
            'type' => 'asset',
            'balance' => 100000,
            'is_active' => true,
        ]);

        $account1110 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '1110',
            'name' => 'Accounts Receivable',
            'type' => 'asset',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account1210 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '1210',
            'name' => 'Inventory Asset',
            'type' => 'asset',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account2110 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '2110',
            'name' => 'Accounts Payable',
            'type' => 'liability',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account4010 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '4010',
            'name' => 'Sales Revenue',
            'type' => 'income',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account5010 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '5010',
            'name' => 'Cost of Goods Sold',
            'type' => 'expense',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account5800 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '5800',
            'name' => 'Inventory Adjustment',
            'type' => 'expense',
            'balance' => 0,
            'is_active' => true,
        ]);

        $account6081 = Account::create([
            'subsidiary_id' => $subsidiary->id,
            'number' => '6081',
            'name' => 'Purchase Expense',
            'type' => 'expense',
            'balance' => 0,
            'is_active' => true,
        ]);

        // Create tax schedules
        $taxableTax = TaxSchedule::create([
            'name' => 'Taxable',
            'rate' => 8.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $nonTaxableTax = TaxSchedule::create([
            'name' => 'Non-Taxable',
            'rate' => 0.00,
            'is_taxable' => false,
            'is_active' => true,
        ]);

        // Create items
        $itemInventory = Item::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'Exercise Inventory Item',
            'sku' => 'EXE-INV-001',
            'type' => 'inventory',
            'units_type' => 'Each',
            'base_price' => 200,
            'purchase_price' => 125,
            'is_active' => true,
        ]);

        ItemAccount::create([
            'item_id' => $itemInventory->id,
            'cogs_account_id' => $account5010->id,
            'asset_account_id' => $account1210->id,
            'income_account_id' => $account4010->id,
            'tax_schedule_id' => $taxableTax->id,
        ]);

        // Create vendor
        Vendor::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'Apple Store',
            'vendor_code' => 'APPLE-001',
            'email' => 'vendor@apple.com',
            'phone' => '555-1234',
            'address' => 'Apple Store, Cupertino, CA',
            'credit_limit' => 50000,
            'payment_terms' => 'net30',
            'is_active' => true,
        ]);

        // Create customer
        Customer::create([
            'subsidiary_id' => $subsidiary->id,
            'name' => 'ABC Corporation',
            'customer_code' => 'CUST-001',
            'email' => 'contact@abc.com',
            'phone' => '555-5678',
            'address' => '456 Business Ave, San Francisco, CA',
            'credit_limit' => 100000,
            'is_active' => true,
        ]);

        // Create users
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'admin',
            'current_role' => 'admin',
            'available_roles' => json_encode(['admin', 'purchasing_manager', 'inventory_manager', 'ap_analyst', 'accounting_manager', 'sales_representative', 'sales_manager', 'ar_analyst']),
            'subsidiary_id' => $subsidiary->id,
        ]);

        User::create([
            'name' => 'Purchasing Manager',
            'email' => 'purchasing@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'purchasing_manager',
            'current_role' => 'purchasing_manager',
            'available_roles' => json_encode(['purchasing_manager']),
            'subsidiary_id' => $subsidiary->id,
        ]);

        User::create([
            'name' => 'Inventory Manager',
            'email' => 'inventory@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'inventory_manager',
            'current_role' => 'inventory_manager',
            'available_roles' => json_encode(['inventory_manager']),
            'subsidiary_id' => $subsidiary->id,
        ]);

        User::create([
            'name' => 'AP Analyst',
            'email' => 'ap@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'ap_analyst',
            'current_role' => 'ap_analyst',
            'available_roles' => json_encode(['ap_analyst']),
            'subsidiary_id' => $subsidiary->id,
        ]);

        User::create([
            'name' => 'Sales Representative',
            'email' => 'sales@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'sales_representative',
            'current_role' => 'sales_representative',
            'available_roles' => json_encode(['sales_representative']),
            'subsidiary_id' => $subsidiary->id,
        ]);
    }
}
