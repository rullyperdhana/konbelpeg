<!DOCTYPE html>
<html>
<head>
    <title>Laporan PPPK GURU</title>
    <style>
        @page {
            margin: 5px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 11px;
            text-transform: uppercase;
        }
        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 2px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            padding: 3px;
            font-size: 7px;
            word-wrap: break-word;
        }
        td {
            padding: 3px;
            font-size: 7px;
            word-wrap: break-word;
        }
        .center {
            text-align: center;
        }
        .money {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h2>DAFTAR RINCIAN JUMLAH DAN REALISASI PEMBAYARAN GAJI GURU Gaji Induk PPPK + TPP</h2>
    <p>Nama Daerah : Provinsi Kalimantan Selatan</p>
    <p>BULAN {{ explode(' ', $periode)[0] }}</p>
    <p>TAHUN {{ explode(' ', $periode)[1] ?? '2026' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 50px;">Golongan</th>
                <th rowspan="2">Jumlah GURU<br>PPPK Yang<br>Diangkat</th>
                <th rowspan="2">Jumlah GURU<br>PPPK Yang<br>Telah Diangkat</th>
                <th rowspan="2">Gaji<br>Pokok</th>
                <th rowspan="2">Tunjangan<br>Keluarga</th>
                <th colspan="2">Tunjangan Jabatan</th>
                <th rowspan="2">Tunjangan<br>Umum</th>
                <th rowspan="2">Tunjangan<br>Beras</th>
                <th rowspan="2">Tunjangan<br>Lainnya</th>
                <th rowspan="2">Lain-lain<br>Pembulatan</th>
                <th rowspan="2">Gaji<br>Kotor</th>
                <th rowspan="2">Tunjangan<br>Perbaikan<br>Penghasilan<br>(TPP)/ Tunjangan</th>
                <th rowspan="2">Kode<br>Bayar</th>
                <th rowspan="2">Total<br>Penghasilan</th>
            </tr>
            <tr>
                <th>Struktural</th>
                <th>Fungsional</th>
            </tr>
            <tr style="font-size: 7px;">
                <th>(1)</th>
                <th>(2)</th>
                <th>(3)</th>
                <th>(4)</th>
                <th>(5)</th>
                <th>(6)</th>
                <th>(7)</th>
                <th>(8)</th>
                <th>(9)</th>
                <th>(10)</th>
                <th>(11)</th>
                <th>(12)</th>
                <th>(13)=(5)+..+(12)</th>
                <th>(14)</th>
                <th>(15)</th>
                <th>(16)=(13)+(14)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center"><strong>{{ $row['golongan'] }}</strong></td>
                <td class="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_lainnya'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['pembulatan'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($row['tunj_perbaikan'], 0, ',', '.') }}</td>
                <td class="center">-</td>
                <td class="money">{{ number_format($row['total_penghasilan'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        @if(count($laporan) > 0)
        <tfoot>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="2" class="center">Jumlah</td>
                <td class="center">{{ number_format($totals['jumlah'], 0, ',', '.') }}</td>
                <td class="center">{{ number_format($totals['jumlah'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['gaji_pokok'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_keluarga'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_struktural'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_fungsional'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_umum'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_beras'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_lainnya'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['pembulatan'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['gaji_kotor'], 0, ',', '.') }}</td>
                <td class="money">{{ number_format($totals['tunj_perbaikan'], 0, ',', '.') }}</td>
                <td class="center"></td>
                <td class="money">{{ number_format($totals['total_penghasilan'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
