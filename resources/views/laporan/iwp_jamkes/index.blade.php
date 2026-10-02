@extends('layouts.app')

@section('title', 'Laporan IWP & BPJS Kesehatan (Jamkes)')
@section('page_title', 'Laporan IWP & BPJS Kesehatan (Jamkes)')

@section('content')
<style>
    /* Header & Page Styling */
    .report-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        color: #ffffff;
        padding: 24px 28px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(67, 56, 202, 0.25);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .report-hero-content h2 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .report-hero-content p {
        font-size: 13.5px;
        opacity: 0.85;
        max-width: 720px;
        line-height: 1.5;
    }

    /* KPI Summary Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }

    .kpi-card.highlight {
        background: linear-gradient(135deg, rgba(76, 53, 222, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);
        border: 1.5px solid rgba(76, 53, 222, 0.35);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .kpi-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .kpi-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
    }

    .kpi-subtitle {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Tabs & Filters */
    .controls-wrapper {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px 20px;
        margin-bottom: 24px;
        box-shadow: var(--card-shadow);
    }

    .tab-pills {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        background: var(--bg-surface-subtle);
        border: 1px solid var(--border-color);
        transition: all 0.15s ease;
    }

    .tab-pill:hover {
        color: var(--text-main);
        background: var(--bg-surface-hover);
    }

    .tab-pill.active {
        background: var(--luno-primary);
        color: #ffffff;
        border-color: var(--luno-primary);
        box-shadow: 0 4px 12px rgba(76, 53, 222, 0.25);
    }

    .filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .filter-inputs {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
    }

    .filter-select, .search-input {
        height: 38px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s;
    }

    .filter-select:focus, .search-input:focus {
        border-color: var(--luno-primary);
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background: var(--luno-primary);
        color: white;
    }
    .btn-primary:hover { background: var(--luno-primary-hover); }

    .btn-success {
        background: #10b981;
        color: white;
    }
    .btn-success:hover { background: #059669; }

    .btn-danger {
        background: #ef4444;
        color: white;
    }
    .btn-danger:hover { background: #dc2626; }

    /* Tables */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--card-shadow);
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        white-space: nowrap;
    }

    .report-table thead th {
        background: var(--bg-surface-subtle);
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 1.5px solid var(--border-color);
        border-right: 1px solid var(--border-subtle);
    }

    .report-table tbody td {
        padding: 11px 14px;
        border-bottom: 1px solid var(--border-subtle);
        border-right: 1px solid var(--border-subtle);
        color: var(--text-main);
    }

    .report-table tbody tr:hover {
        background-color: var(--bg-surface-hover);
    }

    .report-table tfoot th {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        font-weight: 800;
        padding: 13px 14px;
        border-top: 2px solid var(--border-color);
        border-right: 1px solid var(--border-subtle);
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .money { text-align: right; font-variant-numeric: tabular-nums; font-weight: 500; }
    .money-bold { text-align: right; font-variant-numeric: tabular-nums; font-weight: 700; }

    .highlight-cell {
        background-color: rgba(76, 53, 222, 0.05);
        color: var(--luno-primary);
        font-weight: 700;
    }

    .highlight-tfoot {
        background-color: rgba(76, 53, 222, 0.12) !important;
        color: var(--luno-primary) !important;
        font-weight: 800 !important;
    }

    .badge-status {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        background: rgba(76, 53, 222, 0.1);
        color: var(--luno-primary);
    }

    .pagination-box {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-surface);
        border-top: 1px solid var(--border-color);
        border-radius: 0 0 14px 14px;
    }
</style>

