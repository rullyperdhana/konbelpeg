<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi & Proyeksi Iuran Tapera (ASN & Pemda)</title>
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
            margin-bottom: 14px;
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
        <h1>PEMERINTAH DAERAH</h1>
        <h2>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH (BPKAD)</h2>
        <p>SIMULASI & PROYEKSI TABUNGAN PERUMAHAN RAKYAT (TAPERA - PP NO. 21 TAHUN 2024)</p>
        <p>PERIODE: {{ strtoupper($periode ?: 'SEMUA PERIODE') }} @if($skpdFilter) &bull; SKPD: {{ strtoupper($skpdFilter) }} @endif</p>
    </div>

    @if($tab === 'rekap')
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">NO</th>
                <th rowspan="2">NAMA SKPD / SATUAN KERJA</th>
                <th rowspan="2" style="width: 45px;">JML ASN</th>
                <th colspan="3">KOMPONEN DASAR PERHITUNGAN</th>
                <th rowspan="2" style="width: 75px;">TOTAL DASAR (100%)</th>
                <th rowspan="2" style="width: 65px;">BEBAN PEMDA (0,5%)</th>
                <th rowspan="2" style="width: 65px;">POTONGAN ASN (2,5%)</th>
                <th rowspan="2" style="width: 70px;">TOTAL IURAN (3,0%)</th>
            </tr>
            <tr>
                <th style="width: 65px;">GAJI POKOK</th>
                <th style="width: 60px;">TUNJ. KELUARGA</th>
                <th style="width: 60px;">TUNJ. JABATAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totPeg = 0; $totGapok = 0; $totKlrg = 0; $totJab = 0; $totDasar = 0;
                $totPemda = 0; $totAsn = 0; $totAll = 0;
            @endphp
            @foreach($rekaps as $index => $row)
            @php
                $totPeg += $row->count_gaji;
                $totGapok += $row->total_gapok;
                $totKlrg += $row->total_tj_keluarga;
                $totJab += $row->total_tj_jabatan;
                $totDasar += $row->dasar_tapera;
                $totPemda += $row->tapera_pemda;
                $totAsn += $row->tapera_asn;
                $totAll += $row->tapera_total;
            @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $row->skpd ?: 'Lainnya' }}</td>
                <td class="center">{{ number_format($row->count_gaji) }}</td>
                <td class="money">{{ number_format($row->total_gapok, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row->total_tj_keluarga, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row->total_tj_jabatan, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold;">{{ number_format($row->dasar_tapera, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; color: #1e40af;">{{ number_format($row->tapera_pemda, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; color: #b45309;">{{ number_format($row->tapera_asn, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; color: #047857;">{{ number_format($row->tapera_total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="center">TOTAL KESELURUHAN</td>
                <td class="center">{{ number_format($totPeg) }}</td>
                <td class="money">{{ number_format($totGapok, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totKlrg, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totJab, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totDasar, 0, ',', '.') }}</td>
                <td class="money" style="color: #1e40af;">{{ number_format($totPemda, 0, ',', '.') }}</td>
                <td class="money" style="color: #b45309;">{{ number_format($totAsn, 0, ',', '.') }}</td>
                <td class="money" style="color: #047857;">{{ number_format($totAll, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 80px;">NIP</th>
                <th>NAMA PEGAWAI</th>
                <th>SKPD / SATUAN KERJA</th>
                <th style="width: 60px;">GAJI POKOK</th>
                <th style="width: 55px;">TUNJ. KLRG</th>
                <th style="width: 55px;">TUNJ. JAB</th>
                <th style="width: 65px;">DASAR TAPERA</th>
                <th style="width: 55px;">PEMDA (0,5%)</th>
                <th style="width: 55px;">ASN (2,5%)</th>
                <th style="width: 60px;">TOTAL (3,0%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($realisasis as $index => $item)
            @php
                $nip = $item->pegawai->nip ?? ($item->raw_data['nip'] ?? $item->raw_data['NIP'] ?? '-');
                $nama = $item->pegawai->nama ?? ($item->raw_data['nama'] ?? $item->raw_data['Nama'] ?? '-');
                $skpd = $item->pegawai->unitKerja->skpd ?? ($item->raw_data['SKPD'] ?? '-');
            @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center" style="font-family: monospace;">{{ $nip }}</td>
                <td>{{ $nama }}</td>
                <td>{{ $skpd }}</td>
                <td class="money">{{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($item->tunj_keluarga, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($item->tunj_jabatan, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold;">{{ number_format($item->dasar_tapera, 0, ',', '.') }}</td>
                <td class="money" style="color: #1e40af;">{{ number_format($item->simulasi_tapera_pk, 0, ',', '.') }}</td>
                <td class="money" style="color: #b45309;">{{ number_format($item->simulasi_tapera_asn, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; color: #047857;">{{ number_format($item->simulasi_tapera_total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Dicetak otomatis dari Sistem Informasi Belanja Pegawai pada {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
