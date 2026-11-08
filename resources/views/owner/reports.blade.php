@extends('layouts.app')
@section('title', 'Madusis - Laporan Keuangan Detail')
@section('styles')
<style>
    .badge-status-paid {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
        background: #dcfce7;
        color: #16a34a;
    }
    .badge-status-unpaid {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
        background: #fee2e2;
        color: #dc2626;
    }
    .badge-payment-cicilan {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
        background: #e0f2fe;
        color: #0369a1;
    }
</style>
@endsection
@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc;">
    <!-- Header Page -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Laporan Keuangan & Audit Utang</h2>
            <p style="font-size: 12px; color: #64748b;">Audit riwayat transaksi penjualan dan aktivitas pembayaran/pelunasan bon</p>
        </div>
        <button onclick="window.print()" style="background: var(--sidebar-bg); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
            <i class="fa-solid fa-print me-1"></i> Cetak Laporan
        </button>
    </div>
    <!-- Filter Bar -->
    <div style="background: #fff; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <form method="GET" action="{{ route('owner.reports') }}" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" style="padding: 7px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 12px; outline: none; background: #f8fafc;">
            </div>
            <div>
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" style="padding: 7px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 12px; outline: none; background: #f8fafc;">
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Cari Pelanggan / Metode</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..." style="width: 100%; padding: 7px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 12px; outline: none; background: #f8fafc;">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('owner.reports') }}" style="background: #e2e8f0; color: #475569; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-block;">Reset</a>
            </div>
        </form>
    </div>
    <!-- Metrik Ringkasan -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
        <div style="background: #fff; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL TRANSAKSI</span>
            <h4 style="font-size: 18px; color: #1e293b; margin-top: 4px;">{{ $totalReportTransactions }} Aktivitas</h4>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL OMZET MASUK</span>
            <h4 style="font-size: 18px; color: #16a34a; margin-top: 4px;">Rp {{ number_format($totalReportOmzet, 0, ',', '.') }}</h4>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">SISA UTANG AKTIF</span>
            <h4 style="font-size: 18px; color: #dc2626; margin-top: 4px;">Rp {{ number_format($totalReportDebt, 0, ',', '.') }}</h4>
        </div>
    </div>
    <!-- Tabel 1: Transaksi Penjualan Utama -->
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Riwayat Penjualan POS</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                    <th style="padding: 10px;">ID / Waktu</th>
                    <th style="padding: 10px;">Pelanggan</th>
                    <th style="padding: 10px;">Item Dibeli</th>
                    <th style="padding: 10px;">Metode & Pembayaran</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px; text-align: right;">Total Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allOrders as $ord)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px;">
                        <strong>#{{ $ord->id }}</strong><br>
                        <span style="font-size: 10px; color: #64748b;">{{ $ord->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td style="padding: 10px; font-weight: 600;">{{ $ord->user->name ?? 'Umum' }}</td>
                    <td style="padding: 10px;">
                        <ul style="padding-left: 15px; margin: 0; font-size: 11px; color: #475569;">
                            @foreach($ord->orderItems as $item)
                                <li>{{ $item->product->name ?? 'Produk' }} (x{{ $item->quantity }})</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="padding: 10px;">
                        <span style="text-transform: uppercase; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #f1f5f9;">
                            {{ $ord->payment_method }}
                        </span>
                        
                        <!-- Rincian Uang Tunai Diterima & Kembalian di Laporan -->
                        @if($ord->payment_method === 'cash')
                            <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                                Diterima: <strong>Rp {{ number_format($ord->paid_amount, 0, ',', '.') }}</strong>
                                @if($ord->change_amount > 0)
                                    <br>Kembalian: <span style="color: #16a34a;">Rp {{ number_format($ord->change_amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        @elseif($ord->payment_method === 'debt' && $ord->paid_amount > 0)
                            <div style="font-size: 10px; color: #0284c7; margin-top: 4px;">
                                DP/Awal: <strong>Rp {{ number_format($ord->paid_amount, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                    </td>
                    <td style="padding: 10px;">
                        <span class="{{ $ord->status === 'paid' ? 'badge-status-paid' : 'badge-status-unpaid' }}">
                            {{ strtoupper($ord->status) }}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #1e293b;">
                        Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data transaksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <!-- Paginasi -->
        <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
            <div>
                Menampilkan <strong>{{ $allOrders->firstItem() ?? 0 }}</strong> - <strong>{{ $allOrders->lastItem() ?? 0 }}</strong> dari <strong>{{ $allOrders->total() }}</strong> transaksi
            </div>
            <div style="display: flex; gap: 5px;">
                @if ($allOrders->onFirstPage())
                    <span style="padding: 5px 10px; background: #f1f5f9; color: #cbd5e1; border-radius: 6px; cursor: not-allowed;">&laquo; Prev</span>
                @else
                    <a href="{{ $allOrders->previousPageUrl() }}" style="padding: 5px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1e293b; text-decoration: none; border-radius: 6px; font-weight: 600;">&laquo; Prev</a>
                @endif
                @if ($allOrders->hasMorePages())
                    <a href="{{ $allOrders->nextPageUrl() }}" style="padding: 5px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1e293b; text-decoration: none; border-radius: 6px; font-weight: 600;">Next &raquo;</a>
                @else
                    <span style="padding: 5px 10px; background: #f1f5f9; color: #cbd5e1; border-radius: 6px; cursor: not-allowed;">Next &raquo;</span>
                @endif
            </div>
        </div>
    </div>
    <!-- Tabel 2: Riwayat Pembayaran & Cicilan Utang -->
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Log Pelunasan & Pembayaran Cicilan Utang</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                    <th style="padding: 10px;">Waktu Pembayaran</th>
                    <th style="padding: 10px;">Nama Pelanggan</th>
                    <th style="padding: 10px;">Nominal Di-bayar</th>
                    <th style="padding: 10px;">Sisa Utang Terakhir</th>
                    <th style="padding: 10px;">Status Bon</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; font-weight: 600; color: #64748b;">
                        {{ $payment->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">
                        {{ $payment->debt->customer->name ?? 'Pelanggan' }}
                    </td>
                    <td style="padding: 10px; font-weight: 700; color: #16a34a;">
                        + Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px; font-weight: 600; color: #dc2626;">
                        Rp {{ number_format($payment->debt->remaining_debt ?? 0, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px;">
                        <span class="{{ ($payment->debt->remaining_debt ?? 0) <= 0 ? 'badge-status-paid' : 'badge-payment-cicilan' }}">
                            {{ ($payment->debt->remaining_debt ?? 0) <= 0 ? 'LUNAS' : 'DICICIL' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Belum ada riwayat pembayaran cicilan utang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection