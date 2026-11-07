<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class AdminController extends Controller
{
    // Pastikan hanya role admin yang bisa akses
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // Halaman Dashboard Admin
    public function dashboard()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();
        
        return view('admin.dashboard', compact('products', 'categories'));
    }
}