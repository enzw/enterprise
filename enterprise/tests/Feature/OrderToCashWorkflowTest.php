<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\ItemAccount;
use App\Models\Location;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Subsidiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderToCashWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Subsidiary $subsidiary;

    private Location $location;

    private Customer $customer;

    private Item $item;

    private Account $cashAccount;

    private Account $arAccount;

    private Account $inventoryAccount;

    private Account $incomeAccount;

    private Account $cogsAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subsidiary = Subsidiary::create([
            'name' => 'OTC Subsidiary',
            'code' => 'OTC',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);

        $this->location = Location::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'OTC Warehouse',
            'code' => 'OTC-WH',
            'is_warehouse' => true,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'OTC Customer',
            'customer_code' => 'OTC-CUST',
            'credit_limit' => 10000,
            'credit_used' => 0,
            'is_active' => true,
        ]);

        $this->cashAccount = $this->account('1000', 'Cash', 'asset');
        $this->arAccount = $this->account('1110', 'Accounts Receivable', 'asset');
        $this->inventoryAccount = $this->account('1210', 'Inventory Asset', 'asset', 1000);
        $this->incomeAccount = $this->account('4010', 'Sales Revenue', 'income');
        $this->cogsAccount = $this->account('5010', 'COGS', 'expense');

        $this->item = Item::create([
            'subsidiary_id' => $this->subsidiary->id,
            'name' => 'OTC Inventory Item',
            'sku' => 'OTC-ITEM',
            'type' => 'inventory',
            'units_type' => 'Each',
            'base_price' => 200,
            'purchase_price' => 100,
            'is_active' => true,
        ]);

        ItemAccount::create([
            'item_id' => $this->item->id,
            'cogs_account_id' => $this->cogsAccount->id,
            'asset_account_id' => $this->inventoryAccount->id,
            'income_account_id' => $this->incomeAccount->id,
        ]);

        InventoryStock::create([
            'item_id' => $this->item->id,
            'location_id' => $this->location->id,
            'quantity_on_hand' => 10,
            'quantity_reserved' => 0,
            'quantity_on_order' => 0,
        ]);
    }

    public function test_complete_order_to_cash_workflow_with_partial_pick_and_payment(): void
    {
        $salesRep = $this->user('sales_representative');

        $this->actingAs($salesRep)
            ->post(route('sales-orders.store'), [
                'customer_id' => $this->customer->id,
                'location_id' => $this->location->id,
                'subsidiary_id' => $this->subsidiary->id,
                'order_date' => '2026-06-20',
                'requested_delivery_date' => '2026-06-25',
                'currency_code' => 'USD',
                'po_reference' => 'CUSTOMER-PO-001',
                'items' => [[
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 200,
                ]],
            ])
            ->assertRedirect();

        $salesOrder = SalesOrder::with('items')->firstOrFail();
        $line = $salesOrder->items->first();
        $this->assertSame('draft', $salesOrder->status);
        $this->assertEquals(2000, $salesOrder->total);

        $this->actingAs($salesRep)
            ->post(route('sales-orders.request-approval', $salesOrder))
            ->assertRedirect();

        $salesManager = $this->user('sales_manager');
        $this->actingAs($salesManager)
            ->post(route('sales-orders.approve', $salesOrder))
            ->assertRedirect();

        $this->assertSame('approved', $salesOrder->fresh()->status);

        $inventoryManager = $this->user('inventory_manager');
        $this->actingAs($inventoryManager)
            ->post(route('sales-orders.pick.store', $salesOrder), [
                'date' => '2026-06-21',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 4,
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('partial', $salesOrder->fresh()->status);
        $this->assertSame(4, $line->fresh()->quantity_fulfilled);
        $this->assertSame(4, InventoryStock::firstOrFail()->quantity_reserved);

        $this->actingAs($inventoryManager)
            ->post(route('sales-orders.pick.store', $salesOrder), [
                'date' => '2026-06-22',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 6,
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('fulfilled', $salesOrder->fresh()->status);
        $this->assertSame(10, $line->fresh()->quantity_fulfilled);
        $this->assertSame(10, InventoryStock::firstOrFail()->quantity_reserved);

        $this->actingAs($inventoryManager)
            ->post(route('sales-orders.pack.store', $salesOrder), [
                'date' => '2026-06-22',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 10,
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('packed', $salesOrder->fresh()->status);
        $this->assertSame(10, $line->fresh()->quantity_packed);

        $this->actingAs($inventoryManager)
            ->post(route('sales-orders.ship.store', $salesOrder), [
                'date' => '2026-06-23',
                'carrier' => 'DHL',
                'tracking_number' => 'DHL-001',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 10,
                ]],
            ])
            ->assertRedirect();

        $stock = InventoryStock::firstOrFail();
        $this->assertSame('shipped', $salesOrder->fresh()->status);
        $this->assertSame(0, $stock->quantity_on_hand);
        $this->assertSame(0, $stock->quantity_reserved);
        $this->assertEquals(1000, $this->cogsAccount->fresh()->balance);
        $this->assertEquals(0, $this->inventoryAccount->fresh()->balance);

        $arAnalyst = $this->user('ar_analyst');
        $this->actingAs($arAnalyst)
            ->post(route('sales-invoices.store', $salesOrder), [
                'invoice_date' => '2026-06-23',
                'due_date' => '2026-07-23',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 10,
                ]],
            ])
            ->assertRedirect();

        $invoice = SalesInvoice::firstOrFail();
        $this->assertSame('draft', $invoice->status);
        $this->assertEquals(2000, $invoice->total);
        $this->assertSame(10, $line->fresh()->quantity_invoiced);
        $this->assertSame('invoiced', $salesOrder->fresh()->status);

        $accountingManager = $this->user('accounting_manager');
        $this->actingAs($accountingManager)
            ->post(route('sales-invoices.approve', $invoice))
            ->assertRedirect();

        $this->assertSame('approved', $invoice->fresh()->status);
        $this->assertEquals(2000, $this->arAccount->fresh()->balance);
        $this->assertEquals(2000, $this->incomeAccount->fresh()->balance);
        $this->assertEquals(2000, $this->customer->fresh()->credit_used);

        $this->actingAs($arAnalyst)
            ->post(route('payments.store'), [
                'customer_id' => $this->customer->id,
                'payment_date' => '2026-06-24',
                'amount' => 800,
                'payment_method' => 'bank_transfer',
                'cash_account_id' => $this->cashAccount->id,
                'reference_no' => 'PAY-OTC-001',
                'allocations' => [[
                    'invoice_id' => $invoice->id,
                    'amount' => 800,
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertEquals(800, $invoice->fresh()->amount_paid);
        $this->assertEquals(800, $this->cashAccount->fresh()->balance);
        $this->assertEquals(1200, $this->arAccount->fresh()->balance);
        $this->assertEquals(1200, $this->customer->fresh()->credit_used);

        $this->actingAs($arAnalyst)
            ->post(route('payments.store'), [
                'customer_id' => $this->customer->id,
                'payment_date' => '2026-06-25',
                'amount' => 1200,
                'payment_method' => 'bank_transfer',
                'cash_account_id' => $this->cashAccount->id,
                'reference_no' => 'PAY-OTC-002',
                'allocations' => [[
                    'invoice_id' => $invoice->id,
                    'amount' => 1200,
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEquals(2000, $invoice->fresh()->amount_paid);
        $this->assertEquals(2000, $this->cashAccount->fresh()->balance);
        $this->assertEquals(0, $this->arAccount->fresh()->balance);
        $this->assertEquals(0, $this->customer->fresh()->credit_used);
        $this->assertSame(2, CustomerPayment::count());
    }

    public function test_credit_stock_and_quantity_limits_are_enforced(): void
    {
        $salesRep = $this->user('sales_representative');
        $salesManager = $this->user('sales_manager');
        $inventoryManager = $this->user('inventory_manager');

        $this->customer->update(['credit_limit' => 100]);

        $this->actingAs($salesRep)->post(route('sales-orders.store'), [
            'customer_id' => $this->customer->id,
            'location_id' => $this->location->id,
            'subsidiary_id' => $this->subsidiary->id,
            'order_date' => '2026-06-20',
            'currency_code' => 'USD',
            'items' => [[
                'item_id' => $this->item->id,
                'quantity' => 5,
                'unit_price' => 200,
            ]],
        ]);

        $salesOrder = SalesOrder::with('items')->firstOrFail();
        $line = $salesOrder->items->first();
        $this->actingAs($salesRep)->post(route('sales-orders.request-approval', $salesOrder));

        $this->actingAs($salesManager)
            ->from(route('sales-orders.show', $salesOrder))
            ->post(route('sales-orders.approve', $salesOrder))
            ->assertSessionHasErrors('credit');

        $this->customer->update(['credit_limit' => 10000]);
        InventoryStock::firstOrFail()->update(['quantity_on_hand' => 4]);

        $this->actingAs($salesManager)
            ->from(route('sales-orders.show', $salesOrder))
            ->post(route('sales-orders.approve', $salesOrder))
            ->assertSessionHasErrors('stock');

        InventoryStock::firstOrFail()->update(['quantity_on_hand' => 10]);
        $this->actingAs($salesManager)->post(route('sales-orders.approve', $salesOrder));

        $this->actingAs($inventoryManager)
            ->from(route('sales-orders.pick.create', $salesOrder))
            ->post(route('sales-orders.pick.store', $salesOrder), [
                'date' => '2026-06-20',
                'items' => [[
                    'so_item_id' => $line->id,
                    'quantity' => 6,
                ]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, $line->fresh()->quantity_fulfilled);
        $this->assertSame(0, InventoryStock::firstOrFail()->quantity_reserved);
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
            'email' => $role.'@otc.test',
            'password' => 'password',
            'default_role' => $role,
            'current_role' => $role,
            'available_roles' => [$role],
            'subsidiary_id' => $this->subsidiary->id,
        ]);
    }
}
