@extends('layouts.app')

@section('title', 'Ringkasan Shift Kasir - Madusis POS')

@section('styles')
<style>
    .summary-container {
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }
    .summary-header {
        margin-bottom: 25px;
    }
    .summary-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--sidebar-bg);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .summary-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .card-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .card-value {
        font-size: 24px;
        font-weight: 700;
        margin-top: 8px;
    }
    .activity-box {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .activity-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .badge-method {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .method-cash { background: #dcfce7; color: #15803d; }
    .method-qris { background: #e0f2fe; color: #0369a1; }
    .method-debt { background: #fee2e2; color: #b91c1c; }
</style>
@endsection

@section('content')
<div class="summary-container">
    <div class="summary-header">
        <h1 class="summary-title">
            <i class="fa-solid fa-chart-pie" style="color: #0284c7;"></i> Ringkasan Shift Kasir
        </h1>
        <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Laporan ringkas transaksi real-time tanggal {{ date('d M Y') }}.</p>
    </div>

    <!-- 2 Kotak Utama: Total Masuk & Total Sisa Utang -->
    <div class="card-grid">
        <div class="summary-card">
            <div class="card-label"><i class="fa-solid fa-wallet text-success"></i> Pendapatan Diterima Hari Ini</div>
            <div class="card-value" style="color: #16a34a;">Rp {{ number_format($totalHarian, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="card-label"><i class="fa-solid fa-book text-danger"></i> Total Sisa Utang/Bon Aktif</div>
            <div class="card-value" style="color: #dc2626;">Rp {{ number_format($totalUtang, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Kotak Aktivitas Terakhir Hari Ini -->
    <div class="activity-box">
        <div class="activity-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: #d97706;"></i> 5 Transaksi Terakhir Hari Ini
        </div>
        
        @if($recentActivities->isEmpty())
            <p style="font-size: 13px; color: #94a3b8; text-align: center; padding: 20px 0;">Belum ada transaksi pada shift hari ini.</p>
        @else
            <ul class="activity-list">
                @foreach($recentActivities as $activity)
                    <li class="activity-item">
                        <div>
                            <strong style="color: #1e293b;">{{ $activity->user->name ?? 'Pelanggan Umum' }}</strong>
                            <span class="badge-method method-{{ $activity->payment_method }}">
                                {{ $activity->payment_method }}
                            </span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Order #{{ $activity->id }} • {{ $activity->created_at->format('H:i:s') }} WIB
                            </div>
                            
                            {{-- Info Tambahan Uang Tunai Diterima & Kembalian --}}
                            @if($activity->payment_method === 'cash')
                                <div style="font-size: 11px; color: #475569; margin-top: 3px;">
                                    Bayar: Rp {{ number_format($activity->paid_amount, 0, ',', '.') }} 
                                    @if($activity->change_amount > 0)
                                        | Kembalian: <span style="color: #16a34a; font-weight: 600;">Rp {{ number_format($activity->change_amount, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #0284c7;">
                                Rp {{ number_format($activity->total_amount, 0, ',', '.') }}
                            </div>
                            <div style="font-size: 10px; color: #64748b;">Total Belanja</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection