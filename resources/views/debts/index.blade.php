@extends('layouts.app')
@section('title', 'Madusis - Buku Utang Pelanggan')

@section('styles')
<style>
    .badge-status-paid {
        font-size: 10px; padding: 3px 8px; border-radius: 12px; font-weight: 600;
        background: #dcfce7; color: #16a34a; display: inline-block;
    }
    .badge-status-partial {
        font-size: 10px; padding: 3px 8px; border-radius: 12px; font-weight: 600;
        background: #fef9c3; color: #ca8a04; display: inline-block;
    }
    .badge-status-unpaid {
        font-size: 10px; padding: 3px 8px; border-radius: 12px; font-weight: 600;
        background: #fee2e2; color: #dc2626; display: inline-block;
    }
</style>
@endsection

@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc;">
    
    <!-- Header Halaman -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Buku Utang & Bon Pelanggan</h2>
            <p style="font-size: 12px; color: #64748b;">Daftar seluruh transaksi bon/utang dan status pelunasannya</p>
        </div>

        <!-- Tombol Arsip Bon Lunas (Khusus Admin/Owner) -->
        <form action="{{ route('debts.archivePaid') }}" method="POST" onsubmit="return confirm('Yakin ingin mengarsipkan semua utang yang sudah lunas?')">
            @csrf
            @method('DELETE')
            <button type="submit" style="background: #fff; border: 1px solid var(--border-color); color: #475569; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">
                <i class="fa-solid fa-box-archive me-1"></i> Arsipkan yang Lunas
            </button>
        </form>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; color: #16a34a; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid #bbf7d0;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Tabel Daftar Utang -->
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                    <th style="padding: 10px;">ID Order / Tanggal</th>
                    <th style="padding: 10px;">Pelanggan</th>
                    <th style="padding: 10px;">Sisa Utang</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts as $debt)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 10px;">
                        <strong>#{{ $debt->order_id }}</strong><br>
                        <span style="font-size: 10px; color: #64748b;">{{ $debt->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">
                        {{ $debt->customer->name ?? 'Umum' }}
                    </td>
                    <td style="padding: 12px 10px; font-weight: 700; color: #dc2626;">
                        Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                    </td>
                    <td style="padding: 12px 10px;">
                        @if($debt->status === 'paid')
                            <span class="badge-status-paid">LUNAS</span>
                        @elseif($debt->status === 'partial')
                            <span class="badge-status-partial">DICICIL</span>
                        @else
                            <span class="badge-status-unpaid">BELUM LUNAS</span>
                        @endif
                    </td>
                    <td style="padding: 12px 10px; text-align: center;">
                        <a href="{{ route('debts.show', $debt->id) }}" style="background: #0284c7; color: #fff; padding: 6px 14px; border-radius: 6px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s;">
                            <i class="fa-solid fa-eye"></i> Kelola / Bayar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">
                        <i class="fa-solid fa-folder-open" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        Tidak ada data utang atau bon saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection