<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ ($tab ?? '') === 'master_skpd' ? 'Matriks Pemetaan Master SKPD SIMGAJI' : 'Laporan BNBA Perbaikan SKPD SIMGAJI' }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.2cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header-container {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header-container h1 {
            font-size: 11px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
        }
        .header-container h2 {
            font-size: 13px;
            font-weight: 800;
            margin: 3px 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .header-container p {
            font-size: 8.5px;
            margin: 2px 0 0 0;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 5px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8px;
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
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }
        .badge-purple {
            background-color: #f3e8ff;
            color: #7c3aed;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .footer-sig {
            margin-top: 20px;
            width: 100%;
        }
        .sig-box {
            float: right;
            width: 250px;
            text-align: center;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <h1>PEMERINTAH PROVINSI KALIMANTAN SELATAN &bull; BADAN KEUANGAN DAN ASET DAERAH</h1>
        @if(($tab ?? '') === 'master_skpd')
            <h2>MATRIKS PEMETAAN MASTER KODE & NAMA SKPD SIMGAJI TASPEN VS SIMPEG (ACUAN RESMI)</h2>
        @else
            <h2>LAPORAN NOMINATIF BY NAME BY ADDRESS (BNBA) PEMETAAN & USULAN PERBAIKAN SKPD SIMGAJI</h2>
        @endif
        <p>
            Acuan Dasar Kebenaran: <strong>SIMPEG / KONBELPEG</strong> &bull; Database SIMGAJI: <strong>{{ $activeFile['filename'] ?? '-' }}</strong> &bull; Waktu Cetak: {{ $printedAt }}
        </p>
    </div>

    @if(($tab ?? '') === 'master_skpd')
        <!-- TABEL MASTER SKPD -->
        <table style="width: 100%; border: none; margin-bottom: 6px; font-size: 8px;">
            <tr style="background: none;">
                <td style="border: none; padding: 0;">
                    <strong>Total Master SKPD SIMGAJI:</strong> {{ count($skpdMappings ?? []) }} Kode SKPD
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    Dicetak melalui Sistem Rekonsiliasi Realisasi Belanja Pegawai (KONBELPEG)
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 55px; text-align: center;">Kode SIMGAJI</th>
                    <th style="width: 180px;">Nama SKPD di SIMGAJI (Eksisting)</th>
                    <th style="width: 180px;">Nama Resmi Acuan (SIMPEG)</th>
                    <th style="width: 50px; text-align: right;">Total Pgw</th>
                    <th style="width: 45px; text-align: right;">Sesuai</th>
                    <th style="width: 45px; text-align: right;">Selisih</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                    <th>Rekomendasi Standardisasi / Tindakan SIMGAJI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skpdMappings ?? [] as $m)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center"><strong>{{ $m['kdskpd'] }}</strong></td>
                        <td>{{ $m['nama_simgaji'] }}</td>
                        <td style="font-weight: bold; color: #065f46;">{{ $m['nama_simpeg_dominan'] }}</td>
                        <td class="right"><strong>{{ number_format($m['total_pegawai'], 0, ',', '.') }}</strong></td>
                        <td class="right" style="color: #059669;">{{ number_format($m['total_sesuai'], 0, ',', '.') }}</td>
                        <td class="right" style="color: {{ $m['total_selisih'] > 0 ? '#dc2626' : '#64748b' }}; font-weight: bold;">
                            {{ number_format($m['total_selisih'], 0, ',', '.') }}
                        </td>
                        <td class="center">
                            @if($m['status_nama'] === 'SESUAI')
                                <span class="badge badge-success">SESUAI</span>
                            @elseif($m['status_nama'] === 'CABANG_DISDIK')
                                <span class="badge badge-purple">CABANG DISDIK</span>
                            @elseif($m['status_nama'] === 'ADA_SELISIH')
                                <span class="badge badge-danger">ADA SELISIH</span>
                            @else
                                <span class="badge badge-warning">{{ $m['status_nama'] }}</span>
                            @endif
                        </td>
                        <td>{{ $m['rekomendasi'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center" style="padding: 20px;">
                            Data master SKPD tidak tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <!-- TABEL DATA BNBA -->
        <table style="width: 100%; border: none; margin-bottom: 6px; font-size: 8px;">
            <tr style="background: none;">
                <td style="border: none; padding: 0;">
                    <strong>Total Data Ditampilkan:</strong> {{ number_format(count($items), 0, ',', '.') }} orang
                    @if(!empty($statusFilter) && $statusFilter !== 'semua')
                        &bull; <strong>Status:</strong> {{ $statusFilter }}
                    @endif
                    @if(!empty($kategori) && $kategori !== 'semua')
                        &bull; <strong>Kategori:</strong> {{ $kategori }}
                    @endif
                    @if(!empty($skpdFilter) && $skpdFilter !== 'semua')
                        &bull; <strong>Filter SKPD SIMPEG:</strong> {{ $skpdFilter }}
                    @endif
                    @if(!empty($simgajiFilter) && $simgajiFilter !== 'semua')
                        &bull; <strong>Filter SKPD SIMGAJI:</strong> {{ $simgajiFilter }}
                    @endif
                    @if($isLimited ?? false)
                        &bull; <span style="color: #dc2626; font-weight: bold;">(Dibatasi 500 data pertama untuk cetak PDF)</span>
                    @endif
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    Dicetak melalui Sistem Rekonsiliasi Realisasi Belanja Pegawai (KONBELPEG)
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 105px;">NIP / Golru</th>
                    <th style="width: 130px;">Nama Pegawai</th>
                    <th style="width: 120px;">Jabatan SIMPEG</th>
                    <th style="width: 45px; text-align: center;">Kd SIMGAJI</th>
                    <th style="width: 135px;">SKPD SIMGAJI (Eksisting)</th>
                    <th style="width: 110px;">Satker / Inputer</th>
                    <th style="width: 135px;">SKPD Resmi (SIMPEG)</th>
                    <th style="width: 45px; text-align: center;">Kd Rek.</th>
                    <th style="width: 135px;">UPTD / Satker Resmi</th>
                    <th>Rekomendasi Tindakan SIMGAJI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $row)
                    @php
                        $jenis = $row['jenis_selisih'] ?? '';
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $row['nip'] }}</strong><br>
                            <span style="color: #64748b;">Gol: {{ $row['golru'] }} ({{ $row['status_pegawai'] }})</span>
                        </td>
                        <td><strong>{{ $row['nama'] }}</strong></td>
                        <td>{{ $row['jabatan'] }}</td>
                        <!-- SIMGAJI -->
                        <td class="center" style="font-weight: bold; color: #dc2626;">{{ $row['kdskpd_simgaji'] }}</td>
                        <td>{{ $row['skpd_simgaji'] }}</td>
                        <td>
                            <span style="font-size: 7.5px;">Satker: {{ $row['kdsatker_simgaji'] }}</span><br>
                            <span style="color: #64748b; font-size: 7px;">Inputer: {{ $row['inputer_simgaji'] }}</span>
                        </td>
                        <!-- SIMPEG -->
                        <td style="font-weight: bold; color: #065f46;">{{ $row['skpd_simpeg'] }}</td>
                        <td class="center" style="font-weight: bold; color: #047857;">{{ $row['kdskpd_rekomendasi'] }}</td>
                        <td>{{ $row['upt_simpeg'] !== '-' ? $row['upt_simpeg'] : 'Induk SKPD' }}</td>
                        <!-- Rekomendasi -->
                        <td>
                            @if($jenis === 'Beda SKPD Induk')
                                <span class="badge badge-danger">BEDA SKPD INDUK</span>
                            @elseif($jenis === 'Beda Cabang Disdik')
                                <span class="badge badge-purple">BEDA CABANG DISDIK</span>
                            @elseif($jenis === 'Beda UPTD / Satker')
                                <span class="badge badge-info">BEDA UPTD/SATKER</span>
                            @elseif($jenis === 'Tidak Terdaftar di SIMPEG')
                                <span class="badge badge-warning">BELUM DI SIMPEG</span>
                            @elseif($jenis === 'Sesuai')
                                <span class="badge badge-success">SUDAH SESUAI</span>
                            @else
                                <span class="badge badge-warning">{{ $jenis }}</span>
                            @endif
                            <br>
                            <span style="font-weight: bold; font-size: 7.5px;">{{ $row['rekomendasi'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="center" style="padding: 20px;">
                            Tidak ada data perbedaan SKPD / Satker yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Tanda Tangan Pejabat -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Banjarbaru, {{ date('d F Y') }}<br>
            Pejabat Pengelola Data Belanja Pegawai</p>
            <br><br><br><br>
            <p><strong><u>___________________________________</u></strong><br>
            NIP. .....................................................</p>
        </div>
    </div>

</body>
</html>
