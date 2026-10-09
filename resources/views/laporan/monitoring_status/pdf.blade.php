<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Status Pegawai & Pensiun</title>
    <style>
        @page {
            margin: 10mm 8mm;
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
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .header h1 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0 0 0;
            font-size: 11px;
            font-weight: normal;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #475569;
        }
        .kpi-bar {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px;
        }
        .kpi-item {
            display: table-cell;
            text-align: center;
            font-size: 8px;
            border-right: 1px solid #e2e8f0;
        }
        .kpi-item:last-child {
            border-right: none;
        }
        .kpi-item strong {
            font-size: 10px;
            display: block;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 4px 5px;
            font-size: 7.5px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            width: 100%;
            display: table;
        }
        .footer-cell {
            display: table-cell;
            width: 50%;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pemerintah Daerah Kabupaten Tapin</h1>
        <h2>Laporan Monitoring Status Pegawai & Pensiun (SIMGAJI Taspen)</h2>
        <p>
            TAB: {{ strtoupper($tab) }} &bull; SUMBER: DATABASE MASTER SIMGAJI ({{ $summary['active_file_name'] ?? 'MST_PGW' }}) &bull; CETAK: {{ date('d-m-Y H:i') }}
        </p>
    </div>

    <!-- Ringkasan Eksekutif -->
    <div class="kpi-bar">
        <div class="kpi-item">
            Total Arsip
            <strong>{{ number_format($summary['total_records'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            ASN Aktif (PNS/PPPK)
            <strong>{{ number_format(($summary['pns_aktif'] ?? 0) + ($summary['pppk_aktif'] ?? 0), 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            Pensiun (BUP/APS)
            <strong style="color: #991b1b;">{{ number_format(($summary['pensiun_bup'] ?? 0) + ($summary['pensiun_sendiri'] ?? 0), 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            Meninggal Dunia
            <strong>{{ number_format($summary['meninggal'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            Pindah / Keluar / CLTN
            <strong>{{ number_format(($summary['pindah'] ?? 0) + ($summary['keluar'] ?? 0) + ($summary['cuti'] ?? 0), 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            Proyeksi Pensiun (1 Thn)
            <strong>{{ number_format($summary['proyeksi_12_bulan'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="kpi-item">
            Anomali Pasca Stop
            <strong style="color: #dc2626;">{{ number_format($summary['total_anomali'] ?? 0, 0, ',', '.') }}</strong>
        </div>
    </div>

    @if($tab === 'rekap')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Nama SKPD / Satuan Kerja</th>
                    <th style="width: 50px;">PNS Aktif</th>
                    <th style="width: 50px;">PPPK Aktif</th>
                    <th style="width: 55px;">Pensiun BUP</th>
                    <th style="width: 50px;">Meninggal</th>
                    <th style="width: 45px;">Pindah</th>
                    <th style="width: 45px;">Keluar</th>
                    <th style="width: 45px;">Cuti/MPP</th>
                    <th style="width: 55px;">Proyeksi 26-27</th>
                    <th style="width: 55px;">Total Rekam</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tPns = 0; $tPppk = 0; $tBup = 0; $tMeninggal = 0; $tPindah = 0; $tKeluar = 0; $tCuti = 0; $tProyeksi = 0; $tTotal = 0;
                @endphp
                @foreach($items as $idx => $r)
                @php
                    $cutiTotal = $r['cuti_cltn'] + $r['mpp'] + $r['pensiun_sendiri'];
                    $tPns += $r['pns_aktif']; $tPppk += $r['pppk_aktif']; $tBup += $r['pensiun_bup'];
                    $tMeninggal += $r['meninggal']; $tPindah += $r['pindah']; $tKeluar += $r['keluar'];
                    $tCuti += $cutiTotal; $tProyeksi += $r['proyeksi_pensiun']; $tTotal += $r['total'];
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $r['skpd'] }}</td>
                    <td class="text-right">{{ number_format($r['pns_aktif'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['pppk_aktif'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #991b1b; font-weight: bold;">{{ number_format($r['pensiun_bup'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['meninggal'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['pindah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($cutiTotal, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #0369a1; font-weight: bold;">{{ number_format($r['proyeksi_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($r['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-right">{{ number_format($tPns, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tPppk, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #991b1b;">{{ number_format($tBup, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tMeninggal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tPindah, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tKeluar, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tCuti, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #0369a1;">{{ number_format($tProyeksi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @elseif($tab === 'nominatif')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 105px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th style="width: 35px;">Golru</th>
                    <th>SKPD / Unit Kerja</th>
                    <th style="width: 110px;">Status di SIMGAJI</th>
                    <th style="width: 65px;">TMT Stop</th>
                    <th>Catatan SK Mutasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items->take(500) as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $r['nip'] }}</td>
                    <td style="font-weight: bold;">{{ $r['nama'] }}</td>
                    <td class="text-center">{{ $r['golru'] ?: '-' }}</td>
                    <td>{{ $r['skpd'] }}</td>
                    <td>{{ $r['status_label'] }}</td>
                    <td class="text-center" style="color: #991b1b; font-weight: bold;">{{ $r['tmtstop'] ?: '-' }}</td>
                    <td style="font-size: 7px; color: #475569;">{{ $r['catatan'] ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($tab === 'proyeksi')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 105px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th style="width: 45px;">Jenis ASN</th>
                    <th style="width: 35px;">Golru</th>
                    <th>SKPD / Unit Kerja</th>
                    <th style="width: 35px;">BUP</th>
                    <th style="width: 70px;">TMT Pensiun</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items->take(500) as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $r['nip'] }}</td>
                    <td style="font-weight: bold;">{{ $r['nama'] }}</td>
                    <td class="text-center">{{ $r['status_asn'] }}</td>
                    <td class="text-center">{{ $r['golru'] ?: '-' }}</td>
                    <td>{{ $r['skpd'] }}</td>
                    <td class="text-center">{{ $r['bup'] }} Thn</td>
                    <td class="text-center" style="color: #0369a1; font-weight: bold;">{{ $r['tmt_pensiun'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 105px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD Asal</th>
                    <th style="width: 100px;">Status SIMGAJI</th>
                    <th style="width: 65px;">TMT Stop</th>
                    <th>Indikasi Anomali & Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center" style="font-family: monospace; color: #991b1b; font-weight: bold;">{{ $r['nip'] }}</td>
                    <td style="font-weight: bold;">{{ $r['nama'] }}</td>
                    <td>{{ $r['skpd'] }}</td>
                    <td>{{ $r['status_simgaji'] }}</td>
                    <td class="text-center" style="color: #991b1b; font-weight: bold;">{{ $r['tmtstop'] ?: '-' }}</td>
                    <td style="font-size: 7px;">
                        <strong>{{ $r['indikasi'] }}</strong>
                        @if(!empty($r['catatan']))
                            <br><span style="color: #475569;">Catatan: {{ $r['catatan'] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div class="footer-cell">
            Dicetak melalui Sistem Informasi Realisasi Belanja Pegawai (Konbelpeg)<br>
            BKAD Kabupaten Tapin
        </div>
        <div class="footer-cell" style="text-align: right;">
            Halaman 1 &bull; Dokumen Pengawasan Kepegawaian
        </div>
    </div>
</body>
</html>
