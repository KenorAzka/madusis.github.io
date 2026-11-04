<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madusis - Buat Password Baru</title>
    <link rel="icon" href="{{ asset('images/logo2.svg') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login-swirl.css') }}">
</head>
<body>
    <canvas id="water-canvas"></canvas>
    
    <div class="top-navbar">
        <a href="#" class="brand-logo">
            <i class="fa-solid fa-store"></i> MADUSIS
        </a>
    </div>

    <div class="login-card">
        <div class="login-header">
            <h2>PASSWORD BARU</h2>
            <p>Masukkan password baru untuk akun Anda</p>
        </div>

        @if ($errors->any())
            <div class="alert-danger">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" style="text-align: left;">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="input-wrapper">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Min. 6 karakter" required oninput="validatePassword(this.value)">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
                <!-- Keterangan Syarat Password -->
                <ul id="password-requirements" style="list-style: none; font-size: 10px; color: rgba(255, 255, 255, 0.7); margin-top: 6px; padding-left: 5px; line-height: 1.4;">
                    <li id="length" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Minimal 6 karakter</li>
                    <li id="letter" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Ada huruf besar & kecil</li>
                    <li id="number" style="color: #fca5a5;"><i class="fa-solid fa-circle-xmark"></i> Ada angka atau simbol</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="password-confirm">Konfirmasi Password Baru</label>
                <div class="input-wrapper">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password baru" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password-confirm', this)"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 10px;">
                Reset Password
            </button>
        </form>
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