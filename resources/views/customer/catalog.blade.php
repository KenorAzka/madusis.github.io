@extends('layouts.customer')
@section('title', 'Katalog Galon & Gas - Madusis')
@section('styles')
<style>
    /* Tombol Filter Kategori Pelanggan */
    .cat-filter-btn {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        background: #f1f5f9;
        color: #475569;
        transition: 0.2s;
        white-space: nowrap;
    }
    .cat-filter-btn.active {
        background: #1c2a3a;
        color: #fff;
    }
    /* Responsif Container Dibuat Lebar Penuh (Tanpa max-width) */
    .catalog-container {
        padding: 20px 25px;
        width: 100%;
        box-sizing: border-box;
    }
    .catalog-banner {
        background: linear-gradient(135deg, #1c2a3a, #0284c7);
        color: #fff;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .filter-search-wrapper {
        background: #fff;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .product-grid-custom {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    /* Penyesuaian Tampilan HP */
    @media (max-width: 768px) {
        .catalog-container {
            padding: 15px;
        }
        .filter-search-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-search-wrapper form {
            width: 100%;
        }
        .filter-search-wrapper input[type="text"] {
            width: 100% !important;
        }
        .product-grid-custom {
            grid-template-columns: repeat(2, 1fr); /* 2 kolom di HP agar rapi */
            gap: 10px;
        }
    }
</style>
@endsection
@section('content')
<div class="catalog-container">
   
    <!-- Banner Informasi -->
    <div class="catalog-banner">
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 6px;">Pesan Galon & Gas Cepat Sampai!</h2>
        <p style="font-size: 12px; opacity: 0.9;">Klik tombol "Pesan WhatsApp" di bawah produk untuk memesan langsung secara cepat ke admin toko kami.</p>
    </div>
    <!-- Filter Kategori & Search -->
    <div class="filter-search-wrapper">
        <div style="display: flex; gap: 8px; overflow-x: auto; max-width: 100%; padding-bottom: 4px;">
            <!-- Tombol Semua Kategori -->
            <a href="{{ route('customer.catalog') }}" class="cat-filter-btn {{ !$categoryId ? 'active' : '' }}">
                Semua
            </a>
            <!-- Loop Kategori Lainnya -->
            @foreach($categories as $cat)
                <a href="{{ route('customer.catalog', ['category' => $cat->id]) }}" class="cat-filter-btn {{ $categoryId == $cat->id ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('customer.catalog') }}" style="display: flex; gap: 8px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari galon/gas..." style="padding: 7px 12px; border-radius: 20px; border: 1px solid var(--border-color); font-size: 12px; outline: none; background: #f8fafc; width: 180px;">
            <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 7px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; font-weight: 600;">Cari</button>
        </form>
    </div>
    <!-- Grid Produk Tersedia -->
    <div class="product-grid-custom">
        @forelse($products as $prod)
        @php
            $noWhatsAppToko = '62882005641003';
            $pesanWa = "Halo Admin Madusis, saya ingin memesan:\n\n*Produk:* {$prod->name}\n*Harga:* Rp " . number_format($prod->selling_price, 0, ',', '.') . "\n*Nama Pemesan:* " . Auth::user()->name . "\n\nMohon segera dikirim ya. Terima kasih!";
            $urlWa = "https://wa.me/{$noWhatsAppToko}?text=" . urlencode($pesanWa);
        @endphp
        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="width: 100%; height: 130px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative;">
                @if($prod->image)
                    <img src="{{ asset($prod->image) }}" alt="{{ $prod->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fa-solid fa-fire-flame-curved" style="font-size: 28px; color: #cbd5e1;"></i>
                @endif
                <span style="position: absolute; top: 8px; right: 8px; background: #dcfce7; color: #16a34a; font-size: 9px; font-weight: 700; padding: 3px 6px; border-radius: 4px;">Tersedia</span>
            </div>
           
            <div style="padding: 12px; display: flex; flex-direction: column; gap: 8px; flex: 1; justify-content: space-between;">
                <div>
                    <h4 style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">{{ $prod->name }}</h4>
                    <span style="font-size: 13px; font-weight: 800; color: #0284c7;">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                </div>
               
                <a href="{{ $urlWa }}" target="_blank" style="width: 100%; background: #16a34a; color: #fff; text-decoration: none; padding: 8px; border-radius: 8px; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 14px;"></i> Pesan WhatsApp
                </a>
            </div>
        </div>
        @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8; background: #fff; border-radius: 12px; border: 1px solid var(--border-color);">
            <i class="fa-solid fa-box-open" style="font-size: 36px; margin-bottom: 10px; color: #cbd5e1; display: block;"></i>
            Belum ada produk galon atau gas yang tersedia saat ini.
        </div>
        @endforelse
    </div>
</div>
@endsection