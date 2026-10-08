@extends('layouts.app')

@section('title', 'Laporan BNBA & Pemetaan Master SKPD SIMGAJI')
@section('page_title', 'BNBA & Pemetaan SKPD SIMGAJI (Acuan SIMPEG)')

@section('content')
<style>
    /* Tab Styling */
    .custom-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 20px;
        padding-bottom: 0;
    }
    .custom-tab-item {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border-radius: 8px 8px 0 0;
    }
    .custom-tab-item:hover {
        color: var(--luno-primary);
        background: rgba(79, 70, 229, 0.04);
    }
    .custom-tab-item.active {
        color: var(--luno-primary);
        border-bottom-color: var(--luno-primary);
        background: rgba(79, 70, 229, 0.06);
    }

    /* Custom Polished Pagination */
    .pagination-container {
        padding: 16px 20px;
        background: var(--bg-surface);
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .pagination-info {
        font-size: 12.5px;
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
        gap: 5px;
        flex-wrap: wrap;
    }
    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 6px;
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
        background: #1e40af !important;
        border-color: #1e40af !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(30, 64, 175, 0.3);
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
        min-width: 20px;
        height: 32px;
        color: var(--text-muted);
        font-weight: 700;
        font-size: 12px;
    }
</style>

<div class="page-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
            <span class="badge" style="background: rgba(30, 64, 175, 0.1); color: #1e40af; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-shield-check"></i> ACUAN RESMI: SIMPEG / KONBELPEG
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-database"></i> 57 KODE MASTER SKPD SIMGAJI
            </span>
            <span class="badge" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-user-list"></i> FORMAT BNBA PER PEGAWAI
            </span>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">
            Laporan BNBA & Pemetaan Master SKPD SIMGAJI
        </h2>
        <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">
            Instrumen sinkronisasi kode & penulisan nama SKPD SIMGAJI Taspen serta daftar perseorangan (BNBA) berdasarkan data master SIMPEG.
        </p>
        <div style="margin-top: 10px; display: inline-flex; align-items: center; gap: 8px; padding: 5px 14px; background: rgba(76, 53, 222, 0.06); border-radius: 999px; font-size: 12px; color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-database"></i> Database Aktif: <strong>{{ $activeDbf['filename'] ?? '-' }}</strong> &bull; Pembaruan Terakhir: {{ $cachedAt ?? '-' }}
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.refresh') }}" class="btn btn-export" title="Kalkulasi ulang pemetaan data dari DBF aktif">
            <i class="ph-bold ph-arrows-clockwise"></i> Refresh Cache
        </a>

        <!-- Separator -->
        <div style="width: 1px; height: 26px; background: var(--border-color); margin: 0 4px;"></div>

        <!-- Ekspor Excel Sesuai Filter -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_excel', ['scope' => 'perbaikan', 'status' => $statusFilter, 'kategori' => $kategori, 'skpd' => $skpdFilter, 'skpd_simgaji' => $simgajiFilter, 'search' => $search]) }}" class="btn btn-export" style="color: #059669; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;" title="Unduh Berita Acara Perbaikan (Hanya yang selisih + Sheet Master SKPD)">
            <i class="ph-bold ph-file-xls" style="font-size: 16px; color: #10b981;"></i> Ekspor Excel (Perbaikan)
        </a>

        <!-- Ekspor Excel Full Pemetaan -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_excel', ['scope' => 'full']) }}" class="btn btn-export" style="color: #1e40af; border-color: rgba(30, 64, 175, 0.4); font-weight: 600;" title="Unduh Full Pemetaan seluruh pegawai aktif SIMGAJI dan 57 Master SKPD">
            <i class="ph-bold ph-file-arrow-down" style="font-size: 16px; color: #1e40af;"></i> Ekspor Full Pemetaan
        </a>

        <!-- Ekspor PDF Resmi -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_pdf', ['tab' => $tab, 'status' => $statusFilter, 'kategori' => $kategori, 'skpd' => $skpdFilter, 'skpd_simgaji' => $simgajiFilter, 'search' => $search]) }}" target="_blank" class="btn btn-export" style="color: #dc2626; border-color: rgba(239, 68, 68, 0.4); font-weight: 600;" title="Buka / Cetak dokumen PDF resmi A4 Landscape">
            <i class="ph-bold ph-file-pdf" style="font-size: 16px; color: #ef4444;"></i> Cetak PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(!empty($error))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <div>{{ $error }}</div>
    </div>
