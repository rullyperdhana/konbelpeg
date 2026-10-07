<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KONBELPEG BKAD Kabupaten Tapin</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --primary: #1e40af;
            --primary-hover: #1d4ed8;
            --primary-subtle: #eff6ff;
            --primary-border: #bfdbfe;
            --brand-navy: #0f172a;
            --brand-navy-light: #1e293b;
            --accent-gold: #b45309;
            --accent-gold-subtle: #fef3c7;
            
            --bg-canvas: #f1f5f9;
            --bg-surface: #ffffff;
            --border-color: #e2e8f0;
            --border-focus: #3b82f6;
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            
            --card-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }

        :root.dark-mode {
            --primary: #3b82f6;
            --primary-hover: #60a5fa;
            --primary-subtle: rgba(59, 130, 246, 0.12);
            --primary-border: rgba(59, 130, 246, 0.25);
            --brand-navy: #0b1120;
            --brand-navy-light: #131d33;
            --accent-gold: #f59e0b;
            --accent-gold-subtle: rgba(245, 158, 11, 0.12);
            
            --bg-canvas: #090e17;
            --bg-surface: #111827;
            --border-color: #1f2937;
            --border-focus: #60a5fa;
            
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-light: #64748b;
            
            --card-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
        }

        /* Subtle Government Grid Background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(100, 116, 139, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(100, 116, 139, 0.04) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        /* Top Bar Controls */
        .top-bar {
            position: absolute;
            top: 1.25rem;
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
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.15rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .theme-toggle-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Main Container */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 980px;
            background: var(--bg-surface);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            overflow: hidden;
        }

        @media (max-width: 860px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
            .panel-institutional {
                display: none;
            }
        }

        /* Left Panel: Institutional & Brand */
        .panel-institutional {
            background: linear-gradient(165deg, #0f172a 0%, #1e293b 60%, #172554 100%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .inst-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .inst-badge-icon {
            width: 46px;
            height: 46px;
            background: #1e3a8a;
            border: 1px solid rgba(191, 219, 254, 0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .inst-badge-text h3 {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.25;
            color: #ffffff;
        }

        .inst-badge-text p {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .inst-body {
            margin: 2.5rem 0;
        }

        .inst-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(180, 83, 9, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.35);
            color: #fde68a;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.725rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .inst-title {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.025em;
            color: #ffffff;
            margin-bottom: 0.85rem;
        }

        .inst-desc {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #cbd5e1;
            font-weight: 400;
        }

        .inst-features {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .inst-feature-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.825rem;
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 0.75rem 0.9rem;
            border-radius: 8px;
        }

        .inst-feature-item i {
            font-size: 1.15rem;
            color: #60a5fa;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .inst-feature-item strong {
            display: block;
            font-size: 0.85rem;
            color: #ffffff;
            margin-bottom: 2px;
        }

        .inst-footer {
            font-size: 0.75rem;
            color: #94a3b8;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .inst-footer span {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Right Panel: Clean Professional Form */
        .panel-form {
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--bg-surface);
        }

        @media (max-width: 480px) {
            .panel-form {
                padding: 2.5rem 1.75rem;
            }
        }

        .mobile-header {
            display: none;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        @media (max-width: 860px) {
            .mobile-header {
                display: flex;
                align-items: center;
                gap: 0.85rem;
            }
        }

        .mobile-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .mobile-header-text h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }

        .mobile-header-text p {
            font-size: 0.775rem;
            color: var(--text-muted);
        }

        .form-header {
            margin-bottom: 1.75rem;
        }

        .form-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .form-header p {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Alerts */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.825rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            line-height: 1.45;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .alert-info {
            background-color: var(--primary-subtle);
            border: 1px solid var(--primary-border);
            color: var(--primary);
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--text-main);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 0.9rem;
            font-size: 1.15rem;
            color: var(--text-light);
            pointer-events: none;
            transition: color 0.15s ease;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 1rem 0 2.6rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--primary-subtle);
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--primary);
        }

        .btn-toggle-password {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            font-size: 1.15rem;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-toggle-password:hover {
            color: var(--text-main);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.825rem;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            user-select: none;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            height: 46px;
            background: #1e3a8a;
            color: #ffffff;
            border: 1px solid #172554;
            border-radius: 8px;
            font-size: 0.925rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.1);
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .btn-submit:hover {
            background: #1d4ed8;
            border-color: #1e40af;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .security-notice {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .security-notice i {
            font-size: 1.05rem;
            color: #059669;
            flex-shrink: 0;
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode" title="Ganti Mode Gelap / Terang">
            <i class="ph ph-moon"></i>
        </button>
    </div>

    <div class="login-card">
        <!-- Panel Kiri: Identitas Institusi Pemerintah -->
        <div class="panel-institutional">
            <div>
                <div class="inst-header">
                    <div class="inst-badge-icon">
                        <i class="ph ph-buildings"></i>
                    </div>
                    <div class="inst-badge-text">
                        <h3>BKAD KABUPATEN TAPIN</h3>
                        <p>Bidang Anggaran & Perbendaharaan</p>
                    </div>
                </div>

                <div class="inst-body">
                    <div class="inst-tag">
                        <i class="ph-bold ph-shield-check"></i> Portal Resmi Pemerintah Daerah
                    </div>
                    <h1 class="inst-title">KONBELPEG</h1>
                    <p class="inst-desc">
                        Sistem Rekonsiliasi & Realisasi Belanja Pegawai Pemerintah Kabupaten Tapin. Terintegrasi dengan database penggajian SIMGAJI Taspen dan SIMPEG.
                    </p>

                    <div class="inst-features">
                        <div class="inst-feature-item">
                            <i class="ph ph-arrows-clockwise"></i>
                            <div>
                                <strong>Sinkronisasi Basis Data SIMGAJI DBF</strong>
                                <span>Pemadanan master data pegawai, riwayat gaji, dan tanggungan keluarga.</span>
                            </div>
                        </div>
                        <div class="inst-feature-item">
                            <i class="ph ph-scales"></i>
                            <div>
                                <strong>Rekonsiliasi & Audit Belanja Pegawai</strong>
                                <span>Verifikasi belanja gaji pokok, tunjangan keluarga, dan Tambahan Penghasilan Pegawai (TPP).</span>
                            </div>
                        </div>
                        <div class="inst-feature-item">
                            <i class="ph ph-file-text"></i>
                            <div>
                                <strong>Pelaporan & Akuntabilitas Anggaran</strong>
                                <span>Format laporan resmi SIKD, belanja ASN, dan cetak ekspor dokumen audit.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="inst-footer">
                <span>&copy; {{ date('Y') }} BKAD Kabupaten Tapin</span>
                <span><i class="ph ph-lock-key"></i> Sistem Terproteksi SSL</span>
            </div>
        </div>

        <!-- Panel Kanan: Formulir Masuk Resmi -->
        <div class="panel-form">
            <!-- Mobile Header (Tampil pada layar kecil) -->
            <div class="mobile-header">
                <div class="mobile-logo-icon">
                    <i class="ph ph-buildings"></i>
                </div>
                <div class="mobile-header-text">
                    <h3>KONBELPEG TAPIN</h3>
                    <p>BKAD Kabupaten Tapin</p>
                </div>
            </div>

            <div class="form-header">
                <h2>Masuk ke Sistem</h2>
                <p>Silakan masukkan kredensial akun pengguna resmi Anda.</p>
            </div>

            @if(session('info'))
                <div class="alert alert-info">
                    <i class="ph ph-info" style="font-size: 1.15rem; flex-shrink: 0; margin-top: 1px;"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="ph ph-warning-circle" style="font-size: 1.15rem; flex-shrink: 0; margin-top: 1px;"></i>
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
                    <label class="form-label" for="email">Alamat Email Pengguna</label>
                    <div class="input-wrapper">
                        <i class="ph ph-envelope-simple input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control" 
                            placeholder="nama@bkadtapin.go.id" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key input-icon"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control" 
                            placeholder="••••••••••••" 
                            required 
                            autocomplete="current-password"
                        >
                        <button type="button" class="btn-toggle-password" id="togglePasswordBtn" aria-label="Lihat Kata Sandi" title="Tampilkan / Sembunyikan Kata Sandi">
                            <i class="ph ph-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat sesi masuk saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Masuk ke Dasbor</span>
                    <i class="ph ph-arrow-right" style="font-size: 1.1rem;"></i>
                </button>
            </form>

            <div class="security-notice">
                <i class="ph-bold ph-shield-check"></i>
                <div>
                    Akses terbatas untuk aparatur yang berwenang di lingkungan Pemerintah Kabupaten Tapin. Seluruh aktivitas akses tercatat dalam log audit sistem.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Handler
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

        // Submit Loading State
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.style.opacity = '0.75';
            btn.innerHTML = '<i class="ph ph-spinner ph-spin" style="font-size: 1.15rem;"></i><span>Memverifikasi...</span>';
        });
    </script>
</body>
</html>
