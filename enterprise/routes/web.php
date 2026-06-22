<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ItemReceiptController;
use App\Http\Controllers\VendorBillController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\OrderFulfillmentController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\CustomerPaymentController;

// Auth Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-role', [DashboardController::class, 'switchRole'])->name('switch-role');

    // Items Management
    Route::resource('items', ItemController::class);
    // Stock management routes for admin
    Route::get('items/{item}/stocks', [ItemController::class, 'manageStocks'])->name('items.manageStocks');
    Route::get('items/{item}/stock/{location}/edit', [ItemController::class, 'editStock'])->name('items.editStock');
    Route::post('items/{item}/stock/{location}', [ItemController::class, 'updateStock'])->name('items.updateStock');

    // Purchase Orders
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    });

    // Item Receipts
    Route::prefix('item-receipts')->group(function () {
        Route::get('/po/{purchaseOrder}/create', [ItemReceiptController::class, 'create'])->name('item-receipts.create');
        Route::post('/po/{purchaseOrder}', [ItemReceiptController::class, 'store'])->name('item-receipts.store');
    });

    // Vendor Bills
    Route::prefix('bills')->group(function () {
        Route::get('/', [VendorBillController::class, 'index'])->name('bills.index');
        Route::get('/create', [VendorBillController::class, 'create'])->name('bills.create');
        Route::post('/', [VendorBillController::class, 'store'])->name('bills.store');
        Route::get('/{bill}', [VendorBillController::class, 'show'])->name('bills.show');
        Route::post('/{bill}/submit', [VendorBillController::class, 'submit'])->name('bills.submit');
        Route::post('/{bill}/approve', [VendorBillController::class, 'approve'])->name('bills.approve');
        Route::post('/{bill}/reject', [VendorBillController::class, 'reject'])->name('bills.reject');
    });

    // Vendor Bill Payments
    Route::prefix('bill-payments')->group(function () {
        Route::get('/', [BillPaymentController::class, 'index'])->name('bill-payments.index');
        Route::get('/{payment}', [BillPaymentController::class, 'show'])->name('bill-payments.show');
    });
    Route::get('/bills/{bill}/payments/create', [BillPaymentController::class, 'create'])->name('bill-payments.create');
    Route::post('/bills/{bill}/payments', [BillPaymentController::class, 'store'])->name('bill-payments.store');

    // Sales Orders
    Route::prefix('sales-orders')->group(function () {
        Route::get('/', [SalesOrderController::class, 'index'])->name('sales-orders.index');
        Route::get('/create', [SalesOrderController::class, 'create'])->name('sales-orders.create');
        Route::post('/', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('/{salesOrder}/edit', [SalesOrderController::class, 'edit'])->name('sales-orders.edit');
        Route::put('/{salesOrder}', [SalesOrderController::class, 'update'])->name('sales-orders.update');
        Route::post('/{salesOrder}/request-approval', [SalesOrderController::class, 'requestApproval'])->name('sales-orders.request-approval');
        Route::post('/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('sales-orders.approve');
        Route::post('/{salesOrder}/reject', [SalesOrderController::class, 'reject'])->name('sales-orders.reject');
        Route::post('/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
        Route::get('/{salesOrder}/pick', [OrderFulfillmentController::class, 'createPick'])->name('sales-orders.pick.create');
        Route::post('/{salesOrder}/pick', [OrderFulfillmentController::class, 'storePick'])->name('sales-orders.pick.store');
        Route::get('/{salesOrder}/pack', [OrderFulfillmentController::class, 'createPack'])->name('sales-orders.pack.create');
        Route::post('/{salesOrder}/pack', [OrderFulfillmentController::class, 'storePack'])->name('sales-orders.pack.store');
        Route::get('/{salesOrder}/ship', [OrderFulfillmentController::class, 'createShip'])->name('sales-orders.ship.create');
        Route::post('/{salesOrder}/ship', [OrderFulfillmentController::class, 'storeShip'])->name('sales-orders.ship.store');
        Route::get('/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
    });

    // Sales Invoices
    Route::prefix('sales-invoices')->group(function () {
        Route::get('/', [SalesInvoiceController::class, 'index'])->name('sales-invoices.index');
        Route::get('/{invoice}', [SalesInvoiceController::class, 'show'])->name('sales-invoices.show');
        Route::post('/{invoice}/submit', [SalesInvoiceController::class, 'submit'])->name('sales-invoices.submit');
        Route::post('/{invoice}/approve', [SalesInvoiceController::class, 'approve'])->name('sales-invoices.approve');
        Route::post('/{invoice}/reject', [SalesInvoiceController::class, 'reject'])->name('sales-invoices.reject');
        Route::post('/{invoice}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales-invoices.cancel');
    });
    Route::get('/sales-orders/{salesOrder}/invoice', [SalesInvoiceController::class, 'create'])->name('sales-invoices.create');
    Route::post('/sales-orders/{salesOrder}/invoice', [SalesInvoiceController::class, 'store'])->name('sales-invoices.store');

    // Customer Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [CustomerPaymentController::class, 'index'])->name('payments.index');
        Route::get('/create', [CustomerPaymentController::class, 'create'])->name('payments.create');
        Route::post('/', [CustomerPaymentController::class, 'store'])->name('payments.store');
        Route::get('/customer/{customerId}/invoices', [CustomerPaymentController::class, 'getCustomerInvoices'])->name('payments.customer-invoices');
        Route::get('/{payment}/edit', [CustomerPaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/{payment}', [CustomerPaymentController::class, 'update'])->name('payments.update');
        Route::post('/{payment}/allocate', [CustomerPaymentController::class, 'allocate'])->name('payments.allocate');
        Route::delete('/allocations/{allocation}', [CustomerPaymentController::class, 'removeAllocation'])->name('payments.remove-allocation');
        Route::get('/{payment}', [CustomerPaymentController::class, 'show'])->name('payments.show');
    });
});
