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
        overflow-x: auto;
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
        white-space: nowrap;
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
                <i class="ph-bold ph-database"></i> {{ $summary['total_skpd_simgaji'] ?? 62 }} KODE MASTER SKPD
            </span>
            <span class="badge" style="background: rgba(4, 120, 87, 0.1); color: #047857; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-tree-structure"></i> {{ $summary['total_satker_simgaji'] ?? 551 }} MASTER SATKER SIMGAJI
            </span>
            <span class="badge" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-user-list"></i> FORMAT BNBA PER PEGAWAI
            </span>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">
            Laporan BNBA & Pemetaan Master SKPD SIMGAJI
        </h2>
        <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">
            Instrumen sinkronisasi kode & penulisan nama SKPD / Satker SIMGAJI Taspen serta daftar nominatif perseorangan (BNBA) berdasarkan data master SIMPEG.
        </p>
        <div style="margin-top: 10px; display: inline-flex; align-items: center; gap: 8px; padding: 5px 14px; background: rgba(76, 53, 222, 0.06); border-radius: 999px; font-size: 12px; color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-database"></i> Database Aktif: <strong>{{ $activeDbf['filename'] ?? '-' }}</strong> &bull; Pembaruan Terakhir: {{ $cachedAt ?? '-' }}
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.refresh') }}" class="btn btn-export" title="Kalkulasi ulang pemetaan data dari DBF aktif dan file Excel Master">
            <i class="ph-bold ph-arrows-clockwise"></i> Refresh Cache
        </a>

        <!-- Separator -->
        <div style="width: 1px; height: 26px; background: var(--border-color); margin: 0 4px;"></div>

        <!-- Ekspor Excel Sesuai Filter -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_excel', ['scope' => 'perbaikan', 'status' => $statusFilter, 'kategori' => $kategori, 'skpd' => $skpdFilter, 'skpd_simgaji' => $simgajiFilter, 'search' => $search]) }}" class="btn btn-export" style="color: #059669; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;" title="Unduh Berita Acara Perbaikan (Hanya yang selisih + Sheet Master SKPD & Satker)">
            <i class="ph-bold ph-file-xls" style="font-size: 16px; color: #10b981;"></i> Ekspor Excel (Perbaikan)
        </a>

        <!-- Ekspor Excel Full Pemetaan -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_excel', ['scope' => 'full']) }}" class="btn btn-export" style="color: #1e40af; border-color: rgba(30, 64, 175, 0.4); font-weight: 600;" title="Unduh Full Pemetaan seluruh pegawai aktif SIMGAJI, 62 Master SKPD, dan 551 Satker">
            <i class="ph-bold ph-file-arrow-down" style="font-size: 16px; color: #1e40af;"></i> Ekspor Full Pemetaan
        </a>

        <!-- Ekspor BNBA SIMPEG Lengkap + Data SIMGAJI -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_simpeg_lengkap', request()->query()) }}" class="btn btn-export" style="color: #0d9488; border-color: rgba(13, 148, 136, 0.4); font-weight: 600;" title="Unduh Laporan BNBA Data SIMPEG Lengkap (Biodata, Status, Pangkat, Jabatan, Unit Kerja, Finansial) disandingkan dengan Kd SKPD, Nama SKPD SIMGAJI, Kode Satker & Nama Satker Resmi SIMGAJI">
            <i class="ph-bold ph-identification-card" style="font-size: 16px; color: #0d9488;"></i> Ekspor SIMPEG Lengkap + SIMGAJI
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
        <span>2. Matriks Pemetaan Master SKPD ({{ count($skpdMappings) }} SKPD SIMGAJI)</span>
        <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">
            {{ count($skpdMappings) }} SKPD
        </span>
    </a>
    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', array_merge(request()->query(), ['tab' => 'master_satker', 'page' => 1])) }}" class="custom-tab-item {{ $tab === 'master_satker' ? 'active' : '' }}">
        <i class="ph-bold ph-tree-structure"></i>
        <span>3. Matriks Master Satker ({{ $summary['total_satker_simgaji'] ?? 551 }} Satker SIMGAJI)</span>
        <span class="badge" style="background: #047857; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">
            {{ $summary['total_satker_simgaji'] ?? 551 }} Satker
        </span>
    </a>
    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', array_merge(request()->query(), ['tab' => 'simpeg_lengkap', 'page' => 1])) }}" class="custom-tab-item {{ $tab === 'simpeg_lengkap' ? 'active' : '' }}">
        <i class="ph-bold ph-identification-card"></i>
        <span>4. BNBA SIMPEG Lengkap + SIMGAJI</span>
        <span class="badge" style="background: #0d9488; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">
            {{ number_format($totalSimpegCount ?? 0, 0, ',', '.') }} Pegawai
        </span>
    </a>
</div>

