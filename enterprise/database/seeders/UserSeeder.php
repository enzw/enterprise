<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subsidiary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first subsidiary or create default
        $subsidiary = Subsidiary::first();
        if (!$subsidiary) {
            $subsidiary = Subsidiary::create([
                'name' => 'United States - West',
                'code' => 'US-WEST',
                'country' => 'United States',
                'address' => '123 Main Street, Los Angeles, CA',
                'is_active' => true,
            ]);
        }

        // Admin User - Can access all roles
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'admin',
            'current_role' => 'admin',
            'available_roles' => [
                'admin',
                'purchasing_manager',
                'inventory_manager',
                'ap_analyst',
                'accounting_manager',
                'sales_representative',
                'sales_manager',
                'ar_analyst'
            ],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // Purchasing Manager
        User::create([
            'name' => 'Purchasing Manager',
            'email' => 'purchasing@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'purchasing_manager',
            'current_role' => 'purchasing_manager',
            'available_roles' => ['purchasing_manager'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // Inventory Manager
        User::create([
            'name' => 'Inventory Manager',
            'email' => 'inventory@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'inventory_manager',
            'current_role' => 'inventory_manager',
            'available_roles' => ['inventory_manager'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // A/P Analyst
        User::create([
            'name' => 'AP Analyst',
            'email' => 'ap@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'ap_analyst',
            'current_role' => 'ap_analyst',
            'available_roles' => ['ap_analyst'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // Accounting Manager
        User::create([
            'name' => 'Accounting Manager',
            'email' => 'accounting@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'accounting_manager',
            'current_role' => 'accounting_manager',
            'available_roles' => ['accounting_manager'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // Sales Representative
        User::create([
            'name' => 'Sales Representative',
            'email' => 'sales@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'sales_representative',
            'current_role' => 'sales_representative',
            'available_roles' => ['sales_representative'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // Sales Manager
        User::create([
            'name' => 'Sales Manager',
            'email' => 'sales_manager@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'sales_manager',
            'current_role' => 'sales_manager',
            'available_roles' => ['sales_manager'],
            'subsidiary_id' => $subsidiary->id,
        ]);

        // A/R Analyst
        User::create([
            'name' => 'AR Analyst',
            'email' => 'ar@example.com',
            'password' => Hash::make('password'),
            'default_role' => 'ar_analyst',
            'current_role' => 'ar_analyst',
            'available_roles' => ['ar_analyst'],
            'subsidiary_id' => $subsidiary->id,
        ]);
    }
}
