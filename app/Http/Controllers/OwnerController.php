<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Debt;
use App\Models\DebtPayment;
use Carbon\Carbon;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:owner']);
    }

    public function dashboard()
    {
        // Omzet Hari Ini (Total belanja riil hari ini + pembayaran cicilan utang hari ini)
        $todayOrderOmzet = Order::whereDate('created_at', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount'); // Menggunakan total_amount, bukan paid_amount agar pas sesuai nominal belanja

        $todayDebtPaymentOmzet = DebtPayment::whereDate('created_at', Carbon::today())
            ->sum('amount_paid');

        $todayOmzet = $todayOrderOmzet + $todayDebtPaymentOmzet;

        // Total Omzet Keseluruhan
        $totalOrderOmzet = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalDebtPaymentOmzet = DebtPayment::sum('amount_paid');
        $totalOmzet = $totalOrderOmzet + $totalDebtPaymentOmzet;

        // Total Utang Aktif Sisa yang Belum Lunas
        $totalDebt = Debt::where('status', '!=', 'paid')->sum('remaining_debt');

        // Ambil Transaksi POS Terbaru (Menyertakan paid_amount & change_amount untuk rincian tunai)
        $orders = Order::with(['user', 'orderItems.product'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                $order->activity_type = 'order';
                $order->activity_time = $order->created_at;
                return $order;
            });

        // Ambil Riwayat Pembayaran Utang Terbaru
        $payments = DebtPayment::with(['debt.customer'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($payment) {
                $payment->activity_type = 'debt_payment';
                $payment->activity_time = $payment->created_at;
                return $payment;
            });

        // Gabungkan dan urutkan berdasarkan waktu terbaru (ambil 10 teratas)
        $recentActivities = $orders->concat($payments)
            ->sortByDesc('activity_time')
            ->take(10);

        return view('owner.dashboard', compact('todayOmzet', 'totalOmzet', 'totalDebt', 'recentActivities'));
    }

    public function reports(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        // 1. Query Transaksi Penjualan Utama (Orders)
        $ordersQuery = Order::with(['user', 'orderItems.product']);

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        if ($search) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Query Riwayat Pembayaran Utang (Debt Payments)
        $paymentsQuery = DebtPayment::with(['debt.customer']);

        if ($startDate && $endDate) {
            $paymentsQuery->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        if ($search) {
            $paymentsQuery->whereHas('debt.customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 3. Perhitungan Metrik Keuangan Akurat (Menggunakan total_amount untuk penjualan bersih)
        $totalReportOmzet = (clone $ordersQuery)->where('status', '!=', 'cancelled')->sum('total_amount')
                          + (clone $paymentsQuery)->sum('amount_paid');
        
        $totalReportTransactions = (clone $ordersQuery)->count() + (clone $paymentsQuery)->count();

        // Total Utang Aktif diambil langsung dari Sisa Utang di Tabel Debts
        $debtsQuery = Debt::where('status', '!=', 'paid');
        if ($search) {
            $debtsQuery->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        $totalReportDebt = $debtsQuery->sum('remaining_debt');

        // 4. Ambil data gabungan transaksi & pembayaran cicilan (Paginate)
        $allOrders = $ordersQuery->latest()->paginate(15)->withQueryString();
        $recentPayments = $paymentsQuery->latest()->get();

        return view('owner.reports', compact(
            'allOrders',
            'recentPayments',
            'totalReportOmzet',
            'totalReportTransactions',
            'totalReportDebt',
            'startDate',
            'endDate',
            'search'
        ));
    }
}