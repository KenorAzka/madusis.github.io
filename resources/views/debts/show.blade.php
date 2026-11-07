@extends('layouts.app')
@section('title', 'Madusis - Detail & Pembayaran Utang')

@section('styles')
<style>
    .badge-status-paid {
        font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600;
        background: #dcfce7; color: #16a34a; display: inline-block;
    }
    .badge-status-partial {
        font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600;
        background: #fef9c3; color: #ca8a04; display: inline-block;
    }
    .badge-status-unpaid {
        font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600;
        background: #fee2e2; color: #dc2626; display: inline-block;
    }
        /* Styling Toast Notification di Kanan Atas */
    .toast-notification {
        position: fixed;
        top: 25px;
        right: 25px;
        background: #ffffff;
        border: 1px solid var(--border-color, #e2e8f0);
        padding: 14px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 9999;
        animation: slideInRight 0.3s ease forwards;
        max-width: 350px;
    }

    .toast-notification.hiding {
        animation: slideOutRight 0.3s ease forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
@endsection

@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc;">
    
    <!-- Tombol Kembali & Header -->
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
        <a href="{{ route('debts.index') }}" style="background: #e2e8f0; color: #475569; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 style="font-size: 18px; font-weight: 700; color: #1e293b;">Detail Bon & Pembayaran Utang</h2>
            <p style="font-size: 12px; color: #64748b;">Kelola cicilan dan lihat riwayat transaksi bon pelanggan</p>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Kolom Kiri: Informasi Bon & Form Bayar -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Card Informasi Utama -->
            <div style="background: #fff; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    Informasi Pelanggan & Bon
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Nama Pelanggan:</span>
                        <strong style="color: #1e293b;">{{ $debt->customer->name ?? 'Umum' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">No. Referensi Order:</span>
                        <strong style="color: #0284c7;">#{{ $debt->order_id }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Status Utang:</span>
                        <span>
                            @if($debt->status === 'paid')
                                <span class="badge-status-paid">LUNAS</span>
                            @elseif($debt->status === 'partial')
                                <span class="badge-status-partial">DICICIL</span>
                            @else
                                <span class="badge-status-unpaid">BELUM LUNAS</span>
                            @endif
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Total Utang Awal:</span>
                        <strong style="color: #1e293b;">Rp {{ number_format($debt->order->total_amount ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #e2e8f0; padding-top: 10px; margin-top: 5px;">
                        <span style="font-weight: 700; color: #1e293b;">Sisa Utang Saat Ini:</span>
                        <strong style="font-size: 15px; color: #dc2626;">Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Card Form Pembayaran Cicilan (Hanya tampil jika belum lunas) -->
            @if($debt->remaining_debt > 0)
            <div style="background: #fff; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    Form Pembayaran Cicilan
                </h3>
                
                <form action="{{ route('debts.pay', $debt->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                            Masukkan Nominal Bayar (Rp)
                        </label>
                        <input type="number" name="amount_paid" max="{{ $debt->remaining_debt }}" min="1" required placeholder="Contoh: 50000" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; background: #f8fafc; font-weight: 600; color: #1e293b;">
                        <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">Maksimal pembayaran: Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}</span>
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; background: var(--sidebar-bg); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s;">
                        <i class="fa-solid fa-cash-register me-1"></i> Proses Pembayaran Cicilan
                    </button>
                </form>
            </div>
            @else
            <div style="background: #dcfce7; padding: 20px; border-radius: 14px; border: 1px solid #bbf7d0; text-align: center;">
                <i class="fa-solid fa-circle-check" style="font-size: 32px; color: #16a34a; margin-bottom: 8px;"></i>
                <h4 style="font-size: 14px; font-weight: 700; color: #16a34a;">Bon Ini Sudah Lunas!</h4>
                <p style="font-size: 12px; color: #15803d; margin-top: 4px;">Seluruh kewajiban pembayaran telah diselesaikan oleh pelanggan.</p>
            </div>
            @endif

        </div>

        <!-- Kolom Kanan: Rincian Produk & Riwayat Pembayaran -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Rincian Item Barang yang Dibeli -->
            <div style="background: #fff; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    Rincian Barang (Order #{{ $debt->order_id }})
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto;">
                    @foreach($debt->order->orderItems ?? [] as $item)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 8px; font-size: 12px;">
                        <div>
                            <strong style="color: #1e293b;">{{ $item->product->name ?? 'Produk' }}</strong><br>
                            <span style="color: #64748b; font-size: 11px;">Qty: {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        </div>
                        <strong style="color: #1e293b;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Riwayat Log Cicilan / Pembayaran Sebelumnya -->
            <div style="background: #fff; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    Riwayat Cicilan Pembayaran
                </h3>

                <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                    @forelse($debt->payments as $payment)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 8px; font-size: 12px;">
                        <div>
                            <span style="font-weight: 700; color: #16a34a;">Pembayaran Berhasil</span><br>
                            <span style="color: #64748b; font-size: 11px;">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <strong style="color: #16a34a; font-size: 13px;">+ Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</strong>
                    </div>
                    @empty
                    <p style="font-size: 12px; color: #94a3b8; text-align: center; padding: 15px;">Belum ada riwayat cicilan tercatat.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>
<!-- Notifikasi Sukses / Error di Kanan Atas -->
    @if(session('success'))
        <div id="toast-alert" class="toast-notification">
            <div style="background: rgba(2, 199, 127, 0.15); color: #02c77f; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                <i class="fa-solid fa-check"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Berhasil!</span>
                <span style="font-size: 12px; color: #64748b;">{{ session('success') }}</span>
            </div>
            <button onclick="closeToast()" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 10px; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>
            // Otomatis menutup toast setelah 4 detik
            setTimeout(() => {
                closeToast();
            }, 4000);

            function closeToast() {
                const toast = document.getElementById('toast-alert');
                if (toast) {
                    toast.classList.add('hiding');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }
            }
        </script>
    @endif

    @if ($errors->any())
        <div id="toast-error-alert" class="toast-notification" style="border-left: 4px solid #ef4444;">
            <div style="background: rgba(239, 68, 68, 0.15); color: #dc2626; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Terjadi Kesalahan!</span>
                <span style="font-size: 12px; color: #64748b;">Mohon periksa kembali form input Anda.</span>
            </div>
            <button onclick="closeErrorToast()" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 10px; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                closeErrorToast();
            }, 5000);

            function closeErrorToast() {
                const toast = document.getElementById('toast-error-alert');
                if (toast) {
                    toast.classList.add('hiding');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }
            }
        </script>
    @endif
@endsection