@endif

<!-- Tab Navigasi -->
<div class="custom-tabs">
    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', array_merge(request()->query(), ['tab' => 'bnba', 'page' => 1])) }}" class="custom-tab-item {{ $tab === 'bnba' ? 'active' : '' }}">
        <i class="ph-bold ph-users-four"></i>
        <span>1. Data BNBA Pegawai</span>
        <span class="badge" style="background: {{ $statusFilter === 'perlu_perbaikan' ? '#ef4444' : '#1e40af' }}; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">
            {{ $statusFilter === 'perlu_perbaikan' ? number_format($summary['total_perlu_perbaikan'] ?? 0, 0, ',', '.') : number_format($summary['total_aktif_simgaji'] ?? 0, 0, ',', '.') }}
        </span>
    </a>
    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', array_merge(request()->query(), ['tab' => 'master_skpd', 'page' => 1])) }}" class="custom-tab-item {{ $tab === 'master_skpd' ? 'active' : '' }}">
        <i class="ph-bold ph-buildings"></i>
        <span>2. Matriks Pemetaan Master SKPD (57 SKPD SIMGAJI)</span>
        <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">
            57 SKPD
        </span>
    </a>
</div>

@if($tab === 'master_skpd')
    <!-- ======================================================== -->
    <!-- TAB 2: MATRIKS PEMETAAN MASTER SKPD (57 SKPD SIMGAJI) -->
    <!-- ======================================================== -->
    <div style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.05), rgba(30, 64, 175, 0.05)); border: 1px solid rgba(15, 23, 42, 0.15); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
            <i class="ph-bold ph-tree-structure"></i>
        </div>
        <div style="font-size: 13px; line-height: 1.55; color: var(--text-main);">
            <strong style="color: #0f172a; font-size: 13.5px;">Petunjuk Perbaikan Master Kode & Nama SKPD SIMGAJI:</strong><br>
            Tabel di bawah ini memetakan seluruh <strong>57 Kode SKPD</strong> yang ada pada database SIMGAJI Taspen terhadap nama resmi SKPD di <strong>SIMPEG Pemprov Kalsel</strong>. Gunakan kolom <em>Rekomendasi Standardisasi Nama</em> sebagai rujukan perbaikan penulisan instansi di SIMGAJI agar tidak terjadi selisih nomenklatur saat rekonsiliasi keuangan.
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                    Matriks Pemetaan 57 Master SKPD SIMGAJI vs SIMPEG
                </h3>
                <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                    Menyandingkan seluruh kode SKPD eksisting di SIMGAJI dengan nama resmi acuan SIMPEG.
                </p>
            </div>
            <div>
                <span class="badge" style="background: #1e40af; color: #ffffff; padding: 5px 12px; font-size: 12px; border-radius: 6px;">
                    Total: 57 Kode SKPD SIMGAJI
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr style="background: #0f172a; color: #ffffff;">
                        <th style="width: 45px; text-align: center; padding: 10px;">No</th>
                        <th style="width: 100px; text-align: center; padding: 10px;">Kode SIMGAJI</th>
                        <th style="padding: 10px;">Nama SKPD di SIMGAJI (Eksisting)</th>
                        <th style="padding: 10px;">Nama Resmi Acuan (SIMPEG)</th>
                        <th style="width: 90px; text-align: right; padding: 10px;">Total Pegawai</th>
                        <th style="width: 85px; text-align: right; padding: 10px;">Sesuai</th>
                        <th style="width: 85px; text-align: right; padding: 10px;">Selisih</th>
                        <th style="width: 130px; text-align: center; padding: 10px;">Status Keselarasan</th>
                        <th style="padding: 10px;">Rekomendasi Standardisasi / Tindakan SIMGAJI</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noSkpd = 1; @endphp
                    @forelse($skpdMappings as $m)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="text-align: center; color: var(--text-muted);">{{ $noSkpd++ }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #0f172a; color: #ffffff; font-family: monospace; font-size: 11px; padding: 3px 8px;">
                                    {{ $m['kdskpd'] }}
                                </span>
                            </td>
                            <td style="font-weight: 600; color: var(--text-main);">
                                {{ $m['nama_simgaji'] }}
                            </td>
                            <td style="font-weight: 700; color: #1e40af;">
                                {{ $m['nama_simpeg_dominan'] }}
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                {{ number_format($m['total_pegawai'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #059669; font-weight: 600; font-family: monospace;">
                                {{ number_format($m['total_sesuai'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: {{ $m['total_selisih'] > 0 ? '#dc2626' : 'var(--text-muted)' }}; font-weight: 700; font-family: monospace;">
                                {{ number_format($m['total_selisih'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($m['status_nama'] === 'SESUAI')
                                    <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        <i class="ph-bold ph-check-circle"></i> SESUAI
                                    </span>
                                @elseif($m['status_nama'] === 'CABANG_DISDIK')
                                    <span class="badge" style="background: #f3e8ff; color: #7c3aed; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        <i class="ph-bold ph-map-pin"></i> CABANG DISDIK
                                    </span>
                                @elseif($m['status_nama'] === 'ADA_SELISIH')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        <i class="ph-bold ph-warning"></i> ADA SELISIH
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        {{ $m['status_nama'] }}
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 11.5px; line-height: 1.4; color: var(--text-main);">
                                {{ $m['rekomendasi'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Data master SKPD tidak tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@else
    <!-- ======================================================== -->
    <!-- TAB 1: DATA BNBA PEGAWAI (BY NAME BY ADDRESS) -->
    <!-- ======================================================== -->

    <!-- Petunjuk Operasional Resmi -->
    <div style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05), rgba(76, 53, 222, 0.05)); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #1e40af; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
            <i class="ph-bold ph-info"></i>
        </div>
        <div style="font-size: 13px; line-height: 1.55; color: var(--text-main);">
            <strong style="color: #1e40af; font-size: 13.5px;">Ketentuan Acuan Perbaikan Data SIMGAJI Taspen:</strong><br>
            1. <strong>Master SIMPEG / KONBELPEG</strong> adalah acuan tunggal kebenaran penempatan SKPD, UPTD, dan Jabatan pegawai Pemerintah Daerah.<br>
            2. Gunakan pilihan filter <strong>Status Data</strong> di bawah untuk beralih antara <em>Hanya Perlu Perbaikan (118 Data)</em> atau <em>Semua Pegawai SIMGAJI (14.231 Data Full Pemetaan)</em>.<br>
            3. Berkas ekspor Excel dapat langsung dilampirkan sebagai <strong>Lampiran Berita Acara Usulan Perbaikan Data Penggajian ke PT Taspen</strong>.
        </div>
    </div>

    @if(!empty($summary))
    <!-- KPI Widgets Grid -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
        <!-- Total Perlu Perbaikan -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Perlu Perbaikan</p>
                    <h3 class="luno-widget-value" style="color: #dc2626;">{{ number_format($summary['total_perlu_perbaikan'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon icon-danger">
                    <i class="ph-bold ph-warning-octagon"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span class="luno-trend-danger"><i class="ph-bold ph-warning"></i> Pegawai perlu koreksi</span>
            </div>
        </div>

        <!-- Beda SKPD Induk -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Beda SKPD Induk</p>
                    <h3 class="luno-widget-value" style="color: #b45309;">{{ number_format($summary['total_beda_skpd'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                    <i class="ph-bold ph-arrows-left-right"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span style="color: #b45309; font-size: 11px;"><i class="ph-bold ph-warning"></i> Mutasi antar-instansi</span>
            </div>
        </div>

        <!-- Beda Cabang Disdik -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Beda Cabang Disdik</p>
                    <h3 class="luno-widget-value" style="color: #7c3aed;">{{ number_format($summary['total_beda_cabang_disdik'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(124, 58, 237, 0.12); color: #7c3aed;">
                    <i class="ph-bold ph-map-pin"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span style="color: #7c3aed; font-size: 11px;"><i class="ph-bold ph-info"></i> Selisih cabang Kab/Kota</span>
            </div>
        </div>

        <!-- Beda UPTD / Satker -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Beda UPTD / Satker</p>
                    <h3 class="luno-widget-value" style="color: #1d4ed8;">{{ number_format($summary['total_beda_uptd'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(59, 130, 246, 0.12); color: #2563eb;">
                    <i class="ph-bold ph-buildings"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span style="color: #1d4ed8; font-size: 11px;"><i class="ph-bold ph-buildings"></i> Penempatan unit kerja</span>
            </div>
        </div>

        <!-- Total Aktif SIMGAJI -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Total Aktif SIMGAJI</p>
                    <h3 class="luno-widget-value" style="color: #059669;">{{ number_format($summary['total_aktif_simgaji'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon icon-success">
                    <i class="ph-bold ph-users-three"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span class="luno-trend-success"><i class="ph-bold ph-check-circle"></i> Terdaftar di MST_PGW</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px; padding: 18px 20px;">
        <form method="GET" action="{{ route('laporan.perbaikan_simgaji_skpd.index') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="bnba">

            <!-- Filter Status Data -->
            <div style="flex: 1.2; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-toggle-left"></i> Status Data Pegawai
                </label>
                <select name="status" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px; font-weight: 600;" onchange="this.form.submit()">
                    <option value="perlu_perbaikan" {{ $statusFilter === 'perlu_perbaikan' ? 'selected' : '' }}>
                        ⚠️ Hanya Perlu Perbaikan ({{ number_format($summary['total_perlu_perbaikan'] ?? 0, 0, ',', '.') }})
                    </option>
                    <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>
                        🌐 Semua Pegawai SIMGAJI (Full - {{ number_format($summary['total_aktif_simgaji'] ?? 0, 0, ',', '.') }})
                    </option>
                    <option value="sesuai" {{ $statusFilter === 'sesuai' ? 'selected' : '' }}>
                        ✅ Sudah Sesuai ({{ number_format($summary['total_sesuai'] ?? 0, 0, ',', '.') }})
                    </option>
                    <option value="tidak_di_simpeg" {{ $statusFilter === 'tidak_di_simpeg' ? 'selected' : '' }}>
                        ❓ Belum di SIMPEG ({{ number_format($summary['total_tidak_di_simpeg'] ?? 0, 0, ',', '.') }})
                    </option>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-funnel"></i> Kategori Selisih
                </label>
                <select name="kategori" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua" {{ $kategori === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="beda_skpd" {{ $kategori === 'beda_skpd' ? 'selected' : '' }}>🚨 Beda SKPD Induk</option>
                    <option value="beda_cabang_disdik" {{ $kategori === 'beda_cabang_disdik' ? 'selected' : '' }}>📍 Beda Cabang Disdik</option>
                    <option value="beda_uptd" {{ $kategori === 'beda_uptd' ? 'selected' : '' }}>🏢 Beda UPTD / Satker</option>
                    <option value="tidak_di_simpeg" {{ $kategori === 'tidak_di_simpeg' ? 'selected' : '' }}>❓ Belum di SIMPEG</option>
                </select>
            </div>

            <!-- Filter SKPD SIMPEG (Acuan Resmi) -->
            <div style="flex: 1.5; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-buildings"></i> SKPD Acuan Resmi (SIMPEG)
                </label>
                <select name="skpd" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua">Semua SKPD Resmi ({{ count($filterOptions['skpd_simpeg'] ?? []) }})</option>
                    @foreach($filterOptions['skpd_simpeg'] ?? [] as $s)
                        <option value="{{ $s }}" {{ $skpdFilter === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter SKPD SIMGAJI (Eksisting) -->
            <div style="flex: 1.5; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-database"></i> SKPD SIMGAJI (Eksisting)
                </label>
                <select name="skpd_simgaji" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua">Semua SKPD SIMGAJI ({{ count($filterOptions['skpd_simgaji'] ?? []) }})</option>
                    @foreach($filterOptions['skpd_simgaji'] ?? [] as $sg)
                        <option value="{{ $sg }}" {{ $simgajiFilter === $sg ? 'selected' : '' }}>{{ $sg }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Box -->
            <div style="flex: 1.2; min-width: 180px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Pencarian
                </label>
                <input type="text" name="search" class="form-control" placeholder="Cari NIP, Nama, atau Jabatan..." value="{{ $search }}" style="width: 100%; height: 38px; font-size: 12.5px;">
            </div>

            <!-- Tombol Aksi Filter -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cari
                </button>
                @if($statusFilter !== 'perlu_perbaikan' || $kategori !== 'semua' || $skpdFilter !== 'semua' || $simgajiFilter !== 'semua' || $search !== '' || $perPage !== 25)
                    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', ['tab' => 'bnba']) }}" class="btn btn-export" style="height: 38px; color: var(--danger-text); padding: 0 14px;" title="Reset filter">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel BNBA Perbaikan SIMGAJI -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                    Daftar Nominatif BNBA Usulan Perbaikan Data SIMGAJI
                </h3>
                <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                    Menampilkan <strong>{{ number_format($items->count(), 0, ',', '.') }}</strong> dari total <strong>{{ number_format($filteredTotal, 0, ',', '.') }}</strong> data pada filter saat ini.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="badge" style="background: {{ $statusFilter === 'perlu_perbaikan' ? '#ef4444' : '#1e40af' }}; color: #ffffff; padding: 5px 10px; font-size: 11px;">
                    Mode: {{ $statusFilter === 'perlu_perbaikan' ? 'Hanya Perlu Perbaikan' : ($statusFilter === 'semua' ? 'Full Semua Pegawai' : ucfirst($statusFilter)) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <!-- Top Group Headers -->
                    <tr style="background: #1e3a8a; color: #ffffff;">
                        <th colspan="4" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="ph-bold ph-user"></i> Identitas Pegawai
                        </th>
                        <th colspan="3" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); background: #0f172a; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="ph-bold ph-database"></i> Kondisi SIMGAJI (Eksisting)
                        </th>
                        <th colspan="3" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); background: #065f46; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="ph-bold ph-shield-check"></i> Acuan Resmi SIMPEG
                        </th>
                        <th rowspan="2" style="text-align: center; background: #312e81; vertical-align: middle; padding: 8px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; min-width: 280px;">
                            <i class="ph-bold ph-wrench"></i> Rekomendasi Tindakan
                        </th>
                    </tr>
                    <!-- Sub Headers -->
                    <tr style="background: var(--bg-surface-subtle); color: var(--text-main);">
                        <th style="width: 40px; text-align: center;">No</th>
                        <th style="width: 175px;">NIP</th>
                        <th style="min-width: 170px;">Nama Pegawai / Gol</th>
                        <th style="min-width: 160px; border-right: 1px solid var(--border-color);">Jabatan SIMPEG</th>

                        <th style="width: 80px; text-align: center;">Kd SKPD</th>
                        <th style="min-width: 190px;">SKPD SIMGAJI</th>
                        <th style="min-width: 110px; border-right: 1px solid var(--border-color);">Satker SIMGAJI</th>

                        <th style="width: 90px; text-align: center;">Kd Rekomendasi</th>
                        <th style="min-width: 200px;">SKPD Resmi (SIMPEG)</th>
                        <th style="min-width: 150px; border-right: 1px solid var(--border-color);">UPTD / Satker SIMPEG</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = $items->firstItem() ?? 1; @endphp
                    @forelse($items as $row)
                        @php
                            $jenis = $row['jenis_selisih'] ?? '-';
                            $rowBg = '';
                            if ($jenis === 'Beda SKPD Induk') {
                                $rowBg = 'rgba(239, 68, 68, 0.04)';
                            } elseif ($jenis === 'Beda Cabang Disdik') {
                                $rowBg = 'rgba(124, 58, 237, 0.04)';
                            } elseif ($jenis === 'Beda UPTD / Satker') {
                                $rowBg = 'rgba(59, 130, 246, 0.04)';
                            } elseif ($jenis === 'Tidak Terdaftar di SIMPEG') {
                                $rowBg = 'rgba(245, 158, 11, 0.04)';
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid var(--border-color); background: {{ $rowBg }};">
                            <!-- No -->
                            <td style="text-align: center; color: var(--text-muted); font-size: 11px;">
                                {{ $no++ }}
                            </td>

                            <!-- NIP -->
                            <td style="font-family: monospace; font-size: 12px; font-weight: 600;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                    <span>{{ $row['nip'] }}</span>
                                    <button type="button" class="btn btn-export" style="padding: 2px 5px; height: 22px; font-size: 11px; border-radius: 4px;" onclick="copyNip('{{ $row['nip'] }}', this)" title="Salin NIP">
                                        <i class="ph-bold ph-copy"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Nama Pegawai & Golru -->
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 12.5px;">
                                    {{ $row['nama'] }}
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    <span class="badge" style="background: rgba(0,0,0,0.06); font-size: 10px; padding: 1px 6px;">
                                        Gol: {{ $row['golru'] }}
                                    </span>
                                    @if($row['status_pegawai'] !== '-')
                                        <span class="badge" style="background: rgba(30, 64, 175, 0.08); color: #1e40af; font-size: 10px; padding: 1px 6px;">
                                            {{ $row['status_pegawai'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Jabatan SIMPEG -->
                            <td style="border-right: 1px solid var(--border-color); font-size: 11.5px; color: var(--text-muted);">
                                {{ $row['jabatan'] }}
                            </td>

                            <!-- SIMGAJI: Kode SKPD -->
                            <td style="text-align: center; font-family: monospace; font-weight: 700;">
                                <span class="badge" style="background: #0f172a; color: #ffffff; padding: 2px 7px; font-size: 11px;">
                                    {{ $row['kdskpd_simgaji'] }}
                                </span>
                            </td>

                            <!-- SIMGAJI: Nama SKPD -->
                            <td style="font-size: 11.5px; font-weight: 600; color: var(--text-main);">
                                {{ $row['skpd_simgaji'] }}
                            </td>

                            <!-- SIMGAJI: Satker / Inputer -->
                            <td style="border-right: 1px solid var(--border-color); font-size: 11px; color: var(--text-muted);">
                                <div>Satker: <span style="font-family: monospace; font-weight: 600;">{{ $row['kdsatker_simgaji'] }}</span></div>
                                @if($row['inputer_simgaji'] !== '-')
                                    <div style="font-size: 10px; color: var(--text-light);">Inputer: {{ $row['inputer_simgaji'] }}</div>
                                @endif
                            </td>

                            <!-- SIMPEG: Kode Rekomendasi -->
                            <td style="text-align: center; font-family: monospace; font-weight: 700;">
                                @if($row['kdskpd_rekomendasi'] !== '-')
                                    <span class="badge" style="background: #065f46; color: #ffffff; padding: 2px 7px; font-size: 11px;">
                                        {{ $row['kdskpd_rekomendasi'] }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <!-- SIMPEG: SKPD Resmi -->
                            <td>
                                <div style="font-weight: 700; color: #1e40af; font-size: 12px;">
                                    {{ $row['skpd_simpeg'] }}
                                </div>
                            </td>

                            <!-- SIMPEG: UPTD / Satker -->
                            <td style="border-right: 1px solid var(--border-color); font-size: 11px; color: var(--text-muted);">
                                @if($row['upt_simpeg'] !== '-')
                                    <div style="font-weight: 600; color: var(--text-main);">{{ $row['upt_simpeg'] }}</div>
                                @endif
                                @if($row['satker_simpeg'] !== '-' && $row['satker_simpeg'] !== $row['upt_simpeg'])
                                    <div style="font-size: 10.5px; color: var(--text-light);">{{ $row['satker_simpeg'] }}</div>
                                @endif
                            </td>

                            <!-- Rekomendasi & Tindakan -->
                            <td>
                                <div style="margin-bottom: 4px;">
                                    @if($jenis === 'Beda SKPD Induk')
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            <i class="ph-bold ph-warning"></i> BEDA SKPD INDUK
                                        </span>
                                    @elseif($jenis === 'Beda Cabang Disdik')
                                        <span class="badge" style="background: #f3e8ff; color: #7c3aed; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            <i class="ph-bold ph-map-pin"></i> BEDA CABANG DISDIK
                                        </span>
                                    @elseif($jenis === 'Beda UPTD / Satker')
                                        <span class="badge" style="background: #dbeafe; color: #1d4ed8; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            <i class="ph-bold ph-buildings"></i> BEDA UPTD / SATKER
                                        </span>
                                    @elseif($jenis === 'Tidak Terdaftar di SIMPEG')
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            <i class="ph-bold ph-question"></i> BELUM DI SIMPEG
                                        </span>
                                    @elseif($jenis === 'Sesuai')
                                        <span class="badge" style="background: #d1fae5; color: #065f46; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            <i class="ph-bold ph-check-circle"></i> SUDAH SESUAI
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                            {{ $jenis }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 11.5px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
                                    {{ $row['rekomendasi'] }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--text-light); margin-top: 3px;">
                                    {{ $row['keterangan'] }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                <div style="font-size: 36px; margin-bottom: 8px; color: #10b981;">
                                    <i class="ph-bold ph-check-circle"></i>
                                </div>
                                <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                                    Tidak Ada Data yang Ditemukan
                                </div>
                                <p style="margin: 0; font-size: 13px;">
                                    Tidak ada data pegawai yang sesuai dengan kriteria filter yang Anda pilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Polished Pagination Controls -->
        @if($items->total() > 0)
            <div class="pagination-container">
                <div class="pagination-info">
                    <i class="ph-bold ph-list-numbers" style="color: #1e40af; font-size: 16px;"></i>
                    <span>
                        Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> s/d <strong>{{ $items->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($items->total(), 0, ',', '.') }}</strong> data (Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }})
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <!-- Dropdown Page Size -->
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                        <span>Per hal:</span>
                        <select onchange="window.location.href = this.value;" class="form-control" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                            @foreach([25, 50, 100, 250, 500] as $size)
                                <option value="{{ $items->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($items->hasPages())
                        <div class="pagination-nav">
                            {{-- First Page --}}
                            @if(!$items->onFirstPage())
                                <a href="{{ $items->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                    <i class="ph-bold ph-caret-double-left"></i>
                                </a>
                                <a href="{{ $items->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                    <i class="ph-bold ph-caret-left"></i> Prev
                                </a>
                            @else
                                <span class="page-btn disabled" title="Halaman Pertama"><i class="ph-bold ph-caret-double-left"></i></span>
                                <span class="page-btn disabled" title="Halaman Sebelumnya"><i class="ph-bold ph-caret-left"></i> Prev</span>
                            @endif

                            {{-- Sliding Window Pagination --}}
                            @php
                                $currentPage = $items->currentPage();
                                $lastPage = $items->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $items->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                                @if($start > 2)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                            @endif

                            @for($p = $start; $p <= $end; $p++)
                                @if($p == $currentPage)
                                    <span class="page-btn active">{{ $p }}</span>
                                @else
                                    <a href="{{ $items->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                                @endif
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                                <a href="{{ $items->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                            @endif

                            {{-- Next Page --}}
                            @if($items->hasMorePages())
                                <a href="{{ $items->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                    Next <i class="ph-bold ph-caret-right"></i>
                                </a>
                                <a href="{{ $items->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
                                    <i class="ph-bold ph-caret-double-right"></i>
                                </a>
                            @else
                                <span class="page-btn disabled" title="Halaman Selanjutnya">Next <i class="ph-bold ph-caret-right"></i></span>
                                <span class="page-btn disabled" title="Halaman Terakhir"><i class="ph-bold ph-caret-double-right"></i></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endif

<script>
    function copyNip(nip, btn) {
        navigator.clipboard.writeText(nip).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="ph-bold ph-check" style="font-size: 13px; color: #10b981;"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        });
    }
</script>
@endsection
