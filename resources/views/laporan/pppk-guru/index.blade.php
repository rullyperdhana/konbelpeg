@extends('layouts.app')

@section('title', 'Laporan Gaji Guru PPPK + TPP')
@section('page_title', 'Laporan Khusus PPPK Guru')

@section('content')
<div class="app-header">
    <div>
        <h2>Daftar Rincian & Realisasi Pembayaran Gaji Guru (PPPK)</h2>
        <p>Rekapitulasi khusus untuk jenis pegawai PPPK Guru dan Tenaga Kependidikan.</p>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
    <form action="/laporan/pppk-guru" method="GET" class="toolbar" id="filterForm" style="margin-bottom: 0;">
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
        <a href="/laporan/pppk-guru/export/excel?periode={{ urlencode($periode) }}" class="btn-export" style="color: #16a34a; border-color: #16a34a;">
            <i class="ph ph-file-xls"></i> Export Excel
        </a>
        <a href="/laporan/pppk-guru/export/pdf?periode={{ urlencode($periode) }}" class="btn-export" style="color: #dc2626; border-color: #dc2626;">
            <i class="ph ph-printer"></i> Print PDF
        </a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 50px;">No</th>
                <th rowspan="2">Golongan</th>
                <th rowspan="2">Jumlah GURU<br>PPPK Yang<br>Diangkat</th>
                <th rowspan="2">Jumlah GURU<br>PPPK Yang<br>Telah Diangkat</th>
                <th rowspan="2">Gaji<br>Pokok</th>
                <th rowspan="2">Tunjangan<br>Keluarga</th>
                <th colspan="2">Tunjangan Jabatan</th>
                <th rowspan="2">Tunjangan<br>Umum</th>
                <th rowspan="2">Tunjangan<br>Beras</th>
                <th rowspan="2">Tunjangan<br>Lainnya</th>
                <th rowspan="2">Lain-lain<br>Pembulatan</th>
                <th rowspan="2">Gaji<br>Kotor</th>
                <th rowspan="2">Tunjangan<br>Perbaikan<br>Penghasilan<br>(TPP)/ Tunjangan</th>
                <th rowspan="2">Kode<br>Bayar</th>
                <th rowspan="2">Total<br>Penghasilan</th>
            </tr>
            <tr>
                <th>Struktural</th>
                <th>Fungsional</th>
            </tr>
            <tr style="background: #e2e8f0; font-size: 11px;">
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
                <th>(13)=(5)+..+(12)</th>
                <th>(14)</th>
                <th>(15)</th>
                <th>(16)=(13)+(14)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center"><strong>{{ $row['golongan'] }}</strong></td>
                <td class="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
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
                <td colspan="2" class="center">Jumlah Keseluruhan</td>
                <td class="center">{{ number_format($totals['jumlah'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($totals['jumlah'], 0, ',', '.') }}</td>
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
</div>
@endsection
