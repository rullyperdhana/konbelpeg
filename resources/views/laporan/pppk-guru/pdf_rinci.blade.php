<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rinci PPPK GURU</title>
    <style>
        @page {
            margin: 5px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 10px;
            text-transform: uppercase;
        }
        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 2px;
            font-size: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            padding: 3px;
            font-size: 6px;
            word-wrap: break-word;
        }
        td {
            padding: 3px;
            font-size: 6px;
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
    <h2>LAPORAN RINCIAN GAJI DAN TPP PPPK GURU (PER INDIVIDU)</h2>
    <p>Nama Daerah : Provinsi Kalimantan Selatan</p>
    <p>BULAN {{ explode(' ', $periode)[0] }} TAHUN {{ explode(' ', $periode)[1] ?? '2026' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 15px;">No</th>
                <th rowspan="2" style="width: 50px;">NIP</th>
                <th rowspan="2" style="width: 70px;">Nama Pegawai</th>
                <th rowspan="2" style="width: 60px;">SKPD</th>
                <th rowspan="2" style="width: 25px;">Gol.</th>
                <th rowspan="2">Gaji<br>Pokok</th>
                <th rowspan="2">Tunj.<br>Keluarga</th>
                <th colspan="2">Tunjangan Jabatan</th>
                <th rowspan="2">Tunj.<br>Umum</th>
                <th rowspan="2">Tunj.<br>Beras</th>
                <th rowspan="2">Tunj.<br>Lainnya</th>
                <th rowspan="2">Pembulatan</th>
                <th rowspan="2">Gaji<br>Kotor</th>
                <th rowspan="2">TPP /<br>Tunj.</th>
                <th rowspan="2">Kode<br>Bayar</th>
                <th rowspan="2">Total<br>Penghasilan</th>
            </tr>
            <tr>
                <th>Struktural</th>
                <th>Fungsional</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $row['nip'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td style="font-size: 5px;">{{ $row['skpd'] }}</td>
                <td class="center"><strong>{{ $row['golongan'] }}</strong></td>
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
                <td colspan="5" class="center">Jumlah Keseluruhan (Berdasarkan Filter)</td>
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
