<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Madusis POS')</title>
    
    <!-- Google Fonts (Poppins & Plus Jakarta Sans) & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Global CSS Sesuai Kebutuhan Kasir & macOS Dock -->
    <style>
        :root {
            --bg-main: #f8fafc;
            --sidebar-bg: #1c2a3a;
            --card-bg: rgba(28, 42, 58, 0.04);
            --accent-color: #0284c7;
            --border-color: rgba(28, 42, 58, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: #1e293b;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Navbar */
        .top-navbar {
            height: 55px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            z-index: 10;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 14px;
            color: var(--sidebar-bg);
        }

        .status-badge {
            background: rgba(34, 197, 94, 0.15);
            color: #16a34a;
            font-size: 11px;
            padding: 3px 12px;
            border-radius: 20px;
            font-weight: 500;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .user-info {
            font-size: 12px;
            color: #64748b;
        }

        /* App Body Layout */
        .app-body {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        /* macOS Floating Sidebar Dock (Di Sisi Kiri) */
        .macos-sidebar-dock {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 14px 10px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04);
            z-index: 100;
        }

        .dock-item {
            position: relative;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: transform 0.2s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .dock-item:hover {
            transform: scale(1.15) translateX(4px);
        }

        .dock-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--sidebar-bg);
            background: #f1f5f9;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }

        .dock-item.active .dock-icon {
            background: linear-gradient(135deg, var(--sidebar-bg), #0284c7);
            color: #fff;
            box-shadow: 0 6px 15px rgba(28, 42, 58, 0.35);
        }

        /* Tooltip ala macOS */
        .dock-tooltip {
            position: absolute;
            left: 58px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            transform: translateX(-5px);
        }

        .dock-item:hover .dock-tooltip {
            opacity: 1;
            transform: translateX(0);
        }

        .dock-dot {
            position: absolute;
            left: -6px;
            width: 4px;
            height: 4px;
            background: var(--sidebar-bg);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .dock-item.active .dock-dot {
            opacity: 1;
        }

        .dock-divider {
            height: 1px;
            width: 30px;
            background: rgba(203, 213, 225, 0.7);
            margin: 2px auto;
        }

        /* Area Konten Utama */
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding-left: 85px; /* Ruang agar tidak tertutup Dock kiri */
            display: flex;
            flex-direction: column;
        }
    </style>

    <!-- CSS Khusus Halaman -->
    <link rel="stylesheet" href="{{ asset('css/cashier-pos.css') }}">
    <!-- Jika halaman spesifik ingin memuat CSS tambahan -->
    @yield('styles')
</head>
<body>

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="brand-section">
            <i class="fa-solid fa-cash-register"></i>
            <span>MADUSIS</span>
        </div>
        <div class="user-info">
            <i class="fa-regular fa-calendar me-1"></i> {{ date('l, d M Y') }} &nbsp;|&nbsp; 
            Kasir: <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
        </div>
    </header>

    <div class="app-body">
        <!-- Sidebar macOS Dock (Melayang di Kiri) -->
        <aside class="macos-sidebar-dock">
            <!-- Menu Kasir / POS -->
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'cashier')
        <a href="{{ route('cashier.index') }}" class="dock-item {{ request()->routeIs('cashier.index') ? 'active' : '' }}">
            <div class="dock-dot"></div>
            <div class="dock-icon">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <span class="dock-tooltip">Kasir</span>
        </a>
        @endif


            @if(auth()->user()->role === 'owner')
            <!-- Executive Summary / Dashboard Owner -->
            <a href="{{ route('owner.dashboard') }}" class="dock-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <div class="dock-dot"></div>
                <div class="dock-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="dock-tooltip">Executive Summary</span>
            </a>

            <!-- Laporan Keuangan Owner -->
            <a href="{{ route('owner.reports') }}" class="dock-item {{ request()->routeIs('owner.reports') ? 'active' : '' }}">
                <div class="dock-dot"></div>
                <div class="dock-icon">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span class="dock-tooltip">Laporan Keuangan</span>
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
            <!-- Admin Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="dock-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <div class="dock-dot"></div>
                <div class="dock-icon">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                <span class="dock-tooltip">Dashboard Admin</span>
            </a>
            @endif

            <!-- Buku Utang -->
            <a href="{{ route('debts.index') }}" class="dock-item {{ request()->routeIs('debts.*') ? 'active' : '' }}">
                <div class="dock-dot"></div>
                <div class="dock-icon">
                    <i class="fa-solid fa-book"></i>
                </div>
                <span class="dock-tooltip">Buku Utang</span>
            </a>

            @if(auth()->user()->role === 'cashier')
            <!-- Menu Ringkasan Shift Kasir -->
            <a href="{{ route('cashier.summary') }}" class="dock-item {{ request()->routeIs('cashier.summary') ? 'active' : '' }}">
                <div class="dock-dot"></div>
                <div class="dock-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="dock-tooltip">Ringkasan Shift</span>
            </a>
            @endif

            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'owner')
                <a href="{{ route('database.index') }}" class="dock-item {{ request()->routeIs('database.*') ? 'active' : '' }}">
                    <div class="dock-dot"></div>
                    <div class="dock-icon">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <span class="dock-tooltip">Backup & Restore</span>
                </a>
            @endif

            <div class="dock-divider"></div>


            <!-- Tombol Logout -->
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dock-item">
                <div class="dock-icon" style="color: #dc2626; background: #fee2e2;">
                    <i class="fa-solid fa-power-off"></i>
                </div>
                <span class="dock-tooltip">Keluar</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </aside>
        

<!-- Area Konten Utama -->
        <main class="main-content">
            @yield('content')
        </main>
    </div> <!-- Penutup .app-body -->
    @yield('modals')
    @yield('scripts')
    </div>

</body>
</html>