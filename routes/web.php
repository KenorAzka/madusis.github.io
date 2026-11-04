<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DebtController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\LoginController; // Panggil LoginController kustom kita
use App\Http\Controllers\CashierController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

// Route Login Manual (Menggantikan Auth::routes() agar aman dengan String Primary Key USR-xxx)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Route Registrasi Customer
Route::get('/register', [CustomerAuthController::class, 'create'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'store']);

// ==========================================
// ROUTE VERIFIKASI EMAIL
// ==========================================
Route::middleware(['auth'])->group(function () {
    // 1. Halaman pemberitahuan "Silakan verifikasi email Anda"
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // 2. Proses saat pengguna mengklik tautan di email
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('cashier.index')->with('success', 'Email berhasil diverifikasi!');
    })->middleware(['signed'])->name('verification.verify');

    // 3. Tombol untuk mengirim ulang email verifikasi
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Tautan verifikasi baru telah dikirim ke email Anda!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// ==========================================
// ROUTE UTAMA (DENGAN MIDDLEWARE AUTH & VERIFIED)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Dashboard Utama / Kasir
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/order', [CashierController::class, 'storeOrder'])->name('cashier.order.store'); 
    
    Route::get('/cashier/product/create', [CashierController::class, 'createProduct'])->name('cashier.product.create');
    Route::post('/cashier/product', [CashierController::class, 'storeProduct'])->name('cashier.product.store');

    // Manajemen Produk & Stok
    Route::put('/product/{id}', [CashierController::class, 'updateProduct'])->name('product.update');

    // Buku Utang / Bon Pelanggan
    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/{id}', [DebtController::class, 'show'])->name('debts.show');
    Route::post('/debts/{id}/pay', [DebtController::class, 'pay'])->name('debts.pay');
    Route::delete('/debts/archive-paid', [DebtController::class, 'archivePaid'])->name('debts.archivePaid');
});