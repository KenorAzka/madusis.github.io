@extends('layouts.app')
@section('title', 'Madusis')
@section('styles')
<style>
    .text-stock-habis {
        font-size: 11px;
        color: #dc2626;
        font-weight: 700;
    }
    .text-stock-normal {
        font-size: 11px;
        color: #64748b;
        font-weight: normal;
    }
    /* Tambahkan CSS Toast di sini agar posisinya melayang di kanan atas */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 14px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 99999;
        animation: slideInRight 0.3s ease forwards;
        max-width: 350px;
    }
    .toast-notification.hiding {
        animation: slideOutRight 0.3s ease forwards;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    /* Styling status kartu produk */
    .product-card-available {
        background: #fff;
        cursor: pointer;
        transition: 0.2s;
    }
    .product-card-disabled {
        background: #f1f5f9;
        opacity: 0.45;
        filter: grayscale(100%);
        cursor: not-allowed;
    }
</style>
@endsection
@section('content')
        <!-- POS Layout Utama (Membungkus Kiri & Kanan Berjajar) -->
        <div class="pos-layout" style="display: flex; flex: 1; overflow: hidden;">
    
<!-- KOLOM KIRI: Kategori, Search, Daftar Produk, & Tombol Kelola Admin -->
            <div class="pos-main-content" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
        
                <!-- Baris Kategori & Pencarian -->
                <div class="category-search-bar" style="display: flex; align-items: center; justify-content: space-between; gap: 15px; background: #ffffff; padding: 10px 15px; border-radius: 12px; border: 1px solid var(--border-color);">
                    
                    <!-- Tombol Kategori -->
                    <div class="categories-scroll" id="category-pills-container" style="display: flex; gap: 8px; overflow-x: auto; white-space: nowrap;">
                        <button type="button" class="category-pill active" data-category="all" onclick="filterProducts('all', this)">Semua Kategori</button>
                        @foreach($categories as $cat)
                            <button type="button" class="category-pill" data-category="{{ $cat->id }}" onclick="filterProducts('{{ $cat->id }}', this)">{{ $cat->name }}</button>
                        @endforeach
                    </div>

                    <!-- Input Search Produk -->
                    <div class="search-box" style="width: auto; flex-shrink: 0; position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                        <input type="text" id="productSearchInput" oninput="filterProducts()" placeholder="Cari menu..." style="padding: 7px 12px 7px 32px; border-radius: 20px; border: 1px solid var(--border-color); background: #f8fafc; font-size: 12px; outline: none; width: 160px;">
                    </div>
                </div>

                <!-- Notifikasi Error / Success -->
                @if ($errors->any())
                    <div style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 12px; border-radius: 8px; font-size: 13px;">
                        <ul style="padding-left: 15px; margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Daftar Produk -->
                <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px;">
                    @foreach($products as $prod)
                    @php
                        // Tentukan apakah produk tidak bisa dibeli (stok <= 0 atau dinonaktifkan admin)
                        $isHabis = $prod->stock <= 0;
                        $isNonAktif = !$prod->is_available; // Perbaikan tanda $
                        $tidakTersedia = $isHabis || $isNonAktif;
                    @endphp
                    <div class="product-card {{ $tidakTersedia ? 'product-card-disabled' : 'product-card-available' }}"
                        data-id="{{ $prod->id }}"
                        data-name="{{ $prod->name }}"
                        data-price="{{ $prod->selling_price }}"
                        data-stock="{{ $prod->stock }}"
                        data-category-id="{{ $prod->category_id }}"
                        @if(!$tidakTersedia) onclick="handleCardClick(this)" @endif
                        style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;"
                        @if(!$tidakTersedia)
                        onmouseover="this.style.borderColor='#1c2a3a'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateY(0)'"
                        @endif>
                   
                        <!-- Kotak Gambar Produk -->
                        <div style="width: 100%; height: 120px; background: #f1f5f9; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            
                            <!-- TAMPILKAN GAMBAR JIKA ADA -->
                            @if($prod->image)
                                <img src="{{ asset($prod->image) }}" alt="{{ $prod->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fa-solid fa-image" style="font-size: 28px; color: #cbd5e1;"></i>
                            @endif

                            <!-- Badge Status -->
                            @if($isHabis)
                                <span style="position: absolute; top: 10px; right: 10px; background: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">Stok Habis</span>
                            @elseif($isNonAktif)
                                <span style="position: absolute; top: 10px; right: 10px; background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; border: 1px solid #cbd5e1;">Non-Aktif</span>
                            @else
                                <span style="position: absolute; top: 10px; right: 10px; background: #dcfce7; color: #16a34a; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">Tersedia</span>
                            @endif
                        </div>
           
                        <div style="padding: 12px; display: flex; flex-direction: column; gap: 4px; position: relative;">
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $prod->name }}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #02c77f;">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                           
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                <span class="{{ $isHabis ? 'text-stock-habis' : 'text-stock-normal' }}">
                                    Stok: {{ $prod->stock }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. ORDER SUMMARY DI KANAN -->
            <div class="order-summary-panel" style="width: 380px; min-width: 380px; background: #ffffff; border-left: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 20px; box-sizing: border-box; height: 100%;">
            
                <form action="{{ route('cashier.order.store') }}" method="POST" id="checkout-form" onsubmit="prepareCheckout(event)" style="display: flex; flex-direction: column; height: 100%; justify-content: space-between;">
                    @csrf
                    
                    <!-- Hidden input untuk total_amount agar terbaca oleh backend/validasi -->
                    <input type="hidden" name="total_amount" id="totalAmountInput" value="0">

                    <!-- BAGIAN ATAS: Header, Pilih Customer & List Item Keranjang -->
                    <div style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
                        <div class="summary-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                            <span style="font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-receipt" style="color: var(--sidebar-bg);"></i> Order Summary
                            </span>
                            <span class="summary-invoice" style="font-size: 11px; font-weight: 600; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #475569;">#POS-001</span>
                        </div>

                        <!-- Dropdown / Input Pilih atau Tambah Customer -->
                        <div style="margin-bottom: 12px; position: relative;">
                            <label for="customer_search" style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Pelanggan:</label>
                           
                            <input type="text" id="customer_search"
                                data-customers="{{ json_encode($customers) }}"
                                placeholder="Ketik nama pelanggan..." autocomplete="off"
                                style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 11px; background: #f8fafc; outline: none;" required>
                           
                            <!-- Hidden input untuk menyimpan ID customer -->
                            <input type="hidden" name="customer_id" id="customer_id">
                            
                            <!-- Kotak Saran / Autocomplete List -->
                            <div id="customer_suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border-color); border-radius: 6px; max-height: 150px; overflow-y: auto; z-index: 99; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            </div>
                        </div>

                        <!-- Daftar Item Keranjang Dinamis -->
                        <div class="summary-items-list" id="cart-items-container" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 4px; max-height: calc(100vh - 440px);">
                            <div id="cart-empty-state" style="text-align: center; color: #94a3b8; font-size: 12px; padding: 30px 0;">
                                <i class="fa-solid fa-basket-shopping" style="font-size: 24px; margin-bottom: 8px; color: #cbd5e1; display: block;"></i>
                                Belum ada produk dipilih
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN BAWAH: Kalkulasi & Tombol Pembayaran -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; background: #ffffff;">
                        <!-- Perhitungan Biaya -->
                        <div class="summary-calc" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                            <div class="calc-row" style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                                <span>Subtotal</span>
                                <span id="cart-subtotal" style="font-weight: 600; color: #1e293b;">Rp 0</span>
                            </div>
                            <div class="calc-row" style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                                <span>Pajak / Layanan (0%)</span>
                                <span>Rp 0</span>
                            </div>
                            <div class="calc-row total" style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; color: #1e293b; border-top: 1px dashed #e2e8f0; padding-top: 8px; margin-top: 2px;">
                                <span>Total Payment</span>
                                <span id="cart-total" style="color: #0284c7;">Rp 0</span>
                            </div>
                        </div>

                        <!-- Input Metode Pembayaran -->
                        <div style="margin-bottom: 12px;">
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Metode Pembayaran:</label>
                            <select name="payment_method" id="paymentMethodSelect" onchange="toggleCashInput()" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 11px; background: #f8fafc;">
                                <option value="cash">Tunai / Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="debt">Utang / Bon (Khusus Level 2)</option>
                            </select>
                        </div>  

                        <!-- Input Khusus Pembayaran Tunai (Nominal Bayar & Kembalian) -->
                        <div id="cashPaymentSection" style="margin-bottom: 12px;">
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Uang Tunai Diterima (Rp):</label>
                            <input type="number" name="paid_amount" id="paidAmountInput" placeholder="Masukkan jumlah uang..." oninput="calculateChange()" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 11px; background: #f8fafc; outline: none;">
                           
                            <!-- Pesan error jika uang kurang -->
                            @error('paid_amount')
                                <div style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 10px; margin-top: 3px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                                </div>
                            @enderror

                            <!-- Informasi Kembalian -->
                            <div style="margin-top: 6px; font-size: 11px; font-weight: 600; color: #16a34a;">
                                Kembalian: <span id="changeTextDisplay">Rp 0</span>
                            </div>
                        </div>

                        <!-- Tombol Konfirmasi Pembayaran -->
                        <button type="submit" class="btn-confirm-payment" style="width: 100%; padding: 11px; background: var(--sidebar-bg); border: none; border-radius: 10px; color: #ffffff; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 12px rgba(28, 42, 58, 0.15);">
                            Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection

@section('modals')
    @if(session('success'))
        <div id="toast-alert" class="toast-notification">
            <div style="background: rgba(2, 199, 127, 0.15); color: #02c77f; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                <i class="fa-solid fa-check"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Berhasil!</span>
                <span style="font-size: 12px; color: #64748b;">{{ session('success') }}</span>
            </div>
            <button onclick="closeToast()" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 15px; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                closeToast();
            }, 4000);
            function closeToast() {
                const toast = document.getElementById('toast-alert');
                if (toast) {
                    toast.classList.add('hiding');
                    setTimeout(() => {
                        toast.remove();
                    }, 400);
                }
            }
        </script>
    @endif
