<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
       
        $customers = User::where('role', 'customer')->get()->append('buyer_level');
        $categories = Category::all();
        $products = Product::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })->get();

        $debts = Debt::with(['customer', 'order'])
            ->when($search, function ($query, $search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->get()
            ->groupBy('customer_id')
            ->map(function ($customerDebts) {
                $first = $customerDebts->first();
                $first->total_debt = $customerDebts->sum('total_debt');
                $first->remaining_debt = $customerDebts->sum('remaining_debt');
               
                if ($first->remaining_debt <= 0) {
                    $first->status = 'paid';
                } else if ($customerDebts->contains('status', 'partial')) {
                    $first->status = 'partial';
                } else {
                    $first->status = 'unpaid';
                }
               
                return $first;
            })
            ->values();

        if ($request->ajax() && $request->has('search')) {
            return view('debts.partials.table', compact('debts'));
        }

        return view('cashier.index', compact('customers', 'categories', 'products', 'debts'));
    }

    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'is_available'   => 'required|in:0,1',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = Product::findOrFail($id);

        $data = [
            'name'           => $request->name,
            'category_id'    => $request->category_id,
            'purchase_price' => $request->purchase_price,
            'selling_price'  => $request->selling_price,
            'stock'          => $request->stock,
            'is_available'   => ($request->stock <= 0) ? 0 : $request->is_available,
        ];

        // Cek apakah ada file gambar baru yang diunggah
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
            
            // Simpan ke folder public/products
            $image->move(public_path('products'), $imageName);
            $data['image'] = 'products/' . $imageName;
        }

        // Eksekusi update ke database
        $product->update($data);

        return redirect()->back()->with('success', "Produk {$product->name} berhasil diperbarui!");
    }

    public function storeOrder(Request $request)
    {
        $messages = [
            'total_amount.required' => 'Belum ada produk yang dipilih dalam keranjang!',
            'paid_amount.required'  => 'Gagal, Masukkan uang sesuai total atau lebih',
            'paid_amount.numeric'   => 'Gagal, Masukkan uang sesuai total atau lebih',
            'customer_id.required'  => 'Silakan pilih atau masukkan nama pelanggan terlebih dahulu!',
        ];

        $rawCustomerId = $request->customer_id;
        $resolvedCustomerId = $rawCustomerId;
        if ($rawCustomerId && str_starts_with($rawCustomerId, 'NEW:')) {
            $newCustomerName = str_replace('NEW:', '', $rawCustomerId);
       
            $newCustomer = User::create([
                'name'     => $newCustomerName,
                'email'    => strtolower(str_replace(' ', '', $newCustomerName)) . rand(100, 999) . '@madusis.local',
                'password' => bcrypt('password123'),
                'role'     => 'customer',
            ]);
       
            $resolvedCustomerId = $newCustomer->id;
            $request->merge(['customer_id' => $resolvedCustomerId]);
        }

        $request->validate([
            'customer_id'    => 'required|exists:users,id',
            'payment_method' => 'required|in:cash,qris,debt',
            'total_amount'   => 'required|numeric|min:1',
            'paid_amount'    => 'nullable|numeric',
            'cart'           => 'required|array|min:1',
        ], $messages);

        $customer = User::findOrFail($resolvedCustomerId);

        if ($request->payment_method === 'debt' && !$customer->canUseDebt()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['payment_method' => "Pelanggan {$customer->name} masih Level 1 (Belum memenuhi syarat minimal 5x transaksi atau total belanja Rp 200.000 untuk menggunakan metode Utang)."]);
        }

        $totalAmount = (float) $request->total_amount;
        $paidAmount = (float) ($request->paid_amount ?? 0);
        $remainingDebt = max(0, $totalAmount - $paidAmount);

        if ($request->payment_method === 'cash') {
            if ($paidAmount < $totalAmount || $paidAmount <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['paid_amount' => 'Gagal, Masukkan uang sesuai total atau lebih']);
            }
        }

        $changeAmount = ($request->payment_method === 'cash') ? max(0, $paidAmount - $totalAmount) : 0;

        DB::beginTransaction();
        try {
            // 1. Simpan Header Transaksi Utama
            $order = Order::create([
                'user_id'        => $resolvedCustomerId,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'change_amount'  => $changeAmount,
                'payment_method' => $request->payment_method,
                'status'         => ($request->payment_method === 'debt') ? ($remainingDebt <= 0 ? 'paid' : 'unpaid') : 'paid',
            ]);

            // 2. Simpan Detail Item dan Potong Stok Produk
            foreach ($request->cart as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $product->selling_price,
                        'subtotal'   => $product->selling_price * $item['quantity'],
                    ]);

                    $product->stock -= $item['quantity'];
                    if ($product->stock < 0) $product->stock = 0;
                    if ($product->stock == 0) $product->is_available = 0;
                    $product->save();
                }
            }

            // 3. Catat Utang jika metode pembayaran adalah utang
            if ($request->payment_method === 'debt') {
                Debt::create([
                    'customer_id'    => $resolvedCustomerId,
                    'order_id'       => $order->id,
                    'total_debt'     => $totalAmount,
                    'remaining_debt' => $remainingDebt,
                    'status'         => ($remainingDebt <= 0) ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            DB::commit();
            return redirect()->route('cashier.index')->with('success', 'Transaksi berhasil diproses, stok diperbarui, dan level pelanggan diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
        }
    }
    public function dailySummary()
    {
        $today = now()->toDateString();

        // 1. Perbaikan: Total Pendapatan Harian dihitung dari total belanja (total_amount), 
        // bukan dari uang yang dibayarkan pelanggan (paid_amount).
        $totalHarian = Order::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled') // Opsional jika ada status batal
            ->sum('total_amount');

        // 2. Total Sisa Utang / Bon Aktif
        $totalUtang = Debt::where('remaining_debt', '>', 0)
            ->sum('remaining_debt');

        // 3. Aktivitas Transaksi Terakhir (menyertakan paid_amount & change_amount)
        $recentActivities = Order::with('user')
            ->whereDate('created_at', $today)
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.summary', compact('totalHarian', 'totalUtang', 'recentActivities'));
    }
}