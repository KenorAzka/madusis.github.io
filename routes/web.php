<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\DebtController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect Halaman Utama ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Nonaktifkan route auth bawaan laravel (karena kita pakai LoginController & Register kustom)
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// ==========================================
// ROUTE AUTENTIKASI MANUAL (LOGIN & REGISTER)
// ==========================================
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [CustomerAuthController::class, 'create'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'store']);

// ==========================================
// ROUTE LUPA PASSWORD & RESET PASSWORD
// ==========================================
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// ==========================================
// ROUTE VERIFIKASI EMAIL
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('cashier.index')->with('success', 'Email berhasil diverifikasi!');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Tautan verifikasi baru telah dikirim ke email Anda!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});


// Route Halaman Buku Utang & Detail Pembayaran
Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
Route::get('/debts/{id}', [DebtController::class, 'show'])->name('debts.show');
Route::post('/debts/{id}/pay', [DebtController::class, 'pay'])->name('debts.pay');
Route::delete('/debts/archive-paid', [DebtController::class, 'archivePaid'])->name('debts.archive');
Route::delete('/debts/archive-paid', [DebtController::class, 'archivePaid'])->name('debts.archivePaid');
// ==========================================
// ROUTE UTAMA TERPROTEKSI (AUTH & VERIFIED)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    // 1. AKSES KASIR & OPERASIONAL POS
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/order', [CashierController::class, 'storeOrder'])->name('cashier.order.store');
   
    // Manajemen Produk & Stok dari POS
    Route::get('/cashier/product/create', [CashierController::class, 'createProduct'])->name('cashier.product.create');
    Route::post('/cashier/product', [CashierController::class, 'storeProduct'])->name('cashier.product.store');
    Route::put('/product/{id}', [CashierController::class, 'updateProduct'])->name('product.update');

    // Buku Utang / Bon Pelanggan (Cukup diletakkan di sini saja)
    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/{id}', [DebtController::class, 'show'])->name('debts.show');
    Route::post('/debts/{id}/pay', [DebtController::class, 'pay'])->name('debts.pay');
    Route::delete('/debts/archive-paid', [DebtController::class, 'archivePaid'])->name('debts.archive');
    Route::delete('/debts/archive-paid', [DebtController::class, 'archivePaid'])->name('debts.archivePaid');

    // 2. AKSES KHUSUS ADMIN
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::put('/product/{id}', [CashierController::class, 'updateProduct'])->name('product.update');
    });

    // 3. AKSES KHUSUS OWNER
    Route::middleware(['role:owner'])->prefix('owner')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('owner.dashboard');
        Route::get('/reports', [OwnerController::class, 'reports'])->name('owner.reports');
    });
});