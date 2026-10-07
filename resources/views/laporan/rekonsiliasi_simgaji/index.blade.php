@extends('layouts.app')

@section('title', 'Laporan Rekonsiliasi SIMGAJI')
@section('page_title', 'Rekonsiliasi SIMGAJI')

@section('content')
@php
    $tabLabels = [
        'aktif_baru' => 'Pegawai Aktif Baru di SIMGAJI',
        'beda_pangkat' => 'Perbedaan Golongan / Pangkat',
        'beda_skpd' => 'Perbedaan Penempatan SKPD',
        'beda_jabatan' => 'Perbedaan Jabatan',
        'beda_lahir' => 'Perbedaan Tanggal Lahir',
        'pensiunan' => 'Arsip Pegawai Pensiun / Berhenti',
        'paruh_waktu' => 'PPPK Paruh Waktu',
    ];
    $currentTabLabel = $tabLabels[$activeTab] ?? 'Rekonsiliasi Data';
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
        .print-footer {
            display: block !important;
            margin-top: 20px;
            page-break-inside: avoid;
        }
    }

    .print-header, .print-footer {
        display: none;
    }
</style>

<div class="app-header">
    <div>
        <h2>Rekonsiliasi Database Master SIMGAJI</h2>
        <p>Analisis perbandingan data kepegawaian, kepangkatan, penempatan SKPD, dan jabatan antara database SIMGAJI dengan Master Data Pegawai Aplikasi.</p>
        <div style="margin-top: 8px; display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; background: rgba(76, 53, 222, 0.06); border-radius: 999px; font-size: 12px; color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-database"></i> Database Aktif: <strong>{{ $activeDbfInfo['filename'] ?? '-' }}</strong> ({{ $activeDbfInfo['filesize'] ?? '-' }}) &bull; Diperbarui: {{ $activeDbfInfo['uploaded_at'] ?? '-' }}
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Group Manajemen Database -->
        <button type="button" class="btn btn-primary" onclick="openModal('uploadDbfModal')" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-color: #1d4ed8;" title="Unggah file DBF SIMGAJI terbaru">
            <i class="ph-bold ph-upload-simple"></i> Upload DBF
        </button>
        <a href="{{ route('master.simgaji_dbf.index') }}" class="btn btn-export" title="Kelola dan lihat riwayat file DBF SIMGAJI">
            <i class="ph-bold ph-folder-open"></i> Kelola DBF
        </a>
        <a href="{{ route('laporan.rekonsiliasi_simgaji.refresh') }}" class="btn btn-export" title="Baca ulang file DBF dan perbarui ringkasan">
            <i class="ph-bold ph-arrows-clockwise"></i> Refresh
        </a>

        <!-- Separator -->
        <div style="width: 1px; height: 26px; background: var(--border-color); margin: 0 4px;"></div>

        <!-- Mode Export -->
        <a href="{{ route('laporan.rekonsiliasi_simgaji.export_excel', ['tab' => $activeTab, 'search' => $search, 'status_pensiun' => $statusPensiun ?? 'semua', 'status_sk' => $statusSk ?? 'semua']) }}" class="btn btn-export" style="color: #059669; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;" title="Unduh data tab ini ke format Excel (.xlsx)">
            <i class="ph-bold ph-file-xls" style="font-size: 16px; color: #10b981;"></i> Export Excel
        </a>
        <a href="{{ route('laporan.rekonsiliasi_simgaji.export_pdf', ['tab' => $activeTab, 'search' => $search, 'status_pensiun' => $statusPensiun ?? 'semua', 'status_sk' => $statusSk ?? 'semua']) }}" class="btn btn-export" style="color: #dc2626; border-color: rgba(239, 68, 68, 0.4); font-weight: 600;" title="Unduh dokumen resmi PDF">
            <i class="ph-bold ph-file-pdf" style="font-size: 16px; color: #ef4444;"></i> Export PDF
        </a>
        <button type="button" class="btn btn-export" onclick="window.print()" style="color: var(--text-main); font-weight: 600;" title="Cetak halaman atau simpan PDF langsung dari browser">
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

@if(session('error') || !empty($error))
    <div class="alert alert-error">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <div>{{ session('error') ?? $error }}</div>
    </div>
@endif

