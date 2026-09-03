@extends('layouts.app')

@section('title', 'Log NIP Tidak Ditemukan')
@section('page_title', 'Laporan NIP Tidak Ditemukan Saat Upload')

@section('content')
<div class="app-header">
    <div>
        <h2>Log NIP Tidak Ditemukan</h2>
        <p>Daftar pegawai yang ada di file Excel/DBF namun belum terdaftar di Master Data Pegawai.</p>
    </div>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #bbf7d0;">
        {{ session('success') }}
    </div>
@endif

<div class="toolbar">
    <form action="/laporan/unmatched-nip" method="GET" class="toolbar-left" style="margin: 0;">
        <select name="periode">
            <option value="">Semua Periode</option>
            <option value="Januari 2026" {{ request('periode') == 'Januari 2026' ? 'selected' : '' }}>Januari 2026</option>
            <option value="Februari 2026" {{ request('periode') == 'Februari 2026' ? 'selected' : '' }}>Februari 2026</option>
            <option value="Maret 2026" {{ request('periode') == 'Maret 2026' ? 'selected' : '' }}>Maret 2026</option>
            <option value="April 2026" {{ request('periode') == 'April 2026' ? 'selected' : '' }}>April 2026</option>
            <option value="Mei 2026" {{ request('periode') == 'Mei 2026' ? 'selected' : '' }}>Mei 2026</option>
            <option value="Juni 2026" {{ request('periode') == 'Juni 2026' ? 'selected' : '' }}>Juni 2026</option>
            <option value="Juli 2026" {{ request('periode') == 'Juli 2026' ? 'selected' : '' }}>Juli 2026</option>
            <option value="Agustus 2026" {{ request('periode') == 'Agustus 2026' ? 'selected' : '' }}>Agustus 2026</option>
            <option value="September 2026" {{ request('periode') == 'September 2026' ? 'selected' : '' }}>September 2026</option>
            <option value="Oktober 2026" {{ request('periode') == 'Oktober 2026' ? 'selected' : '' }}>Oktober 2026</option>
            <option value="November 2026" {{ request('periode') == 'November 2026' ? 'selected' : '' }}>November 2026</option>
            <option value="Desember 2026" {{ request('periode') == 'Desember 2026' ? 'selected' : '' }}>Desember 2026</option>
        </select>
        
        <select name="jenis_file">
            <option value="">Semua Jenis Laporan</option>
            <option value="Gaji" {{ request('jenis_file') == 'Gaji' ? 'selected' : '' }}>Gaji (DBF/Excel)</option>
            <option value="TPP" {{ request('jenis_file') == 'TPP' ? 'selected' : '' }}>TPP (Excel)</option>
        </select>
        
        <button type="submit"><i class="ph ph-funnel"></i> Filter</button>
    </form>
    
    <div>
        <form action="/laporan/unmatched-nip/clear" method="POST" onsubmit="return confirm('Yakin ingin menghapus seluruh riwayat log ini?');" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"><i class="ph ph-trash"></i> Bersihkan Log</button>
        </form>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>NIP</th>
                <th>Nama di File Upload</th>
                <th>Jenis Laporan</th>
                <th>Periode</th>
                <th>Waktu Gagal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td>{{ $logs->firstItem() + $index }}</td>
                <td style="font-weight: 600; color: #dc2626;">{{ $log->nip }}</td>
                <td>{{ $log->nama ?: '-' }}</td>
                <td>
                    <span class="badge {{ $log->jenis_file == 'Gaji' ? 'badge-gaji' : 'badge-tpp' }}">
                        {{ $log->jenis_file }}
                    </span>
                </td>
                <td>{{ $log->periode }}</td>
                <td style="color: #64748b; font-size: 13px;">{{ $log->created_at->format('d M Y, H:i') }}</td>
                <td style="color: #64748b; font-size: 13px;">{{ $log->keterangan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="ph ph-check-circle" style="font-size: 48px; color: #16a34a; margin-bottom: 12px; display: block;"></i>
                    Tidak ada log kegagalan NIP untuk filter saat ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($logs->hasPages())
    <div class="pagination-wrapper">
        <div style="color: #64748b; font-size: 14px;">
            Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$logs->onFirstPage())
                <a href="{{ $logs->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($logs->hasMorePages())
                <a href="{{ $logs->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