@if($tab === 'master_skpd')
    <!-- ======================================================== -->
    <!-- TAB 2: MATRIKS PEMETAAN MASTER SKPD (62 SKPD SIMGAJI) -->
    <!-- ======================================================== -->
    <div style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.05), rgba(30, 64, 175, 0.05)); border: 1px solid rgba(15, 23, 42, 0.15); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
            <i class="ph-bold ph-tree-structure"></i>
        </div>
        <div style="font-size: 13px; line-height: 1.55; color: var(--text-main);">
            <strong style="color: #0f172a; font-size: 13.5px;">Petunjuk Perbaikan Master Kode & Nama SKPD SIMGAJI:</strong><br>
            Tabel di bawah ini memetakan seluruh <strong>{{ count($skpdMappings) }} Kode SKPD</strong> dari file master SIMGAJI Taspen terhadap nama resmi SKPD di <strong>SIMPEG Pemprov Kalsel</strong>. Gunakan kolom <em>Rekomendasi Standardisasi Nama</em> sebagai rujukan perbaikan penulisan instansi di SIMGAJI agar tidak terjadi selisih nomenklatur saat rekonsiliasi keuangan.
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                    Matriks Pemetaan {{ count($skpdMappings) }} Master SKPD SIMGAJI vs SIMPEG
                </h3>
                <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                    Menyandingkan seluruh kode SKPD eksisting di SIMGAJI dengan nama resmi acuan SIMPEG beserta jumlah Satker terdaftar.
                </p>
            </div>
            <div>
                <span class="badge" style="background: #1e40af; color: #ffffff; padding: 5px 12px; font-size: 12px; border-radius: 6px;">
                    Total: {{ count($skpdMappings) }} Kode SKPD SIMGAJI
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr style="background: #0f172a; color: #ffffff;">
                        <th style="width: 45px; text-align: center; padding: 10px;">No</th>
                        <th style="width: 90px; text-align: center; padding: 10px;">Kode SIMGAJI</th>
                        <th style="padding: 10px;">Nama SKPD di SIMGAJI (Eksisting)</th>
                        <th style="width: 105px; text-align: center; padding: 10px;">Jumlah Satker</th>
                        <th style="padding: 10px;">Nama Resmi Acuan (SIMPEG)</th>
                        <th style="width: 85px; text-align: right; padding: 10px;">Total Pegawai</th>
                        <th style="width: 75px; text-align: right; padding: 10px;">Sesuai</th>
                        <th style="width: 75px; text-align: right; padding: 10px;">Selisih</th>
                        <th style="width: 120px; text-align: center; padding: 10px;">Status</th>
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
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(4, 120, 87, 0.1); color: #047857; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                                    {{ $m['total_satker'] ?? 0 }} Satker
                                </span>
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
                                @elseif($m['status_nama'] === 'NON_AKTIF')
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        <i class="ph-bold ph-minus-circle"></i> 0 PEGAWAI
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
                            <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Data master SKPD tidak tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($tab === 'master_satker')
    <!-- ======================================================== -->
    <!-- TAB 3: MATRIKS MASTER SATKER SIMGAJI (551 SATKER) -->
    <!-- ======================================================== -->
    <div style="background: linear-gradient(135deg, rgba(4, 120, 87, 0.05), rgba(30, 64, 175, 0.05)); border: 1px solid rgba(4, 120, 87, 0.2); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #047857; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
            <i class="ph-bold ph-tree-structure"></i>
        </div>
        <div style="font-size: 13px; line-height: 1.55; color: var(--text-main);">
            <strong style="color: #047857; font-size: 13.5px;">Master Data Satuan Kerja (Satker) SIMGAJI Taspen:</strong><br>
            Tabel di bawah ini memuat seluruh <strong>{{ $summary['total_satker_simgaji'] ?? 551 }} Satker SIMGAJI</strong> dari file master <code>kode_skpd_satker_simgaji.xlsx</code> yang mencakup UPTD, Balai, Rumah Sakit, Biro, Bagian, serta seluruh SMAN/SMKN/SLBN di 13 Kabupaten/Kota.
        </div>
    </div>

    <!-- Filter Bar Satker -->
    <div class="card" style="margin-bottom: 24px; padding: 18px 20px;">
        <form method="GET" action="{{ route('laporan.perbaikan_simgaji_skpd.index') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="master_satker">

            <!-- Filter SKPD SIMGAJI -->
            <div style="flex: 1.5; min-width: 240px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-buildings"></i> Filter SKPD SIMGAJI
                </label>
                <select name="satker_skpd" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua">Semua SKPD ({{ count($filterOptions['all_master_skpds'] ?? []) }})</option>
                    @foreach($filterOptions['all_master_skpds'] ?? [] as $kd => $nm)
                        <option value="{{ $kd }}" {{ $satkerSkpd === $kd ? 'selected' : '' }}>[{{ $kd }}] {{ $nm }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Keaktifan -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-toggle-left"></i> Status Keaktifan
                </label>
                <select name="status_satker" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua" {{ $statusSatker === 'semua' ? 'selected' : '' }}>Semua Satker (551)</option>
                    <option value="aktif" {{ $statusSatker === 'aktif' ? 'selected' : '' }}>🟢 Memiliki Pegawai Aktif ({{ $summary['total_satker_aktif'] ?? 0 }})</option>
                    <option value="0_pegawai" {{ $statusSatker === '0_pegawai' ? 'selected' : '' }}>⚪ 0 Pegawai Aktif</option>
                </select>
            </div>

            <!-- Search Box Satker -->
            <div style="flex: 1.5; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cari Kode / Nama Satker
                </label>
                <input type="text" name="satker_search" class="form-control" placeholder="Cari Kode Satker atau Nama Satker/Sekolah..." value="{{ $satkerSearch }}" style="width: 100%; height: 38px; font-size: 12.5px;">
            </div>

            <!-- Tombol Aksi Filter -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cari
                </button>
                @if($satkerSkpd !== 'semua' || $statusSatker !== 'semua' || $satkerSearch !== '')
                    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', ['tab' => 'master_satker']) }}" class="btn btn-export" style="height: 38px; color: var(--danger-text); padding: 0 14px;" title="Reset filter satker">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                    Daftar Master 551 Satuan Kerja (Satker) SIMGAJI
                </h3>
                <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                    Menampilkan <strong>{{ number_format($satkerMappings->count(), 0, ',', '.') }}</strong> dari total <strong>{{ number_format($satkerFilteredTotal, 0, ',', '.') }}</strong> Satker pada filter saat ini.
                </p>
            </div>
            <div>
                <span class="badge" style="background: #047857; color: #ffffff; padding: 5px 12px; font-size: 12px; border-radius: 6px;">
                    Total Master: 551 Satker
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr style="background: #064e3b; color: #ffffff;">
                        <th style="width: 45px; text-align: center; padding: 10px;">No</th>
                        <th style="width: 80px; text-align: center; padding: 10px;">Kd SKPD</th>
                        <th style="width: 220px; padding: 10px;">Nama SKPD SIMGAJI</th>
                        <th style="width: 140px; text-align: center; padding: 10px;">Kode Satker</th>
                        <th style="padding: 10px;">Nama Satker Resmi SIMGAJI</th>
                        <th style="width: 100px; text-align: right; padding: 10px;">Pegawai Aktif</th>
                        <th style="padding: 10px;">Dominan Unit / UPT SIMPEG</th>
                        <th style="width: 110px; text-align: center; padding: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noSat = $satkerMappings->firstItem() ?? 1; @endphp
                    @forelse($satkerMappings as $sat)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="text-align: center; color: var(--text-muted);">{{ $noSat++ }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #0f172a; color: #ffffff; font-family: monospace; font-size: 11px; padding: 2px 6px;">
                                    {{ $sat['kdskpd'] }}
                                </span>
                            </td>
                            <td style="font-size: 11.5px; color: var(--text-main);">
                                {{ $sat['nmskpd'] }}
                            </td>
                            <td style="text-align: center; font-family: monospace; font-weight: 700;">
                                <span class="badge" style="background: rgba(4, 120, 87, 0.12); color: #047857; font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                    {{ $sat['kdsatker'] }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: var(--text-main);">
                                {{ $sat['nmsatker'] }}
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                {{ number_format($sat['total_pegawai'], 0, ',', '.') }}
                            </td>
                            <td style="font-size: 11.5px; color: #1e40af; font-weight: 600;">
                                {{ $sat['dominan_simpeg_upt'] }}
                            </td>
                            <td style="text-align: center;">
                                @if($sat['status'] === 'AKTIF')
                                    <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                                        <i class="ph-bold ph-check-circle"></i> AKTIF
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #94a3b8; font-size: 10px; font-weight: 600; padding: 2px 7px;">
                                        0 PEGAWAI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Data Satker tidak ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($satkerMappings->total() > 0)
            <div class="pagination-container">
                <div class="pagination-info">
                    <i class="ph-bold ph-list-numbers" style="color: #047857; font-size: 16px;"></i>
                    <span>
                        Menampilkan <strong>{{ $satkerMappings->firstItem() ?? 0 }}</strong> s/d <strong>{{ $satkerMappings->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($satkerMappings->total(), 0, ',', '.') }}</strong> Satker (Halaman {{ $satkerMappings->currentPage() }} dari {{ $satkerMappings->lastPage() }})
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                        <span>Per hal:</span>
                        <select onchange="window.location.href = this.value;" class="form-control" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                            @foreach([25, 50, 100, 250, 500] as $size)
                                <option value="{{ $satkerMappings->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($satkerMappings->hasPages())
                        <div class="pagination-nav">
                            @if(!$satkerMappings->onFirstPage())
                                <a href="{{ $satkerMappings->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                    <i class="ph-bold ph-caret-double-left"></i>
                                </a>
                                <a href="{{ $satkerMappings->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                    <i class="ph-bold ph-caret-left"></i> Prev
                                </a>
                            @else
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-double-left"></i></span>
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-left"></i> Prev</span>
                            @endif

                            @php
                                $cPage = $satkerMappings->currentPage();
                                $lPage = $satkerMappings->lastPage();
                                $sPage = max(1, $cPage - 2);
                                $ePage = min($lPage, $cPage + 2);
                            @endphp

                            @if($sPage > 1)
                                <a href="{{ $satkerMappings->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                                @if($sPage > 2)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                            @endif

                            @for($p = $sPage; $p <= $ePage; $p++)
                                @if($p == $cPage)
                                    <span class="page-btn active" style="background: #047857 !important; border-color: #047857 !important;">{{ $p }}</span>
                                @else
                                    <a href="{{ $satkerMappings->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                                @endif
                            @endfor

                            @if($ePage < $lPage)
                                @if($ePage < $lPage - 1)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                                <a href="{{ $satkerMappings->appends(request()->query())->url($lPage) }}" class="page-btn">{{ $lPage }}</a>
                            @endif

                            @if($satkerMappings->hasMorePages())
                                <a href="{{ $satkerMappings->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                    Next <i class="ph-bold ph-caret-right"></i>
                                </a>
                                <a href="{{ $satkerMappings->appends(request()->query())->url($lPage) }}" class="page-btn" title="Halaman Terakhir">
                                    <i class="ph-bold ph-caret-double-right"></i>
                                </a>
                            @else
                                <span class="page-btn disabled">Next <i class="ph-bold ph-caret-right"></i></span>
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-double-right"></i></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@elseif($tab === 'simpeg_lengkap')
    <!-- ======================================================== -->
    <!-- TAB 4: BNBA DATA SIMPEG LENGKAP & INTEGRASI DATA SIMGAJI -->
    <!-- ======================================================== -->

    <!-- Petunjuk Operasional Tab 4 -->
    <div style="background: linear-gradient(135deg, rgba(13, 148, 136, 0.06), rgba(30, 64, 175, 0.05)); border: 1px solid rgba(13, 148, 136, 0.25); border-radius: 12px; padding: 16px 20px; margin-bottom: 22px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; margin-top: 2px;">
            <i class="ph-bold ph-identification-card"></i>
        </div>
        <div style="font-size: 13px; line-height: 1.6; color: var(--text-main);">
            <strong style="color: #0f766e; font-size: 14px;">Laporan BNBA Pegawai SIMPEG Lengkap Disandingkan Data SIMGAJI:</strong><br>
            Tabel di bawah ini menyajikan seluruh basis data profil pegawai dari <strong>SIMPEG BKD</strong> (Identitas lengkap, NIK, Status Pegawai, Pangkat/Golru, Riwayat TMT, Jabatan, Acuan Unit Kerja, Pendidikan, Rekening Bank, dan NPWP) yang disandingkan langsung dengan 4 atribut acuan dari sistem penggajian <strong>SIMGAJI Taspen</strong>: <strong>Kd SKPD</strong>, <strong>Nama SKPD SIMGAJI</strong>, <strong>Kode Satker</strong>, dan <strong>Nama Satker Resmi SIMGAJI</strong>.
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <!-- Total Pegawai SIMPEG -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Total Pegawai SIMPEG</p>
                    <h3 class="luno-widget-value" style="color: #1e40af;">{{ number_format($totalSimpegCount, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(30, 64, 175, 0.1); color: #1e40af;">
                    <i class="ph-bold ph-users-four"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span class="luno-trend-neutral"><i class="ph-bold ph-database"></i> Seluruh Master Pegawai SIMPEG</span>
            </div>
        </div>

        <!-- Terdaftar di SIMGAJI -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Terdaftar di SIMGAJI</p>
                    <h3 class="luno-widget-value" style="color: #059669;">{{ number_format($totalSimpegTerdaftarSimgaji, 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(5, 150, 105, 0.1); color: #059669;">
                    <i class="ph-bold ph-check-circle"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span class="luno-trend-up"><i class="ph-bold ph-check"></i> Memiliki NIP di Database DBF</span>
            </div>
        </div>

        <!-- Belum Terdaftar di SIMGAJI -->
        <div class="luno-widget">
            <div class="luno-widget-top">
                <div>
                    <p class="luno-widget-title">Belum di SIMGAJI</p>
                    <h3 class="luno-widget-value" style="color: #d97706;">{{ number_format(max(0, $totalSimpegCount - $totalSimpegTerdaftarSimgaji), 0, ',', '.') }}</h3>
                </div>
                <div class="luno-widget-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;">
                    <i class="ph-bold ph-warning-circle"></i>
                </div>
            </div>
            <div class="luno-widget-bottom">
                <span class="luno-trend-danger"><i class="ph-bold ph-info"></i> PPPK Paruh Waktu / Pegawai Baru</span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card" style="padding: 18px 20px; margin-bottom: 24px; border: 1px solid var(--border-color);">
        <form method="GET" action="{{ route('laporan.perbaikan_simgaji_skpd.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="tab" value="simpeg_lengkap">

            <!-- Filter SKPD SIMPEG -->
            <div style="flex: 1.5; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-buildings"></i> SKPD Resmi (SIMPEG)
                </label>
                <select name="simpeg_skpd" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua">Semua SKPD ({{ count($simpegSkpdOptions) }})</option>
                    @foreach($simpegSkpdOptions as $skpd)
                        <option value="{{ $skpd }}" {{ $simpegSkpdFilter === $skpd ? 'selected' : '' }}>{{ $skpd }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pegawai -->
            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-user-circle"></i> Status Pegawai
                </label>
                <select name="status_pegawai" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua">Semua Status</option>
                    @foreach($statusPegawaiOptions as $st)
                        <option value="{{ $st }}" {{ $statusPegawaiFilter === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status di SIMGAJI -->
            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-toggle-left"></i> Status di SIMGAJI
                </label>
                <select name="status_simgaji" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                    <option value="semua" {{ $statusSimgajiFilter === 'semua' ? 'selected' : '' }}>Semua Pegawai</option>
                    <option value="terdaftar" {{ $statusSimgajiFilter === 'terdaftar' ? 'selected' : '' }}>🟢 Terdaftar di SIMGAJI</option>
                    <option value="belum_terdaftar" {{ $statusSimgajiFilter === 'belum_terdaftar' ? 'selected' : '' }}>🟡 Belum di SIMGAJI</option>
                </select>
            </div>

            <!-- Search Box -->
            <div style="flex: 1.5; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Pencarian
                </label>
                <input type="text" name="simpeg_search" class="form-control" placeholder="Cari NIP, Nama, NIK, Satker, Jabatan..." value="{{ $simpegSearch }}" style="width: 100%; height: 38px; font-size: 12.5px;">
            </div>

            <!-- Tombol Aksi Filter -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cari
                </button>
                @if($simpegSkpdFilter !== 'semua' || $statusPegawaiFilter !== 'semua' || $statusSimgajiFilter !== 'semua' || $simpegSearch !== '' || $perPage !== 25)
                    <a href="{{ route('laporan.perbaikan_simgaji_skpd.index', ['tab' => 'simpeg_lengkap']) }}" class="btn btn-export" style="height: 38px; color: var(--danger-text); padding: 0 14px;" title="Reset filter">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset
                    </a>
                @endif
                <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_simpeg_lengkap', request()->query()) }}" class="btn btn-export" style="height: 38px; color: #0d9488; border-color: rgba(13, 148, 136, 0.4); font-weight: 700;" title="Unduh hasil filter ke Excel">
                    <i class="ph-bold ph-file-xls"></i> Unduh Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel BNBA SIMPEG Lengkap + SIMGAJI -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                    Data BNBA Pegawai SIMPEG Lengkap & Data SIMGAJI
                </h3>
                <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                    Menampilkan <strong>{{ $simpegItems ? number_format($simpegItems->total(), 0, ',', '.') : 0 }}</strong> data pegawai sesuai filter yang dipilih.
                </p>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="badge" style="background: rgba(13, 148, 136, 0.1); color: #0d9488; padding: 6px 12px; font-size: 11px; font-weight: 700; border-radius: 6px;">
                    <i class="ph-bold ph-database"></i> 4 Kolom SIMGAJI: Kd SKPD, Nama SKPD, Kode Satker, Nama Satker Resmi
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="font-size: 11.5px;">
                <thead>
                    <!-- Top Group Headers -->
                    <tr>
                        <th colspan="4" style="text-align: center; background: #1e3a8a; color: #ffffff; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase;">
                            <i class="ph-bold ph-user"></i> Identitas Pegawai (SIMPEG)
                        </th>
                        <th colspan="4" style="text-align: center; background: #047857; color: #ffffff; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase;">
                            <i class="ph-bold ph-medal"></i> Status & Pangkat (SIMPEG)
                        </th>
                        <th colspan="3" style="text-align: center; background: #0f766e; color: #ffffff; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase;">
                            <i class="ph-bold ph-buildings"></i> Acuan Unit Kerja (SIMPEG)
                        </th>
                        <th colspan="4" style="text-align: center; background: #0f172a; color: #ffffff; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase;">
                            <i class="ph-bold ph-database"></i> Data dari SIMGAJI Taspen
                        </th>
                        <th rowspan="2" style="text-align: center; background: #374151; color: #ffffff; vertical-align: middle; padding: 8px 12px; font-size: 11px; text-transform: uppercase; width: 130px;">
                            Status SIMGAJI
                        </th>
                    </tr>
                    <!-- Sub Headers -->
                    <tr style="background: var(--bg-surface-subtle); color: var(--text-main);">
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="width: 165px;">NIP</th>
                        <th style="min-width: 200px;">Nama Pegawai / Biodata</th>
                        <th style="width: 150px; border-right: 1px solid var(--border-color);">NIK</th>

                        <th style="width: 110px;">Status / Jenis</th>
                        <th style="width: 100px; text-align: center;">Gol / TMT</th>
                        <th style="min-width: 180px;">Jabatan SIMPEG</th>
                        <th style="min-width: 160px; border-right: 1px solid var(--border-color);">Pendidikan</th>

                        <th style="min-width: 180px;">SKPD Resmi</th>
                        <th style="min-width: 160px;">UPTD Resmi</th>
                        <th style="min-width: 160px; border-right: 1px solid var(--border-color);">Satker / Sub Unit</th>

                        <th style="width: 80px; text-align: center;">Kd SKPD</th>
                        <th style="min-width: 190px;">Nama SKPD SIMGAJI</th>
                        <th style="width: 90px; text-align: center;">Kode Satker</th>
                        <th style="min-width: 190px; border-right: 1px solid var(--border-color);">Nama Satker Resmi SIMGAJI</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noSimpeg = $simpegItems ? ($simpegItems->firstItem() ?? 1) : 1; @endphp
                    @forelse($simpegItems ?? [] as $pgw)
                        @php
                            $isRegisteredSimgaji = ($pgw->kdskpd_simgaji !== '-');
                            $rowBg = $isRegisteredSimgaji ? '' : 'rgba(245, 158, 11, 0.04)';
                        @endphp
                        <tr style="border-bottom: 1px solid var(--border-color); background: {{ $rowBg }};">
                            <!-- No -->
                            <td style="text-align: center; color: var(--text-muted); font-size: 11px;">
                                {{ $noSimpeg++ }}
                            </td>

                            <!-- NIP -->
                            <td style="font-family: monospace; font-size: 11.5px; font-weight: 600;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                    <span>{{ $pgw->nip }}</span>
                                    <button type="button" class="btn btn-export" style="padding: 1px 4px; height: 20px; font-size: 10px; border-radius: 4px;" onclick="copyNip('{{ $pgw->nip }}', this)" title="Salin NIP">
                                        <i class="ph-bold ph-copy"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Nama Pegawai / Biodata -->
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 12px;">
                                    {{ $pgw->nama }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                                    {{ $pgw->tempat_lahir ?: '-' }}, {{ $pgw->tgl_lahir ? date('d-m-Y', strtotime($pgw->tgl_lahir)) : '-' }} &bull; {{ $pgw->jk ?: '-' }} &bull; {{ $pgw->agama ?: '-' }}
                                </div>
                            </td>

                            <!-- NIK -->
                            <td style="font-family: monospace; font-size: 11px; color: var(--text-muted); border-right: 1px solid var(--border-color);">
                                {{ $pgw->nik ?: '-' }}
                            </td>

                            <!-- Status / Jenis Pegawai -->
                            <td>
                                <span class="badge" style="background: rgba(30, 64, 175, 0.1); color: #1e40af; font-size: 10px; padding: 2px 6px;">
                                    {{ $pgw->status_pegawai ?: '-' }}
                                </span>
                                @if($pgw->jenis_pegawai)
                                    <span class="badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; font-size: 10px; padding: 2px 6px; margin-top: 2px; display: inline-block;">
                                        {{ $pgw->jenis_pegawai }}
                                    </span>
                                @endif
                            </td>

                            <!-- Gol / TMT & Masa Kerja -->
                            <td style="text-align: center;">
                                <div style="font-weight: 700; color: var(--text-main);">{{ $pgw->golru ?: '-' }}</div>
                                <div style="font-size: 10px; color: var(--text-muted);">
                                    TMT: {{ $pgw->tmt_golru ?: '-' }}
                                </div>
                                @if($pgw->masa_kerja_tahun !== null)
                                    <div style="font-size: 10px; color: var(--text-muted);">
                                        MK: {{ $pgw->masa_kerja_tahun }} th {{ $pgw->masa_kerja_bulan ?? 0 }} bl
                                    </div>
                                @endif
                            </td>

                            <!-- Jabatan SIMPEG -->
                            <td>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 11.5px;">
                                    {{ $pgw->jabatan?->nama ?? '-' }}
                                </div>
                                @if($pgw->jabatan?->eselon && $pgw->jabatan->eselon !== '-')
                                    <span class="badge" style="background: rgba(0,0,0,0.06); font-size: 9.5px; padding: 1px 5px;">
                                        Eselon: {{ $pgw->jabatan->eselon }}
                                    </span>
                                @endif
                                @if($pgw->tmt_jabatan)
                                    <span style="font-size: 10px; color: var(--text-muted); margin-left: 4px;">
                                        TMT: {{ $pgw->tmt_jabatan }}
                                    </span>
                                @endif
                            </td>

                            <!-- Pendidikan -->
                            <td style="border-right: 1px solid var(--border-color); font-size: 11px;">
                                <div style="font-weight: 600; color: var(--text-main);">
                                    {{ $pgw->tk_ijazah ?: '-' }}
                                </div>
                                <div style="color: var(--text-muted); font-size: 10.5px;">
                                    {{ $pgw->nm_pendidikan ?: '-' }} {{ $pgw->th_lulus ? '('.$pgw->th_lulus.')' : '' }}
                                </div>
                            </td>

                            <!-- Acuan Unit Kerja SIMPEG -->
                            <td style="font-size: 11px;">
                                <div style="font-weight: 600; color: var(--text-main);">
                                    {{ $pgw->unitKerja?->skpd ?? '-' }}
                                </div>
                            </td>
                            <td style="font-size: 11px;">
                                @if($pgw->unitKerja?->upt && $pgw->unitKerja->upt !== '-')
                                    <span style="color: #0d9488; font-weight: 600;">
                                        <i class="ph-bold ph-buildings"></i> {{ $pgw->unitKerja->upt }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td style="font-size: 11px; border-right: 1px solid var(--border-color);">
                                @if($pgw->unitKerja?->satker && $pgw->unitKerja->satker !== '-')
                                    <span>{{ $pgw->unitKerja->satker }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <!-- DATA SIMGAJI: Kd SKPD -->
                            <td style="text-align: center; font-family: monospace; font-weight: 700;">
                                @if($pgw->kdskpd_simgaji !== '-')
                                    <span class="badge" style="background: #0f172a; color: #ffffff; padding: 2px 7px; font-size: 11px;">
                                        {{ $pgw->kdskpd_simgaji }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <!-- DATA SIMGAJI: Nama SKPD SIMGAJI -->
                            <td style="font-size: 11.5px; font-weight: 600; color: var(--text-main);">
                                @if($pgw->kdskpd_simgaji !== '-')
                                    <span>{{ $pgw->nmskpd_simgaji }}</span>
                                @else
                                    <span style="color: var(--danger-text); font-style: italic; font-weight: 400;">
                                        Belum Terdaftar di SIMGAJI
                                    </span>
                                @endif
                            </td>

                            <!-- DATA SIMGAJI: Kode Satker -->
                            <td style="text-align: center; font-family: monospace;">
                                @if($pgw->kdsatker_simgaji !== '-')
                                    <span class="badge" style="background: #047857; color: #ffffff; padding: 2px 7px; font-size: 10.5px;">
                                        {{ $pgw->kdsatker_simgaji }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <!-- DATA SIMGAJI: Nama Satker Resmi SIMGAJI -->
                            <td style="font-size: 11px; color: var(--text-main); border-right: 1px solid var(--border-color);">
                                @if($pgw->nmsatker_simgaji !== '-')
                                    <strong style="color: #047857;">{{ $pgw->nmsatker_simgaji }}</strong>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <!-- Status di SIMGAJI -->
                            <td style="text-align: center;">
                                @if($pgw->status_simgaji === 'Aktif')
                                    <span class="badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; font-size: 10.5px; padding: 3px 8px; border: 1px solid rgba(5, 150, 105, 0.25);">
                                        <i class="ph-bold ph-check"></i> Aktif
                                    </span>
                                @elseif(str_contains($pgw->status_simgaji, 'Pensiun'))
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; font-size: 10px; padding: 3px 8px; border: 1px solid rgba(245, 158, 11, 0.25);">
                                        <i class="ph-bold ph-user-minus"></i> Pensiun/Stop
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.08); color: #dc2626; font-size: 10px; padding: 3px 8px; border: 1px solid rgba(239, 68, 68, 0.2);">
                                        <i class="ph-bold ph-x"></i> Belum di SIMGAJI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                <i class="ph-bold ph-user-minus" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                Tidak ada data pegawai SIMPEG yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($simpegItems && $simpegItems->total() > 0)
            <div class="pagination-container">
                <div class="pagination-info">
                    <i class="ph-bold ph-list-numbers" style="color: #0d9488; font-size: 16px;"></i>
                    <span>
                        Menampilkan <strong>{{ $simpegItems->firstItem() ?? 0 }}</strong> s/d <strong>{{ $simpegItems->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($simpegItems->total(), 0, ',', '.') }}</strong> Pegawai (Halaman {{ $simpegItems->currentPage() }} dari {{ $simpegItems->lastPage() }})
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                        <span>Per hal:</span>
                        <select onchange="window.location.href = this.value;" class="form-control" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                            @foreach([25, 50, 100, 250, 500] as $size)
                                <option value="{{ $simpegItems->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($simpegItems->hasPages())
                        <div class="pagination-nav">
                            @if(!$simpegItems->onFirstPage())
                                <a href="{{ $simpegItems->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                    <i class="ph-bold ph-caret-double-left"></i>
                                </a>
                                <a href="{{ $simpegItems->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                    <i class="ph-bold ph-caret-left"></i> Prev
                                </a>
                            @else
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-double-left"></i></span>
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-left"></i> Prev</span>
                            @endif

                            @php
                                $cPage = $simpegItems->currentPage();
                                $lPage = $simpegItems->lastPage();
                                $sPage = max(1, $cPage - 2);
                                $ePage = min($lPage, $cPage + 2);
                            @endphp

                            @if($sPage > 1)
                                <a href="{{ $simpegItems->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                                @if($sPage > 2)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                            @endif

                            @for($p = $sPage; $p <= $ePage; $p++)
                                @if($p == $cPage)
                                    <span class="page-btn active" style="background: #0d9488 !important; border-color: #0d9488 !important;">{{ $p }}</span>
                                @else
                                    <a href="{{ $simpegItems->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                                @endif
                            @endfor

                            @if($ePage < $lPage)
                                @if($ePage < $lPage - 1)
                                    <span class="page-dots">&hellip;</span>
                                @endif
                                <a href="{{ $simpegItems->appends(request()->query())->url($lPage) }}" class="page-btn">{{ $lPage }}</a>
                            @endif

                            @if($simpegItems->hasMorePages())
                                <a href="{{ $simpegItems->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                    Next <i class="ph-bold ph-caret-right"></i>
                                </a>
                                <a href="{{ $simpegItems->appends(request()->query())->url($lPage) }}" class="page-btn" title="Halaman Terakhir">
                                    <i class="ph-bold ph-caret-double-right"></i>
                                </a>
                            @else
                                <span class="page-btn disabled">Next <i class="ph-bold ph-caret-right"></i></span>
                                <span class="page-btn disabled"><i class="ph-bold ph-caret-double-right"></i></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
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
            2. Seluruh nama Satker pada kolom <em>Kondisi SIMGAJI (Eksisting)</em> kini telah disinkronkan langsung dari file master <strong>kode_skpd_satker_simgaji.xlsx</strong>.<br>
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

    <!-- Filter Bar BNBA -->
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
                <input type="text" name="search" class="form-control" placeholder="Cari NIP, Nama, atau Satker..." value="{{ $search }}" style="width: 100%; height: 38px; font-size: 12.5px;">
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
                        <th colspan="4" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); background: #065f46; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="ph-bold ph-shield-check"></i> Acuan Resmi SIMPEG
                        </th>
                        <th rowspan="2" style="text-align: center; background: #312e81; vertical-align: middle; padding: 8px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; min-width: 290px;">
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
                        <th style="min-width: 200px; border-right: 1px solid var(--border-color);">Satker SIMGAJI (Resmi)</th>

                        <th style="width: 90px; text-align: center;">Kd Rekomendasi</th>
                        <th style="min-width: 190px;">SKPD Resmi (SIMPEG)</th>
                        <th style="min-width: 170px;">UPTD Resmi (SIMPEG)</th>
                        <th style="min-width: 170px; border-right: 1px solid var(--border-color);">Satker / Sub Unit (SIMPEG)</th>
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
                                <div style="font-weight: 700; color: var(--text-main); font-size: 11.5px; line-height: 1.35;">
                                    {{ $row['nmsatker_simgaji'] ?? '-' }}
                                </div>
                                <div style="margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span class="badge" style="background: rgba(15, 23, 42, 0.08); font-family: monospace; font-size: 10px; padding: 1px 6px; border-radius: 4px;">
                                        Kd: {{ $row['kdsatker_simgaji'] }}
                                    </span>
                                    @if($row['inputer_simgaji'] !== '-')
                                        <span style="font-size: 10px; color: var(--text-light);">Inputer: {{ $row['inputer_simgaji'] }}</span>
                                    @endif
                                </div>
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

                            <!-- SIMPEG: UPTD Resmi -->
                            <td>
                                @if(!empty($row['upt_simpeg']) && $row['upt_simpeg'] !== '-')
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 11.5px;">
                                        <i class="ph-bold ph-buildings" style="color: #059669; font-size: 12px; margin-right: 2px;"></i> {{ $row['upt_simpeg'] }}
                                    </div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 11px;">Induk SKPD (-)</span>
                                @endif
                            </td>

                            <!-- SIMPEG: Satker / Sub Unit Resmi -->
                            <td style="border-right: 1px solid var(--border-color); font-size: 11px; color: var(--text-muted);">
                                @if(!empty($row['satker_simpeg']) && $row['satker_simpeg'] !== '-')
                                    <div style="color: var(--text-main); font-weight: 500;">{{ $row['satker_simpeg'] }}</div>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
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
                                @if(!empty($row['satker_rekomendasi']) && $row['satker_rekomendasi'] !== '-' && $row['kdsatker_rekomendasi'] !== '-')
                                    <div style="margin-top: 4px; padding: 4px 8px; background: rgba(16, 185, 129, 0.08); border-left: 3px solid #10b981; border-radius: 4px; font-size: 10.5px; color: #065f46;">
                                        <strong>Rujukan Satker SIMGAJI:</strong> {{ $row['satker_rekomendasi'] }} <code style="font-family: monospace; font-size: 10px; background: rgba(0,0,0,0.06); padding: 1px 4px; border-radius: 3px;">{{ $row['kdsatker_rekomendasi'] }}</code>
                                    </div>
                                @endif
                                <div style="font-size: 10.5px; color: var(--text-light); margin-top: 3px;">
                                    {{ $row['keterangan'] }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
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
