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
    <!-- Custom CSS External -->
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
        <div class="menu-btn">
            <i class="fa-solid fa-bars"></i> MENU
        </div>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="login-header">
            <h2>LOGIN</h2>
            <p>Masukkan kredensial Anda untuk masuk</p>
        </div>

        <!-- Notifikasi Error Laravel -->
        @if ($errors->any())
            <div class="alert-danger">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Field Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus>
                </div>
            </div>

            <!-- Field Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Masukkan password Anda" required>
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility()"></i>
                </div>
            </div>

            <!-- Remember me & Forgot Password -->
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa Password?</a>
                @endif
            </div>

            <!-- Tombol Login -->
            <button type="submit" class="btn-submit">
                Login
            </button>
        </form>

        <!-- Link Register -->
        <div class="register-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </div>
    </div>

    <script>
        // Toggle Intip Password
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Script Simulasi Fisika Air (Water Ripple Grid Physics)
        const canvas = document.getElementById('water-canvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        let cols = 100;
        let rows = 70;
        let buffer1, buffer2;
        let damping = 0.985; // Redaman agar ayunan air perlahan mereda secara alami

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

        // Saat kursor digerakkan, berikan usikan/gaya (disturbance) pada koordinat air
        window.addEventListener('mousemove', function(e) {
            let cx = Math.floor((e.clientX / width) * cols);
            let cy = Math.floor((e.clientY / height) * rows);

            if (cx > 0 && cx < cols - 1 && cy > 0 && cy < rows - 1) {
                buffer1[cx][cy] = 500; // Kekuatan cipratan / usikan air
                buffer1[cx + 1][cy] = 300;
                buffer1[cx - 1][cy] = 300;
                buffer1[cx][cy + 1] = 300;
                buffer1[cx][cy - 1] = 300;
            }
        });

        // Usikan otomatis sesekali agar air tetap berayun tenang walau kursor diam
        setInterval(() => {
            let rx = Math.floor(Math.random() * (cols - 4)) + 2;
            let ry = Math.floor(Math.random() * (rows - 4)) + 2;
            buffer1[rx][ry] = 400;
        }, 1500);

        function computeWaterPhysics() {
            // Persamaan gelombang air standar (Wave Equation)
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

            // Tukar buffer
            let temp = buffer1;
            buffer1 = buffer2;
            buffer2 = temp;
        }

        function drawWater() {
            computeWaterPhysics();

            // Gradasi warna dasar air yang mendalam (#1c2a3a)
            let bgGrad = ctx.createLinearGradient(0, 0, width, height);
            bgGrad.addColorStop(0, '#050b14');
            bgGrad.addColorStop(0.5, '#0f1c2e');
            bgGrad.addColorStop(1, '#1c2a3a');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);

            // Render efek permukaan air berayun
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