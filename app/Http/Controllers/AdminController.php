<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class AdminController extends Controller
{
    /**
     * Menampilkan Dashboard Admin (Tabel Stok & Produk + Live Search)
     */
    public function dashboard()
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::all();

        return view('admin.dashboard', compact('products', 'categories'));
    }

    /**
     * Menampilkan Halaman Form Tambah Produk Baru
     */
    public function createProduct()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Menampilkan Halaman Form Edit Detail Produk
     */
    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();

        return view('admin.products.edit', compact('product', 'categories'));
    }
}