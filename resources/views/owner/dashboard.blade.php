@extends('layouts.app')
@section('title', 'Madusis - Dashboard Owner')
@section('styles')
<style>
    .badge-order-debt {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
        background: #fee2e2;
        color: #dc2626;
    }
    .badge-order-paid {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
        background: #dcfce7;
        color: #16a34a;
    }
</style>
@endsection
@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Executive Summary</h2>
        <p style="font-size: 12px; color: #64748b;">Pantauan performa keuangan & transaksi Warung Madusis</p>
    </div>

    <!-- Ringkasan Keuangan Dinamis -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
        <div style="background: #fff; padding: 18px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">OMZET HARI INI</span>
            <h3 style="font-size: 22px; color: #0284c7; margin-top: 5px;">Rp {{ number_format($todayOmzet, 0, ',', '.') }}</h3>
        </div>
        <div style="background: #fff; padding: 18px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL OMZET LUNAS</span>
            <h3 style="font-size: 22px; color: #16a34a; margin-top: 5px;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
        </div>
        <div style="background: #fff; padding: 18px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL BON / UTANG AKTIF</span>
            <h3 style="font-size: 22px; color: #dc2626; margin-top: 5px;">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h3>
        </div>
    </div>

    <!-- Log Riwayat Transaksi -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 15px;">Aktivitas Transaksi & Pembayaran Terbaru</h4>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($recentActivities as $act)
                @if($act->activity_type === 'order')
                    <!-- Tampilan Aktivitas Order POS -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9; font-size: 12px;">
                        <div>
                            <span style="font-weight: 700; color: #1e293b;">
                                <i class="fa-solid fa-receipt me-1" style="color: #0284c7;"></i> Order #{{ $act->id }}
                            </span>
                            <span style="color: #64748b; margin-left: 8px;">Pelanggan: {{ $act->user->name ?? 'Umum' }}</span>
                        </div>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <span class="{{ $act->payment_method === 'debt' ? 'badge-order-debt' : 'badge-order-paid' }}">
                                {{ strtoupper($act->payment_method) }}
                            </span>
                            <span style="font-weight: 700; color: #1e293b;">Rp {{ number_format($act->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <!-- Tampilan Aktivitas Pembayaran Utang / Cicilan -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f0fdf4; border-radius: 10px; border: 1px solid #dcfce7; font-size: 12px;">
                        <div>
                            <span style="font-weight: 700; color: #16a34a;">
                                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Pembayaran Utang / Cicilan
                            </span>
                            <span style="color: #64748b; margin-left: 8px;">Pelanggan: {{ $act->debt->customer->name ?? 'Pelanggan' }}</span>
                        </div>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 4px; font-weight: 600; background: #e0f2fe; color: #0369a1;">
                                CICILAN
                            </span>
                            <span style="font-weight: 700; color: #16a34a;">+ Rp {{ number_format($act->amount_paid, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
            @empty
            <p style="font-size: 12px; color: #94a3b8; text-align: center; padding: 20px;">Belum ada aktivitas tercatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection