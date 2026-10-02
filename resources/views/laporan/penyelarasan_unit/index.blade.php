@extends('layouts.app')

@section('title', 'Laporan Penyelarasan SKPD — UPTD — SATKER')
@section('page_title', 'Penyelarasan Unit Kerja (SIMGAJI vs SIMPEG)')

@section('content')
@php
    $tabLabels = [
        'rekap_skpd' => 'Rekapitulasi SKPD Induk',
        'pemetaan_satker' => 'Pemetaan UPTD & SATKER',
        'beda_pegawai' => 'Daftar Pegawai Beda Penempatan',
    ];
    $currentTabLabel = $tabLabels[$activeTab] ?? 'Penyelarasan Unit Kerja';
@endphp

<style>
    @media print {
        @page {
            size: landscape;
            margin: 1cm 0.8cm 1cm 0.8cm;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 8.5pt !important;
        }
        .sidebar, .topbar, .app-header, .stats-grid, .tab-nav, .filter-card, .pagination-container, .modal-overlay, .btn, td.action-col, th.action-col, .alert, .theme-toggle {
            display: none !important;
        }
        .content-area, .main-content, .card {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .data-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 8px !important;
        }
        .data-table th, .data-table td {
            border: 1px solid #334155 !important;
            padding: 4px 6px !important;
            color: #000000 !important;
            font-size: 8pt !important;
        }
        .data-table th {
            background-color: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: 700 !important;
            text-transform: uppercase;
        }
        .badge {
            border: 1px solid #94a3b8 !important;
            background: transparent !important;
            color: #000000 !important;
            padding: 1px 4px !important;
            font-size: 7.5pt !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .print-header h2 {
            font-size: 13pt;
            margin: 0;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .print-header h3 {
            font-size: 15pt;
            margin: 3px 0;
            font-weight: 800;
            text-transform: uppercase;
        }
        .print-header p {
            font-size: 8.5pt;
            margin: 2px 0 0 0;
            color: #333;
        }
    }

    .print-header {
        display: none;
    }
</style>

<div class="app-header">
    <div>
        <h2>Penyelarasan SKPD — UPTD — SATKER</h2>
        <p>Audit perbandingan dan matriks penyelarasan struktur unit kerja antara database SIMGAJI dengan SIMPEG (Master Pegawai).</p>
        <div style="margin-top: 8px; display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; background: rgba(76, 53, 222, 0.06); border-radius: 999px; font-size: 12px; color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-database"></i> Database Acuan: <strong>{{ $activeFile['filename'] ?? '-' }}</strong> ({{ $activeFile['size'] ?? '-' }}) &bull; Data per: {{ $cachedAt ?? '-' }}
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('laporan.penyelarasan_unit.refresh') }}" class="btn btn-export" title="Kalkulasi ulang pemetaan unit kerja dari file DBF aktif">
            <i class="ph-bold ph-arrows-clockwise"></i> Refresh Cache
        </a>

        <!-- Separator -->
        <div style="width: 1px; height: 26px; background: var(--border-color); margin: 0 4px;"></div>

        <!-- Mode Export -->
        <a href="{{ route('laporan.penyelarasan_unit.export_excel', ['tab' => $activeTab, 'search' => $search, 'status' => $statusFilter, 'skpd' => $skpdFilter]) }}" class="btn btn-export" style="color: #059669; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;" title="Unduh data tab ini ke Excel (.xlsx)">
            <i class="ph-bold ph-file-xls" style="font-size: 16px; color: #10b981;"></i> Export Excel
        </a>
        <a href="{{ route('laporan.penyelarasan_unit.export_pdf', ['tab' => $activeTab, 'search' => $search, 'status' => $statusFilter, 'skpd' => $skpdFilter]) }}" class="btn btn-export" style="color: #dc2626; border-color: rgba(239, 68, 68, 0.4); font-weight: 600;" title="Unduh dokumen resmi PDF">
            <i class="ph-bold ph-file-pdf" style="font-size: 16px; color: #ef4444;"></i> Export PDF
        </a>
        <button type="button" class="btn btn-export" onclick="window.print()" style="color: var(--text-main); font-weight: 600;" title="Cetak halaman">
            <i class="ph-bold ph-printer" style="font-size: 16px;"></i> Cetak
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(!empty($error))
    <div class="alert alert-error">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <div>{{ $error }}</div>
    </div>
