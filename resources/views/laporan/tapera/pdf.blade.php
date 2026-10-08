<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi & Proyeksi Iuran Tapera (ASN & Pemda)</title>
    <style>
        @page {
            margin: 10mm 8mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 7.5px;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .header h1 {
            margin: 0;
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0 0 0;
            font-size: 10.5px;
            font-weight: normal;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 3.5px 4.5px;
            font-size: 7px;
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
            font-size: 6.8px;
        }
        .center {
            text-align: center;
        }
        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 12px;
            font-size: 7px;
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
        <p>
            PERIODE: {{ strtoupper($periode ?: 'SEMUA PERIODE') }}
            &bull; KATEGORI: {{ strtoupper($kategoriFilter === 'all' ? 'SEMUA KATEGORI (PNS, PPPK-FULL, PPPK-PARUH)' : ($kategoriFilter === 'pns' ? 'HANYA PNS' : ($kategoriFilter === 'pppk' ? 'HANYA PPPK FULL WAKTU' : 'HANYA PPPK PARUH WAKTU'))) }}
            @if($skpdFilter) &bull; SKPD: {{ strtoupper($skpdFilter) }} @endif
        </p>
    </div>

    @if($tab === 'rekap')
        @if(($kategoriFilter ?? 'all') === 'all')
        <!-- MATRIKS PEMISAHAN PNS, PPPK FULL WAKTU, & PPPK PARUH WAKTU -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">NO</th>
                    <th rowspan="2">NAMA SKPD / SATUAN KERJA</th>
                    <th colspan="4">JUMLAH ASN</th>
                    <th colspan="4">DASAR TAPERA (100%)</th>
                    <th colspan="4">BEBAN PEMDA (0,5%)</th>
                    <th colspan="4">POTONGAN ASN (2,5%)</th>
                    <th rowspan="2" style="width: 50px;">TOTAL (3%)</th>
                </tr>
                <tr>
                    <th style="width: 24px;">PNS</th>
                    <th style="width: 24px;">PPPK</th>
                    <th style="width: 24px;">PW</th>
                    <th style="width: 28px;">TOT</th>

                    <th style="width: 44px;">PNS</th>
                    <th style="width: 44px;">PPPK</th>
                    <th style="width: 44px;">PW</th>
                    <th style="width: 48px;">TOT</th>

                    <th style="width: 36px;">PNS</th>
                    <th style="width: 36px;">PPPK</th>
                    <th style="width: 36px;">PW</th>
                    <th style="width: 40px;">TOT</th>

                    <th style="width: 36px;">PNS</th>
                    <th style="width: 36px;">PPPK</th>
                    <th style="width: 36px;">PW</th>
                    <th style="width: 40px;">TOT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekaps as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->skpd ?: 'Lainnya' }}</td>

                    <td class="center">{{ number_format($row->count_pns) }}</td>
                    <td class="center">{{ number_format($row->count_pppk) }}</td>
                    <td class="center">{{ number_format($row->count_pppk_pw) }}</td>
                    <td class="center" style="font-weight: bold;">{{ number_format($row->count_total) }}</td>

                    <td class="money">{{ number_format($row->dasar_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->dasar_pppk, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->dasar_pppk_pw, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: bold;">{{ number_format($row->dasar_total, 0, ',', '.') }}</td>

                    <td class="money">{{ number_format($row->pemda_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->pemda_pppk, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->pemda_pppk_pw, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: bold; color: #1e40af;">{{ number_format($row->pemda_total, 0, ',', '.') }}</td>

                    <td class="money">{{ number_format($row->asn_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->asn_pppk, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($row->asn_pppk_pw, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: bold; color: #b45309;">{{ number_format($row->asn_total, 0, ',', '.') }}</td>

                    <td class="money" style="font-weight: bold; color: #047857;">{{ number_format($row->total_all, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="center">GRAND TOTAL</td>

                    <td class="center">{{ number_format($rekaps->sum('count_pns')) }}</td>
                    <td class="center">{{ number_format($rekaps->sum('count_pppk')) }}</td>
                    <td class="center">{{ number_format($rekaps->sum('count_pppk_pw')) }}</td>
                    <td class="center">{{ number_format($rekaps->sum('count_total')) }}</td>

                    <td class="money">{{ number_format($rekaps->sum('dasar_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('dasar_pppk'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('dasar_pppk_pw'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('dasar_total'), 0, ',', '.') }}</td>

                    <td class="money">{{ number_format($rekaps->sum('pemda_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('pemda_pppk'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('pemda_pppk_pw'), 0, ',', '.') }}</td>
                    <td class="money" style="color: #1e40af;">{{ number_format($rekaps->sum('pemda_total'), 0, ',', '.') }}</td>

                    <td class="money">{{ number_format($rekaps->sum('asn_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('asn_pppk'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('asn_pppk_pw'), 0, ',', '.') }}</td>
                    <td class="money" style="color: #b45309;">{{ number_format($rekaps->sum('asn_total'), 0, ',', '.') }}</td>

                    <td class="money" style="color: #047857;">{{ number_format($rekaps->sum('total_all'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <!-- TABEL KATEGORI TERTENTU -->
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
                @foreach($rekaps as $index => $row)
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
                    <td class="center">{{ number_format($rekaps->sum('count_gaji')) }}</td>
                    <td class="money">{{ number_format($rekaps->sum('total_gapok'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('total_tj_keluarga'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('total_tj_jabatan'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('dasar_tapera'), 0, ',', '.') }}</td>
                    <td class="money" style="color: #1e40af;">{{ number_format($rekaps->sum('tapera_pemda'), 0, ',', '.') }}</td>
                    <td class="money" style="color: #b45309;">{{ number_format($rekaps->sum('tapera_asn'), 0, ',', '.') }}</td>
                    <td class="money" style="color: #047857;">{{ number_format($rekaps->sum('tapera_total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif
    @else
    <!-- RINCI NOMINATIF PDF -->
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 75px;">NIP</th>
                <th>NAMA PEGAWAI</th>
                <th style="width: 55px;">KATEGORI</th>
                <th>SKPD / SATUAN KERJA</th>
                <th style="width: 55px;">GAJI POKOK</th>
                <th style="width: 50px;">TUNJ. KLRG</th>
                <th style="width: 50px;">TUNJ. JAB</th>
                <th style="width: 60px;">DASAR TAPERA</th>
                <th style="width: 50px;">PEMDA (0,5%)</th>
                <th style="width: 50px;">ASN (2,5%)</th>
                <th style="width: 55px;">TOTAL (3%)</th>
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
                <td class="center">{{ $item->kategori_asn }}</td>
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
