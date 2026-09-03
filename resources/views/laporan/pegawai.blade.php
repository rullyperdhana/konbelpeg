@extends('layouts.app')

@section('title', 'Laporan Daftar Pegawai')
@section('page_title', 'Laporan Daftar Pegawai')

@section('content')
<style>
    .aas-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .aas-header h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .aas-header p { color: #94a3b8; font-size: 14px; }

    .btn-print {
        background: white; color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; text-decoration: none;
    }
    .btn-print:hover { background: #f8fafc; }

    .toolbar { display: flex; gap: 16px; margin-bottom: 24px; background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .toolbar select { border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 8px; outline: none; background: white; font-size: 14px; color: #475569; min-width: 200px; }
    .toolbar button { background: #3b82f6; border: none; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; }
    .toolbar button:hover { background: #2563eb; }
    
    .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; color: #475569; font-weight: 600; text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
    td { padding: 12px 16px; color: #1e293b; font-size: 12px; border-bottom: 1px solid #e2e8f0; }
    
    .pagination-wrapper { padding: 16px 24px; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    
    /* Print Styles */
    @media print {
        @page { size: landscape; margin: 10mm; }
        body { background: white; }
        .sidebar, .page-header, .aas-header, .app-header, .toolbar, .pagination-wrapper { display: none !important; }
        .main-wrapper { margin-left: 0 !important; padding: 0 !important; }
        .content-area { max-width: 100% !important; padding: 0 !important; }
        .table-container { box-shadow: none; border: none; }
        table { border: 1px solid #000; }
        th, td { border: 1px solid #000; padding: 8px; color: black; font-size: 11px; }
        th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
        
        .print-header { display: block !important; text-align: center; margin-bottom: 20px; }
        .print-header h1 { font-size: 18px; margin-bottom: 5px; font-weight: bold; }
        .print-header p { font-size: 12px; margin: 0; }
    }
    
    .print-header { display: none; }
</style>

<div class="app-header">
    <div>
        <h2>Laporan Daftar Pegawai</h2>
        <p>Saring data per SKPD untuk pratinjau dan pencetakan dokumen resmi.</p>
    </div>
    <button class="btn btn-export" onclick="window.print()">
        <i class="ph ph-printer"></i> Cetak Laporan
    </button>
</div>

<form action="/laporan/pegawai" method="GET" class="toolbar">
    <select name="skpd_filter" id="skpd-filter" placeholder="Ketik nama SKPD..." autocomplete="off" style="flex-grow: 1;">
        <option value="">Semua SKPD</option>
        @foreach($filterUnitKerjas as $uk)
            <option value="{{ $uk->skpd }}" {{ request('skpd_filter') == $uk->skpd ? 'selected' : '' }}>
                {{ $uk->skpd }}
            </option>
        @endforeach
    </select>
    
    <select name="status_filter">
        <option value="">Semua Status</option>
        <option value="PNS" {{ request('status_filter') == 'PNS' ? 'selected' : '' }}>PNS</option>
        <option value="PPPK" {{ request('status_filter') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
        <option value="CPNS" {{ request('status_filter') == 'CPNS' ? 'selected' : '' }}>CPNS</option>
    </select>
    
    <button type="submit">Filter Laporan</button>
</form>

<div class="print-header">
    <h1>LAPORAN DAFTAR PEGAWAI</h1>
    <p>PEMERINTAH PROVINSI</p>
    @if(request('skpd_filter'))
        <p style="margin-top: 5px;"><strong>UNIT KERJA:</strong> {{ request('skpd_filter') }}</p>
    @endif
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Gol/Ruang</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawais as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $pegawais->firstItem() + $index }}</td>
                <td>{{ $item->nip }}</td>
                <td style="font-weight: 600;">{{ $item->nama }}</td>
                <td>{{ $item->golru ?? '-' }}</td>
                <td>{{ $item->jabatan->nama ?? '-' }}</td>
                <td>{{ $item->unitKerja->skpd ?? '-' }}</td>
                <td>{{ $item->status_pegawai ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">Data tidak ditemukan. Silakan ubah filter pencarian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div style="font-size: 13px; color: #64748b;">
            Menampilkan {{ $pegawais->firstItem() ?? 0 }} - {{ $pegawais->lastItem() ?? 0 }} dari {{ $pegawais->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$pegawais->onFirstPage())
                <a href="{{ $pegawais->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($pegawais->hasMorePages())
                <a href="{{ $pegawais->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
</div>

<script>
    // Initialize TomSelect for Search Filter
    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('skpd-filter')) {
            new TomSelect("#skpd-filter",{
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
    });
</script>
@endsection
