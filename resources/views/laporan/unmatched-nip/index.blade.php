@extends('layouts.app')

@section('title', 'Log NIP Tidak Ditemukan')
@section('page_title', 'Laporan NIP Tidak Ditemukan & Diagnosa SIMGAJI')

@section('content')
<style>
    .unmatched-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .unmatched-title h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .unmatched-title p {
        margin: 0;
        font-size: 13.5px;
        color: var(--text-muted);
    }

    .stat-badge-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .stat-pill {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        box-shadow: var(--card-shadow);
    }

    .stat-pill strong {
        color: var(--text-main);
        font-size: 14px;
    }

    .stat-pill.pensiun {
        border-left: 4px solid #ef4444;
    }
    .stat-pill.pensiun strong {
        color: #dc2626;
    }

    .stat-pill.meninggal {
        border-left: 4px solid #475569;
    }
    .stat-pill.meninggal strong {
        color: #334155;
    }

    .stat-pill.aktif-simgaji {
        border-left: 4px solid #2563eb;
    }
    .stat-pill.aktif-simgaji strong {
        color: #2563eb;
    }

    .stat-pill.anomali {
        border-left: 4px solid #f59e0b;
    }
    .stat-pill.anomali strong {
        color: #d97706;
    }

    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .filter-form-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
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
        vertical-align: top;
    }

    .table-container tbody tr:hover {
        background-color: var(--bg-surface-hover);
    }

    .badge-status-pns {
        background: var(--luno-primary-light);
        color: var(--luno-primary-text);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid var(--luno-primary-border);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-pppk {
        background: var(--success-light);
        color: var(--success-text);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(16, 185, 129, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-paruh {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(245, 158, 11, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-pejabat {
        background: rgba(139, 92, 246, 0.12);
        color: #7c3aed;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(139, 92, 246, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-gaji {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .badge-tpp {
        background: rgba(168, 85, 247, 0.12);
        color: #9333ea;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid rgba(168, 85, 247, 0.25);
    }

    /* Badges Status SIMGAJI */
    .badge-pensiun {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #f87171;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-meninggal {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-pindah {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-stop {
        background: #ffedd5;
        color: #9a3412;
        border: 1px solid #fdba74;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-aktif-simgaji {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-tidak-terdaftar {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #d1d5db;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .diagnosa-box {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.45;
        margin-top: 4px;
    }

    .pagination-wrapper {
        padding: 16px 22px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        background: var(--bg-surface);
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pagination-info strong {
        color: var(--text-main);
        font-weight: 700;
    }

    .per-page-select {
        padding: 5px 10px;
        font-size: 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-surface);
        color: var(--text-main);
        outline: none;
        cursor: pointer;
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
        border-color: #2563eb;
        color: #2563eb;
        transform: translateY(-1px);
    }

    .page-btn.active {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
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
        font-size: 13px;
        color: var(--text-muted);
    }
</style>

<div class="unmatched-header">
    <div class="unmatched-title">
        <h2>
            <i class="ph ph-warning-circle" style="color: var(--danger-text);"></i>
            Log NIP Tidak Ditemukan & Diagnosa SIMGAJI
        </h2>
        <p>Analisis cerdas data NIP tidak cocok saat impor TPP/Gaji yang disandingkan otomatis dengan Master Database SIMGAJI.</p>
    </div>

    <!-- Quick Stats Pills -->
    <div class="stat-badge-group">
        <div class="stat-pill">
            <span style="color: var(--text-muted);">Total Gagal:</span>
            <strong>{{ number_format($countTotal, 0, ',', '.') }}</strong>
        </div>
        <div class="stat-pill pensiun" title="Pegawai terkonfirmasi Pensiun di SIMGAJI">
            <i class="ph-bold ph-calendar-x" style="color: #ef4444;"></i>
            <span style="color: var(--text-muted);">Terindikasi Pensiun:</span>
            <strong>{{ number_format($countPensiun, 0, ',', '.') }}</strong>
        </div>
        @if($countMeninggal > 0)
        <div class="stat-pill meninggal" title="Pegawai terkonfirmasi Meninggal Dunia di SIMGAJI">
            <i class="ph-bold ph-heart-break" style="color: #475569;"></i>
            <span style="color: var(--text-muted);">Meninggal:</span>
            <strong>{{ number_format($countMeninggal, 0, ',', '.') }}</strong>
        </div>
        @endif
        <div class="stat-pill aktif-simgaji" title="Pegawai aktif di SIMGAJI namun belum terdata di Master SIMPEG aplikasi">
            <i class="ph-bold ph-user-plus" style="color: #2563eb;"></i>
            <span style="color: var(--text-muted);">Aktif SIMGAJI (Belum Sync):</span>
            <strong>{{ number_format($countAktifSimgaji, 0, ',', '.') }}</strong>
        </div>
        <div class="stat-pill anomali" title="NIP tidak ditemukan di SIMGAJI maupun Master SIMPEG">
            <i class="ph-bold ph-question" style="color: #f59e0b;"></i>
            <span style="color: var(--text-muted);">NIP Tidak Dikenal:</span>
            <strong>{{ number_format($countTidakTerdaftar, 0, ',', '.') }}</strong>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="background: var(--success-light); color: var(--success-text); padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class="ph ph-check-circle" style="font-size: 20px;"></i>
        {{ session('success') }}
    </div>
@endif

<!-- Toolbar Filter -->
<div class="filter-card">
    <form action="/laporan/unmatched-nip" method="GET" class="filter-form-group" style="margin: 0;">
        <input type="text" 
               name="search" 
               value="{{ request('search') }}" 
               placeholder="Cari NIP atau Nama..." 
               class="filter-input" 
               style="width: 180px;">

        <!-- Filter Status Diagnosa SIMGAJI -->
        <select name="status_simgaji" class="filter-select" style="font-weight: 600;">
            <option value="semua">-- Semua Diagnosa SIMGAJI --</option>
            <option value="pensiun" {{ request('status_simgaji') == 'pensiun' ? 'selected' : '' }}>🛑 Terindikasi Pensiun</option>
            <option value="meninggal" {{ request('status_simgaji') == 'meninggal' ? 'selected' : '' }}>⚰️ Meninggal Dunia</option>
            <option value="pindah" {{ request('status_simgaji') == 'pindah' ? 'selected' : '' }}>🔄 Pindah Instansi</option>
            <option value="aktif_simgaji" {{ request('status_simgaji') == 'aktif_simgaji' ? 'selected' : '' }}>⚠️ Aktif SIMGAJI (Belum Sync)</option>
            <option value="tidak_terdaftar" {{ request('status_simgaji') == 'tidak_terdaftar' ? 'selected' : '' }}>❓ NIP Tidak Dikenal</option>
        </select>

        <select name="status_pegawai" class="filter-select">
            <option value="">-- Status Pegawai --</option>
            @foreach($statusOptions as $st)
                <option value="{{ $st }}" {{ request('status_pegawai') == $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>

        <select name="periode" class="filter-select">
            <option value="">-- Semua Periode --</option>
            @foreach($availablePeriodes as $p)
                <option value="{{ $p }}" {{ request('periode') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        
        <select name="jenis_file" class="filter-select">
            <option value="">-- Jenis Laporan --</option>
            <option value="Gaji" {{ request('jenis_file') == 'Gaji' ? 'selected' : '' }}>Gaji (DBF/Excel)</option>
            <option value="TPP" {{ request('jenis_file') == 'TPP' ? 'selected' : '' }}>TPP (Excel)</option>
        </select>
        
        <button type="submit" style="background: var(--luno-primary); color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-funnel"></i> Filter
        </button>

        @if(request('search') || request('status_simgaji') || request('status_pegawai') || request('periode') || request('jenis_file'))
            <a href="/laporan/unmatched-nip" style="background: var(--bg-surface-subtle); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </a>
        @endif
    </form>
    
    <div>
        <form action="/laporan/unmatched-nip/clear" method="POST" onsubmit="return confirm('Yakin ingin menghapus seluruh riwayat log NIP tidak ditemukan ini?');" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" style="background: var(--danger-light); color: var(--danger-text); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph ph-trash"></i> Bersihkan Log
            </button>
        </form>
    </div>
</div>

<!-- Table Data -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 45px; text-align: center;">No</th>
                <th style="min-width: 170px;">NIP & Identitas</th>
                <th style="min-width: 130px;">Kategori File</th>
                <th style="min-width: 140px;">Status di SIMGAJI</th>
                <th style="min-width: 250px;">Diagnosa Penyebab & Catatan SIMGAJI</th>
                <th style="min-width: 120px;">Periode & Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            @php
                $sg = $log->simgaji;
            @endphp
            <tr>
                <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $logs->firstItem() + $index }}</td>
                <td>
                    <div style="font-weight: 700; color: var(--danger-text); font-family: monospace; font-size: 13.5px; letter-spacing: 0.02em;">
                        {{ $log->nip }}
                    </div>
                    <div style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-top: 2px;">
                        {{ $log->nama ?: ($sg['nama'] ?? 'Tanpa Nama') }}
                    </div>
                    @if($sg && !empty($sg['skpd_nama']))
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                            <i class="ph ph-buildings"></i> {{ $sg['skpd_nama'] }}
                        </div>
                    @endif
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                        <span class="badge {{ $log->jenis_file == 'Gaji' ? 'badge-gaji' : 'badge-tpp' }}">
                            {{ $log->jenis_file }}
                        </span>
                        @if($log->status_pegawai === 'PNS')
                            <span class="badge-status-pns">
                                <i class="ph ph-shield-check"></i> PNS
                            </span>
                        @elseif($log->status_pegawai === 'PPPK')
                            <span class="badge-status-pppk">
                                <i class="ph ph-identification-badge"></i> PPPK
                            </span>
                        @elseif($log->status_pegawai === 'PPPK PARUH WAKTU')
                            <span class="badge-status-paruh">
                                <i class="ph ph-clock"></i> PPPK Paruh Waktu
                            </span>
                        @elseif($log->status_pegawai === 'Pejabat Negara')
                            <span class="badge-status-pejabat">
                                <i class="ph ph-crown"></i> Pejabat Negara
                            </span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($sg)
                        <div>
                            <span class="{{ $sg['badge_class'] ?? 'badge-aktif-simgaji' }}">
                                @if(($sg['status_key'] ?? '') === 'pensiun')
                                    <i class="ph-bold ph-calendar-x"></i>
                                @elseif(($sg['status_key'] ?? '') === 'meninggal')
                                    <i class="ph-bold ph-heart-break"></i>
                                @elseif(($sg['status_key'] ?? '') === 'pindah')
                                    <i class="ph-bold ph-arrows-left-right"></i>
                                @elseif(($sg['status_key'] ?? '') === 'aktif_simgaji')
                                    <i class="ph-bold ph-check-circle"></i>
                                @else
                                    <i class="ph-bold ph-info"></i>
                                @endif
                                {{ $sg['status_label'] }}
                            </span>
                        </div>
                        @if(!empty($sg['tmtstop']))
                            <div style="font-size: 11px; color: #dc2626; font-weight: 600; margin-top: 4px;">
                                TMT Stop: {{ date('d-m-Y', strtotime($sg['tmtstop'])) }}
                            </div>
                        @endif
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                            Kode: {{ $sg['kdstapeg'] ?? '-' }}
                        </div>
                    @else
                        <span class="badge-tidak-terdaftar">
                            <i class="ph-bold ph-question"></i> Tidak Ditemukan di SIMGAJI
                        </span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                            Bukan NIP aktif/arsip SIMGAJI
                        </div>
                    @endif
                </td>
                <td>
                    @if($sg)
                        <div style="font-weight: 600; color: var(--text-main); font-size: 12.5px;">
                            {{ $sg['diagnosa'] }}
                        </div>
                        @if(!empty($sg['catatan']))
                            <div style="font-size: 11.5px; background: var(--bg-surface-subtle); border-radius: 6px; padding: 4px 8px; margin-top: 4px; color: var(--text-muted); border: 1px dashed var(--border-color);">
                                <i class="ph ph-note"></i> <strong>Catatan SIMGAJI:</strong> {{ $sg['catatan'] }}
                            </div>
                        @endif
                    @else
                        <div style="color: #d97706; font-weight: 600; font-size: 12.5px;">
                            NIP tidak terdaftar di SIMGAJI maupun Master SIMPEG aplikasi.
                        </div>
                        <div class="diagnosa-box">
                            Kemungkinan kesalahan penulisan NIP pada file upload atau pegawai baru yang belum diinput ke database manapun.
                        </div>
                    @endif
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; font-style: italic;">
                        Pesan asli: {{ $log->keterangan }}
                    </div>
                </td>
                <td style="color: var(--text-muted); font-size: 12px;">
                    <div><strong style="color: var(--text-main);">{{ $log->periode }}</strong></div>
                    <div style="margin-top: 4px;">{{ $log->created_at->format('d M Y, H:i') }}</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 48px 24px; color: var(--text-muted);">
                    <i class="ph ph-check-circle" style="font-size: 48px; color: var(--success-text); margin-bottom: 12px; display: block;"></i>
                    <strong style="font-size: 15px; color: var(--text-main); display: block; margin-bottom: 4px;">Tidak Ada NIP Gagal</strong>
                    Seluruh NIP pada filter ini telah cocok dan terdaftar di Master Data Pegawai.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div class="pagination-info">
            <span>
                Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong> - <strong>{{ $logs->lastItem() ?? 0 }}</strong> dari <strong>{{ $logs->total() }}</strong> log data
            </span>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="per_page_select" style="font-size: 12px; color: var(--text-muted); margin: 0;">Per hal:</label>
                <select id="per_page_select" class="per-page-select" onchange="location = this.value;">
                    @foreach([25, 50, 100, 200] as $size)
                        <option value="{{ $logs->appends(array_merge(request()->query(), ['per_page' => $size, 'page' => 1]))->url(1) }}" {{ ($perPage ?? 50) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($logs->hasPages())
        <div class="pagination-nav">
            {{-- First Page --}}
            @if(!$logs->onFirstPage())
                <a href="{{ $logs->appends(request()->query())->url(1) }}" class="page-btn" title="Halaman Pertama">
                    <i class="ph-bold ph-caret-double-left"></i>
                </a>
                <a href="{{ $logs->appends(request()->query())->previousPageUrl() }}" class="page-btn" title="Halaman Sebelumnya">
                    <i class="ph-bold ph-caret-left"></i> Prev
                </a>
            @else
                <span class="page-btn disabled" title="Halaman Pertama"><i class="ph-bold ph-caret-double-left"></i></span>
                <span class="page-btn disabled" title="Halaman Sebelumnya"><i class="ph-bold ph-caret-left"></i> Prev</span>
            @endif

            {{-- Sliding Window Pagination --}}
            @php
                $currentPage = $logs->currentPage();
                $lastPage = $logs->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
            @endphp

            @if($start > 1)
                <a href="{{ $logs->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                @if($start > 2)
                    <span class="page-dots">&hellip;</span>
                @endif
            @endif

            @for($p = $start; $p <= $end; $p++)
                @if($p == $currentPage)
                    <span class="page-btn active">{{ $p }}</span>
                @else
                    <a href="{{ $logs->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                @endif
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="page-dots">&hellip;</span>
                @endif
                <a href="{{ $logs->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
            @endif

            {{-- Next Page --}}
            @if($logs->hasMorePages())
                <a href="{{ $logs->appends(request()->query())->nextPageUrl() }}" class="page-btn" title="Halaman Selanjutnya">
                    Next <i class="ph-bold ph-caret-right"></i>
                </a>
                <a href="{{ $logs->appends(request()->query())->url($lastPage) }}" class="page-btn" title="Halaman Terakhir">
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
@endsection
