<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Menampilkan katalog produk khusus customer (Hanya yang tersedia / stock > 0)
     */
    public function catalog(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category');

        $categories = Category::all();

        $products = Product::where('is_available', 1)
            ->where('stock', '>', 0)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->get();

        return view('customer.catalog', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function myDebts()
    {
        // Ambil data utang HANYA milik user yang sedang login
        $user = Auth::user();
        $debts = \App\Models\Debt::with(['order.orderItems.product'])
                    ->where('customer_id', $user->id)
                    ->latest()
                    ->get();

        return view('customer.debts', compact('debts'));
    }
}