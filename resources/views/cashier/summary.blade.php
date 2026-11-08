@extends('layouts.app')
@section('title', 'Madusis - Ringkasan Shift Kasir')
@section('styles')
    <style>
    /* Styling Custom Scrollbar yang Elegan */
    .custom-scroll {
        overflow-y: auto;
        padding-right: 5px;
    }
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endsection
@section('content')
<!-- Kontainer Utama: Lebar penuh layar (width: 100%) dan terkunci dalam viewport -->
<div style="flex: 1; height: 100%; width: 100%; padding: 20px 25px; background: #f8fafc; display: flex; flex-direction: column; box-sizing: border-box; overflow: hidden;">
    
    <!-- Header Halaman (Lebar Penuh) -->
    <div style="width: 100%; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">Ringkasan Shift Kasir</h2>
                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Laporan ringkas transaksi real-time tanggal {{ now()->translatedFormat('d M Y') }}.</p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Kartu Atas (2 Kolom Lebar Penuh) -->
    <div style="width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; flex-shrink: 0;">
        <!-- Card Pendapatan -->
        <div style="background: #fff; border-radius: 14px; border: 1px solid var(--border-color); padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
            <div style="background: #dcfce7; color: #16a34a; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <span style="display: block; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pendapatan Diterima Hari Ini</span>
                <span style="font-size: 20px; font-weight: 800; color: #16a34a;">Rp {{ number_format($totalHarian, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card Utang/Bon -->
        <div style="background: #fff; border-radius: 14px; border: 1px solid var(--border-color); padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
            <div style="background: #fee2e2; color: #dc2626; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
            <div>
                <span style="display: block; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Sisa Utang / Bon Aktif</span>
                <span style="font-size: 20px; font-weight: 800; color: #dc2626;">Rp {{ number_format($totalUtang, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Kontainer Transaksi Terakhir (Lebar Penuh & Bisa di-Scroll di Dalamnya) -->
    <div style="width: 100%; flex: 1; background: #fff; border-radius: 16px; border: 1px solid var(--border-color); padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; overflow: hidden; min-height: 0;">
        
        <!-- Header Sticky -->
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; flex-shrink: 0;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #0284c7; font-size: 15px;"></i>
            <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0;">Transaksi Terakhir Hari Ini</h3>
        </div>

        <!-- List Transaksi yang Bisa Digulir (Custom Scroll) -->
        <div class="custom-scroll" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-right: 4px;">
            @forelse($recentActivities as $order)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                <!-- Header Transaksi: Nama Pelanggan, Metode Bayar, & Total -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13px; font-weight: 700; color: #1e293b;">{{ $order->user->name ?? 'Pelanggan Umum' }}</span>
                       
                        @if($order->payment_method === 'cash')
                            <span style="font-size: 10px; font-weight: 700; background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 6px;">CASH</span>
                        @elseif($order->payment_method === 'debt')
                            <span style="font-size: 10px; font-weight: 700; background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 6px;">DEBT</span>
                        @else
                            <span style="font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px;">QRIS</span>
                        @endif
                        <span style="font-size: 11px; color: #64748b;">• Order #{{ $order->id }} • {{ $order->created_at->format('H:i:s') }} WIB</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 14px; font-weight: 800; color: #0284c7;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        <span style="display: block; font-size: 9px; color: #94a3b8;">Total Belanja</span>
                    </div>
                </div>

                <!-- Detail Barang yang Dibeli -->
                <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 6px; padding: 8px 12px;">
                    <span style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Item Dibeli:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @forelse($order->orderItems as $item)
                            <span style="font-size: 10px; background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                                <strong>{{ $item->product->name ?? 'Produk Dihapus' }}</strong> ({{ $item->quantity }}x) - Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        @empty
                            <span style="font-size: 10px; color: #94a3b8; font-style: italic;">Rincian item tidak ditemukan</span>
                        @endforelse
                    </div>
                </div>

                <!-- Info Pembayaran Tunai (Jika Cash) -->
                @if($order->payment_method === 'cash')
                <div style="font-size: 10px; color: #64748b; display: flex; gap: 15px;">
                    <span>Bayar: <strong>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</strong></span>
                    <span>Kembalian: <strong style="color: #16a34a;">Rp {{ number_format($order->change_amount, 0, ',', '.') }}</strong></span>
                </div>
                @endif
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fa-solid fa-receipt" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                Belum ada transaksi yang terjadi hari ini.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection