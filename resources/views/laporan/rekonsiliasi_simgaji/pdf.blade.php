<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekonsiliasi SIMGAJI - {{ $tabTitle }}</title>
    <style>
        @page {
            margin: 1.5cm 1cm 1.5cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-container {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-container h1 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-container h2 {
            font-size: 15px;
            font-weight: 800;
            margin: 3px 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .header-container p {
            font-size: 9.5px;
            margin: 2px 0 0 0;
            color: #475569;
        }
        .meta-info {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            background-color: #f8fafc;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }
        th.center, td.center {
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #fcfcfd;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-primary {
            background-color: #e0e7ff;
            color: #4338ca;
        }
        .footer-sig {
            margin-top: 25px;
            width: 100%;
        }
        .sig-box {
            float: right;
            width: 220px;
            text-align: center;
            font-size: 9.5px;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <h1>Pemerintah Provinsi Kalimantan Selatan</h1>
        <h2>Laporan Rekonsiliasi Database Master SIMGAJI</h2>
        <p>
            Kategori: <strong>{{ $tabTitle }}</strong> &bull;
            Database Acuan: <strong>{{ $activeFile['filename'] ?? '-' }}</strong>
            @if($activeTab === 'beda_pangkat')
                &bull; Status Kepegawaian: <strong>{{ ($statusPensiun ?? 'semua') === 'aktif' ? 'Hanya Pegawai Aktif' : (($statusPensiun ?? 'semua') === 'pensiun' ? 'Hanya Pensiunan' : 'Semua Status') }}</strong>
                &bull; Status SK SIMGAJI: <strong>{{ ($statusSk ?? 'semua') === 'belum_diinput' ? 'Belum Diinput di SIMGAJI' : (($statusSk ?? 'semua') === 'sudah_terjadwal' ? 'Sudah Terjadwal di SIMGAJI' : 'Semua Status SK') }}</strong>
            @endif
        </p>
    </div>

    <table style="width: 100%; margin-bottom: 10px; border: none;">
        <tr style="border: none; background: transparent;">
            <td style="border: none; padding: 0; font-size: 9px; color: #475569;">
                Total Data: <strong>{{ number_format($items->count(), 0, ',', '.') }} baris</strong>
                @if($search)
                    (Filter Pencarian: <em>"{{ $search }}"</em>)
                @endif
            </td>
            <td style="border: none; padding: 0; text-align: right; font-size: 9px; color: #475569;">
                Waktu Cetak: <strong>{{ date('d F Y H:i') }} WITA</strong>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            @if($activeTab === 'aktif_baru')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th class="center" style="width: 60px;">Gol/Ruang</th>
                    <th class="center" style="width: 65px;">Kd SKPD</th>
                    <th style="width: 140px;">Satker SIMGAJI</th>
                    <th style="width: 130px;">Instansi Inputer</th>
                    <th class="center" style="width: 70px;">TMT Stop</th>
                </tr>
            @elseif($activeTab === 'beda_pangkat')
                <tr>
                    <th class="center" style="width: 25px;">No</th>
                    <th style="width: 110px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD / Unit Kerja</th>
                    <th class="center" style="width: 75px;">Pangkat Aplikasi</th>
                    <th class="center" style="width: 75px;">Pangkat SIMGAJI</th>
                    <th class="center" style="width: 75px;">Status SIMGAJI</th>
                    <th style="width: 145px;">Status SK di SIMGAJI</th>
                </tr>
            @elseif($activeTab === 'beda_skpd')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD & Satker di Master Aplikasi</th>
                    <th>SKPD & Satker di SIMGAJI</th>
                    <th style="width: 140px;">SKPD di TPP</th>
                </tr>
            @elseif($activeTab === 'beda_jabatan')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD</th>
                    <th>Jabatan di Master Aplikasi</th>
                    <th>Jabatan pada Pembayaran TPP</th>
                    <th style="width: 130px;">Status SIMGAJI</th>
                </tr>
            @elseif($activeTab === 'beda_lahir')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD / Unit Kerja</th>
                    <th class="center" style="width: 120px;">Tgl Lahir di Aplikasi</th>
                    <th class="center" style="width: 120px;">Tgl Lahir di SIMGAJI</th>
                </tr>
            @elseif($activeTab === 'pensiunan')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pensiunan / Mantan Pegawai</th>
                    <th class="center" style="width: 70px;">Gol/Ruang</th>
                    <th class="center" style="width: 80px;">TMT Berhenti</th>
                    <th>Instansi Terakhir (Inputer)</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            @elseif($activeTab === 'paruh_waktu')
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th>SKPD / Unit Kerja</th>
                    <th class="center" style="width: 70px;">Gol/Ruang</th>
                    <th style="width: 120px;">Status Pembayaran</th>
                </tr>
            @else
                <tr>
                    <th class="center" style="width: 30px;">No</th>
                    <th style="width: 125px;">NIP</th>
                    <th>Nama Pegawai</th>
                    <th class="center" style="width: 70px;">Gol/Ruang</th>
                    <th style="width: 110px;">Status</th>
                    <th class="center" style="width: 80px;">TMT Berhenti</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse($items as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td><strong>{{ $row['nip'] }}</strong></td>
                    <td style="font-weight: 600;">{{ $row['nama'] }}</td>

                    @if($activeTab === 'aktif_baru')
                        <td class="center">{{ $row['golru_converted'] }}</td>
                        <td class="center">{{ $row['kdskpd'] }}</td>
                        <td><small>{{ $row['kdsatker'] ?? '-' }}</small></td>
                        <td>{{ $row['inputer'] }}</td>
                        <td class="center">{{ $row['tmtstop'] ?: '-' }}</td>
                    @elseif($activeTab === 'beda_pangkat')
                        <td><small>{{ $row['skpd'] }}</small></td>
                        <td class="center"><span class="badge badge-danger">{{ $row['golru_app'] }}</span></td>
                        <td class="center"><span class="badge badge-success">{{ $row['pangkat_simgaji'] }}</span></td>
                        <td class="center">
                            @if(!empty($row['is_pensiun']))
                                <span class="badge badge-danger">{{ $row['status_kepegawaian'] ?? 'Pensiun' }}</span>
                            @else
                                <span class="badge badge-success">Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if(($row['status_sk'] ?? '') === 'sudah_terjadwal')
                                <span class="badge badge-success">Terjadwal di SIMGAJI</span>
                                @if(!empty($row['sk_info']['nomorskep']))
                                    <br><small style="color: #475569; font-size: 8px;">No: {{ $row['sk_info']['nomorskep'] }}</small>
                                @endif
                                @if(!empty($row['sk_info']['tmtgaji']))
                                    <br><small style="color: #2563eb; font-size: 8px;">TMT Gaji: {{ $row['sk_info']['tmtgaji'] }}</small>
                                @endif
                            @else
                                <span class="badge badge-danger">Belum Diinput</span>
                            @endif
                        </td>
                    @elseif($activeTab === 'beda_skpd')
                        <td>
                            <strong style="color: #b91c1c;">{{ $row['skpd_app'] }}</strong>
                            @if(!empty($row['satker_app']) && $row['satker_app'] !== '-')
                                <br><small style="color: #64748b;">Satker: {{ $row['satker_app'] }}</small>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #047857;">{{ $row['skpd_simgaji'] }}</strong>
                            <br><small style="color: #64748b;">Kd: {{ $row['kdskpd_simgaji'] }} | {{ $row['kdsatker_simgaji'] ?? '-' }}</small>
                        </td>
                        <td>{{ $row['skpd_tpp'] }}</td>
                    @elseif($activeTab === 'beda_jabatan')
                        <td><small>{{ $row['skpd'] }}</small></td>
                        <td><span class="badge badge-danger">{{ $row['jabatan_app'] }}</span></td>
                        <td><span class="badge badge-success">{{ $row['jabatan_tpp'] }}</span></td>
                        <td><small>{{ $row['status_simgaji'] }}</small></td>
                    @elseif($activeTab === 'beda_lahir')
                        <td><small>{{ $row['skpd'] }}</small></td>
                        <td class="center">{{ $row['tgl_app'] }}</td>
                        <td class="center"><strong>{{ $row['tgl_simgaji'] }}</strong></td>
                    @elseif($activeTab === 'pensiunan')
                        <td class="center">{{ $row['golru'] ?? '-' }}</td>
                        <td class="center">{{ $row['tmtstop'] ?? '-' }}</td>
                        <td>{{ $row['inputer'] ?? '-' }}</td>
                        <td>{{ $row['status'] ?? '-' }}</td>
                    @elseif($activeTab === 'paruh_waktu')
                        <td><small>{{ $row['skpd'] }}</small></td>
                        <td class="center">{{ $row['golru'] ?? '-' }}</td>
                        <td>{{ $row['status'] ?? '-' }}</td>
                    @else
                        <td class="center">{{ $row['golru'] ?? ($row['golru_converted'] ?? '-') }}</td>
                        <td>{{ $row['status'] ?? '-' }}</td>
                        <td class="center">{{ $row['tmtstop'] ?? '-' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center" style="padding: 20px; color: #64748b;">
                        Tidak ada data pada kategori ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-box">
            <div>Banjarbaru, {{ date('d F Y') }}</div>
            <div style="margin-top: 4px; font-weight: bold;">Petugas Rekonsiliasi Data,</div>
            <div style="margin-top: 55px; font-weight: bold; text-decoration: underline;">Admin Keuangan</div>
            <div>NIP. ........................................</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
