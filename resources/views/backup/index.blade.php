@extends('layouts.app')

@section('title', 'Backup & Restore Database - Madusis POS')

@section('styles')
<style>
    .backup-container {
        padding: 30px;
        max-width: 900px;
        margin: 0 auto;
        width: 100%;
    }
    .page-header {
        margin-bottom: 25px;
    }
    .page-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--sidebar-bg);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-subtitle {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }
    .grid-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
        gap: 20px;
    }
    .card-backup {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
    }
    .icon-backup {
        background: #e0f2fe;
        color: #0284c7;
    }
    .icon-restore {
        background: #fef3c7;
        color: #d97706;
    }
    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .card-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 20px;
    }
    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-backup {
        background: #0284c7;
        color: #ffffff;
    }
    .btn-backup:hover {
        background: #0369a1;
    }
    .btn-restore {
        background: #d97706;
        color: #ffffff;
    }
    .btn-restore:hover {
        background: #b45309;
    }
    .file-input-wrapper {
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        background: #f8fafc;
        margin-bottom: 15px;
        cursor: pointer;
    }
    .file-input-wrapper:hover {
        border-color: #0284c7;
        background: #f0f9ff;
    }
</style>
@endsection

@section('content')
<div class="backup-container">

    <!-- Notifikasi Sukses / Error -->
    @if(session('success'))
        <div id="toast-success" style="position: fixed; top: 20px; right: 20px; background: #ffffff; border-left: 4px solid #16a34a; padding: 14px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; z-index: 99999; animation: slideInRight 0.3s ease forwards; max-width: 350px;">
            <div style="background: rgba(22, 163, 74, 0.15); color: #16a34a; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Berhasil!</span>
                <span style="font-size: 12px; color: #64748b;">{{ session('success') }}</span>
            </div>
            <button onclick="closeToast('toast-success')" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 15px; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>setTimeout(() => { closeToast('toast-success'); }, 4000);</script>
    @endif

    @if(session('error') || $errors->any())
        <div id="toast-error" style="position: fixed; top: 20px; right: 20px; background: #ffffff; border-left: 4px solid #dc2626; padding: 14px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; z-index: 99999; animation: slideInRight 0.3s ease forwards; max-width: 350px;">
            <div style="background: rgba(220, 38, 38, 0.15); color: #dc2626; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Terjadi Kesalahan!</span>
                <span style="font-size: 12px; color: #64748b;">{{ session('error') ?? $errors->first() }}</span>
            </div>
            <button onclick="closeToast('toast-error')" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 15px; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>setTimeout(() => { closeToast('toast-error'); }, 5000);</script>
    @endif

    <!-- Header Halaman -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fa-solid fa-database"></i> Backup & Restore Database
        </h1>
        <p class="page-subtitle">Kelola dan amankan seluruh data transaksi, produk, serta pelanggan Madusis POS Anda.</p>
    </div>

    <!-- Grid Kartu Fitur -->
    <div class="grid-cards">
        
        <!-- Kartu 1: Backup Database -->
        <div class="card-backup">
            <div>
                <div class="card-icon-wrapper icon-backup">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                </div>
                <h2 class="card-title">Backup Database</h2>
                <p class="card-desc">
                    Unduh file cadangan data berupa skrip `.sql`. Disarankan untuk melakukan backup secara berkala untuk mencegah kehilangan data transaksi penting.
                </p>
            </div>
            <a href="{{ route('database.backup') }}" class="btn-action btn-backup">
                <i class="fa-solid fa-download"></i> Unduh Backup (.sql)
            </a>
        </div>

        <!-- Kartu 2: Restore Database -->
        <div class="card-backup">
            <div>
                <div class="card-icon-wrapper icon-restore">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h2 class="card-title">Restore Database</h2>
                <p class="card-desc">
                    Kembalikan seluruh struktur dan isi data dari file cadangan `.sql`. 
                    <strong style="color: #dc2626;">Perhatian:</strong> Proses ini akan menimpa seluruh data kasir saat ini.
                </p>
            </div>
            
            <form action="{{ route('database.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Peringatan: Melakukan restore akan menimpa seluruh data saat ini! Apakah Anda yakin ingin melanjutkan?')">
                @csrf
                <div class="file-input-wrapper">
                    <input type="file" name="backup_file" accept=".sql" required style="font-size: 11px; width: 100%;">
                </div>
                <button type="submit" class="btn-action btn-restore">
                    <i class="fa-solid fa-rotate-left"></i> Pulihkan Database
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function closeToast(elementId) {
        const toast = document.getElementById(elementId);
        if (toast) {
            toast.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => { toast.remove(); }, 300);
        }
    }
</script>
@endsection