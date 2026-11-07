<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Order; // <-- Tambahkan import Order
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    // Menampilkan halaman buku utang (jika diakses via URL langsung)
    public function index()
    {
        $debts = Debt::with(['customer', 'order'])->latest()->get();
        return view('debts.index', compact('debts'));
    }

    // Menampilkan halaman detail utang & pembayaran per pelanggan/bon
    public function show($id)
    {
        $debt = Debt::with(['customer', 'order.orderItems.product', 'payments'])->findOrFail($id);
        return view('debts.show', compact('debt'));
    }

    // Memproses Pembayaran Utang / Cicilan
    public function pay(Request $request, $id)
    {
        $debt = Debt::findOrFail($id);
        
        // Casting nilai remaining_debt menjadi float murni agar terbaca oleh validator Laravel
        $maxDebt = (float) $debt->remaining_debt;
        
        $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:1', 'max:' . $maxDebt],
        ], [
            'amount_paid.max' => 'Jumlah bayar tidak boleh melebihi sisa utang yaitu Rp ' . number_format($maxDebt, 0, ',', '.')
        ]);

        DB::beginTransaction();
        try {
            $paidAmount = (float) $request->input('amount_paid');

            // 1. Simpan riwayat pembayaran ke tabel debt_payments
            DebtPayment::create([
                'debt_id'     => $debt->id,
                'amount_paid' => $paidAmount,
            ]);

            // 2. Kurangi sisa utang
            $debt->remaining_debt -= $paidAmount;

            // 3. Update status utang
            if ($debt->remaining_debt <= 0) {
                $debt->status = 'paid';
                $debt->remaining_debt = 0;
            } else {
                $debt->status = 'partial';
            }
            $debt->save();

            // 4. SINKRONISASI KE TABEL ORDERS (Agar Owner mendeteksi omzet pelunasan)
            if ($debt->order_id) {
                $order = Order::find($debt->order_id);
                if ($order) {
                    $order->paid_amount += $paidAmount;
                    
                    // Jika utang sudah lunas sepenuhnya, ubah status order jadi 'paid' (Lunas)
                    if ($debt->status === 'paid') {
                        $order->status = 'paid';
                    }
                    $order->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran cicilan berhasil dicatat dan laporan omzet diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    // Mengarsipkan / Menghapus Utang yang Sudah Lunas (Khusus Admin)
    public function archivePaid()
    {
        Debt::where('status', 'paid')->delete();
        return redirect()->back()->with('success', 'Semua utang yang sudah lunas berhasil diarsipkan.');
    }
}