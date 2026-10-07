<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Audit Tunjangan Keluarga SIMGAJI</title>
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
            font-size: 10.5px;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 8px;
            color: #475569;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .summary-box table {
            margin-bottom: 0;
            border: none;
        }
        .summary-box td {
            border: none;
            padding: 2px 4px;
            font-size: 8px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            font-size: 7.5px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .footer {
            margin-top: 15px;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            width: 50%;
            font-size: 7.5px;
            color: #64748b;
        }
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 7.5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Pemerintah Provinsi Kalimantan Selatan</h1>
        <h2>Laporan Hasil Audit & Rekonsiliasi Tunjangan Keluarga (SIMGAJI Taspen)</h2>
        <p>Kategori: 
            <strong>
                @if($tab === 'pasangan')
                    Pasangan ASN Saling Menunjang (Dobel Suami-Istri 10%)
                @elseif($tab === 'kuota')
                    Pegawai Melebihi Batas Kuota Tunjangan Anak (>2 Anak)
                @else
                    Dobel Tunjangan Anak (Ditunjang oleh Kedua Orang Tua ASN)
                @endif
            </strong> 
            | Waktu Cetak: {{ $generatedAt }} WITA
        </p>
    </div>

    @if($tab === 'pasangan')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>NIP Suami / P1</th>
                    <th>Nama Suami / P1</th>
                    <th>SKPD Suami / P1</th>
                    <th>Status di P1</th>
                    <th>NIP Istri / P2</th>
                    <th>Nama Istri / P2</th>
                    <th>SKPD Istri / P2</th>
                    <th>Status di P2</th>
                    <th>Pelanggaran</th>
                    <th>Estimasi Beban/Bln</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditData['dobel_pasangan'] as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $p['nip_1'] }}</td>
                        <td><strong>{{ $p['nama_1'] }}</strong></td>
                        <td>{{ $p['skpd_1'] }}</td>
                        <td class="text-center">{{ $p['tunjang_1'] }}</td>
                        <td>{{ $p['nip_2'] }}</td>
                        <td><strong>{{ $p['nama_2'] }}</strong></td>
                        <td>{{ $p['skpd_2'] }}</td>
                        <td class="text-center">{{ $p['tunjang_2'] }}</td>
                        <td class="text-center text-danger">Saling Menunjang (10%+10%)</td>
                        <td class="text-right text-danger">Rp {{ number_format($p['potensi_kelebihan_bln'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-3">Tidak ditemukan indikasi pasangan saling menunjang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'kuota')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>NIP</th>
                    <th>Nama Pegawai</th>
                    <th>Gol.</th>
                    <th>SKPD & Unit Kerja</th>
                    <th>Total Anak Tertunjang</th>
                    <th>Lebih Kuota</th>
                    <th>Rincian Anak</th>
                    <th>Estimasi Beban Lebih/Bln</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditData['lebih_kuota'] as $idx => $k)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $k['nip'] }}</td>
                        <td><strong>{{ $k['nama'] }}</strong></td>
                        <td class="text-center">{{ $k['golongan'] }}</td>
                        <td>{{ $k['skpd'] }} ({{ $k['upt'] }})</td>
                        <td class="text-center text-danger"><strong>{{ $k['total_anak_tunjang'] }} Anak</strong></td>
                        <td class="text-center text-danger">+{{ $k['kelebihan'] }} Anak</td>
                        <td>{{ $k['daftar_anak_str'] }}</td>
                        <td class="text-right text-danger">Rp {{ number_format($k['potensi_kelebihan_bln'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-3">Tidak ada pegawai yang melebihi kuota 2 anak.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Nama Lengkap Anak</th>
                    <th>Tgl Lahir</th>
                    <th>Usia</th>
                    <th>Orang Tua 1 (NIP & Nama)</th>
                    <th>SKPD Orang Tua 1</th>
                    <th>Orang Tua 2 (NIP & Nama)</th>
                    <th>SKPD Orang Tua 2</th>
                    <th>Status Pelanggaran</th>
                    <th>Estimasi Beban/Bln</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditData['dobel_anak'] as $idx => $d)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $d['nama_anak'] }}</strong></td>
                        <td class="text-center">{{ $d['tgl_lahir_anak'] }}</td>
                        <td class="text-center">{{ $d['usia_anak'] }}</td>
                        <td>{{ $d['nip_1'] }} - {{ $d['nama_1'] }} ({{ $d['hub_1'] }})</td>
                        <td>{{ $d['skpd_1'] }}</td>
                        <td>{{ $d['nip_2'] }} - {{ $d['nama_2'] }} ({{ $d['hub_2'] }})</td>
                        <td>{{ $d['skpd_2'] }}</td>
                        <td class="text-center text-danger">Tertunjang Ganda (2%+2%)</td>
                        <td class="text-right text-danger">Rp {{ number_format($d['potensi_kelebihan_bln'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-3">Tidak ditemukan indikasi dobel tunjangan anak.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div class="footer-left">
            * Dokumen ini digenerate secara otomatis oleh Sistem Informasi KONBELPEG berdasarkan sinkronisasi basis data SIMGAJI Taspen.
        </div>
        <div class="footer-right">
            Dicetak pada: {{ $generatedAt }} WITA
        </div>
    </div>

</body>
</html>
