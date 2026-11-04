<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madusis</title>
    <link rel="icon" href="{{ asset('images/logo2.svg') }}" type="image/png">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Menggunakan CSS External yang sama dengan halaman login -->
    <link rel="stylesheet" href="{{ asset('css/login-swirl.css') }}">
</head>
<body>

    <!-- Canvas Simulasi Fisika Air -->
    <canvas id="water-canvas"></canvas>

    <!-- Header / Brand -->
    <div class="top-navbar">
        <a href="#" class="brand-logo">
            <i class="fa-solid fa-store"></i> MADUSIS
        </a>
        <a href="{{ route('login') }}" class="menu-btn">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI
        </a>
    </div>

    <!-- Register Card (Disamakan ukurannya dengan login card, menyesuaikan form pendaftaran) -->
    <div class="login-card" style="max-width: 460px; padding: 35px 30px;">
        <div class="login-header">
            <h2>DAFTAR AKUN</h2>
            <p>Buat akun customer untuk memesan dari rumah</p>
        </div>

        <!-- Notifikasi Error Validasi Laravel -->
        @if ($errors->any())
            <div class="alert-danger">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Field Nama Lengkap -->
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autofocus>
                </div>
            </div>

            <!-- Field Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                </div>
            </div>

            <!-- Field Nomor WhatsApp -->
            <div class="form-group">
                <label for="phone">No. WhatsApp</label>
                <div class="input-wrapper">
                    <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <!-- Field Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Minimal 8 karakter" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
            </div>

            <!-- Field Konfirmasi Password -->
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn-submit" style="margin-top: 10px;">
                Daftar Sekarang
            </button>
        </form>

        <!-- Link Kembali ke Login -->
        <div class="register-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </div>

    <script>
        // Fungsi Sederhana untuk Toggle Intip Password
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

        // Script Simulasi Fisika Air (Konsisten dengan halaman login)
        const canvas = document.getElementById('water-canvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        let cols = 100;
        let rows = 70;
        let buffer1, buffer2;
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
                buffer1[cx + 1][cy] = 300;
                buffer1[cx - 1][cy] = 300;
                buffer1[cx][cy + 1] = 300;
                buffer1[cx][cy - 1] = 300;
            }
        });

        setInterval(() => {
            let rx = Math.floor(Math.random() * (cols - 4)) + 2;
            let ry = Math.floor(Math.random() * (rows - 4)) + 2;
            buffer1[rx][ry] = 400;
        }, 1500);

        function computeWaterPhysics() {
            for (let x = 1; x < cols - 1; x++) {
                for (let y = 1; y < rows - 1; y++) {
                    buffer2[x][y] = (
                        buffer1[x - 1][y] +
                        buffer1[x + 1][y] +
                        buffer1[x][y - 1] +
                        buffer1[x][y + 1]
                    ) / 2 - buffer2[x][y];

                    buffer2[x][y] *= damping;
                }
            }
            let temp = buffer1;
            buffer1 = buffer2;
            buffer2 = temp;
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