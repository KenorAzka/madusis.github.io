<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Debt;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSalesToday = Order::whereDate('created_at', today())->sum('total_amount');
        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $totalUnpaidDebts = Debt::where('status', '!=', 'paid')->sum('remaining_amount');

        return view('dashboard.index', compact('totalSalesToday', 'lowStockProducts', 'totalUnpaidDebts'));
    }
}