<!-- Hero Section -->
<div class="report-hero">
    <div class="report-hero-content">
        <h2>
            <i class="ph ph-heartbeat" style="font-size: 26px; color: #a5b4fc;"></i>
            Laporan IWP & Jaminan Kesehatan (Jamkes BPJS)
        </h2>
        <p>
            Rekonsiliasi komprehensif Iuran Wajib Pegawai (IWP 10% Taspen) dan porsi pemotongan Jaminan Kesehatan (Jamkes / BPJS Kesehatan) dari Gaji Reguler (2%) serta Tambahan Penghasilan Pegawai / TPP (1%).
        </p>
    </div>
    <div>
        <span style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); padding: 6px 14px; border-radius: 30px; font-size: 12.5px; font-weight: 600;">
            <i class="ph ph-calendar-blank"></i> Periode: {{ $periode ?: 'Semua Periode' }}
        </span>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <!-- Grand Total Jamkes -->
    <div class="kpi-card highlight">
        <div class="kpi-header">
            <span class="kpi-title" style="color: var(--luno-primary);">Total Iuran Jamkes (BPJS Kes)</span>
            <div class="kpi-icon" style="background: rgba(76, 53, 222, 0.12); color: var(--luno-primary);">
                <i class="ph ph-shield-check"></i>
            </div>
        </div>
        <div class="kpi-value" style="color: var(--luno-primary);">Rp {{ number_format($grandTotalJamkes, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Akumulasi Gaji (2%) + TPP (1%)</div>
    </div>

    <!-- IWP Gaji Jamkes -->
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">IWP 2% Jamkes (Gaji)</span>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="ph ph-first-aid"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalIwpGajiJamkes, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Dari database SIMGAJI (PIWP2)</div>
    </div>

    <!-- IWP TPP Jamkes -->
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">IWP 1% Jamkes (TPP)</span>
            <div class="kpi-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                <i class="ph ph-money"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalIwpTppJamkes, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Iuran BPJS atas komponen TPP</div>
    </div>

    <!-- IWP Pensiun Gaji -->
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">IWP 8% Pensiun & THT</span>
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="ph ph-piggy-bank"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalIwpGajiPensiun, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Disetor ke PT Taspen (PIWP8)</div>
    </div>

    <!-- Total IWP Seluruhnya -->
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Seluruh IWP</span>
            <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="ph ph-calculator"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($grandTotalIwp, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Total IWP Gaji (10%) + IWP TPP</div>
    </div>
</div>

<!-- Controls & Filters -->
<div class="controls-wrapper">
    <div class="tab-pills">
        <a href="{{ request()->fullUrlWithQuery(['tipe_laporan' => 'rekap']) }}" class="tab-pill {{ $tipeLaporan === 'rekap' ? 'active' : '' }}">
            <i class="ph ph-chart-pie-slice"></i>
            <span>1. Rekapitulasi per SKPD</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tipe_laporan' => 'rinci']) }}" class="tab-pill {{ $tipeLaporan === 'rinci' ? 'active' : '' }}">
            <i class="ph ph-list-numbers"></i>
            <span>2. Rincian per Pegawai</span>
        </a>
    </div>

    <form method="GET" action="{{ url('/laporan/iwp-jamkes') }}" class="filter-bar">
        <input type="hidden" name="tipe_laporan" value="{{ $tipeLaporan }}">

        <div class="filter-inputs">
            <!-- Filter Periode -->
            <select name="periode_filter" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>

            <!-- Filter SKPD -->
            <select name="skpd_filter" class="filter-select" onchange="this.form.submit()" style="max-width: 320px;">
                <option value="">Semua SKPD / Unit Kerja</option>
                @foreach($filterUnitKerjas as $skpd)
                    <option value="{{ $skpd }}" {{ $skpdFilter == $skpd ? 'selected' : '' }}>{{ $skpd }}</option>
                @endforeach
            </select>

            @if($tipeLaporan === 'rinci')
                <input type="text" name="search" class="search-input" placeholder="Cari NIP / Nama..." value="{{ request('search') }}">
                <button type="submit" class="btn-action btn-primary">
                    <i class="ph ph-magnifying-glass"></i> Cari
                </button>
            @endif

            @if($skpdFilter || request('search'))
                <a href="{{ url('/laporan/iwp-jamkes?tipe_laporan=' . $tipeLaporan . '&periode_filter=' . $periode) }}" class="btn-action" style="background: var(--bg-surface-hover); color: var(--text-muted);" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise"></i> Reset
                </a>
            @endif
        </div>

        <!-- Export Buttons -->
        <div style="display: flex; gap: 8px;">
            <a href="{{ url('/laporan/iwp-jamkes/export/excel?' . http_build_query(request()->all())) }}" class="btn-action btn-success">
                <i class="ph ph-file-xls" style="font-size: 16px;"></i>
                <span>Ekspor Excel</span>
            </a>
            <a href="{{ url('/laporan/iwp-jamkes/export/pdf?' . http_build_query(request()->all())) }}" class="btn-action btn-danger" target="_blank">
                <i class="ph ph-file-pdf" style="font-size: 16px;"></i>
                <span>Cetak PDF</span>
            </a>
        </div>
    </form>
</div>

<!-- Table Area -->
@if($tipeLaporan === 'rekap')
    <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center" style="width: 45px;">No</th>
                    <th rowspan="2" style="text-align: left;">Nama SKPD / Unit Kerja</th>
                    <th colspan="3" class="text-center">IWP Dari Gaji (SIMGAJI)</th>
                    <th rowspan="2" class="text-right">IWP 1% (TPP)</th>
                    <th rowspan="2" class="text-right highlight-cell">Grand Total Jamkes<br><small style="font-weight: 500;">(Gaji 2% + TPP 1%)</small></th>
                    <th rowspan="2" class="text-right">Total Seluruh IWP<br><small style="font-weight: 500;">(Gaji 10% + TPP)</small></th>
                    <th colspan="3" class="text-center">Jumlah Pegawai</th>
                </tr>
                <tr>
                    <th class="text-right">IWP 2% (Jamkes)</th>
                    <th class="text-right">IWP 8% (Pensiun/THT)</th>
                    <th class="text-right">Total IWP (10%)</th>
                    <th class="text-center" title="Pegawai Terima Gaji">Gaji</th>
                    <th class="text-center" title="Pegawai Terima TPP">TPP</th>
                    <th class="text-center" title="Total Pegawai Master">Master</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $row->skpd }}</td>
                    <td class="money">{{ number_format($row->iwp_gaji_jamkes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->iwp_gaji_pensiun, 0, ',', '.') }}</td>
                    <td class="money" style="color: #6366f1;">{{ number_format($row->total_iwp_gaji, 0, ',', '.') }}</td>
                    <td class="money" style="color: #06b6d4;">{{ number_format($row->iwp_tpp_jamkes, 0, ',', '.') }}</td>
                    <td class="money-bold highlight-cell">Rp {{ number_format($row->total_jamkes, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: 700;">Rp {{ number_format($row->total_seluruh_iwp, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row->count_gaji }}</td>
                    <td class="text-center">{{ $row->count_tpp }}</td>
                    <td class="text-center" style="font-weight: 600;">{{ $row->total_pegawai }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 40px; color: var(--text-muted);">
                        Data rekapitulasi belum tersedia untuk filter yang dipilih.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr>
                    <th colspan="2" class="text-center">TOTAL KESELURUHAN</th>
                    <th class="money-bold">{{ number_format($rekaps->sum('iwp_gaji_jamkes'), 0, ',', '.') }}</th>
                    <th class="money-bold">{{ number_format($rekaps->sum('iwp_gaji_pensiun'), 0, ',', '.') }}</th>
                    <th class="money-bold" style="color: #6366f1;">{{ number_format($rekaps->sum('total_iwp_gaji'), 0, ',', '.') }}</th>
                    <th class="money-bold" style="color: #06b6d4;">{{ number_format($rekaps->sum('iwp_tpp_jamkes'), 0, ',', '.') }}</th>
                    <th class="money-bold highlight-tfoot">Rp {{ number_format($rekaps->sum('total_jamkes'), 0, ',', '.') }}</th>
                    <th class="money-bold">Rp {{ number_format($rekaps->sum('total_seluruh_iwp'), 0, ',', '.') }}</th>
                    <th class="text-center">{{ number_format($rekaps->sum('count_gaji')) }}</th>
                    <th class="text-center">{{ number_format($rekaps->sum('count_tpp')) }}</th>
                    <th class="text-center">{{ number_format($rekaps->sum('total_pegawai')) }}</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
@else
    <!-- Tab Rinci -->
    <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">No</th>
                    <th>Periode</th>
                    <th style="text-align: left;">Pegawai</th>
                    <th style="text-align: left;">SKPD & Jabatan</th>
                    <th class="text-right">Gaji Pokok</th>
                    <th class="text-right">IWP Gaji 2%<br><small style="font-weight: 500;">(Jamkes)</small></th>
                    <th class="text-right">IWP Gaji 8%<br><small style="font-weight: 500;">(Pensiun)</small></th>
                    <th class="text-right">Total IWP Gaji</th>
                    <th class="text-right">IWP TPP 1%<br><small style="font-weight: 500;">(Jamkes)</small></th>
                    <th class="text-right highlight-cell">Grand Total Jamkes<br><small style="font-weight: 500;">(Gaji + TPP)</small></th>
                </tr>
            </thead>
            <tbody>
                @forelse($realisasis as $index => $pegawai)
                @php
                    $gajiPokok = (float) $pegawai->realisasiGajis->sum('gaji_pokok');
                    $iwpGajiJamkes = (float) $pegawai->realisasiGajis->sum(fn ($g) => $g->raw_data['piwp2'] ?? 0);
                    $iwpGajiPensiun = (float) $pegawai->realisasiGajis->sum(fn ($g) => $g->raw_data['piwp8'] ?? 0);
                    $totalIwpGaji = (float) $pegawai->realisasiGajis->sum('iwp');
                    $iwpTppJamkes = (float) $pegawai->realisasiTpps->sum('iuran_iwp');
                    $totalJamkes = $iwpGajiJamkes + $iwpTppJamkes;
                @endphp
                <tr>
                    <td class="text-center">{{ $realisasis->firstItem() + $index }}</td>
                    <td class="text-center" style="font-weight: 600;">{{ $periode ?: 'Semua' }}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main);">{{ $pegawai->nama ?? 'Tidak Diketahui' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            NIP: {{ $pegawai->nip ?? '-' }}
                            <span class="badge-status">{{ $pegawai->status_pegawai ?? '-' }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $pegawai->jabatan?->nama ?? '-' }}</div>
                    </td>
                    <td class="money">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                    <td class="money" style="color: #10b981;">Rp {{ number_format($iwpGajiJamkes, 0, ',', '.') }}</td>
                    <td class="money" style="color: #f59e0b;">Rp {{ number_format($iwpGajiPensiun, 0, ',', '.') }}</td>
                    <td class="money" style="color: #6366f1;">Rp {{ number_format($totalIwpGaji, 0, ',', '.') }}</td>
                    <td class="money" style="color: #06b6d4;">Rp {{ number_format($iwpTppJamkes, 0, ',', '.') }}</td>
                    <td class="money-bold highlight-cell">Rp {{ number_format($totalJamkes, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 40px; color: var(--text-muted);">
                        Data rincian pegawai tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($realisasis->hasPages())
        <div class="pagination-box">
            <span style="font-size: 12.5px; color: var(--text-muted);">
                Menampilkan {{ $realisasis->firstItem() }} - {{ $realisasis->lastItem() }} dari {{ $realisasis->total() }} pegawai
            </span>
            <div>
                {{ $realisasis->links() }}
            </div>
        </div>
        @endif
    </div>
@endif

@endsection
