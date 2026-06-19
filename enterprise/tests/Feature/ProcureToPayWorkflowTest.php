<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\ItemAccount;
use App\Models\ItemReceipt;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\Subsidiary;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcureToPayWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Subsidiary $subsidiary;

    private Vendor $vendor;

    private Location $location;

    private Department $department;

    private Item $item;

    private Account $cashAccount;

    private Account $apAccount;

    private Account $inventoryAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subsidiary = Subsidiary::create([
            'name' => 'Test Subsidiary',
            'code' => 'TEST',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);

        $this->location = Location::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-01',
            'is_warehouse' => true,
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'Purchasing',
            'code' => 'PUR',
            'is_active' => true,
        ]);

        $this->vendor = Vendor::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'Test Vendor',
            'vendor_code' => 'V-001',
            'credit_limit' => 10000,
            'payment_terms' => 'net30',
            'is_active' => true,
        ]);

        $this->cashAccount = $this->account('1000', 'Cash', 'asset', 5000);
        $this->inventoryAccount = $this->account('1210', 'Inventory Asset', 'asset');
        $this->apAccount = $this->account('2110', 'Accounts Payable', 'liability');
        $incomeAccount = $this->account('4010', 'Sales Revenue', 'income');
        $cogsAccount = $this->account('5010', 'COGS', 'expense');
        $this->account('6081', 'Purchase Expense', 'expense');

        $this->item = Item::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'Inventory Item',
            'sku' => 'ITEM-001',
            'type' => 'inventory',
            'units_type' => 'Each',
            'purchase_price' => 100,
            'base_price' => 150,
            'is_active' => true,
        ]);

        ItemAccount::create([
            'item_id' => $this->item->id,
            'cogs_account_id' => $cogsAccount->id,
            'asset_account_id' => $this->inventoryAccount->id,
            'income_account_id' => $incomeAccount->id,
        ]);
    }

    public function test_complete_procure_to_pay_workflow_with_partial_receipt_and_payment(): void
    {
        $purchasingUser = $this->user('purchasing_manager');

        $this->actingAs($purchasingUser)
            ->post(route('purchase-orders.store'), [
                'vendor_id' => $this->vendor->id,
                'location_id' => $this->location->id,
                'subsidiary_id' => $this->subsidiary->id,
                'order_date' => '2026-06-20',
                'expected_delivery_date' => '2026-06-25',
                'memo' => 'P2P test order',
                'items' => [[
                    'item_id' => $this->item->id,
                    'department_id' => $this->department->id,
                    'quantity' => 10,
                    'unit_price' => 100,
                ]],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::with('items')->firstOrFail();
        $this->assertSame('draft', $purchaseOrder->status);
        $this->assertEquals(1000, $purchaseOrder->total);

        $this->actingAs($purchasingUser)
            ->post(route('purchase-orders.approve', $purchaseOrder))
            ->assertRedirect();

        $purchaseOrder->refresh();
        $stock = InventoryStock::firstOrFail();
        $this->assertSame('approved', $purchaseOrder->status);
        $this->assertSame(10, $stock->quantity_on_order);
        $this->assertSame(0, $stock->quantity_on_hand);

        $inventoryUser = $this->user('inventory_manager');
        $poItem = $purchaseOrder->items->first();

        $this->actingAs($inventoryUser)
            ->post(route('item-receipts.store', $purchaseOrder), [
                'receipt_date' => '2026-06-24',
                'memo' => 'First delivery',
                'items' => [[
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 4,
                ]],
            ])
            ->assertRedirect();

        $purchaseOrder->refresh();
        $stock->refresh();
        $this->assertSame('partial', $purchaseOrder->status);
        $this->assertSame(4, $stock->quantity_on_hand);
        $this->assertSame(6, $stock->quantity_on_order);

        $this->actingAs($inventoryUser)
            ->post(route('item-receipts.store', $purchaseOrder), [
                'receipt_date' => '2026-06-25',
                'memo' => 'Final delivery',
                'items' => [[
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 6,
                ]],
            ])
            ->assertRedirect();

        $purchaseOrder->refresh();
        $stock->refresh();
        $this->assertSame('received', $purchaseOrder->status);
        $this->assertSame(10, $stock->quantity_on_hand);
        $this->assertSame(0, $stock->quantity_on_order);
        $this->assertSame(2, ItemReceipt::count());

        $apUser = $this->user('ap_analyst');

        $this->actingAs($apUser)
            ->post(route('bills.store'), [
                'vendor_id' => $this->vendor->id,
                'purchase_order_id' => $purchaseOrder->id,
                'bill_number' => 'BILL-0001',
                'reference_no' => 'INV-VENDOR-001',
                'bill_date' => '2026-06-25',
                'due_date' => '2026-07-25',
                'items' => [[
                    'po_item_id' => $poItem->id,
                    'item_id' => $this->item->id,
                    'description' => 'Inventory Item',
                    'quantity' => 10,
                    'unit_price' => 100,
                    'line_amount' => 1000,
                ]],
            ])
            ->assertRedirect();

        $bill = VendorBill::firstOrFail();
        $this->assertSame('pending_approval', $bill->status);
        $this->assertSame(10, $poItem->fresh()->quantity_billed);

        $accountingUser = $this->user('accounting_manager');

        $this->actingAs($accountingUser)
            ->post(route('bills.approve', $bill))
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame('approved', $bill->status);
        $this->assertEquals(1000, $this->apAccount->fresh()->balance);
        $this->assertEquals(1000, $this->inventoryAccount->fresh()->balance);
        $this->assertEquals(1000, $this->vendor->fresh()->credit_used);

        $this->actingAs($apUser)
            ->post(route('bill-payments.store', $bill), [
                'payment_date' => '2026-06-26',
                'amount' => 400,
                'payment_method' => 'bank_transfer',
                'cash_account_id' => $this->cashAccount->id,
                'reference_no' => 'TRF-001',
            ])
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame('partial', $bill->status);
        $this->assertEquals(400, $bill->amount_paid);
        $this->assertEquals(4600, $this->cashAccount->fresh()->balance);
        $this->assertEquals(600, $this->apAccount->fresh()->balance);
        $this->assertEquals(600, $this->vendor->fresh()->credit_used);

        $this->actingAs($apUser)
            ->post(route('bill-payments.store', $bill), [
                'payment_date' => '2026-06-27',
                'amount' => 600,
                'payment_method' => 'bank_transfer',
                'cash_account_id' => $this->cashAccount->id,
                'reference_no' => 'TRF-002',
            ])
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertEquals(1000, $bill->amount_paid);
        $this->assertEquals(4000, $this->cashAccount->fresh()->balance);
        $this->assertEquals(0, $this->apAccount->fresh()->balance);
        $this->assertEquals(0, $this->vendor->fresh()->credit_used);
        $this->assertCount(2, $bill->payments);
    }

    public function test_receipts_and_bills_cannot_exceed_available_quantities(): void
    {
        $purchasingUser = $this->user('purchasing_manager');

        $this->actingAs($purchasingUser)->post(route('purchase-orders.store'), [
            'vendor_id' => $this->vendor->id,
            'location_id' => $this->location->id,
            'subsidiary_id' => $this->subsidiary->id,
            'order_date' => '2026-06-20',
            'items' => [[
                'item_id' => $this->item->id,
                'quantity' => 5,
                'unit_price' => 100,
            ]],
        ]);

        $purchaseOrder = PurchaseOrder::with('items')->firstOrFail();
        $poItem = $purchaseOrder->items->first();
        $this->actingAs($purchasingUser)->post(route('purchase-orders.approve', $purchaseOrder));

        $inventoryUser = $this->user('inventory_manager');
        $this->actingAs($inventoryUser)
            ->from(route('item-receipts.create', $purchaseOrder))
            ->post(route('item-receipts.store', $purchaseOrder), [
                'receipt_date' => '2026-06-20',
                'items' => [[
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 6,
                ]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, $poItem->fresh()->quantity_received);

        $this->actingAs($inventoryUser)->post(route('item-receipts.store', $purchaseOrder), [
            'receipt_date' => '2026-06-20',
            'items' => [[
                'po_item_id' => $poItem->id,
                'quantity_received' => 5,
            ]],
        ]);

        $apUser = $this->user('ap_analyst');
        $this->actingAs($apUser)
            ->from(route('bills.create'))
            ->post(route('bills.store'), [
                'vendor_id' => $this->vendor->id,
                'purchase_order_id' => $purchaseOrder->id,
                'bill_number' => 'BILL-OVER',
                'reference_no' => 'INV-OVER',
                'bill_date' => '2026-06-20',
                'items' => [[
                    'po_item_id' => $poItem->id,
                    'item_id' => $this->item->id,
                    'description' => 'Inventory Item',
                    'quantity' => 6,
                    'unit_price' => 100,
                    'line_amount' => 600,
                ]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, VendorBill::count());
        $this->assertSame(0, $poItem->fresh()->quantity_billed);
    }

    private function account(string $number, string $name, string $type, float $balance = 0): Account
    {
        return Account::create([
            'subsidiary_id' => $this->subsidiary->id,
            'number' => $number,
            'name' => $name,
            'type' => $type,
            'balance' => $balance,
            'is_active' => true,
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => $role.'@example.test',
            'password' => 'password',
            'default_role' => $role,
            'current_role' => $role,
            'available_roles' => [$role],
            'subsidiary_id' => $this->subsidiary->id,
        ]);
    }
}
