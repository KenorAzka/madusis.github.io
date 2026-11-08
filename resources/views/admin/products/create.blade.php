@extends('layouts.app')
@section('title', 'Admin - Tambah Produk Baru')
@section('content')
<div style="flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; width: 100%; box-sizing: border-box;">
   
    <!-- Wrapper Header Lebar Penuh -->
    <div style="display: flex; gap: 10px; width: 100%; margin-bottom: 20px; align-items: center;">
        <a href="{{ route('admin.dashboard') }}" style="background: #e2e8f0; color: #475569; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; flex-shrink: 0;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b;">Tambah Produk Baru</h2>
            <p style="font-size: 12px; color: #64748b;">Isi rincian informasi produk baru untuk dimasukkan ke dalam stok toko.</p>
        </div>
    </div>

    <!-- Layout Form 2 Kolom Lebar Penuh (Tanpa max-width) -->
    <form action="{{ route('cashier.product.store') }}" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; align-items: stretch; box-sizing: border-box;">
        @csrf
        <!-- KOTAK KIRI -->
        <div style="background: #fff; border-radius: 16px; border: 1px solid var(--border-color); padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Nama Produk</label>
                    <input type="text" name="name" placeholder="Contoh: Djarum Super 12" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Kategori</label>
                    <select name="category_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Status Ketersediaan</label>
                    <select name="is_available" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                        <option value="1">Available (Tersedia)</option>
                        <option value="0">Not Available (Habis)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Harga Beli / Modal (Rp)</label>
                    <input type="number" name="purchase_price" placeholder="Contoh: 25000" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Harga Jual (Rp)</label>
                    <input type="number" name="selling_price" placeholder="Contoh: 28000" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Stok Awal</label>
                    <input type="number" name="stock" placeholder="Contoh: 50" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; outline: none; box-sizing: border-box;">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Foto Produk (Opsional)</label>
                    <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewFile(event)" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; background: #f8fafc; box-sizing: border-box;">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('admin.dashboard') }}" style="background: #e2e8f0; color: #475569; text-decoration: none; padding: 10px 22px; border-radius: 8px; font-size: 12px; font-weight: 600;">Batal</a>
                <button type="submit" style="background: var(--sidebar-bg); color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Simpan Produk</button>
            </div>
        </div>

        <!-- KOTAK KANAN -->
        <div style="background: #fff; border-radius: 16px; border: 1px solid var(--border-color); padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; min-height: 440px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0 5px;">
                <span style="font-size: 12px; font-weight: 700; color: #1e293b;">Pratinjau Foto Produk</span>
                <span style="font-size: 10px; color: #64748b; background: #f1f5f9; padding: 3px 8px; border-radius: 6px;"><i class="fa-solid fa-hand-pointer"></i> Klik & Geser Gambar</span>
            </div>
            <div id="imageContainer" style="flex: 1; width: 100%; border-radius: 12px; background: #0f172a; overflow: hidden; position: relative; cursor: grab; display: flex; align-items: center; justify-content: center;">
                <img id="imagePreview" src="" alt="Preview Produk"
                     style="max-width: none; max-height: none; min-width: 80%; min-height: 80%; object-fit: contain; position: absolute; user-select: none; display: none;">
               
                <div id="placeholderText" style="display: flex; flex-direction: column; align-items: center; color: #64748b; gap: 8px;">
                    <i class="fa-solid fa-image" style="font-size: 48px; color: #334155;"></i>
                    <span style="font-size: 12px;">Belum Ada Foto Produk</span>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const container = document.getElementById('imageContainer');
    const img = document.getElementById('imagePreview');
    const placeholder = document.getElementById('placeholderText');
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;

    container.addEventListener('mousedown', (e) => {
        if (img.style.display === 'none') return;
        isDragging = true;
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        startY = e.pageY - container.offsetTop;
        scrollLeft = container.scrollLeft;
        scrollTop = container.scrollTop;
    });

    container.addEventListener('mouseleave', () => {
        isDragging = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mouseup', () => {
        isDragging = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        const walkX = (x - startX) * 1.5;
        const walkY = (y - startY) * 1.5;
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });

    function previewFile(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection