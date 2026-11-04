<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin & Kasir Default
        User::create([
            'name' => 'Administrator Madusis',
            'email' => 'admin@madusis.test',
            'password' => Hash::make('password123'), // Wajib menggunakan Hash::make()
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir Warung',
            'email' => 'kasir@madusis.test',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
        ]);

        // 2. Buat Kategori Produk Khas Warung Madura
        $categories = [
            'Rokok & Tembakau',
            'Sembako & Beras',
            'Minuman & Es',
            'Kebutuhan Mandi & Cuci',
            'Gas & Galon Air'
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat,
                'slug' => Str::slug($cat)
            ]);
        }

        // 3. Buat Data Produk Dummy
        $categoryRokok = Category::where('slug', 'rokok-tembakau')->first();
        $categorySembako = Category::where('slug', 'sembako-beras')->first();
        $categoryMinuman = Category::where('slug', 'minuman-es')->first();

        $products = [
            [
                'category_id' => $categoryRokok->id,
                'name' => 'Djarum Super 12',
                'slug' => Str::slug('Djarum Super 12'),
                'purchase_price' => 24000,
                'selling_price' => 27000,
                'stock' => 50,
                'unit' => 'slop',
                'is_available' => true,
            ],
            [
                'category_id' => $categorySembako->id,
                'name' => 'Beras Premium 5kg',
                'slug' => Str::slug('Beras Premium 5kg'),
                'purchase_price' => 65000,
                'selling_price' => 70000,
                'stock' => 20,
                'unit' => 'karung',
                'is_available' => true,
            ],
            [
                'category_id' => $categoryMinuman->id,
                'name' => 'Aqua Botol 600ml',
                'slug' => Str::slug('Aqua Botol 600ml'),
                'purchase_price' => 3000,
                'selling_price' => 4000,
                'stock' => 48,
                'unit' => 'pcs',
                'is_available' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}