@endsection

@section('scripts')
<script>
    let cart = [];
    let currentGrandTotal = 0;
    let activeCategory = 'all';

    const customerSearch = document.getElementById('customer_search');
    const customersData = customerSearch ? JSON.parse(customerSearch.getAttribute('data-customers') || '[]') : [];
    const customerIdInput = document.getElementById('customer_id');
    const suggestionsBox = document.getElementById('customer_suggestions');

    if (customerSearch) {
        customerSearch.addEventListener('input', function() {
            const keyword = this.value.trim();
            const keywordLower = keyword.toLowerCase();
            suggestionsBox.innerHTML = '';
            customerIdInput.value = ''; // Reset ID jika diketik ulang

            if (keyword.length === 0) {
                suggestionsBox.style.display = 'none';
                checkCustomerLevelUI(1);
                return;
            }

            // 1. Filter customer yang sudah ada di database
            const filtered = customersData.filter(c => c.name.toLowerCase().includes(keywordLower));
            
            if (filtered.length > 0) {
                filtered.forEach(cust => {
                    let div = document.createElement('div');
                    div.style.cssText = 'padding: 8px 12px; font-size: 11px; cursor: pointer; border-bottom: 1px solid #f1f5f9;';
                    let level = cust.buyer_level || 1;
                    div.innerHTML = `<strong>${cust.name}</strong> <span style="font-size: 9px; background: ${level >= 2 ? '#dcfce7' : '#f1f5f9'}; color: ${level >= 2 ? '#16a34a' : '#64748b'}; padding: 2px 6px; border-radius: 4px; float: right;">Level ${level}</span>`;
                   
                    div.onclick = function() {
                        customerSearch.value = cust.name;
                        customerIdInput.value = cust.id;
                        suggestionsBox.style.display = 'none';
                        checkCustomerLevelUI(level);
                    };
                    suggestionsBox.appendChild(div);
                });
            }

            // 2. Opsi membuat Pelanggan Baru (Tampil jika diketik nama baru)
            let createDiv = document.createElement('div');
            createDiv.style.cssText = 'padding: 8px 12px; font-size: 11px; cursor: pointer; background: #f0fdf4; color: #16a34a; font-weight: 600; border-top: 1px solid #dcfce7;';
            createDiv.innerHTML = `<i class="fa-solid fa-plus"></i> Tambah "${keyword}" sebagai Pelanggan Baru`;
            
            createDiv.onclick = function() {
                customerSearch.value = keyword;
                customerIdInput.value = 'NEW:' + keyword; // Penanda untuk dibuat otomatis di backend
                suggestionsBox.style.display = 'none';
                checkCustomerLevelUI(1); // Pelanggan baru default ke Level 1
            };
            
            suggestionsBox.appendChild(createDiv);
            suggestionsBox.style.display = 'block';
        });
    }

    document.addEventListener('click', function(e) {
        if (customerSearch && suggestionsBox && !customerSearch.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Fungsi Gabungan untuk Filter Kategori & Pencarian Real-time
    function filterProducts(categoryId = null, element = null) {
        if (categoryId !== null) {
            activeCategory = categoryId;
            
            // Ubah class 'active' pada tombol kategori secara visual
            document.querySelectorAll('.category-pill').forEach(btn => {
                btn.classList.remove('active');
                // Berikan styling aktif sederhana jika diperlukan
                btn.style.background = 'transparent';
                btn.style.color = '#475569';
            });
            if (element) {
                element.classList.add('active');
                element.style.background = 'var(--sidebar-bg, #1c2a3a)';
                element.style.color = '#ffffff';
            }
        }

        const searchInput = document.getElementById('productSearchInput');
        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const productCards = document.querySelectorAll('.product-card');

        let visibleCount = 0;

        productCards.forEach(card => {
            const productName = (card.getAttribute('data-name') || '').toLowerCase();
            const productCatId = card.getAttribute('data-category-id');

            // Cek kecocokan kategori
            const matchesCategory = (activeCategory === 'all' || productCatId === String(activeCategory));
            
            // Cek kecocokan keyword pencarian
            const matchesSearch = productName.includes(keyword);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex'; // Tampilkan kembali
                visibleCount++;
            } else {
                card.style.display = 'none'; // Sembunyikan
            }
        });
    }

    function addToCart(id, name, price, stock) {
        let existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            if (existingItem.qty < stock) {
                existingItem.qty++;
            } else {
                alert('Stok produk tidak mencukupi!');
                return;
            }
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                qty: 1,
                maxStock: stock
            });
        }
        renderCart();
    }

    function updateQty(id, change) {
        let item = cart.find(item => item.id === id);
        if (item) {
            let newQty = item.qty + change;
            if (newQty > 0 && newQty <= item.maxStock) {
                item.qty = newQty;
            } else if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else {
                alert('Stok maksimum tercapai!');
            }
        }
        renderCart();
    }

    function setQtyManual(id, newQtyValue) {
        let newQty = parseInt(newQtyValue);
        let item = cart.find(i => i.id === id);
        if (item) {
            if (isNaN(newQty) || newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.maxStock) {
                alert('Stok maksimum hanya ' + item.maxStock + '!');
                item.qty = item.maxStock;
            } else {
                item.qty = newQty;
            }
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const subtotalEl = document.getElementById('cart-subtotal');
        const totalEl = document.getElementById('cart-total');
        const totalInput = document.getElementById('totalAmountInput');

        if (!container) return;
        container.innerHTML = '';

        if (cart.length === 0) {
            container.innerHTML = `
                <div id="cart-empty-state" style="text-align: center; color: #94a3b8; font-size: 12px; padding: 30px 0;">
                    <i class="fa-solid fa-basket-shopping" style="font-size: 24px; margin-bottom: 8px; color: #cbd5e1; display: block;"></i>
                    Belum ada produk dipilih
                </div>`;
            subtotalEl.innerText = 'Rp 0';
            totalEl.innerText = 'Rp 0';
            currentGrandTotal = 0;
            if (totalInput) totalInput.value = 0;
            calculateChange();
            return;
        }

        let subtotal = 0;
        cart.forEach(item => {
            let itemTotal = item.price * item.qty;
            subtotal += itemTotal;
            let itemElement = document.createElement('div');
            itemElement.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px;';
           
            itemElement.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 2px; max-width: 140px;">
                    <span style="font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name}</span>
                    <span style="font-size: 11px; color: #64748b;">Rp ${item.price.toLocaleString('id-ID')}</span>
                </div>
               
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 2px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px;">
                        <button type="button" onclick="updateQty('${item.id}', -1)" style="background: none; border: none; width: 20px; height: 20px; cursor: pointer; font-size: 11px; font-weight: bold; color: #475569;">-</button>
                        <input type="number" value="${item.qty}" min="1" max="${item.maxStock}" onchange="setQtyManual('${item.id}', this.value)" style="width: 38px; text-align: center; border: none; outline: none; font-size: 11px; font-weight: 700; color: #1e293b; -moz-appearance: textfield; padding: 0;">
                        <button type="button" onclick="updateQty('${item.id}', 1)" style="background: none; border: none; width: 20px; height: 20px; cursor: pointer; font-size: 11px; font-weight: bold; color: #475569;">+</button>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: #1e293b; min-width: 65px; text-align: right;">Rp ${itemTotal.toLocaleString('id-ID')}</span>
                    <button type="button" onclick="setQtyManual('${item.id}', 0)" title="Hapus Barang" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 2px; font-size: 12px;">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            `;
            container.appendChild(itemElement);
        });

        let formattedTotal = 'Rp ' + subtotal.toLocaleString('id-ID');
        subtotalEl.innerText = formattedTotal;
        totalEl.innerText = formattedTotal;
       
        currentGrandTotal = subtotal;
        if (totalInput) {
            totalInput.value = subtotal;
        }
        calculateChange();
    }

    function handleCardClick(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        const price = parseFloat(element.getAttribute('data-price'));
        const stock = parseInt(element.getAttribute('data-stock'));
        addToCart(id, name, price, stock);
    }

    function prepareCheckout(event) {
        if (cart.length === 0) {
            event.preventDefault();
            alert('Belum ada produk yang dipilih dalam keranjang!');
            return;
        }
        document.querySelectorAll('input[name^="cart["]').forEach(el => el.remove());
        const form = document.getElementById('checkout-form');
        if (form) {
            cart.forEach((item, index) => {
                let inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = `cart[${index}][product_id]`;
                inputId.value = item.id;
                form.appendChild(inputId);
                let inputQty = document.createElement('input');
                inputQty.type = 'hidden';
                inputQty.name = `cart[${index}][quantity]`;
                inputQty.value = item.qty;
                form.appendChild(inputQty);
            });
        }
    }

    // Fungsi untuk menyembunyikan/menampilkan input cash berdasarkan pilihan metode pembayaran
    function toggleCashInput() {
        const paymentMethod = document.getElementById('paymentMethodSelect');
        const cashSection = document.getElementById('cashPaymentSection');
    
        if (paymentMethod && cashSection) {
            // Tampilkan input jika memilih 'cash' ATAU 'debt' (utang)
            if (paymentMethod.value === 'cash' || paymentMethod.value === 'debt') {
                cashSection.style.display = 'block';
            } else {
                cashSection.style.display = 'none';
            }
        }
    }

    function calculateChange() {
        const paidInput = document.getElementById('paidAmountInput');
        const changeText = document.getElementById('changeTextDisplay');
       
        if (!paidInput || !changeText) return;
        let paidAmount = parseFloat(paidInput.value || 0);
        let change = paidAmount - currentGrandTotal;
       
        if (paidInput.value === '' || paidAmount === 0) {
            changeText.style.color = '#16a34a';
            changeText.innerText = 'Rp 0';
        } else if (change >= 0) {
            changeText.style.color = '#16a34a';
            changeText.innerText = 'Rp ' + change.toLocaleString('id-ID');
        } else {
            changeText.style.color = '#dc2626';
            changeText.innerText = 'Uang kurang Rp ' + Math.abs(change).toLocaleString('id-ID');
        }
    }

    // --- Fungsi Modal Edit Produk ---
    function openEditModal(id, name, categoryId, purchasePrice, sellingPrice, stock, isAvailable) {
        const modal = document.getElementById('editProductModal');
        const form = document.getElementById('editProductForm');
        
        // Set action form ke route update produk (sesuaikan dengan nama route update di web.php Anda, misal: /product/{id})
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
    function previewImageModal(event) {
        const reader = new FileReader();
        const imagePreview = document.getElementById('img-preview-mac');
        const previewIcon = document.getElementById('preview-icon-mac');
        reader.onload = function() {
            if (imagePreview && previewIcon) {
                imagePreview.src = reader.result;
                imagePreview.style.display = 'block';
                previewIcon.style.display = 'none';
            }
        }
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
    function openCashierDebtModal(event) {
        if (event) event.preventDefault();
        const modal = document.getElementById('cashierDebtModal');
        if (modal) modal.classList.add('active');
    }
    function closeCashierDebtModal() {
        const modal = document.getElementById('cashierDebtModal');
        if (modal) modal.classList.remove('active');
    }
    function switchDebtTab(tab) {
        ['unpaid', 'partial', 'paid'].forEach(t => {
            const content = document.getElementById('dcontent-' + t);
            const btn = document.getElementById('dtab-' + t);
            if (content) content.style.display = (t === tab) ? 'block' : 'none';
            if (btn) {
                if(t === tab) {
                    btn.style.background = 'var(--sidebar-bg)';
                    btn.style.color = '#fff';
                } else {
                    btn.style.background = '#e2e8f0';
                    btn.style.color = '#64748b';
                }
            }
        });
    }

    window.addEventListener('click', function(event) {
        const addProductModal = document.getElementById('addProductModal');
        const cashierDebtModal = document.getElementById('cashierDebtModal');
        if (event.target === addProductModal) closeAddModal();
        if (event.target === cashierDebtModal) closeCashierDebtModal();
    });

    function checkCustomerLevelUI(level) {
        const paymentSelect = document.querySelector('select[name="payment_method"]');
        if (!paymentSelect) return;
        let debtOption = paymentSelect.querySelector('option[value="debt"]');
       
        if (level < 2) {
            if (debtOption) {
                debtOption.disabled = true;
                debtOption.text = "Utang / Bon (Khusus Level 2)";
            }
            if (paymentSelect.value === 'debt') {
                paymentSelect.value = 'cash';
                toggleCashInput();
            }
        } else {
            if (debtOption) {
                debtOption.disabled = false;
                debtOption.text = "Utang / Bon";
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleCashInput();
        calculateChange();
    });
</script>
@endsection