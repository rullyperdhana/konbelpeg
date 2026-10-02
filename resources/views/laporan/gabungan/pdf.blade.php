<!DOCTYPE html>
<html>
<head>
    <title>Export Laporan Gabungan</title>
    <style>
        @page {
            margin: 5px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            word-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .center { text-align: center; }
        .money { text-align: right; }
    </style>
</head>
<body>
    <h2 style="text-align: center; font-size: 14px; margin-bottom: 5px;">LAPORAN GABUNGAN REALISASI GAJI DAN TPP</h2>
    <h3 style="text-align: center; font-size: 12px; margin-top: 0;">PERIODE: {{ strtoupper($periode) }} @if(!empty($jenisGaji) && $jenisGaji !== 'Semua') | KRITERIA GAJI: {{ strtoupper($jenisGaji) }} @endif</h3>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" style="width: 100px;">UNIT KERJA / SKPD</th>
                
                <th colspan="4">TOTAL PEGAWAI</th>
                <th colspan="4" style="background: #fecaca;">BELUM DIBAYAR GAJI</th>
                <th colspan="4" style="background: #fed7aa;">BELUM DIBAYAR TPP</th>
                
                <th colspan="8" style="background: #bbf7d0;">TOTAL NOMINAL GAJI</th>
                <th colspan="8" style="background: #bae6fd;">TOTAL NOMINAL TPP</th>
                
                <th rowspan="2" style="background: #fef08a;">GRAND TOTAL (GAJI+TPP)</th>
            </tr>
            <tr>
                <th>PNS</th><th>PPPK</th><th>PARUH WAKTU</th><th>TOTAL</th>
                <th style="background: #fecaca;">PNS</th><th style="background: #fecaca;">PPPK</th><th style="background: #fecaca;">PARUH WAKTU</th><th style="background: #fecaca;">TOTAL</th>
                <th style="background: #fed7aa;">PNS</th><th style="background: #fed7aa;">PPPK</th><th style="background: #fed7aa;">PARUH WAKTU</th><th style="background: #fed7aa;">TOTAL</th>
                <th style="background: #bbf7d0;">PNS</th><th style="background: #bbf7d0;">PPPK GURU</th><th style="background: #bbf7d0;">PPPK KES</th><th style="background: #bbf7d0;">PPPK TEKNIS</th><th style="background: #bbf7d0;">PARUH GURU</th><th style="background: #bbf7d0;">PARUH KES</th><th style="background: #bbf7d0;">PARUH TEKNIS</th><th style="background: #bbf7d0;">TOTAL GAJI</th>
                <th style="background: #bae6fd;">PNS</th><th style="background: #bae6fd;">PPPK GURU</th><th style="background: #bae6fd;">PPPK KES</th><th style="background: #bae6fd;">PPPK TEKNIS</th><th style="background: #bae6fd;">PARUH GURU</th><th style="background: #bae6fd;">PARUH KES</th><th style="background: #bae6fd;">PARUH TEKNIS</th><th style="background: #bae6fd;">TOTAL TPP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekaps as $index => $rekap)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $rekap->skpd }}</td>
                <td class="center">{{ $rekap->count_pns }}</td>
                <td class="center">{{ $rekap->count_pppk }}</td>
                <td class="center">{{ $rekap->count_paruh }}</td>
                <td class="center" style="font-weight:bold;">{{ $rekap->count_total }}</td>
                
                <td class="center" style="color: red;">{{ $rekap->blm_gaji_pns }}</td>
                <td class="center" style="color: red;">{{ $rekap->blm_gaji_pppk }}</td>
                <td class="center" style="color: red;">{{ $rekap->blm_gaji_paruh }}</td>
                <td class="center" style="font-weight:bold; color: red;">{{ $rekap->blm_gaji_total }}</td>
                
                <td class="center" style="color: red;">{{ $rekap->blm_tpp_pns }}</td>
                <td class="center" style="color: red;">{{ $rekap->blm_tpp_pppk }}</td>
                <td class="center" style="color: red;">{{ $rekap->blm_tpp_paruh }}</td>
                <td class="center" style="font-weight:bold; color: red;">{{ $rekap->blm_tpp_total }}</td>
                
                <td class="money">{{ number_format($rekap->nom_gaji_pns, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_teknis, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_teknis, 0, ',', '.') }}</td>
                <td class="money" style="font-weight:bold;">{{ number_format($rekap->nom_gaji_total, 0, ',', '.') }}</td>
                
                <td class="money">{{ number_format($rekap->nom_tpp_pns, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_teknis, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_teknis, 0, ',', '.') }}</td>
                <td class="money" style="font-weight:bold;">{{ number_format($rekap->nom_tpp_total, 0, ',', '.') }}</td>
                
                <td class="money" style="font-weight:bold;">{{ number_format($rekap->nom_gaji_total + $rekap->nom_tpp_total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="31" class="center">Data tidak tersedia</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($rekaps) > 0)
        <tfoot>
            <tr style="background: #e2e8f0; font-weight: bold;">
                <td colspan="2" class="center">TOTAL KESELURUHAN</td>
                <td class="center">{{ $rekaps->sum('count_pns') }}</td>
                <td class="center">{{ $rekaps->sum('count_pppk') }}</td>
                <td class="center">{{ $rekaps->sum('count_paruh') }}</td>
                <td class="center">{{ $rekaps->sum('count_total') }}</td>
                
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_gaji_pns') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_gaji_pppk') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_gaji_paruh') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_gaji_total') }}</td>
                
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_tpp_pns') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_tpp_pppk') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_tpp_paruh') }}</td>
                <td class="center" style="color: red;">{{ $rekaps->sum('blm_tpp_total') }}</td>
                
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_pns'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_pppk_guru'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_pppk_kes'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_pppk_teknis'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_paruh_guru'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_paruh_kes'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_paruh_teknis'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_total'), 0, ',', '.') }}</td>
                
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_pns'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_pppk_guru'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_pppk_kes'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_pppk_teknis'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_paruh_guru'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_paruh_kes'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_paruh_teknis'), 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekaps->sum('nom_tpp_total'), 0, ',', '.') }}</td>
                
                <td class="money">{{ number_format($rekaps->sum('nom_gaji_total') + $rekaps->sum('nom_tpp_total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