@endif

@if(!empty($summary))
<!-- KPI Widgets Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Pegawai Aktif SIMGAJI</p>
                <h3 class="luno-widget-value">{{ number_format($summary['total_aktif_simgaji'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-users"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-check"></i> Diluar Pensiunan/Non-aktif</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Kode SKPD SIMGAJI</p>
                <h3 class="luno-widget-value" style="color: #4C35DE;">{{ number_format($summary['total_skpd_simgaji'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-info">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-tree-structure"></i> vs {{ $summary['total_skpd_simpeg'] ?? 42 }} SKPD SIMPEG</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Total Satker Aktif</p>
                <h3 class="luno-widget-value">{{ number_format($summary['total_satker_simgaji'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-git-branch"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-check-circle"></i> Unit & Sub-Unit SIMGAJI</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Satker Multi-UPTD</p>
                <h3 class="luno-widget-value" style="color: #f59e0b;">{{ number_format($summary['multi_upt_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-warning">
                <i class="ph-bold ph-arrows-split"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-down" style="color: #d97706;"><i class="ph-bold ph-warning"></i> 1 Satker &gt; 1 UPTD SIMPEG</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Selisih Penempatan</p>
                <h3 class="luno-widget-value" style="color: #ef4444;">{{ number_format($summary['beda_pegawai_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-danger">
                <i class="ph-bold ph-arrows-left-right"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-down" style="color: #dc2626;"><i class="ph-bold ph-user-switch"></i> Beda SKPD / UPTD</span>
        </div>
    </div>
</div>
@endif

<!-- Navigasi Tab Analisis -->
<div class="tab-nav">
    <a href="{{ route('laporan.penyelarasan_unit.index', ['tab' => 'rekap_skpd', 'skpd' => $skpdFilter]) }}" class="tab-item {{ $activeTab === 'rekap_skpd' ? 'active' : '' }}">
        <i class="ph-bold ph-buildings"></i> 1. Rekapitulasi SKPD Induk
    </a>
    <a href="{{ route('laporan.penyelarasan_unit.index', ['tab' => 'pemetaan_satker', 'skpd' => $skpdFilter]) }}" class="tab-item {{ $activeTab === 'pemetaan_satker' ? 'active' : '' }}">
        <i class="ph-bold ph-git-branch"></i> 2. Pemetaan UPTD & SATKER
        @if(($summary['multi_upt_count'] ?? 0) > 0)
            <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #d97706; margin-left: 6px;">{{ $summary['multi_upt_count'] }} Multi</span>
        @endif
    </a>
    <a href="{{ route('laporan.penyelarasan_unit.index', ['tab' => 'beda_pegawai', 'skpd' => $skpdFilter]) }}" class="tab-item {{ $activeTab === 'beda_pegawai' ? 'active' : '' }}">
        <i class="ph-bold ph-users-three"></i> 3. Pegawai Beda Penempatan
        @if(($summary['beda_pegawai_count'] ?? 0) > 0)
            <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #dc2626; margin-left: 6px;">{{ $summary['beda_pegawai_count'] }}</span>
        @endif
    </a>
</div>

<!-- Header Cetak -->
<div class="print-header">
    <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
    <h3>LAPORAN PENYELARASAN SKPD — UPTD — SATKER</h3>
    <p>Kategori: {{ $currentTabLabel }} | Database Acuan: {{ $activeFile['filename'] ?? '-' }} | Dicetak: {{ date('d/m/Y H:i') }} WITA</p>
</div>

<!-- Card Konten Utama -->
<div class="card">
    <div class="filter-card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('laporan.penyelarasan_unit.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; flex: 1;">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <!-- Kotak Pencarian -->
            <div style="position: relative; min-width: 260px; flex: 1;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 16px;"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama SKPD, UPTD, satker..." class="form-control" style="padding-left: 36px; height: 38px; font-size: 13px; width: 100%;">
            </div>

            <!-- Filter Status Dinamis per Tab -->
            <div style="min-width: 200px;">
                <select name="status" class="form-control" style="height: 38px; font-size: 13px;" onchange="this.form.submit()">
                    @if($activeTab === 'rekap_skpd')
                        <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua Status Kesesuaian</option>
                        <option value="sesuai" {{ $statusFilter === 'sesuai' ? 'selected' : '' }}>🟢 Sesuai (Matched)</option>
                        <option value="beda_jumlah" {{ $statusFilter === 'beda_jumlah' ? 'selected' : '' }}>🟡 Ada Selisih Pegawai</option>
                        <option value="cabang_wilayah" {{ $statusFilter === 'cabang_wilayah' ? 'selected' : '' }}>🔵 Pecahan Cabang Disdik (070-082)</option>
                        <option value="tidak_terpetakan" {{ $statusFilter === 'tidak_terpetakan' ? 'selected' : '' }}>🔴 Belum Terpetakan di SIMPEG</option>
                        <option value="belum_ada_di_simgaji" {{ $statusFilter === 'belum_ada_di_simgaji' ? 'selected' : '' }}>⚪ Belum Ada di SIMGAJI</option>
                    @elseif($activeTab === 'pemetaan_satker')
                        <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua Status Pemetaan</option>
                        <option value="sesuai_upt" {{ $statusFilter === 'sesuai_upt' ? 'selected' : '' }}>🟢 Terpetakan ke 1 UPTD Jelas</option>
                        <option value="multi_upt" {{ $statusFilter === 'multi_upt' ? 'selected' : '' }}>🟡 Bercampur &gt;1 UPTD (Multi)</option>
                        <option value="induk_skpd" {{ $statusFilter === 'induk_skpd' ? 'selected' : '' }}>🔵 Satker Tingkat Induk SKPD</option>
                        <option value="belum_terpetakan" {{ $statusFilter === 'belum_terpetakan' ? 'selected' : '' }}>🔴 Belum Ada di SIMPEG</option>
                    @else
                        <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua Jenis Selisih</option>
                        <option value="Beda SKPD Induk" {{ $statusFilter === 'Beda SKPD Induk' ? 'selected' : '' }}>🔴 Beda SKPD Induk</option>
                        <option value="Beda UPTD / Sekolah" {{ $statusFilter === 'Beda UPTD / Sekolah' ? 'selected' : '' }}>🟡 Beda UPTD / Sekolah</option>
                    @endif
                </select>
            </div>

            <!-- Filter SKPD SIMPEG -->
            <div style="min-width: 240px;">
                <select name="skpd" class="form-control" style="height: 38px; font-size: 13px;" onchange="this.form.submit()">
                    <option value="semua">Semua SKPD SIMPEG</option>
                    @foreach($allSkpds as $sOption)
                        <option value="{{ $sOption }}" {{ $skpdFilter === $sOption ? 'selected' : '' }}>{{ $sOption }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-export">
                <i class="ph-bold ph-funnel"></i> Filter
            </button>

            @if($search || $statusFilter !== 'semua' || $skpdFilter !== 'semua')
                <a href="{{ route('laporan.penyelarasan_unit.index', ['tab' => $activeTab]) }}" class="btn btn-export" style="color: var(--danger-text);" title="Reset semua filter">
                    <i class="ph-bold ph-x"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Tampilan Konten Berdasarkan Tab Aktif -->
    <div class="table-responsive">
        @if($activeTab === 'rekap_skpd')
            <!-- TAB 1: REKAPITULASI SKPD -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 110px; text-align: center;">Kd SKPD</th>
                        <th>Nama SKPD di SIMGAJI</th>
                        <th>Nomenklatur di SIMPEG (Master)</th>
                        <th style="width: 110px; text-align: center;">Peg. SIMGAJI</th>
                        <th style="width: 110px; text-align: center;">Peg. SIMPEG</th>
                        <th style="width: 90px; text-align: center;">Selisih</th>
                        <th style="width: 90px; text-align: center;">Jml Satker</th>
                        <th style="width: 160px; text-align: center;">Status Kesesuaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); font-weight: 700; font-size: 11.5px;">
                                    {{ $row['kdskpd'] }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $row['nama_simgaji'] }}</strong>
                                @if(!empty($row['inputers']) && $row['inputers'] !== '-')
                                    <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">
                                        Inputer: {{ $row['inputers'] }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($row['nama_simpeg'] !== '-')
                                    <span style="font-weight: 600; color: var(--text-main);">{{ $row['nama_simpeg'] }}</span>
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">(Tidak Ada Relasi Pegawai di SIMPEG)</span>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ number_format($row['jml_simgaji'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">
                                {{ number_format($row['jml_simpeg'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($row['selisih'] == 0)
                                    <span style="color: #059669; font-weight: 700;">0</span>
                                @elseif($row['selisih'] > 0)
                                    <span style="color: #2563eb; font-weight: 700;">+{{ $row['selisih'] }}</span>
                                @else
                                    <span style="color: #dc2626; font-weight: 700;">{{ $row['selisih'] }}</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: var(--bg-surface-hover); color: var(--text-main); font-weight: 600;">
                                    {{ $row['satker_count'] }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($row['status'] === 'sesuai')
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-check-circle"></i> Sesuai
                                    </span>
                                @elseif($row['status'] === 'cabang_wilayah')
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.12); color: #2563eb; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;" title="Kode cabang Disdik per wilayah di SIMGAJI yang di SIMPEG menginduk ke Dinas Pendidikan">
                                        <i class="ph-bold ph-map-pin"></i> Cabang Disdik
                                    </span>
                                @elseif($row['status'] === 'beda_jumlah')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #d97706; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;" title="Nama cocok namun jumlah pegawai aktif di SIMGAJI berbeda dengan SIMPEG">
                                        <i class="ph-bold ph-warning"></i> Selisih Pegawai
                                    </span>
                                @elseif($row['status'] === 'tidak_terpetakan')
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-prohibit"></i> Tidak Terpetakan
                                    </span>
                                @else
                                    <span class="badge" style="background: var(--bg-surface-hover); color: var(--text-muted); padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                        Belum Ada di SIMGAJI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-magnifying-glass" style="font-size: 32px; color: var(--text-subtle); display: block; margin-bottom: 8px;"></i>
                                Tidak ada data rekapitulasi SKPD yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'pemetaan_satker')
            <!-- TAB 2: PEMETAAN SATKER & UPTD -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 80px; text-align: center;">Kd SKPD</th>
                        <th style="width: 150px;">Kode Satker SIMGAJI</th>
                        <th>Nama Satker / SKPD di SIMGAJI</th>
                        <th style="width: 90px; text-align: center;">Pegawai</th>
                        <th>Pemetaan SKPD di SIMPEG</th>
                        <th>Pemetaan UPTD di SIMPEG</th>
                        <th style="width: 150px; text-align: center;">Status Pemetaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); font-weight: 700;">
                                    {{ $row['kdskpd'] }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $row['kdsatker'] }}</code>
                                @if(!empty($row['inputer']) && $row['inputer'] !== '-')
                                    <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">
                                        Inputer: {{ $row['inputer'] }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $row['nama_skpd_simgaji'] }}</strong>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ number_format($row['jml_simgaji'], 0, ',', '.') }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-main);">{{ $row['skpd_simpeg'] }}</span>
                            </td>
                            <td>
                                @if($row['upt_simpeg'] !== '-')
                                    <div style="font-weight: 600; color: var(--luno-primary); line-height: 1.35;">
                                        <i class="ph-bold ph-graduation-cap" style="font-size: 13px;"></i> {{ $row['upt_simpeg'] }}
                                    </div>
                                    @if($row['status_satker'] === 'multi_upt')
                                        <div style="margin-top: 4px; font-size: 11px; color: #d97706; background: rgba(245, 158, 11, 0.08); padding: 4px 6px; border-radius: 4px; border: 1px solid rgba(245, 158, 11, 0.2);" title="{{ $row['detail_upts'] }}">
                                            <i class="ph-bold ph-warning"></i> Bercampur {{ $row['upt_count'] }} UPTD di satker ini:
                                            <br><span style="color: var(--text-muted); font-size: 10.5px;">{{ Str::limit($row['detail_upts'], 85) }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">(Unit Induk Tanpa UPT)</span>
                                    @if(!empty($row['satker_simpeg']) && $row['satker_simpeg'] !== '-')
                                        <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">
                                            Satker SIMPEG: {{ $row['satker_simpeg'] }}
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($row['status_satker'] === 'sesuai_upt')
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-check"></i> 1 UPTD Jelas
                                    </span>
                                @elseif($row['status_satker'] === 'multi_upt')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;" title="Satker ini menampung pegawai dari lebih dari satu sekolah/UPTD di SIMPEG">
                                        <i class="ph-bold ph-warning"></i> Multi UPTD ({{ $row['upt_count'] }})
                                    </span>
                                @elseif($row['status_satker'] === 'induk_skpd')
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #2563eb; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                        <i class="ph-bold ph-buildings"></i> Tingkat Induk
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                        Belum di SIMPEG
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-magnifying-glass" style="font-size: 32px; color: var(--text-subtle); display: block; margin-bottom: 8px;"></i>
                                Tidak ada data pemetaan satker yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @else
            <!-- TAB 3: DAFTAR PEGAWAI BEDA PENEMPATAN -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 170px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Penempatan di SIMPEG (Master)</th>
                        <th>Penempatan di SIMGAJI (Penggajian)</th>
                        <th style="width: 160px; text-align: center;">Jenis Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong style="color: var(--luno-primary);">{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    {{ $row['skpd_simpeg'] }}
                                </div>
                                @if($row['upt_simpeg'] !== '-')
                                    <div style="color: #2563eb; font-size: 12px; margin-top: 2px;">
                                        <i class="ph-bold ph-graduation-cap"></i> UPTD: {{ $row['upt_simpeg'] }}
                                    </div>
                                @endif
                                @if(!empty($row['satker_simpeg']) && $row['satker_simpeg'] !== '-')
                                    <small style="display: block; color: var(--text-muted); font-size: 11px;">
                                        Satker: {{ $row['satker_simpeg'] }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    {{ $row['skpd_simgaji'] }}
                                </div>
                                <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">
                                    Kd SKPD: <strong>{{ $row['kdskpd_simgaji'] }}</strong> &bull; Satker: <code>{{ $row['kdsatker_simgaji'] }}</code> ({{ $row['inputer_simgaji'] }})
                                </small>
                                @if(!empty($row['keterangan']))
                                    <div style="font-size: 11px; color: #64748b; margin-top: 3px; font-style: italic;">
                                        {{ $row['keterangan'] }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($row['jenis_selisih'] === 'Beda SKPD Induk')
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                                        <i class="ph-bold ph-warning-circle"></i> Beda SKPD Induk
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11.5px;" title="Pegawai terdaftar di UPTD/Sekolah yang berbeda dengan Satker SIMGAJI">
                                        <i class="ph-bold ph-arrows-left-right"></i> Beda UPTD / Sekolah
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                                Seluruh penempatan unit kerja pegawai telah sesuai dan sinkron antara SIMGAJI dan SIMPEG.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <!-- Pagination links -->
    <div class="pagination-container" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-top: 14px; border-top: 1px solid var(--border-color);">
        <div style="font-size: 12.5px; color: var(--text-muted);">
            Menampilkan <strong>{{ $paginatedItems->firstItem() ?? 0 }}</strong> - <strong>{{ $paginatedItems->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedItems->total(), 0, ',', '.') }}</strong> data
        </div>
        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
            @if(!$paginatedItems->onFirstPage())
                <a href="{{ $paginatedItems->appends(request()->query())->url(1) }}" class="btn btn-export" style="padding: 6px 10px; font-size: 12px;" title="Halaman Pertama">
                    <i class="ph-bold ph-caret-double-left"></i>
                </a>
                <a href="{{ $paginatedItems->appends(request()->query())->previousPageUrl() }}" class="btn btn-export" style="padding: 6px 12px; font-size: 12px;">
                    <i class="ph-bold ph-caret-left"></i> Prev
                </a>
            @else
                <span class="btn btn-export" style="padding: 6px 10px; font-size: 12px; opacity: 0.35; cursor: not-allowed;" title="Halaman Pertama">
                    <i class="ph-bold ph-caret-double-left"></i>
                </span>
                <span class="btn btn-export" style="padding: 6px 12px; font-size: 12px; opacity: 0.35; cursor: not-allowed;">
                    <i class="ph-bold ph-caret-left"></i> Prev
                </span>
            @endif

            @php
                $startPage = max(1, $paginatedItems->currentPage() - 2);
                $endPage = min($paginatedItems->lastPage(), $paginatedItems->currentPage() + 2);
            @endphp

            @if($startPage > 1)
                <a href="{{ $paginatedItems->appends(request()->query())->url(1) }}" class="btn btn-export" style="padding: 6px 11px; font-size: 12px;">1</a>
                @if($startPage > 2)
                    <span style="color: var(--text-muted); font-size: 12px; padding: 0 3px;">...</span>
                @endif
            @endif

            @for($p = $startPage; $p <= $endPage; $p++)
                @if($p == $paginatedItems->currentPage())
                    <span style="font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px; background: var(--luno-primary); color: #ffffff; box-shadow: 0 2px 5px rgba(76, 53, 222, 0.25);">
                        {{ $p }}
                    </span>
                @else
                    <a href="{{ $paginatedItems->appends(request()->query())->url($p) }}" class="btn btn-export" style="padding: 6px 11px; font-size: 12px;">
                        {{ $p }}
                    </a>
                @endif
            @endfor

            @if($endPage < $paginatedItems->lastPage())
                @if($endPage < $paginatedItems->lastPage() - 1)
                    <span style="color: var(--text-muted); font-size: 12px; padding: 0 3px;">...</span>
                @endif
                <a href="{{ $paginatedItems->appends(request()->query())->url($paginatedItems->lastPage()) }}" class="btn btn-export" style="padding: 6px 11px; font-size: 12px;">{{ $paginatedItems->lastPage() }}</a>
            @endif

            @if($paginatedItems->hasMorePages())
                <a href="{{ $paginatedItems->appends(request()->query())->nextPageUrl() }}" class="btn btn-export" style="padding: 6px 12px; font-size: 12px;">
                    Next <i class="ph-bold ph-caret-right"></i>
                </a>
                <a href="{{ $paginatedItems->appends(request()->query())->url($paginatedItems->lastPage()) }}" class="btn btn-export" style="padding: 6px 10px; font-size: 12px;" title="Halaman Terakhir">
                    <i class="ph-bold ph-caret-double-right"></i>
                </a>
            @else
                <span class="btn btn-export" style="padding: 6px 12px; font-size: 12px; opacity: 0.35; cursor: not-allowed;">
                    Next <i class="ph-bold ph-caret-right"></i>
                </span>
                <span class="btn btn-export" style="padding: 6px 10px; font-size: 12px; opacity: 0.35; cursor: not-allowed;" title="Halaman Terakhir">
                    <i class="ph-bold ph-caret-double-right"></i>
                </span>
            @endif
        </div>
    </div>
</div>
@endsection
