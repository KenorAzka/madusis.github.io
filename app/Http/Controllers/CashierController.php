<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Category;

class CashierController extends Controller
{
    /**
     * Tampilkan halaman utama kasir & buku utang
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // 1. Ambil data customer dengan append buyer_level agar level dinamis terbaca di frontend
        $customers = User::where('role', 'customer')->get()->append('buyer_level');

        // 2. Ambil data kategori dan produk untuk ditampilkan di halaman kasir
        $categories = Category::all();
        $products = Product::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })->get();

        // 3. Ambil data utang dan gabungkan per customer_id untuk buku utang
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

        // Jika request AJAX untuk pencarian live search buku utang
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

        // Tangani upload foto baru jika ada
        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('products'), $imageName);
            $data['image'] = 'products/' . $imageName;
        }

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

            // 1. Tangani Pelanggan Baru (NEW:) SEBELUM validasi 'exists:users,id' dijalankan
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
            
                // Timpa dengan ID asli dari database yang baru saja dibuat
                $resolvedCustomerId = $newCustomer->id;
                
                // Masukkan kembali ke request agar lolos validasi 'exists'
                ['customer_id' => $resolvedCustomerId];
                $request->merge(['customer_id' => $resolvedCustomerId]);
            }

            // 2. Jalankan Validasi setelah customer baru berhasil dibuat/di-resolve ID-nya
            $request->validate([
                'customer_id'    => 'required|exists:users,id',
                'payment_method' => 'required|in:cash,qris,debt',
                'total_amount'   => 'required|numeric|min:1',
                'paid_amount'    => 'nullable|numeric',
                'cart'           => 'required|array|min:1',
            ], $messages);

            $customer = User::findOrFail($resolvedCustomerId);

            // 3. Validasi Metode Utang (Khusus Level 2)
            if ($request->payment_method === 'debt' && !$customer->canUseDebt()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['payment_method' => "Pelanggan {$customer->name} masih Level 1 (Belum memenuhi syarat minimal 5x transaksi atau total belanja Rp 200.000 untuk menggunakan metode Utang)."]);
            }

            $totalAmount = (float) $request->total_amount;
            $paidAmount = (float) ($request->paid_amount ?? 0);
            $remainingDebt = max(0, $totalAmount - $paidAmount);

            // 4. Validasi Pembayaran Tunai / Cash
            if ($request->payment_method === 'cash') {
                if ($paidAmount < $totalAmount || $paidAmount <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['paid_amount' => 'Gagal, Masukkan uang sesuai total atau lebih']);
                }
            }

            $changeAmount = ($request->payment_method === 'cash') ? max(0, $paidAmount - $totalAmount) : 0;

            // 5. Simpan Transaksi Utama ke Tabel Orders (Menggunakan $resolvedCustomerId yang pasti valid)
            $order = Order::create([
                'user_id'        => $resolvedCustomerId,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'change_amount'  => $changeAmount,
                'payment_method' => $request->payment_method,
                'status'         => ($request->payment_method === 'debt') ? ($remainingDebt <= 0 ? 'paid' : 'unpaid') : 'paid',
            ]);

            // 6. Kurangi Stok Produk & Simpan Detail Item
            foreach ($request->cart as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock -= $item['quantity'];
                    if ($product->stock < 0) $product->stock = 0;
                
                    if ($product->stock == 0) {
                        $product->is_available = 0;
                    }
                    $product->save();
                }
            }

            // 7. Catat Utang jika metode pembayaran adalah utang
            if ($request->payment_method === 'debt') {
                Debt::create([
                    'customer_id'    => $resolvedCustomerId,
                    'order_id'       => $order->id,
                    'total_debt'     => $totalAmount,
                    'remaining_debt' => $remainingDebt,
                    'status'         => ($remainingDebt <= 0) ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            return redirect()->route('cashier.index')->with('success', 'Transaksi berhasil diproses, stok diperbarui, dan level pelanggan diperbarui!');
        }
}