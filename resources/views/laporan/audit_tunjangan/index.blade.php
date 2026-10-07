@extends('layouts.app')

@section('title', 'Audit Tunjangan Keluarga (SIMGAJI) - Sistem Realisasi Belanja Pegawai')

@section('content')
<style>
    /* ===== STYLING AUDIT TUNJANGAN KELUARGA (LIGHT & DARK MODE COMPATIBLE) ===== */
    .audit-container {
        padding-bottom: 50px;
    }

    /* Hero Banner */
    .audit-hero-card {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 16px;
        padding: 30px 34px;
        color: #ffffff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    :root.dark-mode .audit-hero-card {
        background: linear-gradient(135deg, #0f0e26 0%, #1e1b4b 50%, #2e266d 100%);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6);
        border-color: rgba(99, 102, 241, 0.25);
    }

    .audit-hero-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .audit-hero-desc {
        font-size: 13.5px;
        color: #e0e7ff;
        max-width: 860px;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .kpi-icon-red { background: #fee2e2; color: #ef4444; }
    .kpi-icon-amber { background: #fef3c7; color: #f59e0b; }
    .kpi-icon-indigo { background: #e0e7ff; color: #4f46e5; }
    .kpi-icon-green { background: #dcfce7; color: #16a34a; }
    .kpi-icon-purple { background: #f3e8ff; color: #9333ea; }

    :root.dark-mode .kpi-icon-red { background: rgba(239, 68, 68, 0.18); color: #f87171; }
    :root.dark-mode .kpi-icon-amber { background: rgba(245, 158, 11, 0.18); color: #fbbf24; }
    :root.dark-mode .kpi-icon-indigo { background: rgba(79, 70, 229, 0.18); color: #818cf8; }
    :root.dark-mode .kpi-icon-green { background: rgba(22, 163, 74, 0.18); color: #4ade80; }
    :root.dark-mode .kpi-icon-purple { background: rgba(147, 51, 234, 0.18); color: #c084fc; }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.2;
    }

    .kpi-sub {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Filter & Action Card */
    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .filter-input-wrap {
        position: relative;
        min-width: 240px;
        flex: 1;
    }

    .filter-input-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 16px;
    }

    .filter-input {
        width: 100%;
        background: var(--bg-main, #f8fafc);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 9px 14px 9px 38px;
        font-size: 13px;
        color: var(--text-main);
        outline: none;
        transition: border-color 0.2s ease;
    }

    :root.dark-mode .filter-input {
        background: #1e1b4b15;
    }

    .filter-input:focus {
        border-color: #4f46e5;
    }

    .filter-select {
        background: var(--bg-main, #f8fafc);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 13px;
        color: var(--text-main);
        outline: none;
        max-width: 240px;
    }

    :root.dark-mode .filter-select {
        background: #1e1b4b15;
    }

    .btn-action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s ease;
    }

    .btn-filter:hover {
        background: #4338ca;
        color: #ffffff;
    }

    .btn-outline-custom {
        background: transparent;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-outline-custom:hover {
        background: var(--border-color);
        color: var(--text-main);
    }

    .btn-excel {
        background: #10b981;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s ease;
    }

    .btn-excel:hover {
        background: #059669;
        color: #ffffff;
    }

    .btn-pdf {
        background: #ef4444;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s ease;
    }

    .btn-pdf:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Tab Navigation */
    .audit-tabs {
        display: flex;
        gap: 10px;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .audit-tab-item {
        padding: 12px 20px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .audit-tab-item:hover {
        color: #4f46e5;
    }

    .audit-tab-item.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
    }

    .tab-badge {
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-tab-red { background: #fee2e2; color: #b91c1c; }
    .badge-tab-amber { background: #fef3c7; color: #b45309; }
    .badge-tab-indigo { background: #e0e7ff; color: #4338ca; }

    :root.dark-mode .badge-tab-red { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }
    :root.dark-mode .badge-tab-amber { background: rgba(245, 158, 11, 0.25); color: #fde68a; }
    :root.dark-mode .badge-tab-indigo { background: rgba(79, 70, 229, 0.25); color: #c7d2fe; }

    /* Tables */
    .table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }

    .audit-table th {
        background: var(--bg-main, #f8fafc);
        color: var(--text-muted);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    :root.dark-mode .audit-table th {
        background: #14122e;
    }

    .audit-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
        vertical-align: middle;
    }

    .audit-table tr:hover {
        background: rgba(79, 70, 229, 0.03);
    }

    :root.dark-mode .audit-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .child-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
    }

    .child-meta {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .parent-card {
        background: var(--bg-main, #f8fafc);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px;
    }

    :root.dark-mode .parent-card {
        background: rgba(255, 255, 255, 0.03);
    }

    .parent-name {
        font-weight: 700;
        color: var(--text-main);
        font-size: 12.5px;
    }

    .parent-nip {
        font-family: monospace;
        font-size: 11.5px;
        color: #4f46e5;
    }

    .parent-skpd {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .badge-status-danger {
        background: #fee2e2;
        color: #dc2626;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    :root.dark-mode .badge-status-danger {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    .badge-status-warning {
        background: #fef3c7;
        color: #d97706;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    :root.dark-mode .badge-status-warning {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }

    .badge-status-success {
        background: #dcfce7;
        color: #15803d;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    :root.dark-mode .badge-status-success {
        background: rgba(22, 163, 74, 0.2);
        color: #4ade80;
    }

    /* Settlement STS Card inside table cell */
    .sts-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 11.5px;
        color: #166534;
        margin-top: 6px;
    }

    :root.dark-mode .sts-box {
        background: rgba(22, 163, 74, 0.1);
        border-color: rgba(34, 197, 94, 0.25);
        color: #86efac;
    }

    .sts-meta {
        font-size: 10.5px;
        color: #15803d;
        opacity: 0.85;
        margin-top: 2px;
    }

    :root.dark-mode .sts-meta {
        color: #bbf7d0;
    }

    .btn-action-resolve {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    .btn-action-resolve:hover {
        background: #4338ca;
        color: #ffffff;
    }

    .btn-action-resolved {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    :root.dark-mode .btn-action-resolved {
        background: rgba(22, 163, 74, 0.2);
        border-color: rgba(34, 197, 94, 0.4);
        color: #4ade80;
    }

    .btn-action-resolved:hover {
        background: #bbf7d0;
    }

    .btn-trace-link {
        font-size: 11px;
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }

    .btn-trace-link:hover {
        text-decoration: underline;
    }

    /* ===== CUSTOM POLISHED PAGINATION ===== */
    .pagination-container {
        padding: 16px 24px;
        background: var(--bg-surface);
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border-radius: 0 0 16px 16px;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-info strong {
        color: var(--text-main);
        font-weight: 700;
    }

    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-main);
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        user-select: none;
    }

    .page-btn:hover:not(.disabled):not(.active) {
        background: var(--bg-surface-subtle, rgba(0, 0, 0, 0.04));
        border-color: var(--luno-primary, #4f46e5);
        color: var(--luno-primary, #4f46e5);
        transform: translateY(-1px);
    }

    .page-btn.active {
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.28);
    }

    .page-btn.disabled {
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
        background: var(--bg-surface-subtle, rgba(0, 0, 0, 0.02));
    }

    .page-dots {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 34px;
        color: var(--text-muted);
        font-weight: 700;
        font-size: 13px;
    }

    .page-indicator-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(79, 70, 229, 0.08);
        color: #4f46e5;
        border: 1px solid rgba(79, 70, 229, 0.2);
        font-size: 12px;
        font-weight: 700;
    }
</style>

<div class="audit-container">

    {{-- Hero Banner --}}
    <div class="audit-hero-card">
        <div class="audit-hero-title">
            <i class="ph ph-shield-check" style="font-size: 28px;"></i>
            Audit Tunjangan Keluarga SIMGAJI (Uji Silang Dobel Menunjang)
        </div>
        <p class="audit-hero-desc">
            Sistem pengawasan cerdas berbasis rekonsiliasi berkas <strong>SIMGAJI Taspen (KEL &amp; MST_PGW)</strong>. Modul ini secara otomatis mengidentifikasi anomali pembayaran ganda tunjangan suami/istri (10%), dobel tunjangan anak (2%), dan kuota &gt;2 anak. Setiap kasus dapat <strong>diselesaikan dengan pencatatan bukti Surat Tanda Setoran (STS)</strong> ke Kas Daerah sehingga auditor dapat fokus menuntaskan kasus yang tersisa.
        </p>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
            <i class="ph ph-check-circle me-2" style="font-size: 18px;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Status Berkas DBF Riwayat Keluarga --}}
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(5, 150, 105, 0.12); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="ph ph-users-four"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-main);">Sumber Berkas DBF Riwayat Keluarga:</span>
                    @if(!empty($activeKel))
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                            <i class="ph ph-file-code"></i> {{ $activeKel['filename'] }} ({{ $activeKel['size'] ?? '-' }})
                        </span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                            <i class="ph ph-warning-circle"></i> Belum ada file KEL_*.DBF aktif
                        </span>
                    @endif

                    @if(($totalKeluargaDb ?? 0) > 0)
                        <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                            <i class="ph ph-database"></i> {{ number_format($totalKeluargaDb, 0, ',', '.') }} tanggungan di database
                        </span>
                    @else
                        <span class="badge" style="background: rgba(245, 158, 11, 0.18); color: #d97706; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                            <i class="ph ph-warning"></i> Tabel database masih 0 baris (Perlu Sinkronisasi)
                        </span>
                    @endif
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                    Modul audit menganalisis data tanggungan dari berkas <code>KEL_*.DBF</code> SIMGAJI Taspen yang telah dimuat ke basis data.
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-sm" onclick="openUploadKelModal()" style="background: #059669; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph ph-upload-simple"></i> Unggah / Ganti KEL_*.DBF
            </button>
            <form action="{{ route('master.simgaji_dbf.sync') }}" method="POST" class="form-sync-dbf" data-label="Data Anggota Keluarga & Tanggungan" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="keluarga">
                <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;" title="Muat ulang seluruh record dari file DBF aktif ke tabel database">
                    <i class="ph ph-arrows-clockwise"></i> Sinkronkan ke DB
                </button>
            </form>
            <a href="{{ route('master.simgaji_dbf.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;" title="Buka menu manajemen seluruh berkas DBF">
                <i class="ph ph-folder-open"></i> Kelola DBF
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        {{-- Card 1: Dobel Anak --}}
        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-red">
                <i class="ph ph-baby"></i>
            </div>
            <div>
                <div class="kpi-label">Kasus Dobel Anak</div>
                <div class="kpi-value text-danger">{{ number_format($stats['total_dobel_anak']) }}</div>
                <div class="kpi-sub">
                    <span class="text-danger fw-bold">{{ $stats['anak_pending'] }} Belum</span> • 
                    <span class="text-success fw-bold">{{ $stats['anak_selesai'] }} STS Selesai</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Pasangan Saling Menunjang --}}
        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-purple">
                <i class="ph ph-users-three"></i>
            </div>
            <div>
                <div class="kpi-label">Pasangan Saling Menunjang</div>
                <div class="kpi-value" style="color: #9333ea;">{{ number_format($stats['total_dobel_pasangan']) }}</div>
                <div class="kpi-sub">
                    <span class="text-danger fw-bold">{{ $stats['pasangan_pending'] }} Belum</span> • 
                    <span class="text-success fw-bold">{{ $stats['pasangan_selesai'] }} STS Selesai</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Melebihi Kuota (>2 Anak) --}}
        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-amber">
                <i class="ph ph-warning-circle"></i>
            </div>
            <div>
                <div class="kpi-label">Melebihi Kuota (&gt;2 Anak)</div>
                <div class="kpi-value text-warning">{{ number_format($stats['total_lebih_kuota']) }}</div>
                <div class="kpi-sub">
                    <span class="text-danger fw-bold">{{ $stats['kuota_pending'] }} Belum</span> • 
                    <span class="text-success fw-bold">{{ $stats['kuota_selesai'] }} STS Selesai</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Setoran STS Kasda --}}
        <div class="kpi-card">
            <div class="kpi-icon-box kpi-icon-green">
                <i class="ph ph-receipt"></i>
            </div>
            <div>
                <div class="kpi-label">Pengembalian STS Disetor</div>
                <div class="kpi-value text-success" style="font-size: 19px;">Rp {{ number_format($stats['total_sts_disetor'], 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    <span class="badge bg-success-subtle text-success">{{ $stats['total_selesai_all'] }} Kasus Diselesaikan</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Actions Card --}}
    <div class="filter-card">
        <form action="{{ route('laporan.audit_tunjangan.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="filter-input-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="q" value="{{ $search }}" class="filter-input" placeholder="Cari nama anak, pegawai, NIP, no STS...">
            </div>

            <select name="status" class="filter-select" style="max-width: 200px;">
                <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>-- Semua Status --</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>🔴 Belum Selesai (Pending)</option>
                <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>🟢 Sudah Selesai (STS)</option>
            </select>

            <select name="skpd" class="filter-select">
                <option value="">-- Semua SKPD --</option>
                @foreach($skpdList as $s)
                    <option value="{{ $s }}" {{ $skpdFilter === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-filter">
                <i class="ph ph-funnel"></i> Saring Data
            </button>

            @if($search !== '' || $skpdFilter !== '' || $statusFilter !== 'semua')
                <a href="{{ route('laporan.audit_tunjangan.index', ['tab' => $tab]) }}" class="btn-outline-custom">
                    <i class="ph ph-x"></i> Reset
                </a>
            @endif
        </form>

        <div class="btn-action-group">
            <a href="{{ route('laporan.audit_tunjangan.refresh') }}" class="btn-outline-custom" title="Hitung Ulang Analisis Data">
                <i class="ph ph-arrows-clockwise"></i> Segarkan Analisis
            </a>
            <a href="{{ route('laporan.audit_tunjangan.export_excel', request()->all()) }}" class="btn-excel" target="_blank">
                <i class="ph ph-file-xls"></i> Ekspor Excel
            </a>
            <a href="{{ route('laporan.audit_tunjangan.export_pdf', request()->all()) }}" class="btn-pdf" target="_blank">
                <i class="ph ph-file-pdf"></i> Cetak PDF
            </a>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="audit-tabs">
        <a href="{{ route('laporan.audit_tunjangan.index', array_merge(request()->except('page'), ['tab' => 'anak'])) }}"
           class="audit-tab-item {{ $tab === 'anak' ? 'active' : '' }}">
            <i class="ph ph-baby"></i>
            1. Dobel Tunjangan Anak
            <span class="tab-badge badge-tab-red">{{ number_format($stats['total_dobel_anak']) }}</span>
        </a>
        <a href="{{ route('laporan.audit_tunjangan.index', array_merge(request()->except('page'), ['tab' => 'pasangan'])) }}"
           class="audit-tab-item {{ $tab === 'pasangan' ? 'active' : '' }}">
            <i class="ph ph-users-three"></i>
            2. Pasangan Saling Menunjang (Suami-Istri)
            <span class="tab-badge badge-tab-indigo">{{ number_format($stats['total_dobel_pasangan']) }}</span>
        </a>
        <a href="{{ route('laporan.audit_tunjangan.index', array_merge(request()->except('page'), ['tab' => 'kuota'])) }}"
           class="audit-tab-item {{ $tab === 'kuota' ? 'active' : '' }}">
            <i class="ph ph-warning-circle"></i>
            3. Melebihi Batas Kuota (&gt;2 Anak)
            <span class="tab-badge badge-tab-amber">{{ number_format($stats['total_lebih_kuota']) }}</span>
        </a>
    </div>

    {{-- Content Table --}}
    <div class="table-card">
        @if($tab === 'pasangan')
            {{-- TAB 2: PASANGAN SALING MENUNJANG --}}
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>Pegawai 1 (Pihak A)</th>
                        <th>Tunjangan di P1</th>
                        <th>Pegawai 2 (Pihak B)</th>
                        <th>Tunjangan di P2</th>
                        <th>Status Audit &amp; Bukti STS</th>
                        <th style="width: 130px; text-align: center;">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedData as $idx => $p)
                        <tr>
                            <td>{{ $paginatedData->firstItem() + $idx }}</td>
                            <td>
                                <div class="parent-card">
                                    <div class="parent-name">{{ $p['nama_1'] }}</div>
                                    <div class="parent-nip">{{ $p['nip_1'] }}</div>
                                    <div class="parent-skpd">{{ $p['skpd_1'] }}</div>
                                    @if($p['pegawai_id_1'])
                                        <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $p['pegawai_id_1']]) }}" class="btn-trace-link" target="_blank">
                                            <i class="ph ph-arrow-square-out"></i> Trace
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-danger">
                                    <i class="ph ph-check-circle"></i> {{ $p['tunjang_1'] }}
                                </span>
                                <div class="child-meta mt-1">Nama: {{ $p['pasangan_di_1'] }}</div>
                            </td>
                            <td>
                                <div class="parent-card">
                                    <div class="parent-name">{{ $p['nama_2'] }}</div>
                                    <div class="parent-nip">{{ $p['nip_2'] }}</div>
                                    <div class="parent-skpd">{{ $p['skpd_2'] }}</div>
                                    @if($p['pegawai_id_2'])
                                        <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $p['pegawai_id_2']]) }}" class="btn-trace-link" target="_blank">
                                            <i class="ph ph-arrow-square-out"></i> Trace
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-danger">
                                    <i class="ph ph-check-circle"></i> {{ $p['tunjang_2'] }}
                                </span>
                                <div class="child-meta mt-1">Nama: {{ $p['pasangan_di_2'] }}</div>
                            </td>
                            <td>
                                @if($p['is_selesai'])
                                    <span class="badge-status-success">
                                        <i class="ph ph-check-circle"></i> Selesai (STS)
                                    </span>
                                    <div class="sts-box">
                                        <strong>No. STS:</strong> {{ $p['no_sts'] ?: '-' }} (Tgl: {{ $p['tgl_sts'] ?: '-' }})<br>
                                        <strong>Pengembalian:</strong> Rp {{ number_format($p['nominal_pengembalian'], 0, ',', '.') }}<br>
                                        @if($p['catatan'])
                                            <div class="sts-meta">"{{ $p['catatan'] }}"</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge-status-danger">
                                        <i class="ph ph-warning-octagon"></i> Saling Menunjang (10% + 10%)
                                    </span>
                                    <div class="child-meta mt-1 text-danger">Belum ada bukti STS pengembalian</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="{{ $p['is_selesai'] ? 'btn-action-resolved' : 'btn-action-resolve' }}"
                                        onclick="openResolusiModal('pasangan', '{{ $p['kunci_kasus'] }}', '{{ addslashes($p['nama_1']) }} &amp; {{ addslashes($p['nama_2']) }}', '{{ $p['is_selesai'] ? 'selesai' : 'pending' }}', '{{ addslashes($p['no_sts'] ?? '') }}', '{{ $p['tgl_sts'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $p['tgl_sts'])->format('Y-m-d') : '' }}', '{{ $p['nominal_pengembalian'] ?? '' }}', '{{ addslashes($p['catatan'] ?? '') }}', '{{ $p['resolusi_id'] ?? '' }}')">
                                    <i class="ph {{ $p['is_selesai'] ? 'ph-pencil-simple' : 'ph-receipt' }}"></i>
                                    {{ $p['is_selesai'] ? 'Ubah STS' : 'Input STS' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981;"></i>
                                <div class="mt-2 fw-semibold">Tidak ditemukan indikasi pasangan saling menunjang yang memenuhi kriteria filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($tab === 'kuota')
            {{-- TAB 3: MELEBIHI KUOTA (>2 ANAK) --}}
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>NIP &amp; Nama Pegawai</th>
                        <th>SKPD &amp; Unit Kerja</th>
                        <th>Gol.</th>
                        <th>Total Anak Tertunjang</th>
                        <th>Kelebihan Kuota</th>
                        <th>Status Audit &amp; Bukti STS</th>
                        <th style="width: 130px; text-align: center;">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedData as $idx => $k)
                        <tr>
                            <td>{{ $paginatedData->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-main">{{ $k['nama'] }}</div>
                                <div class="parent-nip">{{ $k['nip'] }}</div>
                                @if($k['pegawai_id'])
                                    <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $k['pegawai_id']]) }}" class="btn-trace-link" target="_blank">
                                        <i class="ph ph-arrow-square-out"></i> Trace Penggajian
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div>{{ $k['skpd'] }}</div>
                                <div class="child-meta">{{ $k['upt'] }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $k['golongan'] }}</span>
                            </td>
                            <td>
                                <span class="badge-status-danger">
                                    <i class="ph ph-baby"></i> {{ $k['total_anak_tunjang'] }} Anak
                                </span>
                                <div class="child-meta mt-1" style="max-width: 250px;">
                                    {{ $k['daftar_anak_str'] }}
                                </div>
                            </td>
                            <td>
                                <span class="badge-status-warning">
                                    +{{ $k['kelebihan'] }} Anak Lebih
                                </span>
                            </td>
                            <td>
                                @if($k['is_selesai'])
                                    <span class="badge-status-success">
                                        <i class="ph ph-check-circle"></i> Selesai (STS)
                                    </span>
                                    <div class="sts-box">
                                        <strong>No. STS:</strong> {{ $k['no_sts'] ?: '-' }} (Tgl: {{ $k['tgl_sts'] ?: '-' }})<br>
                                        <strong>Pengembalian:</strong> Rp {{ number_format($k['nominal_pengembalian'], 0, ',', '.') }}<br>
                                        @if($k['catatan'])
                                            <div class="sts-meta">"{{ $k['catatan'] }}"</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge-status-warning">
                                        <i class="ph ph-clock"></i> Belum Selesai
                                    </span>
                                    <div class="child-meta mt-1 text-muted">Perlu konfirmasi / pengembalian</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="{{ $k['is_selesai'] ? 'btn-action-resolved' : 'btn-action-resolve' }}"
                                        onclick="openResolusiModal('kuota', '{{ $k['kunci_kasus'] }}', 'Pegawai: {{ addslashes($k['nama']) }} ({{ $k['nip'] }}) - {{ $k['total_anak_tunjang'] }} Anak', '{{ $k['is_selesai'] ? 'selesai' : 'pending' }}', '{{ addslashes($k['no_sts'] ?? '') }}', '{{ $k['tgl_sts'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $k['tgl_sts'])->format('Y-m-d') : '' }}', '{{ $k['nominal_pengembalian'] ?? '' }}', '{{ addslashes($k['catatan'] ?? '') }}', '{{ $k['resolusi_id'] ?? '' }}')">
                                    <i class="ph {{ $k['is_selesai'] ? 'ph-pencil-simple' : 'ph-receipt' }}"></i>
                                    {{ $k['is_selesai'] ? 'Ubah STS' : 'Input STS' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981;"></i>
                                <div class="mt-2 fw-semibold">Tidak ada pegawai yang memenuhi kriteria filter kuota tanggungan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @else
            {{-- TAB 1: DOBEL TUNJANGAN ANAK (DEFAULT) --}}
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th>Data Anak</th>
                        <th>Orang Tua Pertama (Klaim 1)</th>
                        <th>Orang Tua Kedua (Klaim 2)</th>
                        <th>Status Audit &amp; Bukti STS</th>
                        <th style="width: 130px; text-align: center;">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedData as $idx => $d)
                        <tr>
                            <td>{{ $paginatedData->firstItem() + $idx }}</td>
                            <td>
                                <div class="child-title">
                                    <i class="ph ph-baby text-primary me-1"></i> {{ $d['nama_anak'] }}
                                </div>
                                <div class="child-meta">
                                    <i class="ph ph-calendar"></i> Lahir: {{ $d['tgl_lahir_anak'] }} ({{ $d['usia_anak'] }})
                                </div>
                            </td>
                            <td>
                                <div class="parent-card">
                                    <div class="parent-name">{{ $d['nama_1'] }}</div>
                                    <div class="parent-nip">{{ $d['nip_1'] }}</div>
                                    <div class="parent-skpd">{{ $d['skpd_1'] }}</div>
                                    <div class="child-meta mt-1">
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $d['hub_1'] }}</span>
                                        <span class="text-danger fw-semibold">• Tertunjang (2%)</span>
                                    </div>
                                    @if($d['pegawai_id_1'])
                                        <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $d['pegawai_id_1']]) }}" class="btn-trace-link" target="_blank">
                                            <i class="ph ph-arrow-square-out"></i> Trace
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="parent-card">
                                    <div class="parent-name">{{ $d['nama_2'] }}</div>
                                    <div class="parent-nip">{{ $d['nip_2'] }}</div>
                                    <div class="parent-skpd">{{ $d['skpd_2'] }}</div>
                                    <div class="child-meta mt-1">
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $d['hub_2'] }}</span>
                                        <span class="text-danger fw-semibold">• Tertunjang (2%)</span>
                                    </div>
                                    @if($d['pegawai_id_2'])
                                        <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $d['pegawai_id_2']]) }}" class="btn-trace-link" target="_blank">
                                            <i class="ph ph-arrow-square-out"></i> Trace
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($d['is_selesai'])
                                    <span class="badge-status-success">
                                        <i class="ph ph-check-circle"></i> Selesai (STS)
                                    </span>
                                    <div class="sts-box">
                                        <strong>No. STS:</strong> {{ $d['no_sts'] ?: '-' }} (Tgl: {{ $d['tgl_sts'] ?: '-' }})<br>
                                        <strong>Pengembalian:</strong> Rp {{ number_format($d['nominal_pengembalian'], 0, ',', '.') }}<br>
                                        @if($d['catatan'])
                                            <div class="sts-meta">"{{ $d['catatan'] }}"</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge-status-danger">
                                        <i class="ph ph-warning-octagon"></i> Tertunjang Ganda (2% + 2%)
                                    </span>
                                    <div class="child-meta mt-1 text-danger">Belum ada bukti STS pengembalian</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="{{ $d['is_selesai'] ? 'btn-action-resolved' : 'btn-action-resolve' }}"
                                        onclick="openResolusiModal('anak', '{{ $d['kunci_kasus'] }}', 'Anak: {{ addslashes($d['nama_anak']) }} (Lahir {{ $d['tgl_lahir_anak'] }})', '{{ $d['is_selesai'] ? 'selesai' : 'pending' }}', '{{ addslashes($d['no_sts'] ?? '') }}', '{{ $d['tgl_sts'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $d['tgl_sts'])->format('Y-m-d') : '' }}', '{{ $d['nominal_pengembalian'] ?? '' }}', '{{ addslashes($d['catatan'] ?? '') }}', '{{ $d['resolusi_id'] ?? '' }}')">
                                    <i class="ph {{ $d['is_selesai'] ? 'ph-pencil-simple' : 'ph-receipt' }}"></i>
                                    {{ $d['is_selesai'] ? 'Ubah STS' : 'Input STS' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981;"></i>
                                <div class="mt-2 fw-semibold">Tidak ditemukan indikasi dobel tunjangan anak yang memenuhi kriteria filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- Custom Polished Pagination --}}
        @if($paginatedData->hasPages())
            @php
                $currentPage = $paginatedData->currentPage();
                $lastPage = $paginatedData->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
            @endphp
            <div class="pagination-container">
                <div class="pagination-info">
                    <i class="ph-bold ph-list-numbers" style="color: #4f46e5; font-size: 16px;"></i>
                    <span>Menampilkan <strong>{{ $paginatedData->firstItem() ?? 0 }}</strong> s/d <strong>{{ $paginatedData->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedData->total(), 0, ',', '.') }}</strong> kasus</span>
                </div>

                <div class="pagination-nav">
                    {{-- First Page --}}
                    @if(!$paginatedData->onFirstPage())
                        <a href="{{ $paginatedData->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                            <i class="ph-bold ph-caret-double-left"></i>
                        </a>
                        {{-- Previous Page --}}
                        <a href="{{ $paginatedData->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                            <i class="ph-bold ph-caret-left me-1"></i> Prev
                        </a>
                    @else
                        <span class="page-btn disabled" title="Halaman Pertama">
                            <i class="ph-bold ph-caret-double-left"></i>
                        </span>
                        <span class="page-btn disabled" title="Halaman Sebelumnya">
                            <i class="ph-bold ph-caret-left me-1"></i> Prev
                        </span>
                    @endif

                    {{-- Left dots if start > 1 --}}
                    @if($start > 1)
                        <a href="{{ $paginatedData->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                        @if($start > 2)
                            <span class="page-dots">&hellip;</span>
                        @endif
                    @endif

                    {{-- Page Numbers in Window --}}
                    @for($p = $start; $p <= $end; $p++)
                        @if($p == $currentPage)
                            <span class="page-btn active">{{ $p }}</span>
                        @else
                            <a href="{{ $paginatedData->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                        @endif
                    @endfor

                    {{-- Right dots if end < lastPage --}}
                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="page-dots">&hellip;</span>
                        @endif
                        <a href="{{ $paginatedData->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                    @endif

                    {{-- Next Page --}}
                    @if($paginatedData->hasMorePages())
                        <a href="{{ $paginatedData->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Berikutnya">
                            Next <i class="ph-bold ph-caret-right ms-1"></i>
                        </a>
                        {{-- Last Page --}}
                        <a href="{{ $paginatedData->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
                            <i class="ph-bold ph-caret-double-right"></i>
                        </a>
                    @else
                        <span class="page-btn disabled" title="Halaman Berikutnya">
                            Next <i class="ph-bold ph-caret-right ms-1"></i>
                        </span>
                        <span class="page-btn disabled" title="Halaman Terakhir">
                            <i class="ph-bold ph-caret-double-right"></i>
                        </span>
                    @endif

                    {{-- Page Indicator Pill --}}
                    <span class="page-indicator-pill ms-2">
                        Hal {{ $currentPage }} / {{ $lastPage }}
                    </span>
                </div>
            </div>
        @elseif($paginatedData->total() > 0)
            <div class="pagination-container">
                <div class="pagination-info">
                    <i class="ph-bold ph-check-circle" style="color: #10b981; font-size: 16px;"></i>
                    <span>Menampilkan seluruh <strong>{{ $paginatedData->total() }}</strong> kasus (1 Halaman)</span>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL UNGGAH BERKAS DBF KELUARGA (KEL_*.DBF) --}}
