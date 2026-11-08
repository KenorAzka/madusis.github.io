@extends('layouts.app')
@section('title', 'Admin - Kelola Stok & Produk')
@section('styles')
<style>
    .cell-padding {
        padding: 10px;
    }
    .stock-text {
        font-weight: 700;
    }
    .stock-low {
        color: #ef4444;
    }
    .stock-normal {
        color: #1e293b;
    }
    .status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }
    .status-active {
        background: #dcfce7;
        color: #16a34a;
    }
    .status-inactive {
        background: #fee2e2;
        color: #dc2626;
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
    <!-- Header Admin -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Manajemen Stok & Katalog</h2>
            <p style="font-size: 12px; color: #64748b;">Kelola harga jual, stok gudang, dan ketersediaan barang warung</p>
        </div>
        <button onclick="openAddModal(event)" style="background: var(--sidebar-bg); color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
            <i class="fa-solid fa-plus me-1"></i> Tambah Produk Baru
        </button>
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
            <tbody>
                @foreach($products as $prod)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; font-weight: 600;">{{ $prod->name }}</td>
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
                        <button type="button" onclick="openEditModal('{{ $prod->id }}', '{{ $prod->name }}', '{{ $prod->category_id }}', '{{ $prod->purchase_price }}', '{{ $prod->selling_price }}', '{{ $prod->stock }}', '{{ $prod->is_available }}')" style="background: #e2e8f0; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-weight: 600; color: #1e293b;">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL EDIT PRODUK ================= -->
<div id="editProductModal" class="mac-modal-overlay">
    <div class="mac-modal-window">
        <div class="mac-modal-header">
            <div class="mac-window-actions">
                <span class="mac-dot mac-close" onclick="closeEditModal()" title="Tutup"></span>
            </div>
            <span class="mac-window-title">Edit Produk</span>
            <div style="width: 40px;"></div>
        </div>
        <div class="mac-modal-body">
            <form id="editProductForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_name">Nama Produk</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_category_id">Kategori</label>
                        <select id="edit_category_id" name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_purchase_price">Harga Beli / Modal (Rp)</label>
                        <input type="number" id="edit_purchase_price" name="purchase_price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_selling_price">Harga Jual (Rp)</label>
                        <input type="number" id="edit_selling_price" name="selling_price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock">Stok</label>
                        <input type="number" id="edit_stock" name="stock" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_available">Status Ketersediaan</label>
                        <select id="edit_is_available" name="is_available" class="form-control" required>
                            <option value="1">Available (Tersedia)</option>
                            <option value="0">Not Available (Habis)</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_image">Ganti Foto Produk (Opsional)</label>
                        <input type="file" id="edit_image" name="image" class="form-control" accept="image/*" style="padding: 5px;">
                    </div>
                    <button type="submit" class="btn-save-mac" style="background: #1c2a3a; color: #fff; padding: 10px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; grid-column: span 2;">Perbarui Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH PRODUK ================= -->
<div id="addProductModal" class="mac-modal-overlay">
    <div class="mac-modal-window">
        <div class="mac-modal-header">
            <div class="mac-window-actions">
                <span class="mac-dot mac-close" onclick="closeAddModal()" title="Tutup"></span>
            </div>
            <span class="mac-window-title">Tambah Produk Baru</span>
            <div style="width: 40px;"></div>
        </div>
        <div class="mac-modal-body">
            <form action="{{ route('cashier.product.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Produk</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Djarum Super 12" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="purchase_price">Harga Beli / Modal (Rp)</label>
                        <input type="number" id="purchase_price" name="purchase_price" class="form-control" placeholder="Contoh: 25000" required>
                    </div>
                    <div class="form-group">
                        <label for="selling_price">Harga Jual (Rp)</label>
                        <input type="number" id="selling_price" name="selling_price" class="form-control" placeholder="Contoh: 28000" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stok Awal</label>
                        <input type="number" id="stock" name="stock" class="form-control" placeholder="Contoh: 50" required>
                    </div>
                    <div class="form-group">
                        <label for="is_available">Status Ketersediaan</label>
                        <select id="is_available" name="is_available" class="form-control" required>
                            <option value="1">Available (Tersedia)</option>
                            <option value="0">Not Available (Habis)</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="image">Foto Produk</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*" style="padding: 5px;">
                    </div>
                    <button type="submit" class="btn-save-mac" style="background: #1c2a3a; color: #fff; padding: 10px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; grid-column: span 2;">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name, categoryId, purchasePrice, sellingPrice, stock, isAvailable) {
        const modal = document.getElementById('editProductModal');
        const form = document.getElementById('editProductForm');
       
        form.action = `/product/${id}`;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_purchase_price').value = purchasePrice;
        document.getElementById('edit_selling_price').value = sellingPrice;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_is_available').value = isAvailable;
        
        if (modal) modal.classList.add('active');
    }

    function closeEditModal() {
        const modal = document.getElementById('editProductModal');
        if (modal) modal.classList.remove('active');
    }

    function openAddModal(event) {
        if (event) event.preventDefault();
        const modal = document.getElementById('addProductModal');
        if (modal) modal.classList.add('active');
    }

    function closeAddModal() {
        const modal = document.getElementById('addProductModal');
        if (modal) modal.classList.remove('active');
    }

    window.addEventListener('click', function(event) {
        const addProductModal = document.getElementById('addProductModal');
        const editProductModal = document.getElementById('editProductModal');
        if (event.target === addProductModal) closeAddModal();
        if (event.target === editProductModal) closeEditModal();
    });
</script>

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