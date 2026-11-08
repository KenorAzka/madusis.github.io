@extends('layouts.app')
@section('title', 'Admin - Kelola Stok & Produk')
@section('styles')
<style>
    .cell-padding { padding: 10px; }
    .stock-text { font-weight: 700; }
    .stock-low { color: #ef4444; }
    .stock-normal { color: #1e293b; }
    .status-badge { padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; }
    .status-active { background: #dcfce7; color: #16a34a; }
    .status-inactive { background: #fee2e2; color: #dc2626; }
    .btn-action-edit {
        background: #e2e8f0; color: #1e293b; text-decoration: none; padding: 6px 12px;
        border-radius: 6px; font-weight: 600; font-size: 11px; display: inline-flex; align-items: center; gap: 5px;
        transition: background 0.2s;
    }
    .btn-action-edit:hover { background: #cbd5e1; }
    
    /* Toast Notification */
    .toast-notification {
        position: fixed; top: 25px; right: 25px; background: #ffffff; border: 1px solid var(--border-color, #e2e8f0);
        padding: 14px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        display: flex; align-items: center; gap: 12px; z-index: 9999; animation: slideInRight 0.3s ease forwards; max-width: 350px;
    }
    .toast-notification.hiding { animation: slideOutRight 0.3s ease forwards; }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
</style>
@endsection

@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc;">
    <!-- Header Admin -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Manajemen Stok & Katalog</h2>
            <p style="font-size: 12px; color: #64748b;">Kelola harga jual, stok gudang, dan ketersediaan barang warung</p>
        </div>
        <!-- Tombol Buka Halaman Tambah Produk -->
        <a href="{{ route('admin.products.create') }}" style="background: var(--sidebar-bg); color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-plus"></i> Tambah Produk Baru
        </a>
    </div>

    <!-- Live Search & Filter Bar -->
    <div style="background: #fff; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; justify-content: space-between; align-items: center; gap: 15px;">
        <div style="position: relative; width: 100%; max-width: 320px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
            <input type="text" id="adminProductSearch" oninput="liveSearchAdmin()" placeholder="Cari nama produk atau kategori..." style="width: 100%; padding: 8px 12px 8px 34px; border-radius: 8px; border: 1px solid var(--border-color); background: #f8fafc; font-size: 12px; outline: none;">
        </div>
        <span style="font-size: 12px; color: #64748b;" id="productCountInfo">Menampilkan <strong>{{ count($products) }}</strong> produk</span>
    </div>

    <!-- Table Ringkasan Produk -->
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                    <th style="padding: 10px;">Produk</th>
                    <th style="padding: 10px;">Kategori</th>
                    <th style="padding: 10px;">Harga Modal</th>
                    <th style="padding: 10px;">Harga Jual</th>
                    <th style="padding: 10px;">Stok</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @forelse($products as $prod)
                <tr class="product-row" data-name="{{ strtolower($prod->name) }}" data-category="{{ strtolower($prod->category->name ?? '') }}" style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; font-weight: 600; color: #1e293b;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($prod->image)
                                <img src="{{ asset($prod->image) }}" alt="{{ $prod->name }}" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover;">
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            @endif
                            <span>{{ $prod->name }}</span>
                        </div>
                    </td>
                    <td style="padding: 10px;">{{ $prod->category->name ?? '-' }}</td>
                    <td style="padding: 10px; color: #64748b;">Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
                    <td style="padding: 10px; font-weight: 700; color: #02c77f;">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                    <td class="cell-padding">
                        <span class="stock-text {{ $prod->stock <= 5 ? 'stock-low' : 'stock-normal' }}">
                            {{ $prod->stock }}
                        </span>
                    </td>
                    <td class="cell-padding">
                        <span class="status-badge {{ $prod->is_available ? 'status-active' : 'status-inactive' }}">
                            {{ $prod->is_available ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn-action-edit">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="7" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada produk terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Live Search Realtime
    function liveSearchAdmin() {
        const keyword = document.getElementById('adminProductSearch').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.product-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const category = row.getAttribute('data-category');

            if (name.includes(keyword) || category.includes(keyword)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countInfo = document.getElementById('productCountInfo');
        if (countInfo) {
            countInfo.innerHTML = `Menampilkan <strong>${visibleCount}</strong> produk`;
        }
    }
</script>

<!-- Notifikasi Toast -->
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
        setTimeout(() => { closeToast(); }, 4000);
        function closeToast() {
            const toast = document.getElementById('toast-alert');
            if (toast) {
                toast.classList.add('hiding');
                setTimeout(() => { toast.remove(); }, 300);
            }
        }
    </script>
@endif
@endsection