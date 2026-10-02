<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem Realisasi Belanja Pegawai</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --primary: #4C35DE;
            --primary-hover: #3b25cb;
            --primary-light: rgba(76, 53, 222, 0.08);
            --primary-border: rgba(76, 53, 222, 0.2);
            --bg-canvas: #f4f6fa;
            --bg-surface: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.07), 0 0 1px 1px rgba(0, 0, 0, 0.04);
        }

        :root.dark-mode {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: rgba(99, 102, 241, 0.15);
            --primary-border: rgba(99, 102, 241, 0.3);
            --bg-canvas: #0b111e;
            --bg-surface: #151d30;
            --border-color: #212d46;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4), 0 0 1px 1px rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Background */
        .ambient-glow {
            position: fixed;
            width: 650px;
            height: 650px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(76, 53, 222, 0.12) 0%, rgba(76, 53, 222, 0) 70%);
            top: -150px;
            right: -150px;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0) 70%);
            bottom: -100px;
            left: -100px;
            pointer-events: none;
            z-index: 0;
        }

        .dark-mode .ambient-glow {
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, rgba(99, 102, 241, 0) 70%);
        }

        /* Top Bar Actions */
        .top-actions {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 10;
        }

        .theme-toggle-btn {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.25rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .theme-toggle-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Main Card Container */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1020px;
            background: var(--bg-surface);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            overflow: hidden;
        }

        @media (max-width: 860px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 480px;
            }
            .brand-side {
                display: none;
            }
        }

        /* Left Side: Brand & Visuals */
        .brand-side {
            background: linear-gradient(145deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-side::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            border-radius: 50%;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #ffffff;
        }

        .brand-logo-text h3 {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-logo-text p {
            font-size: 0.8rem;
            opacity: 0.8;
            font-weight: 400;
        }

        .brand-hero {
            margin: 3rem 0;
        }

        .brand-hero h1 {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
        }

        .brand-hero p {
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.85;
            font-weight: 400;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.65rem 1rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .feature-item i {
            font-size: 1.15rem;
            color: #a5b4fc;
        }

        .brand-footer {
            font-size: 0.775rem;
            opacity: 0.7;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        /* Right Side: Form */
        .form-side {
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 480px) {
            .form-side {
                padding: 2.25rem 1.75rem;
            }
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Alerts */
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            line-height: 1.45;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #ef4444;
        }

        .alert-info {
            background-color: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.25);
            color: #3b82f6;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            margin-bottom: 0.45rem;
            color: var(--text-main);
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            font-size: 1.25rem;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 1rem 0 2.75rem;
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.925rem;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-input:focus + .input-icon,
        .input-group:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-password {
            position: absolute;
            right: 0.85rem;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--text-main);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(76, 53, 222, 0.35);
            transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(76, 53, 222, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Demo / Dev Helper Box */
        .dev-credentials {
            margin-top: 1.75rem;
            padding: 1rem;
            background: var(--primary-light);
            border: 1px dashed var(--primary-border);
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .dev-credentials-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .btn-quick-fill {
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-quick-fill:hover {
            background: var(--primary-hover);
        }

        .dev-credentials-body {
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="ambient-glow-2"></div>

    <div class="top-actions">
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode" title="Ganti Mode Gelap / Terang">
            <i class="ph ph-moon"></i>
        </button>
    </div>

    <div class="login-wrapper">
        <!-- Brand / Graphic Side -->
        <div class="brand-side">
            <div>
                <div class="brand-header">
                    <div class="brand-logo-icon">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div class="brand-logo-text">
                        <h3>BPKAD</h3>
                        <p>Bidang Anggaran & Perbendaharaan</p>
                    </div>
                </div>

                <div class="brand-hero">
                    <h1>Sistem Realisasi Belanja Pegawai</h1>
                    <p>Platform terpadu untuk monitoring belanja pegawai, sinkronisasi SIMGAJI DBF, kalkulasi TPP, dan pelaporan keuangan daerah.</p>
                </div>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="ph ph-database"></i>
                        <span>Integrasi DBF SIMGAJI & Database Master Pegawai</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-chart-line-up"></i>
                        <span>Rekonsiliasi Realisasi Belanja Gaji & TPP ASN</span>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-file-text"></i>
                        <span>Ekspor Laporan SIKD, PPPK Guru, & Excel/PDF</span>
                    </div>
                </div>
            </div>

            <div class="brand-footer">
                &copy; {{ date('Y') }} Pemerintah Daerah. Hak cipta dilindungi undang-undang.
            </div>
        </div>

        <!-- Form Side -->
        <div class="form-side">
            <div class="form-header">
                <h2>Selamat Datang</h2>
                <p>Masukkan akun Anda untuk melanjutkan ke dasbor sistem.</p>
            </div>

            @if(session('info'))
                <div class="alert alert-info">
                    <i class="ph ph-info" style="font-size: 1.25rem;"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="ph ph-warning-circle" style="font-size: 1.25rem;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-group">
                        <i class="ph ph-envelope-simple input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-input" 
                            placeholder="admin@pemda.go.id" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-group">
                        <i class="ph ph-lock-key input-icon"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-input" 
                            placeholder="••••••••" 
                            required 
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Lihat Kata Sandi">
                            <i class="ph ph-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Masuk ke Dashboard</span>
                    <i class="ph ph-arrow-right" style="font-size: 1.15rem;"></i>
                </button>
            </form>

            <!-- Quick dev access helper -->
            <div class="dev-credentials">
                <div class="dev-credentials-header">
                    <span><i class="ph ph-key"></i> Akun Bawaan (Default):</span>
                    <button type="button" class="btn-quick-fill" id="quickFillBtn" title="Isi form otomatis">
                        <i class="ph ph-lightning"></i> Isi Otomatis
                    </button>
                </div>
                <div class="dev-credentials-body">
                    <div>Email: <strong>admin@pemda.go.id</strong></div>
                    <div>Password: <strong>password</strong></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark-mode');
            themeIcon.classList.replace('ph-moon', 'ph-sun');
        } else {
            document.documentElement.classList.remove('dark-mode');
            themeIcon.classList.replace('ph-sun', 'ph-moon');
        }

        themeToggleBtn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark-mode');
            const isDark = document.documentElement.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            if (isDark) {
                themeIcon.classList.replace('ph-moon', 'ph-sun');
            } else {
                themeIcon.classList.replace('ph-sun', 'ph-moon');
            }
        });

        // Toggle Password Visibility
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            if (isPassword) {
                toggleIcon.classList.replace('ph-eye', 'ph-eye-slash');
            } else {
                toggleIcon.classList.replace('ph-eye-slash', 'ph-eye');
            }
        });

        // Quick Fill for convenience
        document.getElementById('quickFillBtn').addEventListener('click', () => {
            document.getElementById('email').value = 'admin@pemda.go.id';
            document.getElementById('password').value = 'password';
            document.getElementById('remember').checked = true;
        });

        // Submit loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.innerHTML = '<i class="ph ph-spinner ph-spin" style="font-size: 1.25rem;"></i><span>Memproses...</span>';
        });
    </script>
</body>
</html>
