<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madusis - Konfirmasi Password</title>
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

    <div class="login-card" style="max-width: 420px; padding: 30px;">
        <div class="login-header" style="text-align: center; margin-bottom: 20px;">
            <h2>KONFIRMASI PASSWORD</h2>
            <p style="font-size: 12px; color: #64748b;">Harap konfirmasi password Anda sebelum melanjutkan</p>
        </div>

        @if ($errors->any())
            <div class="alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 10px; border-radius: 8px; font-size: 12px; margin-bottom: 15px;">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="password" style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Password</label>
                <div class="input-wrapper" style="position: relative;">
                    <input id="password" type="password" class="form-control" name="password" required style="width: 100%; padding: 8px 32px 8px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px;">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b;"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; padding: 10px; background: #1c2a3a; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                Konfirmasi
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

        // Simulasi Air
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
            requestAnimationFrame(drawWater);
        }
        drawWater();
    </script>
</body>
</html>