@if(!empty($summary))
<!-- KPI Widgets Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Database SIMGAJI</p>
                <h3 class="luno-widget-value">{{ number_format($summary['total_dbf'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-database"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-archive"></i> Arsip + Aktif SIMGAJI</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Master Pegawai</p>
                <h3 class="luno-widget-value">{{ number_format($summary['total_app'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-info">
                <i class="ph-bold ph-users"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-check"></i> PNS & PPPK Aplikasi</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Pegawai Aktif Baru</p>
                <h3 class="luno-widget-value" style="color: #4C35DE;">{{ number_format($summary['aktif_baru_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-warning">
                <i class="ph-bold ph-user-plus"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-arrow-up-right"></i> Belum masuk Master</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Kenaikan Pangkat</p>
                <h3 class="luno-widget-value" style="color: #ef4444;">{{ number_format($summary['beda_pangkat_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-trend-up"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral" style="font-size: 11px;">
                <strong style="color: #2563eb;">{{ $summary['beda_pangkat_simgaji_tinggi_count'] ?? 0 }}</strong> SIMGAJI Tinggi • <strong style="color: #dc2626;">{{ $summary['beda_pangkat_belum_diinput_count'] ?? 0 }}</strong> Belum Diinput
            </span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Perbedaan SKPD</p>
                <h3 class="luno-widget-value" style="color: #f59e0b;">{{ number_format($summary['beda_skpd_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-warning">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-arrows-left-right"></i> Mutasi / Salah Penempatan</span>
        </div>
    </div>

    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Perbedaan Jabatan</p>
                <h3 class="luno-widget-value" style="color: #8b5cf6;">{{ number_format($summary['beda_jabatan_count'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-info">
                <i class="ph-bold ph-identification-badge"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-briefcase"></i> Master vs Penggajian</span>
        </div>
    </div>
</div>
@endif

<!-- Tab Navigation -->
<div class="card" style="padding: 12px 16px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'aktif_baru']) }}" 
               class="btn {{ $activeTab === 'aktif_baru' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-user-plus"></i> Pegawai Baru
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['aktif_baru_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'beda_pangkat']) }}" 
               class="btn {{ $activeTab === 'beda_pangkat' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;"
               title="{{ $summary['beda_pangkat_simgaji_tinggi_count'] ?? 0 }} SIMGAJI lebih tinggi, {{ $summary['beda_pangkat_belum_diinput_count'] ?? 0 }} belum diinput di SIMGAJI, {{ $summary['beda_pangkat_terjadwal_count'] ?? 0 }} sudah sesuai di HIS_GPOK">
                <i class="ph-bold ph-trend-up"></i> Kenaikan Pangkat
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['beda_pangkat_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'beda_skpd']) }}" 
               class="btn {{ $activeTab === 'beda_skpd' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-buildings"></i> Perbedaan SKPD
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['beda_skpd_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'beda_jabatan']) }}" 
               class="btn {{ $activeTab === 'beda_jabatan' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-identification-badge"></i> Perbedaan Jabatan
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['beda_jabatan_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'beda_lahir']) }}" 
               class="btn {{ $activeTab === 'beda_lahir' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-calendar"></i> Beda Tgl Lahir
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['beda_lahir_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'pensiunan']) }}" 
               class="btn {{ $activeTab === 'pensiunan' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-archive"></i> Arsip Pensiunan
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['pensiunan_count'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => 'paruh_waktu']) }}" 
               class="btn {{ $activeTab === 'paruh_waktu' ? 'btn-primary' : 'btn-export' }}"
               style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                <i class="ph-bold ph-clock"></i> PPPK Paruh Waktu
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px; border-radius: 999px; margin-left: 6px; font-size: 11px;">
                    {{ $summary['paruh_waktu_count'] ?? 0 }}
                </span>
            </a>
        </div>
    </div>
</div>

<!-- Main Content Table Card -->
<div class="card">
    <!-- Official Print Header (Only visible when printed) -->
    <div class="print-header">
        <h2>Pemerintah Provinsi Kalimantan Selatan</h2>
        <h3>Laporan Rekonsiliasi Database Master SIMGAJI</h3>
        <p>
            Kategori: <strong>{{ $currentTabLabel }}</strong> &bull;
            Database Acuan: <strong>{{ $activeDbfInfo['filename'] ?? '-' }}</strong> &bull;
            Dicetak pada: <strong>{{ date('d F Y H:i') }} WITA</strong>
            @if($search) | Filter Pencarian: <em>"{{ $search }}"</em> @endif
        </p>
    </div>

    <div class="filter-card" style="margin-bottom: 18px; padding: 0; background: transparent; border: none; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <form method="GET" action="{{ route('laporan.rekonsiliasi_simgaji.index') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex: 1; max-width: 760px;">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div style="flex: 1; min-width: 240px; position: relative;">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari NIP, nama pegawai, jabatan, atau SKPD..." style="padding-left: 36px;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            </div>

            @if($activeTab === 'beda_pangkat')
                <div style="display: flex; align-items: center; gap: 6px;">
                    <select name="status_pensiun" class="form-control" style="width: auto; padding: 7px 12px; font-size: 12.5px; font-weight: 600; border-radius: 8px;" onchange="this.form.submit()" title="Filter status pegawai">
                        <option value="semua" {{ ($statusPensiun ?? 'semua') === 'semua' ? 'selected' : '' }}>
                            Semua Status ({{ $summary['beda_pangkat_count'] ?? 0 }})
                        </option>
                        <option value="aktif" {{ ($statusPensiun ?? 'semua') === 'aktif' ? 'selected' : '' }}>
                            Hanya Pegawai Aktif ({{ $summary['beda_pangkat_aktif_count'] ?? 0 }})
                        </option>
                        <option value="pensiun" {{ ($statusPensiun ?? 'semua') === 'pensiun' ? 'selected' : '' }}>
                            Hanya Pensiunan ({{ $summary['beda_pangkat_pensiun_count'] ?? 0 }})
                        </option>
                    </select>

                    <select name="status_sk" class="form-control" style="width: auto; padding: 7px 12px; font-size: 12.5px; font-weight: 600; border-radius: 8px;" onchange="this.form.submit()" title="Filter status SK di SIMGAJI">
                        <option value="semua_selisih" {{ ($statusSk ?? 'semua_selisih') === 'semua_selisih' ? 'selected' : '' }}>
                            ⚡ Semua Perlu Penyesuaian ({{ $summary['beda_pangkat_count'] ?? 0 }})
                        </option>
                        <option value="simgaji_lebih_tinggi" {{ ($statusSk ?? 'semua_selisih') === 'simgaji_lebih_tinggi' ? 'selected' : '' }}>
                            🔵 Pangkat di SIMGAJI Lebih Tinggi ({{ $summary['beda_pangkat_simgaji_tinggi_count'] ?? 0 }})
                        </option>
                        <option value="belum_diinput" {{ ($statusSk ?? 'semua_selisih') === 'belum_diinput' ? 'selected' : '' }}>
                            🔴 Belum Diinput di SIMGAJI ({{ $summary['beda_pangkat_belum_diinput_count'] ?? 0 }})
                        </option>
                        <option value="sudah_terjadwal" {{ ($statusSk ?? 'semua_selisih') === 'sudah_terjadwal' ? 'selected' : '' }}>
                            🟢 Sudah Sesuai di SIMGAJI via HIS_GPOK ({{ $summary['beda_pangkat_terjadwal_count'] ?? 0 }})
                        </option>
                        <option value="semua" {{ ($statusSk ?? 'semua_selisih') === 'semua' ? 'selected' : '' }}>
                            Semua Riwayat ({{ $summary['beda_pangkat_total_count'] ?? 475 }})
                        </option>
                    </select>
                </div>
            @endif

            <button type="submit" class="btn btn-export">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            @if($search || ($statusPensiun ?? 'semua') !== 'semua' || ($statusSk ?? 'semua_selisih') !== 'semua_selisih')
                <a href="{{ route('laporan.rekonsiliasi_simgaji.index', ['tab' => $activeTab]) }}" class="btn btn-export" style="color: var(--danger-text);" title="Reset semua filter">
                    <i class="ph-bold ph-x"></i> Reset
                </a>
            @endif
        </form>

        <!-- Action Buttons based on Active Tab -->
        @if($activeTab === 'aktif_baru' && ($summary['aktif_baru_count'] ?? 0) > 0)
            <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_pegawai') }}" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh {{ $summary['aktif_baru_count'] }} pegawai aktif ini ke Master Data Pegawai?');">
                @csrf
                <input type="hidden" name="sync_all" value="1">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); border-color: #059669;">
                    <i class="ph-bold ph-cloud-arrow-down"></i> Sinkronkan Semua ({{ $summary['aktif_baru_count'] }}) ke Master
                </button>
            </form>
        @elseif($activeTab === 'beda_pangkat' && $paginatedItems->total() > 0)
            <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_pangkat') }}" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui golru/pangkat {{ $paginatedItems->total() }} pegawai sesuai data mutakhir SIMGAJI?');">
                @csrf
                <input type="hidden" name="sync_all" value="1">
                <input type="hidden" name="status_pensiun" value="{{ $statusPensiun ?? 'semua' }}">
                <input type="hidden" name="status_sk" value="{{ $statusSk ?? 'semua_selisih' }}">
                <button type="submit" class="btn btn-primary">
                    <i class="ph-bold ph-arrows-clockwise"></i> Perbarui Semua Pangkat ({{ $paginatedItems->total() }})
                </button>
            </form>
        @elseif($activeTab === 'beda_skpd' && ($summary['beda_skpd_count'] ?? 0) > 0)
            <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_skpd') }}" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui unit kerja/SKPD {{ $summary['beda_skpd_count'] }} pegawai ini sesuai penempatan SIMGAJI?');">
                @csrf
                <input type="hidden" name="sync_all" value="1">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706); border-color: #d97706;">
                    <i class="ph-bold ph-arrows-clockwise"></i> Sinkronkan Semua SKPD ({{ $summary['beda_skpd_count'] }})
                </button>
            </form>
        @elseif($activeTab === 'beda_jabatan' && ($summary['beda_jabatan_count'] ?? 0) > 0)
            <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_jabatan') }}" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui jabatan {{ $summary['beda_jabatan_count'] }} pegawai sesuai data penggajian TPP terbaru?');">
                @csrf
                <input type="hidden" name="sync_all" value="1">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-color: #6d28d9;">
                    <i class="ph-bold ph-arrows-clockwise"></i> Perbarui Semua Jabatan ({{ $summary['beda_jabatan_count'] }})
                </button>
            </form>
        @endif
    </div>

    <!-- Table Render according to active tab -->
    <div class="table-responsive">
        @if($activeTab === 'aktif_baru')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pegawai (SIMGAJI)</th>
                        <th style="width: 120px; text-align: center;">Pangkat/Gol</th>
                        <th style="width: 110px; text-align: center;">Kd SKPD</th>
                        <th>Instansi / Inputer</th>
                        <th style="width: 120px; text-align: center;">TMT Stop</th>
                        <th style="width: 140px; text-align: center;" class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong style="color: var(--luno-primary);">{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(76, 53, 222, 0.1); color: var(--luno-primary); padding: 4px 8px; border-radius: 6px; font-weight: 700;">
                                    {{ $row['golru_converted'] }}
                                </span>
                            </td>
                            <td style="text-align: center;"><code>{{ $row['kdskpd'] ?: '-' }}</code></td>
                            <td>{{ $row['inputer'] ?: '-' }}</td>
                            <td style="text-align: center;">{{ $row['tmtstop'] ?: '-' }}</td>
                            <td style="text-align: center;" class="action-col">
                                <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_pegawai') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_nips[]" value="{{ $row['nip'] }}">
                                    <button type="submit" class="btn btn-export" style="padding: 4px 10px; font-size: 11.5px; color: var(--luno-primary); border-color: var(--luno-primary-border);" title="Tambahkan pegawai ini ke Master Pegawai">
                                        <i class="ph-bold ph-plus"></i> Tambah
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                                Tidak ada pegawai aktif baru yang belum terdaftar di Master Pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'beda_pangkat')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 170px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>SKPD / Unit Kerja</th>
                        <th style="width: 110px; text-align: center;">Golru Aplikasi</th>
                        <th style="width: 110px; text-align: center;">Golru SIMGAJI</th>
                        <th style="width: 220px;">Status SK di SIMGAJI</th>
                        <th style="width: 110px; text-align: center;">Status Pegawai</th>
                        <th style="width: 90px; text-align: center;" class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong>{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>{{ $row['skpd'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                                    {{ $row['golru_app'] }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                                    <i class="ph-bold ph-arrow-up"></i> {{ $row['pangkat_simgaji'] }} ({{ $row['pangkat_raw_dbf'] }})
                                </span>
                                @if(!empty($row['pangkat_mst']) && $row['pangkat_mst'] !== $row['pangkat_simgaji'])
                                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;" title="Pangkat tercatat pada master aktif berjalan SIMGAJI">
                                        Master: {{ $row['pangkat_mst'] }} ({{ $row['pangkat_raw_mst'] ?? '' }})
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(($row['status_sk'] ?? '') === 'sudah_terjadwal')
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-check-circle"></i> Sudah Sesuai di SIMGAJI
                                    </span>
                                    @if(!empty($row['sk_info']))
                                        <div style="margin-top: 4px; font-size: 11px; color: var(--text-muted); line-height: 1.4;">
                                            <span title="Nomor SK"><i class="ph-bold ph-file-text"></i> {{ $row['sk_info']['nomorskep'] ?: '-' }}</span><br>
                                            <span>TMT Gaji: <strong>{{ !empty($row['sk_info']['tmtgaji']) ? date('d/m/Y', strtotime($row['sk_info']['tmtgaji'])) : '-' }}</strong></span>
                                            @if(!empty($row['sk_info']['gapok']))
                                                • <span>Rp {{ number_format((float)$row['sk_info']['gapok'], 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @elseif(($row['status_sk'] ?? '') === 'simgaji_lebih_tinggi')
                                    <span class="badge" style="background: rgba(37, 99, 235, 0.12); color: #2563eb; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-arrow-circle-up"></i> Pangkat di SIMGAJI Lebih Tinggi
                                    </span>
                                    <div style="margin-top: 3px; font-size: 11px; color: var(--text-muted); line-height: 1.35;">
                                        SIMGAJI: <strong style="color: #2563eb;">{{ $row['pangkat_simgaji'] }}</strong> &gt; Aplikasi: <strong>{{ $row['golru_app'] }}</strong>
                                        @if(!empty($row['sk_info']['nomorskep']))
                                            <br><span title="Nomor SK"><i class="ph-bold ph-file-text"></i> {{ $row['sk_info']['nomorskep'] }}</span>
                                        @endif
                                        @if(!empty($row['sk_info']['tmtgaji']))
                                            <br><span>TMT Gaji: <strong>{{ date('d/m/Y', strtotime($row['sk_info']['tmtgaji'])) }}</strong></span>
                                            @if(!empty($row['sk_info']['gapok']))
                                                • <span>Rp {{ number_format((float)$row['sk_info']['gapok'], 0, ',', '.') }}</span>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-warning-circle"></i> Belum Diinput di SIMGAJI
                                    </span>
                                    <div style="margin-top: 3px; font-size: 11px; color: var(--text-muted); line-height: 1.35;">
                                        Aplikasi: <strong style="color: #dc2626;">{{ $row['golru_app'] }}</strong> &gt; SIMGAJI: <strong>{{ $row['pangkat_simgaji'] }}</strong>
                                        <br>Perlu pemutakhiran SK di SIMGAJI
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if(!empty($row['is_pensiun']))
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;" title="{{ !empty($row['tmtstop']) ? 'TMT Berhenti: '.$row['tmtstop'] : 'Status Pensiun' }}">
                                        <i class="ph-bold ph-prohibit"></i> {{ $row['status_kepegawaian'] ?? 'Pensiun' }}
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                        <i class="ph-bold ph-check-circle"></i> Aktif
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;" class="action-col">
                                <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_pangkat') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_nips[]" value="{{ $row['nip'] }}">
                                    <button type="submit" class="btn btn-export" style="padding: 4px 8px; font-size: 11.5px; color: #059669; border-color: rgba(16,185,129,0.3);" title="Perbarui pangkat pegawai ini">
                                        <i class="ph-bold ph-arrows-clockwise"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                                Seluruh pangkat di Master Pegawai telah sesuai dengan data mutakhir SIMGAJI.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'beda_skpd')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>SKPD di Master Aplikasi</th>
                        <th>SKPD di SIMGAJI (Penggajian)</th>
                        <th style="width: 140px;">SKPD di TPP</th>
                        <th style="width: 120px; text-align: center;" class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong>{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>
                                <span class="badge" style="background: rgba(239, 68, 68, 0.08); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    {{ $row['skpd_app'] }}
                                </span>
                                @if(!empty($row['satker_app']) && $row['satker_app'] !== '-')
                                    <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">Satker: {{ $row['satker_app'] }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    {{ $row['skpd_simgaji'] }}
                                </span>
                                <small style="display: block; color: var(--text-muted); font-size: 11px; margin-top: 2px;">
                                    Kd SKPD: <strong>{{ $row['kdskpd_simgaji'] }}</strong> | Satker: <code>{{ $row['kdsatker_simgaji'] ?? '-' }}</code> ({{ $row['inputer_simgaji'] }})
                                </small>
                            </td>
                            <td><small>{{ $row['skpd_tpp'] }}</small></td>
                            <td style="text-align: center;" class="action-col">
                                <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_skpd') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_nips[]" value="{{ $row['nip'] }}">
                                    <button type="submit" class="btn btn-export" style="padding: 4px 10px; font-size: 11.5px; color: #d97706; border-color: rgba(245,158,11,0.3);" title="Sesuaikan SKPD pegawai ke {{ $row['skpd_simgaji'] }}">
                                        <i class="ph-bold ph-arrows-clockwise"></i> Sesuaikan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                                Seluruh penempatan SKPD di Master Pegawai telah sesuai dengan database SIMGAJI.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'beda_jabatan')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>SKPD / Unit Kerja</th>
                        <th>Jabatan di Master Aplikasi</th>
                        <th>Jabatan pada Penggajian (TPP)</th>
                        <th style="width: 180px;">Status SIMGAJI</th>
                        <th style="width: 120px; text-align: center;" class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong>{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>{{ $row['skpd'] }}</td>
                            <td>
                                <span class="badge" style="background: rgba(239, 68, 68, 0.08); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    {{ $row['jabatan_app'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #7c3aed; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    {{ $row['jabatan_tpp'] }}
                                </span>
                            </td>
                            <td><small style="color: var(--text-muted);">{{ $row['status_simgaji'] }}</small></td>
                            <td style="text-align: center;" class="action-col">
                                <form method="POST" action="{{ route('laporan.rekonsiliasi_simgaji.sync_jabatan') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="selected_nips[]" value="{{ $row['nip'] }}">
                                    <button type="submit" class="btn btn-export" style="padding: 4px 10px; font-size: 11.5px; color: #7c3aed; border-color: rgba(139,92,246,0.3);" title="Perbarui jabatan pegawai ini menjadi {{ $row['jabatan_tpp'] }}">
                                        <i class="ph-bold ph-arrows-clockwise"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-check-circle" style="font-size: 32px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                                Seluruh jabatan di Master Pegawai telah sesuai dengan data penggajian TPP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'beda_lahir')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>SKPD / Unit Kerja</th>
                        <th style="width: 180px; text-align: center;">Tgl Lahir di Aplikasi</th>
                        <th style="width: 180px; text-align: center;">Tgl Lahir di SIMGAJI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong>{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>{{ $row['skpd'] }}</td>
                            <td style="text-align: center;"><code>{{ $row['tgl_app'] }}</code></td>
                            <td style="text-align: center;"><code>{{ $row['tgl_simgaji'] }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                Tidak ada selisih pencatatan tanggal lahir.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'pensiunan')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pensiunan / Mantan Pegawai</th>
                        <th style="width: 120px; text-align: center;">Golru Terakhir</th>
                        <th style="width: 140px; text-align: center;">TMT Berhenti / Pensiun</th>
                        <th>Instansi Terakhir (Inputer)</th>
                        <th style="width: 140px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><code>{{ $row['nip'] }}</code></td>
                            <td>{{ $row['nama'] }}</td>
                            <td style="text-align: center;">{{ $row['golru_converted'] }}</td>
                            <td style="text-align: center;"><span style="color: #dc2626; font-weight: 600;">{{ $row['tmtstop'] }}</span></td>
                            <td>{{ $row['inputer'] ?: '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px;">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                Tidak ada data pensiunan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($activeTab === 'paruh_waktu')
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 190px;">NIP</th>
                        <th>Nama Pegawai</th>
                        <th>SKPD / Unit Kerja</th>
                        <th style="width: 120px; text-align: center;">Golru</th>
                        <th style="width: 160px; text-align: center;">Status Kepegawaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedItems as $index => $row)
                        <tr>
                            <td style="text-align: center;">{{ $paginatedItems->firstItem() + $index }}</td>
                            <td><strong>{{ $row['nip'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $row['nama'] }}</td>
                            <td>{{ $row['skpd'] }}</td>
                            <td style="text-align: center;">{{ $row['golru'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(6, 182, 212, 0.1); color: #0891b2; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                Tidak ada data PPPK Paruh Waktu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <!-- Official Print Signature Footer (Only visible when printed) -->
    <div class="print-footer">
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 230px; text-align: center; font-size: 9.5pt;">
                <div>Banjarbaru, {{ date('d F Y') }}</div>
                <div style="margin-top: 4px; font-weight: 600;">Petugas Rekonsiliasi Data,</div>
                <div style="margin-top: 55px; font-weight: 700; text-decoration: underline;">Admin Keuangan</div>
                <div>NIP. ........................................</div>
            </div>
        </div>
    </div>

    <!-- Pagination links -->
    <div class="pagination-container" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-top: 14px; border-top: 1px solid var(--border-color);">
        <div style="font-size: 12.5px; color: var(--text-muted);">
            Menampilkan <strong>{{ $paginatedItems->firstItem() ?? 0 }}</strong> - <strong>{{ $paginatedItems->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedItems->total(), 0, ',', '.') }}</strong> data
        </div>
        <div style="display: flex; gap: 6px; align-items: center;">
            @if(!$paginatedItems->onFirstPage())
                <a href="{{ $paginatedItems->appends(request()->query())->url(1) }}" class="btn btn-export" style="padding: 6px 10px; font-size: 12px;" title="Halaman Pertama">
                    <i class="ph-bold ph-caret-double-left"></i>
                </a>
                <a href="{{ $paginatedItems->appends(request()->query())->previousPageUrl() }}" class="btn btn-export" style="padding: 6px 12px; font-size: 12px;">
                    <i class="ph-bold ph-caret-left"></i> Prev
                </a>
            @else
                <span class="btn btn-export" style="padding: 6px 12px; font-size: 12px; opacity: 0.4; cursor: not-allowed;">
                    <i class="ph-bold ph-caret-left"></i> Prev
                </span>
            @endif

            <span style="font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 8px; background: var(--luno-primary-light); color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
                Hal {{ $paginatedItems->currentPage() }} dari {{ $paginatedItems->lastPage() }}
            </span>

            @if($paginatedItems->hasMorePages())
                <a href="{{ $paginatedItems->appends(request()->query())->nextPageUrl() }}" class="btn btn-export" style="padding: 6px 12px; font-size: 12px;">
                    Next <i class="ph-bold ph-caret-right"></i>
                </a>
                <a href="{{ $paginatedItems->appends(request()->query())->url($paginatedItems->lastPage()) }}" class="btn btn-export" style="padding: 6px 10px; font-size: 12px;" title="Halaman Terakhir">
                    <i class="ph-bold ph-caret-double-right"></i>
                </a>
            @else
                <span class="btn btn-export" style="padding: 6px 12px; font-size: 12px; opacity: 0.4; cursor: not-allowed;">
                    Next <i class="ph-bold ph-caret-right"></i>
                </span>
            @endif
        </div>
    </div>
</div>

<!-- Modal Upload DBF SIMGAJI -->
<div class="modal-overlay" id="uploadDbfModal">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="ph-bold ph-database"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px;">Unggah File Database SIMGAJI</h3>
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Pilih berkas ekspor SIMGAJI Taspen (.DBF) — Master Pegawai, Histori SK, atau Riwayat Keluarga</p>
                </div>
            </div>
            <button class="btn-close" onclick="closeModal('uploadDbfModal')">&times;</button>
        </div>
        <form id="formUploadRekonDbf" action="{{ route('laporan.rekonsiliasi_simgaji.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="padding: 20px 24px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 8px; display: block;">Jenis Database DBF</label>
                    <select name="jenis_dbf" id="modalRekonSelectJenis" class="form-control" style="padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; width: 100%;">
                        <option value="auto">🤖 Otomatis Deteksi (Rekomendasi)</option>
                        <option value="mst_pgw">👤 Master Pegawai (MST_PGW_*.DBF)</option>
                        <option value="his_gpok">📄 Histori Gaji Pokok & SK (HIS_GPOK_*.DBF)</option>
                        <option value="kel">👨‍👩‍👧‍👦 Riwayat Anggota Keluarga & Tanggungan (KEL_*.DBF)</option>
                    </select>
                    <small style="display: block; color: var(--text-muted); margin-top: 6px; font-size: 11.5px;">
                        Pilih jenis berkas atau biarkan otomatis dideteksi berdasarkan nama berkas dan kolom DBF.
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 8px; display: block;">File DBF SIMGAJI (.dbf)</label>
                    <input type="file" name="file_dbf" id="modalRekonFileInput" accept=".dbf,.DBF" required class="form-control" style="padding: 10px; border: 2px dashed var(--border-color); border-radius: 8px;">
                    
                    <!-- Live Preview Detection -->
                    <div id="modalRekonPreviewBox" style="display: none; margin-top: 8px; padding: 10px 12px; border-radius: 8px; font-size: 12px; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2);">
                        <div style="font-weight: 700; color: var(--text-main);" id="modalRekonPreviewName"></div>
                        <div style="color: var(--text-muted); font-size: 11.5px; margin-top: 2px;" id="modalRekonPreviewInfo"></div>
                        <div id="modalRekonPreviewBadge" style="margin-top: 6px;"></div>
                    </div>

                    <small style="display: block; color: var(--text-muted); margin-top: 6px; font-size: 11.5px;">
                        Mendukung <code>MST_PGW_*.DBF</code> (Master Pegawai), <code>HIS_GPOK_*.DBF</code> (Histori SK), dan <code>KEL_*.DBF</code> (Riwayat Keluarga).
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 16px; background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin: 0; font-size: 12px; color: var(--text-main);">
                        <input type="checkbox" name="auto_sync" value="1" checked style="margin-top: 2px;">
                        <span><strong>Otomatis sinkronkan ke database</strong> setelah upload berhasil (data langsung tersedia di analisis).</span>
                    </label>
                </div>

                <div style="background: rgba(76, 53, 222, 0.04); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 14px; font-size: 12px; color: var(--text-muted);">
                    <strong style="color: var(--text-main); display: block; margin-bottom: 4px;">
                        <i class="ph-bold ph-info"></i> Informasi & Tata Cara:
                    </strong>
                    <ul style="margin: 0; padding-left: 18px; line-height: 1.6;">
                        <li>Berkas yang diunggah akan langsung menjadi database acuan aktif untuk jenisnya.</li>
                        <li>Berkas <strong>HIS_GPOK_*.DBF</strong> memverifikasi SK kenaikan pangkat/gaji yang sudah terjadwal di SIMGAJI.</li>
                        <li>Berkas <strong>KEL_*.DBF</strong> memuat data tanggungan keluarga untuk Audit Tunjangan Keluarga dan Trace Gaji.</li>
                        <li>Kelola riwayat seluruh berkas DBF di menu <a href="{{ route('master.simgaji_dbf.index') }}" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Master Database SIMGAJI</a>.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; padding: 14px 24px; border-top: 1px solid var(--border-color); background: var(--bg-surface);">
                <button type="button" class="btn btn-export" onclick="closeModal('uploadDbfModal')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-color: #1d4ed8;">
                    <i class="ph-bold ph-upload-simple"></i> Unggah & Proses
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('active');
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('active');
    }
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('modalRekonFileInput');
        const previewBox = document.getElementById('modalRekonPreviewBox');
        const previewName = document.getElementById('modalRekonPreviewName');
        const previewInfo = document.getElementById('modalRekonPreviewInfo');
        const previewBadge = document.getElementById('modalRekonPreviewBadge');
        const select = document.getElementById('modalRekonSelectJenis');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) {
                    if (previewBox) previewBox.style.display = 'none';
                    return;
                }

                const nameUpper = file.name.toUpperCase();
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                previewBox.style.display = 'block';
                previewName.textContent = '📄 ' + file.name;
                previewInfo.textContent = 'Ukuran berkas: ' + sizeMb + ' MB (' + file.size.toLocaleString('id-ID') + ' bytes)';

                if (nameUpper.includes('KEL')) {
                    previewBadge.innerHTML = '<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">👨‍👩‍👧‍👦 Terdeteksi: Riwayat Anggota Keluarga & Tanggungan (KEL_*.DBF)</span>';
                    if (select) select.value = 'kel';
                } else if (nameUpper.includes('HIS') || nameUpper.includes('GPOK')) {
                    previewBadge.innerHTML = '<span class="badge" style="background: rgba(147, 51, 234, 0.15); color: #9333ea; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">📜 Terdeteksi: Histori Gaji Pokok & SK (HIS_GPOK_*.DBF)</span>';
                    if (select) select.value = 'his_gpok';
                } else if (nameUpper.includes('MST') || nameUpper.includes('PGW')) {
                    previewBadge.innerHTML = '<span class="badge" style="background: rgba(37, 99, 235, 0.15); color: #2563eb; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">👤 Terdeteksi: Master Pegawai (MST_PGW_*.DBF)</span>';
                    if (select) select.value = 'mst_pgw';
                } else {
                    previewBadge.innerHTML = '<span class="badge" style="background: rgba(100, 116, 139, 0.15); color: #475569; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">🤖 Berkas DBF Terbaca</span>';
                }
            });
        }

        // Attach DBF upload progress modal handler
        if (typeof window.attachDbfUploadHandler === 'function') {
            window.attachDbfUploadHandler('#formUploadRekonDbf', '#modalRekonFileInput');
        }
    });
</script>

@include('master.dbf_upload_scripts')
@endsection
