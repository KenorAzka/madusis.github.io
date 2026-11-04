<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madusis - Daftar Akun</title>
    <link rel="icon" href="{{ asset('images/logo2.svg') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login-swirl.css') }}">
</head>
<body>
    <!-- Canvas Simulasi Fisika Air -->
    <canvas id="water-canvas"></canvas>
    
    <!-- Top Navbar -->
    <div class="top-navbar">
        <a href="#" class="brand-logo">
            <i class="fa-solid fa-store"></i> MADUSIS
        </a>
        <a href="{{ route('login') }}" class="menu-btn">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI
        </a>
    </div>

    <!-- Register Card (Melebar ke samping dengan Grid 2 Kolom) -->
    <div class="login-card" style="max-width: 650px;">
        <div class="login-header">
            <h2>DAFTAR AKUN</h2>
            <p>Buat akun customer untuk memesan dari rumah</p>
        </div>

        @if ($errors->any())
            <div class="alert-danger" style="grid-column: span 2;">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Grid 2 Kolom Menyamping -->
        <form method="POST" action="{{ route('register') }}" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left;">
            @csrf
            
            <!-- Nama Lengkap (Full Width) -->
            <div class="form-group" style="grid-column: span 2; margin-bottom: 0;">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autofocus>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="email">Email (Untuk Verifikasi)</label>
                <div class="input-wrapper">
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                </div>
            </div>

            <!-- No WhatsApp -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="phone">No. WhatsApp</label>
                <div class="input-wrapper">
                    <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Min. 6 karakter (A-Z, a-z, 0-9)" required oninput="validatePassword(this.value)">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
                <!-- Keterangan Syarat Password di Bawah Input -->
                <ul id="password-requirements" style="list-style: none; font-size: 10px; color: rgba(255, 255, 255, 0.7); margin-top: 6px; padding-left: 5px; line-height: 1.4;">
                    <li id="length" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Minimal 6 karakter</li>
                    <li id="letter" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Ada huruf besar & kecil</li>
                    <li id="number" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Ada angka atau simbol</li>
                </ul>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
                </div>
            </div>

            <!-- Tombol Submit (Full Width) -->
            <div style="grid-column: span 2; margin-top: 10px;">
                <button type="submit" class="btn-submit">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <!-- Footer Link -->
        <div class="register-footer" style="margin-top: 20px;">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, iconElement) {
            const passwordInput = document.getElementById(fieldId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            }
        }
    function validatePassword(password) {
        const lengthItem = document.getElementById('length');
        const letterItem = document.getElementById('letter');
        const numberItem = document.getElementById('number');

        // Cek Panjang Min 6
        if (password.length >= 6) {
            lengthItem.style.color = '#86efac'; // Hijau
            lengthItem.innerHTML = '<i class="fa-solid fa-circle-check"></i> Minimal 6 karakter';
        } else {
            lengthItem.style.color = '#fca5a5'; // Merah
            lengthItem.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Minimal 6 karakter';
        }

        // Cek Huruf Besar & Kecil
        if (/(?=.*[a-z])(?=.*[A-Z])/.test(password)) {
            letterItem.style.color = '#86efac';
            letterItem.innerHTML = '<i class="fa-solid fa-circle-check"></i> Ada huruf besar & kecil';
        } else {
            letterItem.style.color = '#fca5a5';
            letterItem.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Ada huruf besar & kecil';
        }

        // Cek Angka atau Simbol
        if (/(?=.*\d|.*[@$!%*#?&])/.test(password)) {
            numberItem.style.color = '#86efac';
            numberItem.innerHTML = '<i class="fa-solid fa-circle-check"></i> Ada angka atau simbol';
        } else {
            numberItem.style.color = '#fca5a5';
            numberItem.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Ada angka atau simbol';
        }
    }

        // Script Simulasi Fisika Air
        const canvas = document.getElementById('water-canvas');
        const ctx = canvas.getContext('2d');
        let width, height, cols, rows, buffer1, buffer2;
        let damping = 0.985;
        function initWaterGrid() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            cols = Math.floor(width / 15);
            rows = Math.floor(height / 15);
            buffer1 = Array(cols).fill(0).map(() => Array(rows).fill(0));
            buffer2 = Array(cols).fill(0).map(() => Array(rows).fill(0));
        }
        window.addEventListener('resize', initWaterGrid);
        initWaterGrid();
        window.addEventListener('mousemove', function(e) {
            let cx = Math.floor((e.clientX / width) * cols);
            let cy = Math.floor((e.clientY / height) * rows);
            if (cx > 0 && cx < cols - 1 && cy > 0 && cy < rows - 1) {
                buffer1[cx][cy] = 500;
            }
        });
        function computeWaterPhysics() {
            for (let x = 1; x < cols - 1; x++) {
                for (let y = 1; y < rows - 1; y++) {
                    buffer2[x][y] = (buffer1[x - 1][y] + buffer1[x + 1][y] + buffer1[x][y - 1] + buffer1[x][y + 1]) / 2 - buffer2[x][y];
                    buffer2[x][y] *= damping;
                }
            }
            let temp = buffer1; buffer1 = buffer2; buffer2 = temp;
        }
        function drawWater() {
            computeWaterPhysics();
            let bgGrad = ctx.createLinearGradient(0, 0, width, height);
            bgGrad.addColorStop(0, '#050b14');
            bgGrad.addColorStop(0.5, '#0f1c2e');
            bgGrad.addColorStop(1, '#1c2a3a');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);
            
            let cellW = width / cols;
            let cellH = height / rows;
            ctx.fillStyle = 'rgba(56, 189, 248, 0.12)';
            for (let x = 1; x < cols - 1; x++) {
                for (let y = 1; y < rows - 1; y++) {
                    let val = buffer1[x][y];
                    if (val > 0.5) {
                        let px = x * cellW;
                        let py = y * cellH;
                        let size = Math.min(Math.abs(val) * 0.05, cellW * 1.5);
                        ctx.beginPath();
                        ctx.arc(px, py, size, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }
            }
            requestAnimationFrame(drawWater);
        }
        drawWater();
    </script>
</body>
</html>