<div class="modal-overlay" id="modalUploadKelDbf">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(5, 150, 105, 0.12); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="ph-bold ph-users-four"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Unggah Berkas DBF Riwayat Keluarga</h3>
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Format ekspor SIMGAJI: Berkas <code>KEL_*.DBF</code></p>
                </div>
            </div>
            <button type="button" class="btn-close" onclick="closeUploadKelModal()">&times;</button>
        </div>

        <form id="formUploadAuditKel" action="{{ route('master.simgaji_dbf.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="jenis_dbf" value="kel">
            <input type="hidden" name="redirect_to" value="audit_tunjangan">

            <div style="padding: 20px 24px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 8px; display: block;">Pilih Berkas KEL_*.DBF <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="file_dbf" id="inputAuditKelFile" accept=".dbf,.DBF" required class="form-control" style="padding: 10px; border: 2px dashed var(--border-color); border-radius: 8px;">
                    
                    <!-- Live preview detection -->
                    <div id="previewAuditKelBox" style="display: none; margin-top: 8px; padding: 10px 12px; border-radius: 8px; font-size: 12px; background: rgba(5, 150, 105, 0.08); border: 1px solid rgba(5, 150, 105, 0.25);">
                        <div style="font-weight: 700; color: var(--text-main);" id="previewAuditKelName"></div>
                        <div style="color: var(--text-muted); font-size: 11.5px; margin-top: 2px;" id="previewAuditKelInfo"></div>
                        <div style="margin-top: 4px;">
                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                <i class="ph-bold ph-check-circle"></i> Berkas Riwayat Keluarga Terbaca
                            </span>
                        </div>
                    </div>

                    <small style="display: block; color: var(--text-muted); margin-top: 6px; font-size: 11.5px;">
                        Berkas ini biasanya bernama <code>KEL_2026-10-011600.DBF</code> atau berawalan <code>KEL_</code> hasil ekspor SIMGAJI Taspen.
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 16px; background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin: 0; font-size: 12px; color: var(--text-main);">
                        <input type="checkbox" name="auto_sync" value="1" checked style="margin-top: 2px;">
                        <span><strong>Otomatis sinkronkan ke basis data</strong> (data seluruh tanggungan langsung dimuat ke tabel <code>simgaji_keluargas</code>).</span>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Catatan / Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Misal: Berkas KEL SIMGAJI Taspen Periode Oktober 2026" style="border-radius: 8px; font-size: 12.5px;">
                </div>

                <div style="background: rgba(5, 150, 105, 0.05); border: 1px solid rgba(5, 150, 105, 0.2); border-radius: 8px; padding: 12px 14px; font-size: 12px; color: var(--text-muted);">
                    <strong style="color: #065f46; display: block; margin-bottom: 4px;">
                        <i class="ph-bold ph-info"></i> Fungsi Berkas KEL_*.DBF:
                    </strong>
                    <ul style="margin: 0; padding-left: 18px; line-height: 1.6;">
                        <li>Mendeteksi anak yang ditunjang ganda oleh Ayah &amp; Ibu (PNS/PPPK).</li>
                        <li>Mendeteksi pasangan (suami-istri) yang saling menunjang 10% + 10%.</li>
                        <li>Mendeteksi kelebihan kuota anak tertunjang (&gt;2 anak).</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; padding: 14px 24px; border-top: 1px solid var(--border-color); background: var(--bg-surface);">
                <button type="button" class="btn btn-export" onclick="closeUploadKelModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #059669, #047857); border-color: #047857;">
                    <i class="ph-bold ph-upload-simple"></i> Unggah &amp; Proses DBF
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TINDAK LANJUT / INPUT BUKTI STS --}}
<div class="modal-overlay" id="resolusiModal">
    <div class="modal-content" style="max-width: 560px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(79, 70, 229, 0.12); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="ph-bold ph-receipt"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Tindak Lanjut &amp; Bukti STS</h3>
                    <p style="margin: 0; font-size: 11.5px; color: var(--text-muted);">Penyelesaian temuan audit &amp; catatan pengembalian ke Kasda</p>
                </div>
            </div>
            <button type="button" class="btn-close" onclick="closeResolusiModal()">&times;</button>
        </div>

        <form action="{{ route('laporan.audit_tunjangan.store_resolusi') }}" method="POST">
            @csrf
            <input type="hidden" name="kategori" id="modal_kategori">
            <input type="hidden" name="kunci_kasus" id="modal_kunci_kasus">

            <div class="modal-body" style="padding: 10px 0;">
                {{-- Case Title Box --}}
                <div id="modal_case_info" class="p-3 mb-3 rounded" style="background: var(--bg-surface-subtle, #f8fafc); border: 1px solid var(--border-color); font-size: 13px; font-weight: 600; color: var(--text-main);">
                </div>

                {{-- Status Dropdown --}}
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-main);">Status Penyelesaian Kasus <span class="text-danger">*</span></label>
                    <select name="status" id="modal_status" class="form-control" required style="border-radius: 10px; font-size: 13px;">
                        <option value="selesai">🟢 Selesai (Sudah Dikembalikan via STS / Penyesuaian SIMGAJI)</option>
                        <option value="pending">🔴 Belum Selesai (Pending / Masih Dalam Pemeriksaan)</option>
                    </select>
                </div>

                {{-- Nomor Bukti STS --}}
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-main);">Nomor Bukti STS (Surat Tanda Setoran)</label>
                    <input type="text" name="no_sts" id="modal_no_sts" class="form-control" placeholder="Contoh: 900/142/BPKAD/2026 atau STS-2026-09-001" style="border-radius: 10px; font-size: 13px;">
                    <small class="text-muted" style="font-size: 11px;">Nomor bukti setor resmi pengembalian ke Kas Daerah.</small>
                </div>

                <div class="row">
                    {{-- Tanggal Setoran STS --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-main);">Tanggal Setor STS</label>
                        <input type="date" name="tgl_sts" id="modal_tgl_sts" class="form-control" style="border-radius: 10px; font-size: 13px;">
                    </div>

                    {{-- Nominal Pengembalian --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-main);">Nominal Setor (Rp)</label>
                        <input type="number" step="1000" name="nominal_pengembalian" id="modal_nominal" class="form-control" placeholder="0" style="border-radius: 10px; font-size: 13px;">
                    </div>
                </div>

                {{-- Catatan / Keterangan --}}
                <div class="mb-2">
                    <label class="form-label fw-bold" style="font-size: 12.5px; color: var(--text-main);">Catatan Tindak Lanjut</label>
                    <textarea name="catatan" id="modal_catatan" rows="3" class="form-control" placeholder="Tuliskan catatan tindak lanjut, contoh: Yang bersangkutan telah mengembalikan kelebihan bayar tunjangan anak ke Kasda dengan bukti STS, data di SIMGAJI telah diperbaiki..." style="border-radius: 10px; font-size: 13px;"></textarea>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 0 0 0; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: transparent;">
                <div id="modal_delete_btn_wrap">
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeResolusiModal()" style="border-radius: 10px; font-size: 13px; font-weight: 600;">Tutup</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; font-size: 13px; font-weight: 600; background: #4f46e5; border-color: #4f46e5;">
                        <i class="ph ph-floppy-disk me-1"></i> Simpan Penyelesaian
                    </button>
                </div>
            </div>
        </form>

        {{-- Hidden delete form --}}
        <form id="deleteResolusiForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
function openResolusiModal(kategori, kunciKasus, caseTitle, status, noSts, tglSts, nominal, catatan, resolusiId) {
    document.getElementById('modal_kategori').value = kategori;
    document.getElementById('modal_kunci_kasus').value = kunciKasus;
    document.getElementById('modal_case_info').innerHTML = '<i class="ph ph-info me-1 text-primary"></i> ' + caseTitle;
    document.getElementById('modal_status').value = status || 'selesai';
    document.getElementById('modal_no_sts').value = noSts || '';
    document.getElementById('modal_tgl_sts').value = tglSts || '';
    document.getElementById('modal_nominal').value = nominal || '';
    document.getElementById('modal_catatan').value = catatan || '';

    var deleteBtnWrap = document.getElementById('modal_delete_btn_wrap');
    if (resolusiId) {
        deleteBtnWrap.innerHTML = '<button type="button" class="btn btn-outline-danger btn-sm" style="border-radius: 8px; font-size: 12px;" onclick="confirmDeleteResolusi(' + resolusiId + ')"><i class="ph ph-trash me-1"></i> Batalkan STS</button>';
    } else {
        deleteBtnWrap.innerHTML = '';
    }

    var modal = document.getElementById('resolusiModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeResolusiModal() {
    var modal = document.getElementById('resolusiModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

document.getElementById('resolusiModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeResolusiModal();
    }
});

function confirmDeleteResolusi(resolusiId) {
    if (confirm('Apakah Anda yakin ingin membatalkan status penyelesaian ini dan mengembalikannya ke status Belum Selesai (Pending)?')) {
        var form = document.getElementById('deleteResolusiForm');
        form.action = '/laporan/audit-tunjangan-keluarga/resolusi/' + resolusiId;
        form.submit();
    }
}

function openUploadKelModal() {
    var modal = document.getElementById('modalUploadKelDbf');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeUploadKelModal() {
    var modal = document.getElementById('modalUploadKelDbf');
    if (modal) {
        modal.classList.remove('active');
    }
}

document.getElementById('modalUploadKelDbf')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadKelModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const kelInput = document.getElementById('inputAuditKelFile');
    const previewBox = document.getElementById('previewAuditKelBox');
    const previewName = document.getElementById('previewAuditKelName');
    const previewInfo = document.getElementById('previewAuditKelInfo');

    if (kelInput) {
        kelInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                if (previewBox) previewBox.style.display = 'none';
                return;
            }
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (previewBox) previewBox.style.display = 'block';
            if (previewName) previewName.textContent = '📄 ' + file.name;
            if (previewInfo) previewInfo.textContent = 'Ukuran berkas: ' + sizeMb + ' MB (' + file.size.toLocaleString('id-ID') + ' bytes)';
        });
    }

    // Attach DBF upload and sync progress modal handlers
    if (typeof window.attachDbfUploadHandler === 'function') {
        window.attachDbfUploadHandler('#formUploadAuditKel', '#inputAuditKelFile');
    }
    if (typeof window.attachDbfSyncHandler === 'function') {
        window.attachDbfSyncHandler('.form-sync-dbf');
    }
});
</script>

@include('master.dbf_upload_scripts')
@endsection
