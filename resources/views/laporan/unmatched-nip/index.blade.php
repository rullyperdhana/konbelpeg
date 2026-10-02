@extends('layouts.app')

@section('title', 'Log NIP Tidak Ditemukan')
@section('page_title', 'Laporan NIP Tidak Ditemukan Saat Upload')

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
        padding: 6px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        box-shadow: var(--card-shadow);
    }

    .stat-pill strong {
        color: var(--text-main);
        font-size: 13.5px;
    }

    .stat-pill.pns strong {
        color: var(--luno-primary-text);
    }

    .stat-pill.pppk strong {
        color: var(--success-text);
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
        vertical-align: middle;
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
        background: var(--warning-light);
        color: var(--warning-text);
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
        background: rgba(168, 85, 247, 0.12);
        color: #9333ea;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(168, 85, 247, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    :root.dark-mode .badge-status-pejabat {
        color: #c084fc;
        background: rgba(168, 85, 247, 0.2);
    }

    .pagination-wrapper {
        padding: 14px 20px;
        background: var(--bg-surface-subtle);
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>

<div class="unmatched-header">
    <div class="unmatched-title">
        <h2>
            <i class="ph ph-warning-circle" style="color: var(--danger-text);"></i>
            Log NIP Tidak Ditemukan Saat Upload
        </h2>
        <p>Daftar pegawai yang tercantum di file penggajian/TPP namun belum terdaftar di Master Data Pegawai.</p>
    </div>

    <!-- Quick Stats Pills -->
    <div class="stat-badge-group">
        <div class="stat-pill">
            <span style="color: var(--text-muted);">Total NIP Gagal:</span>
            <strong>{{ number_format($countTotal, 0, ',', '.') }}</strong>
        </div>
        <div class="stat-pill pns">
            <span style="color: var(--text-muted);">PNS:</span>
            <strong>{{ number_format($countPns, 0, ',', '.') }}</strong>
        </div>
        <div class="stat-pill pppk">
            <span style="color: var(--text-muted);">PPPK:</span>
            <strong>{{ number_format($countPppk, 0, ',', '.') }}</strong>
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
               style="width: 200px;">

        <select name="status_pegawai" class="filter-select">
            <option value="">-- Semua Status Pegawai --</option>
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
            <option value="">-- Semua Jenis Laporan --</option>
            <option value="Gaji" {{ request('jenis_file') == 'Gaji' ? 'selected' : '' }}>Gaji (DBF/Excel)</option>
            <option value="TPP" {{ request('jenis_file') == 'TPP' ? 'selected' : '' }}>TPP (Excel)</option>
        </select>
        
        <button type="submit" style="background: var(--luno-primary); color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-funnel"></i> Filter
        </button>

        @if(request('search') || request('status_pegawai') || request('periode') || request('jenis_file'))
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
                <th style="width: 50px; text-align: center;">No</th>
                <th>NIP</th>
                <th>Nama di File Upload</th>
                <th>Status Pegawai</th>
                <th>Jenis Laporan</th>
                <th>Periode</th>
                <th>Waktu Gagal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $logs->firstItem() + $index }}</td>
                <td style="font-weight: 700; color: var(--danger-text); font-family: monospace; font-size: 13px;">
                    {{ $log->nip }}
                </td>
                <td style="font-weight: 600;">{{ $log->nama ?: '-' }}</td>
                <td>
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
                    @elseif($log->status_pegawai)
                        <span class="badge" style="background: var(--bg-surface-subtle); color: var(--text-main); border: 1px solid var(--border-color);">
                            {{ $log->status_pegawai }}
                        </span>
                    @else
                        <span style="color: var(--text-muted);">-</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $log->jenis_file == 'Gaji' ? 'badge-gaji' : 'badge-tpp' }}">
                        {{ $log->jenis_file }}
                    </span>
                </td>
                <td><strong style="color: var(--text-main);">{{ $log->periode }}</strong></td>
                <td style="color: var(--text-muted); font-size: 12.5px;">{{ $log->created_at->format('d M Y, H:i') }}</td>
                <td style="color: var(--text-muted); font-size: 12.5px;">{{ $log->keterangan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 48px 24px; color: var(--text-muted);">
                    <i class="ph ph-check-circle" style="font-size: 48px; color: var(--success-text); margin-bottom: 12px; display: block;"></i>
                    <strong style="font-size: 15px; color: var(--text-main); display: block; margin-bottom: 4px;">Tidak Ada NIP Gagal</strong>
                    Seluruh NIP pada filter ini telah cocok dan terdaftar di Master Data Pegawai.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($logs->hasPages())
    <div class="pagination-wrapper">
        <div style="color: var(--text-muted); font-size: 13.5px;">
            Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$logs->onFirstPage())
                <a href="{{ $logs->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($logs->hasMorePages())
                <a href="{{ $logs->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
