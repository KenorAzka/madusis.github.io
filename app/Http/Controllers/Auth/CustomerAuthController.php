<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    /**
     * Menampilkan halaman form registrasi customer.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Menangani proses input data registrasi customer baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input form
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed', // Jika ada field password_confirmation
                Password::min(6)             // Minimal 6 karakter
                    ->mixedCase()            // Harus ada huruf besar dan kecil
                    ->numbers()              // Harus ada angka
                    ->symbols(),             // Harus ada simbol (bisa disesuaikan jika ingin angka ATAU simbol)
            ],
        ]);

        // Simpan data ke database dengan role customer
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',          // Peran sebagai pembeli online
            'buyer_level' => 1,            // Level awal (regular)
            'is_debt_allowed' => false,    // Belum diizinkan berbon/utang
        ]);

        event(new Registered($user));

        // Otomatis login setelah registrasi berhasil
        Auth::login($user);

        // PERBAIKAN DI SINI: Gunakan url() atau redirect langsung karena '/cashier' adalah path URL
        return redirect()->intended('/cashier');
    }
}