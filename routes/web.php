<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanLabelController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data — semua peran dapat melihat, hanya admin yang dapat mengubah.
    Route::resource('categories', CategoryController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middlewareFor(['store', 'update', 'destroy'], 'role:admin');
    Route::resource('units', UnitController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middlewareFor(['store', 'update', 'destroy'], 'role:admin');
    Route::resource('suppliers', SupplierController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middlewareFor(['store', 'update', 'destroy'], 'role:admin');
    Route::resource('locations', LocationController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middlewareFor(['store', 'update', 'destroy'], 'role:admin');
    Route::resource('products', ProductController::class)
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:admin');

    // Transaksi — semua peran dapat melihat, admin & staf dapat membuat/mengubah.
    Route::resource('stock-ins', StockInController::class)
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:admin,staff');
    Route::post('stock-ins/{stockIn}/post', [StockInController::class, 'post'])
        ->middleware('role:admin,staff')
        ->name('stock-ins.post');
    Route::resource('stock-outs', StockOutController::class)
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:admin,staff');
    Route::post('stock-outs/{stockOut}/post', [StockOutController::class, 'post'])
        ->middleware('role:admin,staff')
        ->name('stock-outs.post');
    Route::resource('stock-adjustments', StockAdjustmentController::class)
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:admin,staff');
    Route::post('stock-adjustments/{stockAdjustment}/post', [StockAdjustmentController::class, 'post'])
        ->middleware('role:admin,staff')
        ->name('stock-adjustments.post');

    // Inventori & Laporan
    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
    Route::prefix('scan-label')->name('scan-label.')->middleware('role:admin,staff')->group(function () {
        Route::get('/', [ScanLabelController::class, 'index'])->name('index');
        Route::get('lookup', [ScanLabelController::class, 'lookup'])->name('lookup');
        Route::post('print', [ScanLabelController::class, 'print'])->name('print');
        Route::get('barcode/{code}', [ScanLabelController::class, 'barcode'])
            ->where('code', '[A-Za-z0-9\-_.]+')
            ->name('barcode');
    });
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('stock/export', [ReportController::class, 'exportStock'])->name('stock.export');
        Route::get('low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('low-stock/export', [ReportController::class, 'exportLowStock'])->name('low-stock.export');
        Route::get('expiry', [ReportController::class, 'expiry'])->name('expiry');
        Route::get('expiry/export', [ReportController::class, 'exportExpiry'])->name('expiry.export');
        Route::get('movements', [ReportController::class, 'movements'])->name('movements');
        Route::get('movements/export', [ReportController::class, 'exportMovements'])->name('movements.export');
    });
});

require __DIR__.'/auth.php';
