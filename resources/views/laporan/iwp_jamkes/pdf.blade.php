<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekonsiliasi IWP & BPJS Kesehatan (Jamkes)</title>
    <style>
        @page {
            margin: 12mm 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 11px;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 8.5px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            font-size: 7.5px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .money {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5px;
        }
        .center {
            text-align: center;
        }
        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .highlight-col {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .summary-box {
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            margin-top: 15px;
            font-size: 7.5px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH PROVINSI</h1>
        <h2>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH (BPKAD)</h2>
        <p>REKONSILIASI IURAN WAJIB PEGAWAI (IWP) & JAMINAN KESEHATAN (JAMKES BPJS) &bull; PERIODE: {{ strtoupper($periode ?: 'SEMUA PERIODE') }}</p>
        @if($skpdFilter)
            <p style="font-weight: bold;">SKPD / UNIT KERJA: {{ strtoupper($skpdFilter) }}</p>
        @endif
    </div>

    @if($tipeLaporan === 'rekap')
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">NO</th>
                    <th rowspan="2">NAMA SKPD / UNIT KERJA</th>
                    <th colspan="3">IWP DARI GAJI (SIMGAJI)</th>
                    <th rowspan="2">IWP 1% (TPP)</th>
                    <th rowspan="2" style="background-color: #e0e7ff;">GRAND TOTAL JAMKES<br><span style="font-size: 6.5px; font-weight: normal;">(Gaji 2% + TPP 1%)</span></th>
                    <th rowspan="2">TOTAL IWP KESELURUHAN<br><span style="font-size: 6.5px; font-weight: normal;">(Gaji 10% + TPP)</span></th>
                    <th colspan="3">JUMLAH PEGAWAI</th>
                </tr>
                <tr>
                    <th>IWP 2% (JAMKES)</th>
                    <th>IWP 8% (PENSIUN & THT)</th>
                    <th>TOTAL IWP GAJI</th>
                    <th style="width: 25px;">GAJI</th>
                    <th style="width: 25px;">TPP</th>
                    <th style="width: 28px;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->skpd }}</td>
                    <td class="money">{{ number_format($row->iwp_gaji_jamkes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->iwp_gaji_pensiun, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->total_iwp_gaji, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->iwp_tpp_jamkes, 0, ',', '.') }}</td>
                    <td class="money highlight-col" style="background-color: #f1f5f9;">{{ number_format($row->total_jamkes, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: bold;">{{ number_format($row->total_seluruh_iwp, 0, ',', '.') }}</td>
                    <td class="center">{{ $row->count_gaji }}</td>
                    <td class="center">{{ $row->count_tpp }}</td>
                    <td class="center" style="font-weight: bold;">{{ $row->total_pegawai }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="center">Data rekapitulasi tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="center">TOTAL KESELURUHAN</td>
                    <td class="money">{{ number_format($rekaps->sum('iwp_gaji_jamkes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('iwp_gaji_pensiun'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('total_iwp_gaji'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('iwp_tpp_jamkes'), 0, ',', '.') }}</td>
                    <td class="money" style="background-color: #cbd5e1;">{{ number_format($rekaps->sum('total_jamkes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('total_seluruh_iwp'), 0, ',', '.') }}</td>
                    <td class="center">{{ $rekaps->sum('count_gaji') }}</td>
                    <td class="center">{{ $rekaps->sum('count_tpp') }}</td>
                    <td class="center">{{ $rekaps->sum('total_pegawai') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @else
        <!-- Rinci Mode -->
        <table>
            <thead>
                <tr>
                    <th style="width: 20px;">NO</th>
                    <th style="width: 75px;">NIP</th>
                    <th>NAMA PEGAWAI</th>
                    <th style="width: 35px;">STATUS</th>
                    <th>UNIT KERJA / SKPD</th>
                    <th>GAJI POKOK</th>
                    <th>IWP GAJI 2% (JAMKES)</th>
                    <th>IWP GAJI 8% (PENSIUN)</th>
                    <th>TOTAL IWP GAJI</th>
                    <th>IWP TPP 1%</th>
                    <th style="background-color: #e0e7ff;">TOTAL JAMKES</th>
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
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $pegawai->nip ?? '-' }}</td>
                    <td>{{ $pegawai->nama ?? 'Tidak Diketahui' }}</td>
                    <td class="center">{{ $pegawai->status_pegawai ?? '-' }}</td>
                    <td>{{ $pegawai->unitKerja?->skpd ?? '-' }}</td>
                    <td class="money">{{ number_format($gajiPokok, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($iwpGajiJamkes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($iwpGajiPensiun, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($totalIwpGaji, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($iwpTppJamkes, 0, ',', '.') }}</td>
                    <td class="money highlight-col">{{ number_format($totalJamkes, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="center">Data rincian tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak secara otomatis melalui Sistem Informasi Realisasi Belanja Pegawai (KONBELPEG) pada {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
