@extends('layouts.app')

@section('title', 'Laporan PPPK Guru (Rincian per NIP)')
@section('page_title', 'Laporan Rincian PPPK Guru')

@section('content')
<style>
    .aas-header {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
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
    .aas-header p { color: #bfdbfe; font-size: 14px; }
    
    .toolbar { display: flex; gap: 16px; margin-bottom: 24px; background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; align-items: center; flex-wrap: wrap;}
    .toolbar select { border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 8px; outline: none; background: white; font-size: 14px; color: #475569; min-width: 150px; }
    .toolbar button { background: #3b82f6; border: none; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; height: 100%;}
    .toolbar button:hover { background: #2563eb; }
    
    .btn-export { display: inline-flex; align-items: center; gap: 8px; background: white; color: #1e293b; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; text-decoration: none; font-size: 14px;}
    .btn-export:hover { background: #f8fafc; border-color: #94a3b8; }
    
    .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9; overflow-x: auto; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; min-width: 2000px; }
    th, td { border: 1px solid #e2e8f0; padding: 12px; font-size: 12px; }
    th { background: #f8fafc; color: #475569; font-weight: 600; text-align: center; vertical-align: middle; position: sticky; top: 0; z-index: 10; }
    td { color: #1e293b; vertical-align: middle; }
    tr:hover { background: #f8fafc; }
    
    .money { text-align: right; white-space: nowrap; font-family: monospace; font-size: 13px; }
    .center { text-align: center; }
    
    /* Sticky first 3 columns for better scrolling */
    th:nth-child(1), td:nth-child(1) { position: sticky; left: 0; background: inherit; z-index: 2; width: 50px; }
    th:nth-child(2), td:nth-child(2) { position: sticky; left: 50px; background: inherit; z-index: 2; width: 120px; font-weight: 600; }
    th:nth-child(3), td:nth-child(3) { position: sticky; left: 170px; background: inherit; z-index: 2; width: 250px; }
    th:nth-child(1), th:nth-child(2), th:nth-child(3) { z-index: 20; background: #f8fafc; }
    tr { background: white; }
    
    .pagination-wrapper { padding: 16px 24px; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 12px 12px;}
</style>

<div class="aas-header">
    <div>
        <h2>Laporan PPPK Guru (Rincian per NIP)</h2>
        <p>Penjabaran detail komponen gaji dan tunjangan setiap PPPK GURU berdasarkan data Master dan Realisasi.</p>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
    <form action="/laporan/pppk-guru/rinci" method="GET" class="toolbar" id="filterForm" style="margin-bottom: 0;">
        <div>
            <select name="periode" id="periode_select">
                <option value="Januari 2026" {{ $periode == 'Januari 2026' ? 'selected' : '' }}>Januari 2026</option>
                <option value="Februari 2026" {{ $periode == 'Februari 2026' ? 'selected' : '' }}>Februari 2026</option>
                <option value="Maret 2026" {{ $periode == 'Maret 2026' ? 'selected' : '' }}>Maret 2026</option>
                <option value="April 2026" {{ $periode == 'April 2026' ? 'selected' : '' }}>April 2026</option>
                <option value="Mei 2026" {{ $periode == 'Mei 2026' ? 'selected' : '' }}>Mei 2026</option>
                <option value="Juni 2026" {{ $periode == 'Juni 2026' ? 'selected' : '' }}>Juni 2026</option>
                <option value="Juli 2026" {{ $periode == 'Juli 2026' ? 'selected' : '' }}>Juli 2026</option>
                <option value="Agustus 2026" {{ $periode == 'Agustus 2026' ? 'selected' : '' }}>Agustus 2026</option>
                <option value="September 2026" {{ $periode == 'September 2026' ? 'selected' : '' }}>September 2026</option>
                <option value="Oktober 2026" {{ $periode == 'Oktober 2026' ? 'selected' : '' }}>Oktober 2026</option>
                <option value="November 2026" {{ $periode == 'November 2026' ? 'selected' : '' }}>November 2026</option>
                <option value="Desember 2026" {{ $periode == 'Desember 2026' ? 'selected' : '' }}>Desember 2026</option>
            </select>
        </div>
        
        <div>
            <select name="golongan" id="golongan_select">
                <option value="">Semua Golongan</option>
                @foreach($golonganList as $gol)
                    <option value="{{ $gol }}" {{ $golonganFilter == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 250px;">
            <select name="skpd" id="skpd_select" placeholder="Filter SKPD (Opsional)...">
                <option value="">Semua SKPD</option>
                @foreach($skpdList as $skpd)
                    <option value="{{ $skpd }}" {{ $skpdFilter == $skpd ? 'selected' : '' }}>{{ $skpd }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIP / Nama..." style="border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 8px; outline: none; font-size: 14px; color: #475569; width: 200px;">
        </div>
        
        <button type="submit"><i class="ph ph-funnel"></i> Tampilkan</button>
    </form>
    
    <div style="display: flex; gap: 12px;">
        <a href="/laporan/pppk-guru/rinci/export/excel?periode={{ urlencode($periode) }}&skpd={{ urlencode($skpdFilter) }}&search={{ urlencode($search) }}&golongan={{ urlencode($golonganFilter) }}" class="btn-export" style="color: #16a34a; border-color: #16a34a;">
            <i class="ph ph-file-xls"></i> Export Excel
        </a>
        <a href="/laporan/pppk-guru/rinci/export/pdf?periode={{ urlencode($periode) }}&skpd={{ urlencode($skpdFilter) }}&search={{ urlencode($search) }}&golongan={{ urlencode($golonganFilter) }}" class="btn-export" style="color: #dc2626; border-color: #dc2626;">
            <i class="ph ph-printer"></i> Print PDF
        </a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">NIP</th>
                <th rowspan="2">Nama Pegawai</th>
                <th rowspan="2">SKPD</th>
                <th rowspan="2">Golongan</th>
                <th rowspan="2">Gaji<br>Pokok</th>
                <th rowspan="2">Tunjangan<br>Keluarga</th>
                <th colspan="2">Tunjangan Jabatan</th>
                <th rowspan="2">Tunjangan<br>Umum</th>
                <th rowspan="2">Tunjangan<br>Beras</th>
                <th rowspan="2">Tunjangan<br>Lainnya</th>
                <th rowspan="2">Lain-lain<br>Pembulatan</th>
                <th rowspan="2">Gaji<br>Kotor</th>
                <th rowspan="2">Tunjangan<br>Perbaikan<br>Penghasilan</th>
                <th rowspan="2">Kode<br>Bayar</th>
                <th rowspan="2">Total<br>Penghasilan</th>
            </tr>
            <tr>
                <th>Struktural</th>
                <th>Fungsional</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $row)
            <tr>
                <td class="center">{{ $laporan->firstItem() + $index }}</td>
                <td class="center">{{ $row['nip'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td style="font-size: 11px;">{{ $row['skpd'] }}</td>
                <td class="center"><strong>{{ $row['golongan'] }}</strong></td>
                <td class="money">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_lainnya'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['pembulatan'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_perbaikan'], 0, ',', '.') }}</td>
                <td class="center">-</td>
                <td class="money">{{ number_format($row['total_penghasilan'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        @if(count($laporan) > 0)
        <tfoot>
            <tr style="font-weight: bold; background-color: #f1f5f9;">
                <td colspan="5" class="center">Jumlah Keseluruhan (Berdasarkan Filter)</td>
                <td class="money">{{ number_format($totals['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_lainnya'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['pembulatan'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_perbaikan'], 0, ',', '.') }}</td>
                <td class="center"></td>
                <td class="money">{{ number_format($totals['total_penghasilan'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
    
    @if($laporan->hasPages())
    <div class="pagination-wrapper">
        <div style="color: #64748b; font-size: 14px;">
            Menampilkan {{ $laporan->firstItem() ?? 0 }} - {{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} pegawai
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$laporan->onFirstPage())
                <a href="{{ $laporan->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($laporan->hasMorePages())
                <a href="{{ $laporan->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#skpd_select', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });
</script>
@endsection
