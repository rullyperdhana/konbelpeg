@extends('layouts.app')

@section('title', 'SIKD Core')
@section('page_title', 'Laporan Rekapitulasi SIKD Core')

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
    
    .toolbar { display: flex; gap: 16px; margin-bottom: 24px; background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; align-items: center; flex-wrap: wrap; }
    .toolbar select { border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 8px; outline: none; background: white; font-size: 14px; color: #475569; min-width: 180px; }
    .toolbar button { background: #3b82f6; border: none; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; height: 100%;}
    .toolbar button:hover { background: #2563eb; }

    .btn-export { background: white; color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border: 1px solid #cbd5e1;}
    .btn-export:hover { background: #f8fafc; }

    .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9; overflow-x: auto; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; min-width: 1500px; }
    
    /* Strict border styling like the screenshot */
    table, th, td {
        border: 2px solid #000 !important; /* Thick black borders */
    }
    th { 
        background: #fff; 
        color: #000; 
        font-weight: 700; 
        text-align: center; 
        padding: 10px 6px; 
        font-size: 12px; 
        vertical-align: middle;
    }
    td { 
        padding: 8px 6px; 
        color: #000; 
        font-size: 12px; 
    }
    
    .number-header th {
        font-size: 10px;
        font-style: italic;
        padding: 4px;
        background: #f8fafc;
        border-top: 1px solid #000 !important;
        position: relative;
    }
    
    /* Green corner triangle in number headers like the screenshot */
    .number-header th::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 0;
        border-top: 10px solid #16a34a;
        border-right: 10px solid transparent;
    }

    .money { font-family: monospace; font-size: 12px; font-weight: 600; text-align: right; }
    .center { text-align: center; }
</style>

<div class="app-header">
    <div>
        <h2>Laporan SIKD Core</h2>
        <p>Rekapitulasi realisasi belanja pegawai dan gaji berdasarkan golongan pegawai sesuai format SIKD Core.</p>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
    <form action="/laporan/sikd-core" method="GET" class="toolbar" id="filterForm" style="margin-bottom: 0;">
        <div>
            <select name="periode">
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
        <button type="submit"><i class="ph ph-funnel"></i> Tampilkan</button>
    </form>
    
    <div style="display: flex; gap: 12px;">
        <a href="/laporan/sikd-core/export/excel?periode={{ urlencode($periode) }}" class="btn-export" style="color: #16a34a; border-color: #16a34a;">
            <i class="ph ph-file-xls"></i> Export Excel
        </a>
        <a href="/laporan/sikd-core/export/pdf?periode={{ urlencode($periode) }}" class="btn-export" style="color: #dc2626; border-color: #dc2626;">
            <i class="ph ph-printer"></i> Print PDF
        </a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">No</th>
                <th rowspan="2" style="width: 100px;">Golongan</th>
                <th rowspan="2" style="width: 60px;">Jumlah</th>
                <th rowspan="2">Gaji<br>Pokok</th>
                <th rowspan="2">Tunjangan<br>Keluarga</th>
                <th colspan="2">Tunjangan Jabatan</th>
                <th rowspan="2">Tunjangan<br>Umum</th>
                <th rowspan="2">Tunjangan<br>PPH</th>
                <th rowspan="2">Tunjangan<br>Beras</th>
                <th rowspan="2">Tunjangan<br>Lainnya</th>
                <th rowspan="2">Lain-lain<br>Pembulatan</th>
                <th rowspan="2">Gaji<br>Kotor</th>
                <th rowspan="2">Tunjangan<br>Perbaikan<br>Penghasilan</th>
                <th rowspan="2" style="width: 60px;">Kode<br>Bayar</th>
                <th rowspan="2">Total<br>Penghasilan</th>
            </tr>
            <tr>
                <th>Struktural</th>
                <th>Fungsional</th>
            </tr>
            <tr class="number-header">
                <th>(1)</th>
                <th>(2)</th>
                <th>(3)</th>
                <th>(4)</th>
                <th>(5)</th>
                <th>(6)</th>
                <th>(7)</th>
                <th>(8)</th>
                <th>(9)</th>
                <th>(10)</th>
                <th>(11)</th>
                <th>(12)</th>
                <th>(13)=(4)+(5)+(6)+(7)+(8)+(9)+(10)+...</th>
                <th>(14)</th>
                <th>(15)</th>
                <th>(16)=(13)+(14)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center" style="font-weight: 600;">{{ $row['golongan'] }}</td>
                <td class="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_pph'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_lainnya'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['pembulatan'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_perbaikan'], 0, ',', '.') }}</td>
                <td class="center">-</td>
                <td class="money">{{ number_format($row['total_penghasilan'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="16" class="center" style="padding: 40px; color: #64748b; font-size: 14px; font-weight: normal; border: 1px solid #e2e8f0 !important;">
                    Belum ada data Realisasi Gaji untuk periode ini. <br>
                    Silakan unggah DBF Gaji terlebih dahulu.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($laporan) > 0)
        <tfoot>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td colspan="2" class="center">Jumlah</td>
                <td class="center">{{ number_format($totals['jumlah'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_pph'], 0, ',', '.') }}</td>
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
</div>
@endsection
