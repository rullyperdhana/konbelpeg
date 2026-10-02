<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Trace Riwayat Penggajian - {{ $pegawai->nip }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 1.2cm 1cm 1cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #334155;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9pt;
            color: #475569;
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .profile-table td {
            padding: 5px 8px;
            font-size: 8.5pt;
            border: none;
        }
        .profile-table td strong {
            color: #334155;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .table-data th, .table-data td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            font-size: 7.5pt;
        }
        .table-data th {
            background-color: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            text-align: center;
        }
        .table-data td.money {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        .table-data tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .highlight-net {
            background-color: #ecfdf5;
            font-weight: bold;
            color: #047857;
        }
        .footer-note {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LEMBAR TRACE RIWAYAT PENGGAJIAN PEGAWAI</h2>
        <p>Sistem Pengelolaan & Pelacakan Belanja Pegawai ASN Daerah</p>
    </div>

    <!-- Data Identitas Pegawai -->
    <table class="profile-table">
        <tr>
            <td style="width: 15%;"><strong>Nama Pegawai</strong></td>
            <td style="width: 35%;">: {{ $pegawai->nama }}</td>
            <td style="width: 15%;"><strong>Status / Jenis</strong></td>
            <td style="width: 35%;">: {{ $pegawai->status_pegawai }} ({{ $pegawai->jenis_pegawai ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>: {{ $pegawai->nip }} @if(!empty($traceData['latestRawMeta']['niplama'])) (NIP Lama: {{ $traceData['latestRawMeta']['niplama'] }}) @endif</td>
            <td><strong>Golongan / Ruang</strong></td>
            <td>: {{ $pegawai->golru ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Jabatan</strong></td>
            <td>: {{ $pegawai->jabatan?->nama ?? '-' }}</td>
            <td><strong>Unit Kerja / SKPD</strong></td>
            <td>: {{ $pegawai->unitKerja?->skpd ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>No. Rekening Bank</strong></td>
            <td>: {{ $traceData['latestRawMeta']['norek'] ?? '-' }}</td>
            <td><strong>NPWP</strong></td>
            <td>: {{ $traceData['latestRawMeta']['npwp'] ?? '-' }}</td>
        </tr>
    </table>

    <!-- Tabel Rincian Riwayat Pembayaran Gaji -->
    <table class="table-data">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 80px;">Periode</th>
                <th rowspan="2" style="width: 65px;">Kriteria</th>
                <th colspan="5">Komponen Penghasilan Kotor</th>
                <th rowspan="2" style="width: 65px;">Gaji Kotor</th>
                <th colspan="4">Komponen Potongan</th>
                <th rowspan="2" style="width: 65px;">Total Potongan</th>
                <th rowspan="2" style="width: 75px; background: #d1fae5; color: #065f46;">Gaji Bersih Diterima</th>
            </tr>
            <tr>
                <th>GAPOK</th>
                <th>Tunj. Kel</th>
                <th>Tunj. Jab/Fung</th>
                <th>Tunj. Beras</th>
                <th>Lain/Pajak</th>
                <th>IWP 2% (Jamkes)</th>
                <th>IWP 8% (Taspen)</th>
                <th>Pajak PPh</th>
                <th>Pot. Lain</th>
            </tr>
        </thead>
        <tbody>
            @forelse($traceData['records'] as $idx => $rec)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td style="text-align: center;"><strong>{{ $rec['periode'] }}</strong></td>
                <td style="text-align: center;">{{ $rec['jenis_gaji'] }}</td>
                <td class="money">{{ number_format($rec['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['tj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['tj_jabatan'] + $rec['tj_fungsional'] + $rec['tj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['tj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['tj_pajak'] + $rec['tj_pembulatan'] + $rec['tj_lain'], 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; background: #f8fafc;">{{ number_format($rec['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['pot_iwp2'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['pot_iwp8'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['pot_pajak'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rec['pot_lain'], 0, ',', '.') }}</td>
                <td class="money" style="font-weight: bold; color: #b91c1c;">{{ number_format($rec['total_potongan'], 0, ',', '.') }}</td>
                <td class="money highlight-net">{{ number_format($rec['gaji_bersih'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="15" style="text-align: center; padding: 15px;">Tidak ada riwayat pembayaran gaji yang tercatat.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($traceData['records']) > 0)
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: center;">TOTAL KESELURUHAN</td>
                <td class="money">{{ number_format($traceData['totals']['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['tj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['tj_jabatan'] + $traceData['totals']['tj_fungsional'] + $traceData['totals']['tj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['tj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['tj_pajak'] + $traceData['totals']['tj_pembulatan'] + $traceData['totals']['tj_lain'], 0, ',', '.') }}</td>
                <td class="money" style="background: #cbd5e1;">{{ number_format($traceData['totals']['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['pot_iwp2'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['pot_iwp8'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['pot_pajak'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($traceData['totals']['pot_lain'], 0, ',', '.') }}</td>
                <td class="money" style="color: #b91c1c;">{{ number_format($traceData['totals']['total_potongan'], 0, ',', '.') }}</td>
                <td class="money highlight-net" style="font-size: 8.5pt;">{{ number_format($traceData['totals']['gaji_bersih'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer-note">
        <span>Dicetak secara otomatis dari Sistem Realisasi Belanja Pegawai pada: {{ $tanggalCetak }} WITA</span>
        <span>Dokumen ini adalah rekapitulasi trace resmi berbasis database SIMGAJI & SIMPEG.</span>
    </div>

</body>
</html>
