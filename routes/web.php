<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\SettingController;
use App\Http\Controllers\Public\PublicOrderController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Dapur\DapurController;
use App\Http\Controllers\Owner\AuditController;
use App\Http\Controllers\Owner\ReportController;
use Illuminate\Support\Facades\Route;

// ==========================================================
# ROUTE PUBLIK (TANPA LOGIN)
# ==========================================================
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// ⬇️ QR SELF-ORDER PUBLIK — TANPA AUTH (PRD 17.3)
// URL: /order/{qr_code} — cocok dengan yang di-generate TableController
Route::middleware(['warung.open', 'throttle:60,1'])->prefix('order')->name('public.')->group(function () {
    Route::get('/{table:qr_code}', [PublicOrderController::class, 'menu'])->name('menu');
    Route::post('/{table:qr_code}', [PublicOrderController::class, 'store'])->name('store');
    Route::get('/{table:qr_code}/confirm', [PublicOrderController::class, 'confirm'])->name('confirm');
});

// TEST SEMENTARA — hapus setelah selesai
// Route::get('/test-open', fn () => 'HALAMAN ORDER DI SINI')->middleware('warung.open');

// ==========================================================
# ROUTE YANG MEMBUTUHKAN LOGIN (AUTH)
# ==========================================================
Route::middleware(['auth'])->group(function () {

    // --- ROUTE OWNER ---
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

        // ⬇️ QR CODE PER MEJA — DI DALAM GROUP, sebelum Route::resource
        Route::get('/tables/{table}/qr', [TableController::class, 'qrImage'])->name('tables.qr-image');
        Route::get('/tables/{table}/qr/print', [TableController::class, 'printQr'])->name('tables.qr-print');
        Route::post('/tables/{table}/qr/regenerate', [TableController::class, 'regenerateQr'])->name('tables.qr-regenerate');

        Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
        Route::resource('tables', TableController::class)->only(['store', 'update', 'destroy']);
        Route::post('/settings/toggle-open', [SettingController::class, 'toggleOpen'])->name('settings.toggle');

        // ROUTE MANAJEMEN MENU
        Route::resource('menus', MenuController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/menus/{menu}/toggle', [MenuController::class, 'toggleAvailability'])->name('menus.toggle');

        // ROUTE KATEGORI
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    });

    // --- ROUTE KASIR ---
    Route::middleware('role:kasir,owner')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/pos', [KasirController::class, 'pos'])->name('pos');
        Route::post('/orders/{order}/pay', [KasirController::class, 'pay'])->name('orders.pay');
    });

    // --- ROUTE PELAYAN ---
    Route::middleware('role:pelayan')->prefix('pelayan')->name('pelayan.')->group(function () {
        // Nanti: Route::get('/order', [PelayanController::class, 'order'])->name('order');
    });

    // --- ROUTE DAPUR ---
    Route::middleware('role:dapur,owner')->prefix('dapur')->name('dapur.')->group(function () {
    Route::get('/queue', [DapurController::class, 'queue'])->name('queue');
    Route::post('/orders/{order}/status', [DapurController::class, 'updateStatus'])->name('orders.status');
    });
});

// Default route root
Route::get('/', function () {
    return redirect()->route('login');
});