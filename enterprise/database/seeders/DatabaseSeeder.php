<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subsidiary;
use App\Models\MasterData;
use App\Models\Item;
use App\Models\ItemAccount;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Location;
use App\Models\Account;
use App\Models\TaxSchedule;
use App\Models\InventoryStock;

// Import classes from MasterData namespace
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create or retrieve subsidiary
        $subsidiary = Subsidiary::firstOrCreate(
            ['code' => 'US-WEST'],
            [
                'name' => 'United States - West',
                'country' => 'United States',
                'address' => '123 Main Street, Los Angeles, CA',
                'is_active' => true,
            ]
        );

        // Create or retrieve departments
        Department::firstOrCreate(
            ['code' => 'PURCH', 'subsidiary_id' => $subsidiary->id],
            ['name' => 'Purchasing', 'is_active' => true]
        );
        Department::firstOrCreate(
            ['code' => 'INV', 'subsidiary_id' => $subsidiary->id],
            ['name' => 'Inventory', 'is_active' => true]
        );

        // Create or retrieve locations
        Location::firstOrCreate(
            ['code' => 'US-1', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'US Warehouse 1',
                'address' => '100 Warehouse Blvd, Los Angeles, CA',
                'is_warehouse' => true,
                'is_active' => true,
            ]
        );
        Location::firstOrCreate(
            ['code' => 'US-2', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'US Warehouse 2',
                'address' => '200 Warehouse Blvd, San Francisco, CA',
                'is_warehouse' => true,
                'is_active' => true,
            ]
        );

        // Create or retrieve accounts
        $account1000 = Account::firstOrCreate(
            ['number' => '1000', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Cash - Checking',
                'type' => 'asset',
                'balance' => 50000,
                'is_active' => true,
            ]
        );
        $account1010 = Account::firstOrCreate(
            ['number' => '1010', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'US Checking Account',
                'type' => 'asset',
                'balance' => 100000,
                'is_active' => true,
            ]
        );
        $account1110 = Account::firstOrCreate(
            ['number' => '1110', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account1210 = Account::firstOrCreate(
            ['number' => '1210', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Inventory Asset',
                'type' => 'asset',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account2110 = Account::firstOrCreate(
            ['number' => '2110', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account4010 = Account::firstOrCreate(
            ['number' => '4010', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Sales Revenue',
                'type' => 'income',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account5010 = Account::firstOrCreate(
            ['number' => '5010', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Cost of Goods Sold',
                'type' => 'expense',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account5800 = Account::firstOrCreate(
            ['number' => '5800', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Inventory Adjustment',
                'type' => 'expense',
                'balance' => 0,
                'is_active' => true,
            ]
        );
        $account6081 = Account::firstOrCreate(
            ['number' => '6081', 'subsidiary_id' => $subsidiary->id],
            [
                'name' => 'Purchase Expense',
                'type' => 'expense',
                'balance' => 0,
                'is_active' => true,
            ]
        );

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

        // Additional demo item – iPad Pro 9.7" (32GB)
        $itemIpad = Item::firstOrCreate(
            ['sku' => 'IPAD-001'],
            [
                'subsidiary_id' => $subsidiary->id,
                'name' => 'iPad Pro 9.7 inch - 32GB',
                'type' => 'inventory',
                'units_type' => 'Each',
                'base_price' => 900,
                'purchase_price' => 600,
                'is_active' => true,
            ]
        );
        ItemAccount::firstOrCreate(
            ['item_id' => $itemIpad->id],
            [
                'cogs_account_id' => $account5010->id,
                'asset_account_id' => $account1210->id,
                'income_account_id' => $account4010->id,
                'tax_schedule_id' => $taxableTax->id,
            ]
        );
        // Seed inventory for iPad at US-1
        $location = Location::where('code', 'US-1')->first();
        InventoryStock::firstOrCreate([
            'item_id' => $itemIpad->id,
            'location_id' => $location->id,
        ], [
            'quantity_on_hand' => 100,
            'quantity_on_order' => 0,
            'quantity_reserved' => 0,
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

        // Redundant customer array removed – customer will be created later in the seeder.
        
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

        // Seed initial inventory for the test item
        $location = Location::where('code', 'US-1')->first();
        InventoryStock::create([
            'item_id' => $itemInventory->id,
            'location_id' => $location->id,
            'quantity_on_hand' => 100,
            'quantity_on_order' => 0,
            'quantity_reserved' => 0,
        ]);

        // Seed users via UserSeeder
        $this->call(UserSeeder::class);
    }
}
