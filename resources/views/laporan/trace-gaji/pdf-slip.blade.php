<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $pegawai->nip }} - {{ $gaji->periode }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
            color: #334155;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9pt;
            color: #64748b;
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }
        .profile-table td {
            padding: 4px 8px;
            font-size: 8.5pt;
        }
        .profile-table td strong {
            color: #1e293b;
        }
        .component-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .component-col {
            width: 50%;
            vertical-align: top;
            padding: 0 6px;
        }
        .box {
            border: 1px solid #94a3b8;
            border-radius: 4px;
            padding: 8px;
            min-height: 250px;
        }
        .box-title {
            font-weight: bold;
            font-size: 9.5pt;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .box-income .box-title {
            color: #1d4ed8;
        }
        .box-deduction .box-title {
            color: #b91c1c;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-table td {
            padding: 3px 0;
            font-size: 8.5pt;
        }
        .item-table td.money {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        .total-box {
            background-color: #f1f5f9;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .net-amount {
            font-size: 13pt;
            font-weight: bold;
            color: #047857;
            font-family: 'Courier New', Courier, monospace;
        }
        .terbilang {
            font-size: 8pt;
            font-style: italic;
            color: #475569;
            margin-top: 4px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
            width: 50%;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>SLIP RINCIAN PEMBAYARAN GAJI PEGAWAI</h2>
        <h3>{{ strtoupper($pegawai->unitKerja?->skpd ?? 'PEMERINTAH DAERAH') }}</h3>
        <p>Periode: <strong>{{ strtoupper($gaji->periode) }}</strong> | Kriteria: <strong>{{ strtoupper($item['jenis_gaji']) }}</strong></p>
    </div>

    <!-- Identitas Pegawai -->
    <table class="profile-table">
        <tr>
            <td style="width: 16%;"><strong>Nama Pegawai</strong></td>
            <td style="width: 34%;">: {{ $pegawai->nama }}</td>
            <td style="width: 16%;"><strong>Status / Golru</strong></td>
            <td style="width: 34%;">: {{ $pegawai->status_pegawai }} / {{ $pegawai->golru ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>: {{ $pegawai->nip }}</td>
            <td><strong>Jabatan</strong></td>
            <td>: {{ $pegawai->jabatan?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Unit Kerja</strong></td>
            <td>: {{ $pegawai->unitKerja?->skpd ?? '-' }}</td>
            <td><strong>No. Rekening</strong></td>
            <td>: {{ $item['raw']['norek'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>NPWP</strong></td>
            <td>: {{ $item['raw']['npwp'] ?? '-' }}</td>
            <td><strong>Tanggungan</strong></td>
            <td>: {{ ($item['raw']['jistri'] ?? 0) }} Suami/Istri, {{ ($item['raw']['janak'] ?? 0) }} Anak</td>
        </tr>
    </table>

    <!-- Rincian Hak Penghasilan & Potongan -->
    <table class="component-table">
        <tr>
            <!-- Kolom Penghasilan -->
            <td class="component-col">
                <div class="box box-income">
                    <div class="box-title">I. PENGHASILAN KOTOR</div>
                    <table class="item-table">
                        <tr><td>Gaji Pokok</td><td class="money">{{ number_format($item['gaji_pokok'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Istri / Suami</td><td class="money">{{ number_format($item['tj_istri'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Anak</td><td class="money">{{ number_format($item['tj_anak'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Jabatan / Struktural</td><td class="money">{{ number_format($item['tj_jabatan'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Fungsional</td><td class="money">{{ number_format($item['tj_fungsional'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Umum</td><td class="money">{{ number_format($item['tj_umum'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Beras</td><td class="money">{{ number_format($item['tj_beras'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Pajak (PPh)</td><td class="money">{{ number_format($item['tj_pajak'], 0, ',', '.') }}</td></tr>
                        <tr><td>Tunjangan Pembulatan</td><td class="money">{{ number_format($item['tj_pembulatan'], 0, ',', '.') }}</td></tr>
                        @if($item['tj_lain'] > 0)
                        <tr><td>Tunjangan Lainnya</td><td class="money">{{ number_format($item['tj_lain'], 0, ',', '.') }}</td></tr>
                        @endif
                    </table>
                    <div style="border-top: 1px solid #94a3b8; margin-top: 10px; padding-top: 6px; display: flex; justify-content: space-between; font-weight: bold; font-size: 8.5pt;">
                        <span>JUMLAH KOTOR:</span>
                        <span style="font-family: 'Courier New', monospace; font-size: 9.5pt;">Rp {{ number_format($item['gaji_kotor'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </td>

            <!-- Kolom Potongan -->
            <td class="component-col">
                <div class="box box-deduction">
                    <div class="box-title">II. POTONGAN GAJI</div>
                    <table class="item-table">
                        <tr><td>Potongan Pajak (PPh)</td><td class="money">{{ number_format($item['pot_pajak'], 0, ',', '.') }}</td></tr>
                        <tr><td>IWP 2% (BPJS Kesehatan / Jamkes)</td><td class="money">{{ number_format($item['pot_iwp2'], 0, ',', '.') }}</td></tr>
                        <tr><td>IWP 8% (Taspen / Pensiun & THT)</td><td class="money">{{ number_format($item['pot_iwp8'], 0, ',', '.') }}</td></tr>
                        @if($item['pot_taperum'] > 0)
                        <tr><td>Potongan Taperum</td><td class="money">{{ number_format($item['pot_taperum'], 0, ',', '.') }}</td></tr>
                        @endif
                        @if($item['pot_lain'] > 0)
                        <tr><td>Potongan Lain-lain</td><td class="money">{{ number_format($item['pot_lain'], 0, ',', '.') }}</td></tr>
                        @endif
                    </table>
                    <div style="border-top: 1px solid #94a3b8; margin-top: 10px; padding-top: 6px; display: flex; justify-content: space-between; font-weight: bold; font-size: 8.5pt; color: #b91c1c;">
                        <span>JUMLAH POTONGAN:</span>
                        <span style="font-family: 'Courier New', monospace; font-size: 9.5pt;">Rp {{ number_format($item['total_potongan'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Bersih -->
    <div class="total-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td>
                    <span style="font-size: 9pt; font-weight: bold; color: #334155; text-transform: uppercase;">Jumlah Penghasilan Bersih Diterima (Take Home Pay):</span>
                    <div class="terbilang">Terbilang: <strong>{{ $terbilang }}</strong></div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <div class="net-amount">Rp {{ number_format($item['gaji_bersih'], 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Bendahara Pengeluaran / Gaji</strong>
                <br><br><br><br>
                ( ____________________________________ )
            </td>
            <td>
                Diterima pada: {{ $tanggalCetak }}<br>
                <strong>Pegawai Yang Bersangkutan</strong>
                <br><br><br><br>
                ( <strong>{{ $pegawai->nama }}</strong> )<br>
                NIP. {{ $pegawai->nip }}
            </td>
        </tr>
    </table>

</body>
</html>
