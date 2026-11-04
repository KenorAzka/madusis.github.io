<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Madusis')</title>
    <link rel="icon" href="{{ asset('images/logo2.svg') }}" type="image/png">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Eksternal -->
    <link rel="stylesheet" href="{{ asset('css/cashier-pos.css') }}">
    @yield('styles')
    <style>
        /* Styling untuk Floating Toast / Popup Notifikasi Kanan Atas */
        @keyframes slideInFromRight {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutToRight {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #ffffff;
            border-left: 4px solid #02c77f;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 14px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            animation: slideInFromRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .toast-notification.hiding {
            animation: slideOutToRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

</head>
<body>
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="header-left">
            <span><i class="fa-solid fa-cash-register"></i> Kasir POS</span>
            <span class="store-status"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Open</span>
        </div>
        <div class="header-right">
            <span><i class="fa-regular fa-calendar"></i> {{ date('l, d M Y') }}</span>
            <span>Kasir: <strong>{{ Auth::user()->name ?? 'Staff Kasir' }}</strong></span>
        </div>
    </div>

    <!-- App Container Layout -->
    <div class="app-container">
   
        <!-- 1. Sidebar Navigasi Kiri (Warna #1c2a3a) -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-store"></i> MADUSIS
            </div>
            <div class="sidebar-menu">
                <a href="{{ route('cashier.index') }}" class="menu-item {{ request()->routeIs('cashier.index') ? 'active' : '' }}" class="sidebar-link menu-item">
                    <i class="fa-solid fa-utensils"></i> Menu Kasir
                </a>
                <a href="#" class="menu-item"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Order</a>
                
                <!-- Menu Buku Utang / Bon Pelanggan -->
                <a href="#" onclick="openCashierDebtModal(event)" class="sidebar-link menu-item">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Buku Utang</span>
                </a>

                @if(Illuminate\Support\Facades\Auth::user()->role === 'admin')
                <a href="#" onclick="openAddModal(event)" class="sidebar-link menu-item">
                    <i class="fa-solid fa-circle-plus"></i> Tambah Produk
                </a>
                @endif
                
                @if(Illuminate\Support\Facades\Auth::user()->role !== 'admin')
                    <a href="#" class="sidebar-link menu-item"><i class="fa-solid fa-box"></i> Stok Produk</a>
                @endif

                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="sidebar-link menu-item" style="background: none; border: none; width: 100%; text-align: left; cursor: pointer; color: #fca5a5;">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. Konten Utama dari Halaman Anak -->
        @yield('content')

    </div><!-- Tutup .app-container -->

    @yield('modals')
    @yield('scripts')
</body>
</html>