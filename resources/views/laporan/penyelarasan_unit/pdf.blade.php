<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penyelarasan SKPD — UPTD — SATKER - {{ $tabTitle }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.2cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-container {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header-container h1 {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-container h2 {
            font-size: 14px;
            font-weight: 800;
            margin: 3px 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .header-container p {
            font-size: 9px;
            margin: 2px 0 0 0;
            color: #475569;
        }
        .meta-info {
            margin-bottom: 10px;
            font-size: 8.5px;
            background-color: #f8fafc;
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8.5px;
            text-align: left;
            text-transform: uppercase;
        }
        th.center, td.center {
            text-align: center;
        }
        th.right, td.right {
            text-align: right;
        }
        tr:nth-child(even) {
            background-color: #fafbfc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .badge-secondary {
            background-color: #f1f5f9;
            color: #475569;
        }
        .footer-sig {
            margin-top: 20px;
            width: 100%;
        }
        .sig-box {
            float: right;
            width: 220px;
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <h1>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h1>
        <h2>LAPORAN PENYELARASAN SKPD — UPTD — SATKER</h2>
        <p>Kategori: {{ $tabTitle }} | Database Acuan: {{ $activeFile['filename'] ?? '-' }}</p>
    </div>

    <table style="width: 100%; border: none; margin-bottom: 8px; font-size: 8.5px;">
        <tr style="background: none;">
            <td style="border: none; padding: 0;">
                <strong>Database DBF:</strong> {{ $activeFile['filename'] ?? '-' }} ({{ $activeFile['size'] ?? '-' }})<br>
                <strong>Total Data:</strong> {{ number_format(count($items), 0, ',', '.') }} baris
                @if(!empty($skpdFilter) && $skpdFilter !== 'semua')
                    &bull; <strong>Filter SKPD:</strong> {{ $skpdFilter }}
                @endif
                @if(!empty($statusFilter) && $statusFilter !== 'semua')
                    &bull; <strong>Filter Status:</strong> {{ $statusFilter }}
                @endif
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                <strong>Waktu Ekspor:</strong> {{ date('d/m/Y H:i') }} WITA<br>
                <strong>Dicetak Oleh:</strong> Sistem Informasi Realisasi Belanja Pegawai (SiReKa)
            </td>
        </tr>
    </table>

    @if($activeTab === 'rekap_skpd')
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 25px;">No</th>
                    <th class="center" style="width: 50px;">Kd SKPD</th>
                    <th>Nama SKPD di SIMGAJI</th>
                    <th>Nomenklatur di SIMPEG (Master)</th>
                    <th class="center" style="width: 55px;">SIMGAJI</th>
                    <th class="center" style="width: 55px;">SIMPEG</th>
                    <th class="center" style="width: 45px;">Selisih</th>
                    <th class="center" style="width: 50px;">Satker</th>
                    <th class="center" style="width: 95px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="center"><strong>{{ $row['kdskpd'] }}</strong></td>
                        <td>
                            <strong>{{ $row['nama_simgaji'] }}</strong>
                            @if(!empty($row['inputers']) && $row['inputers'] !== '-')
                                <div style="font-size: 7.5px; color: #64748b;">Inputer: {{ $row['inputers'] }}</div>
                            @endif
                        </td>
                        <td>{{ $row['nama_simpeg'] }}</td>
                        <td class="center">{{ number_format($row['jml_simgaji'], 0, ',', '.') }}</td>
                        <td class="center">{{ number_format($row['jml_simpeg'], 0, ',', '.') }}</td>
                        <td class="center">
                            @if($row['selisih'] == 0)
                                <span style="color: #15803d; font-weight: bold;">0</span>
                            @elseif($row['selisih'] > 0)
                                <span style="color: #1d4ed8; font-weight: bold;">+{{ $row['selisih'] }}</span>
                            @else
                                <span style="color: #dc2626; font-weight: bold;">{{ $row['selisih'] }}</span>
                            @endif
                        </td>
                        <td class="center">{{ $row['satker_count'] }}</td>
                        <td class="center">
                            @if($row['status'] === 'sesuai')
                                <span class="badge badge-success">Sesuai</span>
                            @elseif($row['status'] === 'cabang_wilayah')
                                <span class="badge badge-info">Cabang Disdik</span>
                            @elseif($row['status'] === 'beda_jumlah')
                                <span class="badge badge-warning">Selisih Pegawai</span>
                            @elseif($row['status'] === 'tidak_terpetakan')
                                <span class="badge badge-danger">Tidak Terpetakan</span>
                            @else
                                <span class="badge badge-secondary">Belum di SIMGAJI</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center" style="padding: 15px;">Tidak ada data rekapitulasi SKPD.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($activeTab === 'pemetaan_satker')
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 25px;">No</th>
                    <th class="center" style="width: 45px;">Kd SKPD</th>
                    <th style="width: 95px;">Kode Satker SIMGAJI</th>
                    <th>Nama SKPD di SIMGAJI</th>
                    <th class="center" style="width: 45px;">Pegawai</th>
                    <th>SKPD di SIMPEG</th>
                    <th>UPTD di SIMPEG</th>
                    <th class="center" style="width: 85px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="center"><strong>{{ $row['kdskpd'] }}</strong></td>
                        <td>
                            <code>{{ $row['kdsatker'] }}</code>
                            @if(!empty($row['inputer']) && $row['inputer'] !== '-')
                                <div style="font-size: 7.5px; color: #64748b;">({{ $row['inputer'] }})</div>
                            @endif
                        </td>
                        <td>{{ $row['nama_skpd_simgaji'] }}</td>
                        <td class="center"><strong>{{ number_format($row['jml_simgaji'], 0, ',', '.') }}</strong></td>
                        <td>{{ $row['skpd_simpeg'] }}</td>
                        <td>
                            @if($row['upt_simpeg'] !== '-')
                                <strong>{{ $row['upt_simpeg'] }}</strong>
                                @if($row['status_satker'] === 'multi_upt')
                                    <div style="font-size: 7.5px; color: #b45309;">
                                        Bercampur {{ $row['upt_count'] }} UPTD: {{ Str::limit($row['detail_upts'], 65) }}
                                    </div>
                                @endif
                            @else
                                <span style="color: #64748b; font-style: italic;">(Tingkat Induk / Tanpa UPT)</span>
                            @endif
                        </td>
                        <td class="center">
                            @if($row['status_satker'] === 'sesuai_upt')
                                <span class="badge badge-success">1 UPTD Jelas</span>
                            @elseif($row['status_satker'] === 'multi_upt')
                                <span class="badge badge-warning">Multi UPTD ({{ $row['upt_count'] }})</span>
                            @elseif($row['status_satker'] === 'induk_skpd')
                                <span class="badge badge-info">Tingkat Induk</span>
                            @else
                                <span class="badge badge-danger">Belum Terpetakan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="center" style="padding: 15px;">Tidak ada data pemetaan satker.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 25px;">No</th>
                    <th style="width: 100px;">NIP</th>
                    <th style="width: 120px;">Nama Pegawai</th>
                    <th>Penempatan di SIMPEG (Master)</th>
                    <th>Penempatan di SIMGAJI</th>
                    <th class="center" style="width: 90px;">Jenis Selisih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td><strong>{{ $row['nip'] }}</strong></td>
                        <td><strong>{{ $row['nama'] }}</strong></td>
                        <td>
                            <div><strong>{{ $row['skpd_simpeg'] }}</strong></div>
                            @if($row['upt_simpeg'] !== '-')
                                <div style="font-size: 8px; color: #1d4ed8;">UPT: {{ $row['upt_simpeg'] }}</div>
                            @endif
                        </td>
                        <td>
                            <div><strong>{{ $row['skpd_simgaji'] }}</strong> ({{ $row['kdskpd_simgaji'] }})</div>
                            <div style="font-size: 8px; color: #475569;">
                                Satker: {{ $row['kdsatker_simgaji'] }} ({{ $row['inputer_simgaji'] }})
                            </div>
                        </td>
                        <td class="center">
                            @if($row['jenis_selisih'] === 'Beda SKPD Induk')
                                <span class="badge badge-danger">Beda SKPD</span>
                            @else
                                <span class="badge badge-warning">Beda UPTD</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center" style="padding: 15px;">Tidak ada data selisih penempatan pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Tanda Tangan Resmi -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Banjarbaru, {{ date('d F Y') }}<br>
            Petugas Pengelola Kepegawaian & Belanja Pegawai,</p>
            <br><br><br>
            <p><strong>_____________________________</strong><br>
            NIP. ......................................................</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
