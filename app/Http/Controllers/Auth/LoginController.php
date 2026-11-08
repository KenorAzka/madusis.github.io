<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // Jangan lupa import Request

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/cashier';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Menyesuaikan arah redirect setelah login berhasil berdasarkan role user.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'customer') {
            return redirect()->route('customer.catalog');
        }

        if ($user->role === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Default untuk kasir atau role lainnya
        return redirect()->route('cashier.index');
    }
}