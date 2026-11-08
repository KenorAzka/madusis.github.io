@extends('layouts.customer')
@section('title', 'Buku Utang Saya - Madusis')
@section('styles')
<style>
    .debt-badge-paid {
        font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        background: #dcfce7; color: #16a34a;
    }
    .debt-badge-partial {
        font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        background: #e0f2fe; color: #0369a1;
    }
    .debt-badge-unpaid {
        font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        background: #fee2e2; color: #dc2626;
    }
    /* Kontainer Dibuat Bebas Lebar (width: 100% tanpa max-width) */
    .debts-container {
        padding: 20px 25px;
        width: 100%;
        box-sizing: border-box;
    }
    .debt-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        font-size: 13px;
    }
    @media (max-width: 768px) {
        .debts-container {
            padding: 15px;
        }
        .debt-card-grid {
            grid-template-columns: 1fr; /* Menumpuk ke bawah di HP */
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="debts-container">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b;">Riwayat Bon / Utang Saya</h2>
        <p style="font-size: 12px; color: #64748b;">Daftar transaksi pembelian secara utang yang tercatat di toko.</p>
    </div>
    <div style="display: flex; flex-direction: column; gap: 15px; width: 100%;">
        @forelse($debts as $debt)
        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
            <!-- Header Kartu -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <div>
                    <strong style="font-size: 13px; color: #1e293b;">Transaksi #{{ $debt->order_id }}</strong>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Tanggal: {{ $debt->created_at->format('d M Y, H:i') }} WIB</div>
                </div>
                <div>
                    @php
                        $isPaid = $debt->remaining_debt <= 0;
                        $isPartial = $debt->status === 'partial';
                       
                        $badgeClass = $isPaid ? 'debt-badge-paid' : ($isPartial ? 'debt-badge-partial' : 'debt-badge-unpaid');
                        $statusText = $isPaid ? 'LUNAS' : ($isPartial ? 'DICICIL' : 'BELUM LUNAS');
                    @endphp
                   
                    <span class="{{ $badgeClass }}">
                        {{ $statusText }}
                    </span>
                </div>
            </div>
            <!-- Isi Detail Utang -->
            <div class="debt-card-grid">
                <div>
                    <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 4px;">Total Belanja Awal</span>
                    <strong style="color: #1e293b; font-size: 13px;">Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 4px;">Sisa Utang Belum Dibayar</span>
                    <strong style="color: #dc2626; font-size: 14px;">Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid var(--border-color); color: #94a3b8; width: 100%; box-sizing: border-box;">
            <i class="fa-solid fa-file-invoice-dollar" style="font-size: 36px; margin-bottom: 10px; color: #cbd5e1; display: block;"></i>
            Anda tidak memiliki catatan utang atau bon saat ini.
        </div>
        @endforelse
    </div>
</div>
@endsection