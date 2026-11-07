<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Cek apakah role user sesuai dengan route
        if ($user->role === $role) {
            return $next($request);
        }

        // Jika bukan rolenya, kembalikan ke halaman kasir
        return redirect('/cashier')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
    }
}