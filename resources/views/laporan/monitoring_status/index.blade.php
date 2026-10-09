@extends('layouts.app')

@section('title', 'Monitoring Status Pegawai & Pensiun')
@section('page_title', 'Laporan Monitoring Status Pegawai & Pensiun')

@section('content')
<style>
    .mon-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .mon-title h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mon-title p {
        margin: 0;
        font-size: 13.5px;
        color: var(--text-muted);
    }

    .mon-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }

    .btn-export-excel {
        background: #ecfdf5;
        color: #059669;
        border-color: rgba(16, 185, 129, 0.3);
    }
    .btn-export-excel:hover {
        background: #d1fae5;
    }

    .btn-export-pdf {
        background: #fef2f2;
        color: #dc2626;
        border-color: rgba(239, 68, 68, 0.3);
    }
    .btn-export-pdf:hover {
        background: #fee2e2;
    }

    .btn-refresh {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        border-color: var(--border-color);
    }
    .btn-refresh:hover {
        background: var(--bg-surface-hover);
    }

    /* KPI Summary Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .kpi-card.blue::before { background: #3b82f6; }
    .kpi-card.emerald::before { background: #10b981; }
    .kpi-card.red::before { background: #ef4444; }
    .kpi-card.slate::before { background: #64748b; }
    .kpi-card.amber::before { background: #f59e0b; }
    .kpi-card.indigo::before { background: #6366f1; }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .kpi-val {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
    }

    .kpi-sub {
        font-size: 11.5px;
        color: var(--text-muted);
    }

    /* Tabs Styling */
    .nav-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1.5px solid var(--border-color);
        margin-bottom: 20px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .nav-tab-item {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        white-space: nowrap;
    }

    .nav-tab-item:hover {
        color: var(--text-main);
    }

    .nav-tab-item.active {
        color: var(--luno-primary);
        border-bottom-color: var(--luno-primary);
        background: var(--bg-surface-subtle);
    }

    /* Filter Card */
    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
        margin: 0;
    }

    .filter-select, .filter-input {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: var(--luno-primary);
    }

    /* Tables */
    .table-container {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .table-container th {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 1.5px solid var(--border-color);
        text-align: left;
        white-space: nowrap;
    }

    .table-container td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
        vertical-align: middle;
    }

    .table-container tbody tr:hover {
        background-color: var(--bg-surface-hover);
    }

    .table-container tfoot td {
        background: var(--bg-surface-subtle);
        font-weight: 800;
        border-top: 2px solid var(--border-color);
    }

    /* Status Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-danger { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    .badge-warning { background: #ffedd5; color: #9a3412; border: 1px solid #fdba74; }
    .badge-dark { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .badge-info { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .badge-secondary { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
    .badge-purple { background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; }
    .badge-amber { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
    .badge-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

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
        background: var(--luno-primary, #1e40af) !important;
        border-color: var(--luno-primary, #1e40af) !important;
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

<!-- Header & Actions -->
<div class="mon-header">
    <div class="mon-title">
        <h2>
            <i class="ph ph-chart-donut" style="color: var(--luno-primary);"></i>
            Monitoring Status Pegawai & Pensiun
        </h2>
        <p>Pengawasan komprehensif status kepegawaian (Pensiun, Meninggal, Pindah, Keluar, CLTN) berbasis Database Master SIMGAJI & Realisasi.</p>
    </div>

    <div class="mon-actions">
        <a href="{{ route('laporan.monitoring_status.refresh') }}" class="btn-action btn-refresh" title="Baca ulang file DBF terbaru">
            <i class="ph ph-arrow-counter-clockwise"></i> Perbarui Data
        </a>
        <a href="{{ route('laporan.monitoring_status.export_excel', ['tab' => $tab, 'skpd' => $skpdFilter, 'status' => $statusFilter, 'search' => $search, 'tahun' => $tahunProyeksi]) }}" class="btn-action btn-export-excel" title="Unduh data tab aktif ke Excel">
            <i class="ph ph-file-xls"></i> Ekspor Excel
        </a>
        <a href="{{ route('laporan.monitoring_status.export_pdf', ['tab' => $tab, 'skpd' => $skpdFilter, 'status' => $statusFilter, 'search' => $search, 'tahun' => $tahunProyeksi]) }}" class="btn-action btn-export-pdf" title="Cetak dokumen resmi PDF">
            <i class="ph ph-file-pdf"></i> Ekspor PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background: var(--success-light); color: var(--success-text); padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class="ph ph-check-circle" style="font-size: 20px;"></i>
        {{ session('success') }}
    </div>
@endif

@if(!empty($error))
    <div style="background: var(--danger-light); color: var(--danger-text); padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(239, 68, 68, 0.3);">
        <strong><i class="ph ph-warning-circle"></i> Perhatian:</strong> {{ $error }}
    </div>
@endif

@if(!empty($summary))
<!-- Dashboard KPI Cards Grid -->
<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-label"><i class="ph ph-users"></i> Total Arsip SIMGAJI</div>
        <div class="kpi-val">{{ number_format($summary['total_records'] ?? 0, 0, ',', '.') }}</div>
        <div class="kpi-sub">Berkas: {{ $summary['active_file_name'] ?? '-' }}</div>
    </div>

    <div class="kpi-card emerald">
        <div class="kpi-label"><i class="ph ph-user-check"></i> ASN Aktif (PNS + PPPK)</div>
        <div class="kpi-val">{{ number_format(($summary['pns_aktif'] ?? 0) + ($summary['pppk_aktif'] ?? 0), 0, ',', '.') }}</div>
        <div class="kpi-sub">PNS: {{ number_format($summary['pns_aktif'] ?? 0, 0, ',', '.') }} • PPPK: {{ number_format($summary['pppk_aktif'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <div class="kpi-card red">
        <div class="kpi-label"><i class="ph ph-calendar-x"></i> Pensiun (BUP & APS)</div>
        <div class="kpi-val">{{ number_format(($summary['pensiun_bup'] ?? 0) + ($summary['pensiun_sendiri'] ?? 0), 0, ',', '.') }}</div>
        <div class="kpi-sub">BUP: {{ number_format($summary['pensiun_bup'] ?? 0, 0, ',', '.') }} • APS/Stop: {{ number_format($summary['pensiun_sendiri'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <div class="kpi-card slate">
        <div class="kpi-label"><i class="ph ph-heart-break"></i> Meninggal Dunia</div>
        <div class="kpi-val">{{ number_format($summary['meninggal'] ?? 0, 0, ',', '.') }}</div>
        <div class="kpi-sub">Kode Status SIMGAJI: 27</div>
    </div>

    <div class="kpi-card amber">
        <div class="kpi-label"><i class="ph ph-arrows-left-right"></i> Pindah / Keluar / CLTN</div>
        <div class="kpi-val">{{ number_format(($summary['pindah'] ?? 0) + ($summary['keluar'] ?? 0) + ($summary['cuti'] ?? 0), 0, ',', '.') }}</div>
        <div class="kpi-sub">Pindah: {{ $summary['pindah'] ?? 0 }} • Keluar: {{ $summary['keluar'] ?? 0 }} • CLTN: {{ $summary['cuti'] ?? 0 }}</div>
    </div>

    <div class="kpi-card indigo">
        <div class="kpi-label"><i class="ph ph-hourglass-high"></i> Proyeksi Pensiun (1 Thn)</div>
        <div class="kpi-val">{{ number_format($summary['proyeksi_12_bulan'] ?? 0, 0, ',', '.') }}</div>
        <div class="kpi-sub">Anomali Pasca Stop: <strong style="color: #dc2626;">{{ $summary['total_anomali'] ?? 0 }}</strong></div>
    </div>
</div>
@endif

<!-- Navigation Tabs -->
<div class="nav-tabs">
    <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'rekap', 'skpd' => $skpdFilter, 'search' => $search]) }}" 
       class="nav-tab-item {{ $tab === 'rekap' ? 'active' : '' }}">
        <i class="ph ph-table"></i> 1. Rekapitulasi per SKPD
    </a>
    <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'nominatif', 'skpd' => $skpdFilter, 'status' => $statusFilter, 'search' => $search, 'per_page' => $perPage]) }}" 
       class="nav-tab-item {{ $tab === 'nominatif' ? 'active' : '' }}">
        <i class="ph ph-user-list"></i> 2. Nominatif Pegawai Non-Aktif
    </a>
    <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'proyeksi', 'skpd' => $skpdFilter, 'tahun' => $tahunProyeksi, 'search' => $search, 'per_page' => $perPage]) }}" 
       class="nav-tab-item {{ $tab === 'proyeksi' ? 'active' : '' }}">
        <i class="ph ph-trend-up"></i> 3. Proyeksi Pensiun Mendatang
    </a>
    <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'anomali', 'skpd' => $skpdFilter, 'search' => $search, 'per_page' => $perPage]) }}" 
       class="nav-tab-item {{ $tab === 'anomali' ? 'active' : '' }}">
        <i class="ph ph-shield-warning"></i> 4. Monitoring Anomali Penggajian Pasca Stop
        @if(($summary['total_anomali'] ?? 0) > 0)
            <span style="background: #ef4444; color: white; padding: 1px 6px; border-radius: 10px; font-size: 11px;">{{ $summary['total_anomali'] }}</span>
        @endif
    </a>
</div>

<!-- TAB 1: REKAPITULASI PER SKPD -->
@if($tab === 'rekap')
    <div class="filter-card">
        <form action="{{ route('laporan.monitoring_status.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="rekap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama SKPD..." class="filter-input" style="width: 250px;">
            <button type="submit" style="background: var(--luno-primary); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                <i class="ph ph-funnel"></i> Cari
            </button>
            @if(request('search'))
                <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'rekap']) }}" style="background: var(--bg-surface-subtle); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none;">Reset</a>
            @endif
        </form>
        <div style="font-size: 12.5px; color: var(--text-muted);">
            Menampilkan <strong>{{ $skpdRekap->count() }}</strong> SKPD
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Nama SKPD / Unit Kerja</th>
                    <th style="text-align: right;">PNS Aktif</th>
                    <th style="text-align: right;">PPPK Aktif</th>
                    <th style="text-align: right; color: #dc2626;">Pensiun BUP</th>
                    <th style="text-align: right; color: #475569;">Meninggal</th>
                    <th style="text-align: right; color: #2563eb;">Pindah</th>
                    <th style="text-align: right; color: #d97706;">Keluar</th>
                    <th style="text-align: right; color: #7c3aed;">Cuti/MPP</th>
                    <th style="text-align: right; color: #0284c7;">Proyeksi 26-27</th>
                    <th style="text-align: right;">Total Arsip</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tPns = 0; $tPppk = 0; $tBup = 0; $tMeninggal = 0; $tPindah = 0; $tKeluar = 0; $tCuti = 0; $tProyeksi = 0; $tTotal = 0;
                @endphp
                @forelse($skpdRekap as $idx => $r)
                @php
                    $cutiTotal = $r['cuti_cltn'] + $r['mpp'] + $r['pensiun_sendiri'];
                    $tPns += $r['pns_aktif']; $tPppk += $r['pppk_aktif']; $tBup += $r['pensiun_bup'];
                    $tMeninggal += $r['meninggal']; $tPindah += $r['pindah']; $tKeluar += $r['keluar'];
                    $tCuti += $cutiTotal; $tProyeksi += $r['proyeksi_pensiun']; $tTotal += $r['total'];
                @endphp
                <tr>
                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $idx + 1 }}</td>
                    <td style="font-weight: 600;">{{ $r['skpd'] }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($r['pns_aktif'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($r['pppk_aktif'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 700; color: #dc2626;">{{ number_format($r['pensiun_bup'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 700; color: #475569;">{{ number_format($r['meninggal'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 600; color: #2563eb;">{{ number_format($r['pindah'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 600; color: #d97706;">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 600; color: #7c3aed;">{{ number_format($cutiTotal, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 700; color: #0284c7;">{{ number_format($r['proyeksi_pensiun'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 800;">{{ number_format($r['total'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 36px; color: var(--text-muted);">Tidak ada data rekapitulasi.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: center;">TOTAL KESELURUHAN</td>
                    <td style="text-align: right;">{{ number_format($tPns, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tPppk, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #dc2626;">{{ number_format($tBup, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #475569;">{{ number_format($tMeninggal, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #2563eb;">{{ number_format($tPindah, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #d97706;">{{ number_format($tKeluar, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #7c3aed;">{{ number_format($tCuti, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #0284c7;">{{ number_format($tProyeksi, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

<!-- TAB 2: NOMINATIF PEGAWAI NON-AKTIF -->
@elseif($tab === 'nominatif')
    <div class="filter-card">
        <form action="{{ route('laporan.monitoring_status.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="nominatif">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="filter-input" style="width: 180px;">
            
            <select name="status" class="filter-select">
                <option value="semua">-- Semua Status Non-Aktif --</option>
                <option value="23" {{ request('status') == '23' ? 'selected' : '' }}>🛑 Pensiun BUP (Kode 23)</option>
                <option value="22" {{ request('status') == '22' ? 'selected' : '' }}>⏸️ Pensiun Sendiri / Stop (Kode 22)</option>
                <option value="27" {{ request('status') == '27' ? 'selected' : '' }}>⚰️ Meninggal Dunia (Kode 27)</option>
                <option value="28" {{ request('status') == '28' ? 'selected' : '' }}>🔄 Pindah Instansi (Kode 28)</option>
                <option value="24" {{ request('status') == '24' ? 'selected' : '' }}>🚪 Berhenti / Keluar (Kode 24)</option>
                <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>🏖️ Cuti / CLTN (Kode 6)</option>
                <option value="9" {{ request('status') == '9' ? 'selected' : '' }}>⏳ MPP / Skorsing (Kode 9)</option>
            </select>

            <select name="skpd" class="filter-select" style="max-width: 250px;">
                <option value="">-- Semua SKPD --</option>
                @foreach($availableSkpds as $s)
                    <option value="{{ $s }}" {{ request('skpd') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" style="background: var(--luno-primary); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                <i class="ph ph-funnel"></i> Filter
            </button>
            @if(request('search') || request('status') || request('skpd'))
                <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'nominatif']) }}" style="background: var(--bg-surface-subtle); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>NIP & Nama Pegawai</th>
                    <th>Golru</th>
                    <th>SKPD / Unit Kerja</th>
                    <th>Status di SIMGAJI</th>
                    <th>TMT Stop Gaji</th>
                    <th>Catatan SK Mutasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedNominatif as $idx => $item)
                <tr>
                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $paginatedNominatif->firstItem() + $idx }}</td>
                    <td>
                        <div style="font-weight: 700; font-family: monospace; font-size: 13px;">{{ $item['nip'] }}</div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-top: 2px;">{{ $item['nama'] }}</div>
                    </td>
                    <td><span class="badge" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); font-weight: 700;">{{ $item['golru'] ?: '-' }}</span></td>
                    <td style="font-weight: 600; color: var(--text-main); font-size: 12.5px;">{{ $item['skpd'] }}</td>
                    <td>
                        <span class="badge-status {{ $item['badge_color'] }}">
                            {{ $item['status_label'] }}
                        </span>
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">Kode: {{ $item['kdstapeg'] }}</div>
                    </td>
                    <td>
                        @if(!empty($item['tmtstop']))
                            <div style="font-weight: 700; color: #dc2626;">{{ date('d M Y', strtotime($item['tmtstop'])) }}</div>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ $item['catatan'] ?: '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">Tidak ada data pegawai non-aktif yang cocok dengan filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($paginatedNominatif->total() > 0)
        <div class="pagination-container">
            <div class="pagination-info">
                <i class="ph-bold ph-list-numbers" style="color: var(--luno-primary); font-size: 16px;"></i>
                <span>
                    Menampilkan <strong>{{ $paginatedNominatif->firstItem() ?? 0 }}</strong> s/d <strong>{{ $paginatedNominatif->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedNominatif->total(), 0, ',', '.') }}</strong> pegawai (Halaman {{ $paginatedNominatif->currentPage() }} dari {{ $paginatedNominatif->lastPage() }})
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <!-- Dropdown Page Size -->
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                    <span>Per hal:</span>
                    <select onchange="window.location.href = this.value;" class="filter-select" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                        @foreach([25, 50, 100, 250, 500] as $size)
                            <option value="{{ $paginatedNominatif->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                @if($paginatedNominatif->hasPages())
                    <div class="pagination-nav">
                        {{-- First Page --}}
                        @if(!$paginatedNominatif->onFirstPage())
                            <a href="{{ $paginatedNominatif->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                <i class="ph-bold ph-caret-double-left"></i>
                            </a>
                            <a href="{{ $paginatedNominatif->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                <i class="ph-bold ph-caret-left"></i> Prev
                            </a>
                        @else
                            <span class="page-btn disabled" title="Halaman Pertama"><i class="ph-bold ph-caret-double-left"></i></span>
                            <span class="page-btn disabled" title="Halaman Sebelumnya"><i class="ph-bold ph-caret-left"></i> Prev</span>
                        @endif

                        {{-- Sliding Window Pagination --}}
                        @php
                            $currentPage = $paginatedNominatif->currentPage();
                            $lastPage = $paginatedNominatif->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $paginatedNominatif->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-dots">&hellip;</span>
                            @endif
                        @endif

                        @for($p = $start; $p <= $end; $p++)
                            @if($p == $currentPage)
                                <span class="page-btn active">{{ $p }}</span>
                            @else
                                <a href="{{ $paginatedNominatif->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-dots">&hellip;</span>
                            @endif
                            <a href="{{ $paginatedNominatif->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif

                        {{-- Next Page --}}
                        @if($paginatedNominatif->hasMorePages())
                            <a href="{{ $paginatedNominatif->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                Next <i class="ph-bold ph-caret-right"></i>
                            </a>
                            <a href="{{ $paginatedNominatif->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
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

<!-- TAB 3: PROYEKSI PENSIUN MENDATANG -->
@elseif($tab === 'proyeksi')
    <div class="filter-card">
        <form action="{{ route('laporan.monitoring_status.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="proyeksi">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="filter-input" style="width: 180px;">
            
            <select name="tahun" class="filter-select">
                <option value="semua">-- Semua Tahun Proyeksi --</option>
                <option value="2026" {{ request('tahun') == '2026' ? 'selected' : '' }}>Tahun 2026</option>
                <option value="2027" {{ request('tahun') == '2027' ? 'selected' : '' }}>Tahun 2027</option>
                <option value="2028" {{ request('tahun') == '2028' ? 'selected' : '' }}>Tahun 2028</option>
            </select>

            <select name="skpd" class="filter-select" style="max-width: 250px;">
                <option value="">-- Semua SKPD --</option>
                @foreach($availableSkpds as $s)
                    <option value="{{ $s }}" {{ request('skpd') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" style="background: var(--luno-primary); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                <i class="ph ph-funnel"></i> Filter
            </button>
            @if(request('search') || request('tahun') || request('skpd'))
                <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'proyeksi']) }}" style="background: var(--bg-surface-subtle); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>NIP & Nama Pegawai</th>
                    <th>Kategori ASN</th>
                    <th>Golru</th>
                    <th>SKPD / Unit Kerja</th>
                    <th>BUP</th>
                    <th>Estimasi TMT Pensiun</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedProyeksi as $idx => $item)
                <tr>
                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $paginatedProyeksi->firstItem() + $idx }}</td>
                    <td>
                        <div style="font-weight: 700; font-family: monospace; font-size: 13px;">{{ $item['nip'] }}</div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-top: 2px;">{{ $item['nama'] }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $item['status_asn'] === 'PNS' ? 'badge-blue' : 'badge-emerald' }}" style="font-weight: 700; font-size: 11px;">
                            {{ $item['status_asn'] }}
                        </span>
                    </td>
                    <td><span class="badge" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); font-weight: 700;">{{ $item['golru'] ?: '-' }}</span></td>
                    <td style="font-weight: 600; color: var(--text-main); font-size: 12.5px;">{{ $item['skpd'] }}</td>
                    <td style="font-weight: 600;">{{ $item['bup'] }} Tahun</td>
                    <td>
                        <div style="font-weight: 700; color: #2563eb; font-size: 13px;">
                            <i class="ph ph-calendar"></i> {{ date('d M Y', strtotime($item['tmt_pensiun'])) }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">Tidak ada proyeksi pegawai pensiun yang cocok dengan filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($paginatedProyeksi->total() > 0)
        <div class="pagination-container">
            <div class="pagination-info">
                <i class="ph-bold ph-list-numbers" style="color: var(--luno-primary); font-size: 16px;"></i>
                <span>
                    Menampilkan <strong>{{ $paginatedProyeksi->firstItem() ?? 0 }}</strong> s/d <strong>{{ $paginatedProyeksi->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedProyeksi->total(), 0, ',', '.') }}</strong> proyeksi pegawai (Halaman {{ $paginatedProyeksi->currentPage() }} dari {{ $paginatedProyeksi->lastPage() }})
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <!-- Dropdown Page Size -->
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                    <span>Per hal:</span>
                    <select onchange="window.location.href = this.value;" class="filter-select" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                        @foreach([25, 50, 100, 250, 500] as $size)
                            <option value="{{ $paginatedProyeksi->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                @if($paginatedProyeksi->hasPages())
                    <div class="pagination-nav">
                        {{-- First Page --}}
                        @if(!$paginatedProyeksi->onFirstPage())
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                <i class="ph-bold ph-caret-double-left"></i>
                            </a>
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                <i class="ph-bold ph-caret-left"></i> Prev
                            </a>
                        @else
                            <span class="page-btn disabled" title="Halaman Pertama"><i class="ph-bold ph-caret-double-left"></i></span>
                            <span class="page-btn disabled" title="Halaman Sebelumnya"><i class="ph-bold ph-caret-left"></i> Prev</span>
                        @endif

                        {{-- Sliding Window Pagination --}}
                        @php
                            $currentPage = $paginatedProyeksi->currentPage();
                            $lastPage = $paginatedProyeksi->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-dots">&hellip;</span>
                            @endif
                        @endif

                        @for($p = $start; $p <= $end; $p++)
                            @if($p == $currentPage)
                                <span class="page-btn active">{{ $p }}</span>
                            @else
                                <a href="{{ $paginatedProyeksi->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-dots">&hellip;</span>
                            @endif
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif

                        {{-- Next Page --}}
                        @if($paginatedProyeksi->hasMorePages())
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                Next <i class="ph-bold ph-caret-right"></i>
                            </a>
                            <a href="{{ $paginatedProyeksi->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
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

<!-- TAB 4: MONITORING ANOMALI PENGGAJIAN PASCA STOP -->
@elseif($tab === 'anomali')
    <div class="filter-card">
        <form action="{{ route('laporan.monitoring_status.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="anomali">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="filter-input" style="width: 200px;">
            
            <select name="skpd" class="filter-select" style="max-width: 250px;">
                <option value="">-- Semua SKPD --</option>
                @foreach($availableSkpds as $s)
                    <option value="{{ $s }}" {{ request('skpd') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" style="background: var(--luno-primary); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                <i class="ph ph-funnel"></i> Filter
            </button>
            @if(request('search') || request('skpd'))
                <a href="{{ route('laporan.monitoring_status.index', ['tab' => 'anomali']) }}" style="background: var(--bg-surface-subtle); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>NIP & Nama Pegawai</th>
                    <th>SKPD Asal</th>
                    <th>Status di SIMGAJI</th>
                    <th>TMT Stop Gaji</th>
                    <th>Indikasi Anomali & Rekomendasi Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedAnomali as $idx => $item)
                <tr>
                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $paginatedAnomali->firstItem() + $idx }}</td>
                    <td>
                        <div style="font-weight: 700; color: #dc2626; font-family: monospace; font-size: 13px;">{{ $item['nip'] }}</div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-top: 2px;">{{ $item['nama'] }}</div>
                    </td>
                    <td style="font-weight: 600; font-size: 12.5px;">{{ $item['skpd'] }}</td>
                    <td>
                        <span class="badge-status {{ $item['badge_color'] }}">
                            {{ $item['status_simgaji'] }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #dc2626;">{{ date('d M Y', strtotime($item['tmtstop'])) }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #b45309; font-size: 12.5px;">
                            <i class="ph-bold ph-warning"></i> {{ $item['indikasi'] }}
                        </div>
                        @if(!empty($item['catatan']))
                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                                Catatan: {{ $item['catatan'] }}
                            </div>
                        @endif
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; font-style: italic;">
                            Rekomendasi: Periksa apakah pembayaran merupakan rapel bulan kerja aktif sebelumnya atau keterlambatan penghentian usulan SKPD.
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        <i class="ph ph-shield-check" style="font-size: 48px; color: var(--success-text); margin-bottom: 8px; display: block;"></i>
                        <strong style="color: var(--text-main); font-size: 14px; display: block;">Tidak Ada Anomali</strong>
                        Seluruh transaksi penggajian sinkron dengan TMT Stop kepegawaian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($paginatedAnomali->total() > 0)
        <div class="pagination-container">
            <div class="pagination-info">
                <i class="ph-bold ph-list-numbers" style="color: var(--luno-primary); font-size: 16px;"></i>
                <span>
                    Menampilkan <strong>{{ $paginatedAnomali->firstItem() ?? 0 }}</strong> s/d <strong>{{ $paginatedAnomali->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($paginatedAnomali->total(), 0, ',', '.') }}</strong> anomali (Halaman {{ $paginatedAnomali->currentPage() }} dari {{ $paginatedAnomali->lastPage() }})
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <!-- Dropdown Page Size -->
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                    <span>Per hal:</span>
                    <select onchange="window.location.href = this.value;" class="filter-select" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 75px;">
                        @foreach([25, 50, 100, 250, 500] as $size)
                            <option value="{{ $paginatedAnomali->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                @if($paginatedAnomali->hasPages())
                    <div class="pagination-nav">
                        {{-- First Page --}}
                        @if(!$paginatedAnomali->onFirstPage())
                            <a href="{{ $paginatedAnomali->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                                <i class="ph-bold ph-caret-double-left"></i>
                            </a>
                            <a href="{{ $paginatedAnomali->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                                <i class="ph-bold ph-caret-left"></i> Prev
                            </a>
                        @else
                            <span class="page-btn disabled" title="Halaman Pertama"><i class="ph-bold ph-caret-double-left"></i></span>
                            <span class="page-btn disabled" title="Halaman Sebelumnya"><i class="ph-bold ph-caret-left"></i> Prev</span>
                        @endif

                        {{-- Sliding Window Pagination --}}
                        @php
                            $currentPage = $paginatedAnomali->currentPage();
                            $lastPage = $paginatedAnomali->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $paginatedAnomali->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                            @if($start > 2)
                                <span class="page-dots">&hellip;</span>
                            @endif
                        @endif

                        @for($p = $start; $p <= $end; $p++)
                            @if($p == $currentPage)
                                <span class="page-btn active">{{ $p }}</span>
                            @else
                                <a href="{{ $paginatedAnomali->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="page-dots">&hellip;</span>
                            @endif
                            <a href="{{ $paginatedAnomali->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                        @endif

                        {{-- Next Page --}}
                        @if($paginatedAnomali->hasMorePages())
                            <a href="{{ $paginatedAnomali->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                                Next <i class="ph-bold ph-caret-right"></i>
                            </a>
                            <a href="{{ $paginatedAnomali->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
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
@endsection
