<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ItemReceiptController;
use App\Http\Controllers\VendorBillController;
use App\Http\Controllers\SalesOrderController;

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
        Route::post('/{bill}/approve', [VendorBillController::class, 'approve'])->name('bills.approve');
        Route::post('/{bill}/reject', [VendorBillController::class, 'reject'])->name('bills.reject');
    });

    // Sales Orders
    Route::prefix('sales-orders')->group(function () {
        Route::get('/', [SalesOrderController::class, 'index'])->name('sales-orders.index');
        Route::get('/create', [SalesOrderController::class, 'create'])->name('sales-orders.create');
        Route::post('/', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
        Route::post('/{salesOrder}/request-approval', [SalesOrderController::class, 'requestApproval'])->name('sales-orders.request-approval');
        Route::post('/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('sales-orders.approve');
        Route::post('/{salesOrder}/reject', [SalesOrderController::class, 'reject'])->name('sales-orders.reject');
    });
});
