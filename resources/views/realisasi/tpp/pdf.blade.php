<!DOCTYPE html>
<html>
<head>
    <title>Laporan Realisasi TPP</title>
    <style>
        @page {
            margin: 10px;
        }
        body { font-family: Arial, sans-serif; font-size: 8px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 14px; }
        .header p { margin: 5px 0 0 0; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; font-size: 8px; }
        .money { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN REALISASI TPP</h2>
        <p>PERIODE: {{ $periode ?: 'SEMUA PERIODE' }}</p>
        @if($skpdFilter)
            <p>UNIT KERJA: {{ strtoupper($skpdFilter) }}</p>
        @endif
    </div>

    @if($tipeLaporan == 'rekap')
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">NO</th>
                    <th rowspan="2">UNIT KERJA</th>
                    <th colspan="9">JUMLAH PEGAWAI (YANG DIBAYARKAN TPP)</th>
                    <th colspan="4">BELUM DIBAYAR</th>
                    <th colspan="8">TOTAL REALISASI TPP</th>
                </tr>
                <tr>
                    <th style="font-size: 7px; vertical-align: top;">PNS</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK GURU DAN TENAGA KEPENDIDIKAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK TENAGA KESEHATAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK TENAGA TEKNIS</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU GURU DAN TENAGA KEPENDIDIKAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU TENAGA KESEHATAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU TENAGA TEKNIS</th>
                    <th style="font-size: 7px; vertical-align: top;">JUMLAH PEGAWAI</th>
                    
                    <th style="font-size: 7px; vertical-align: top; color: #991b1b;">PNS</th>
                    <th style="font-size: 7px; vertical-align: top; color: #991b1b;">PPPK</th>
                    <th style="font-size: 7px; vertical-align: top; color: #991b1b;">PARUH WAKTU</th>
                    <th style="font-size: 7px; vertical-align: top; color: #991b1b;">TOTAL</th>
                    
                    <th style="font-size: 7px; vertical-align: top;">PNS</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK GURU DAN TENAGA KEPENDIDIKAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK TENAGA KESEHATAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK TENAGA TEKNIS</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU GURU DAN TENAGA KEPENDIDIKAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU TENAGA KESEHATAN</th>
                    <th style="font-size: 7px; vertical-align: top;">PPPK PARUH WAKTU TENAGA TEKNIS</th>
                    <th style="font-size: 7px; vertical-align: top;">JUMLAH TPP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $rekap)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td style="font-size: 8px;">{{ $rekap->skpd }}</td>
                    
                    <td class="center">{{ $rekap->count_pns }}</td>
                    <td class="center">{{ $rekap->count_pppk_guru }}</td>
                    <td class="center">{{ $rekap->count_pppk_kes }}</td>
                    <td class="center">{{ $rekap->count_pppk_teknis }}</td>
                    <td class="center">{{ $rekap->count_paruh_guru }}</td>
                    <td class="center">{{ $rekap->count_paruh_kes }}</td>
                    <td class="center">{{ $rekap->count_paruh_teknis }}</td>
                    <td class="center" style="font-weight:bold;">{{ $rekap->count_total }}</td>
                    
                    <td class="center" style="color: #991b1b;">{{ $rekap->count_belum_dibayar_pns }}</td>
                    <td class="center" style="color: #991b1b;">{{ $rekap->count_belum_dibayar_pppk }}</td>
                    <td class="center" style="color: #991b1b;">{{ $rekap->count_belum_dibayar_paruh }}</td>
                    <td class="center" style="font-weight:bold; color: #991b1b;">{{ $rekap->count_belum_dibayar_total }}</td>
                    
                    <td class="money">{{ number_format($rekap->tpp_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_teknis, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_teknis, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight:bold;">{{ number_format($rekap->tpp_total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="22" class="center">Data tidak tersedia</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr style="background: #e2e8f0; font-weight: bold;">
                    <td colspan="2" class="center">TOTAL KESELURUHAN</td>
                    <td class="center">{{ $rekaps->sum('count_pns') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_guru') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_kes') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_teknis') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_guru') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_kes') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_teknis') }}</td>
                    <td class="center">{{ $rekaps->sum('count_total') }}</td>
                    
                    <td class="center" style="color: #991b1b;">{{ $rekaps->sum('count_belum_dibayar_pns') }}</td>
                    <td class="center" style="color: #991b1b;">{{ $rekaps->sum('count_belum_dibayar_pppk') }}</td>
                    <td class="center" style="color: #991b1b;">{{ $rekaps->sum('count_belum_dibayar_paruh') }}</td>
                    <td class="center" style="color: #991b1b;">{{ $rekaps->sum('count_belum_dibayar_total') }}</td>
                    
                    <td class="money">{{ number_format($rekaps->sum('tpp_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Periode</th>
                    <th style="text-align: left;">NIP</th>
                    <th style="text-align: left;">Nama Pegawai</th>
                    <th style="text-align: left;">Status Pegawai</th>
                    <th style="text-align: left;">SKPD / Jabatan</th>
                    <th style="text-align: right;">TPP Bruto</th>
                    <th style="text-align: right;">Nominal PLT</th>
                    <th style="text-align: right;">Potongan</th>
                    <th style="text-align: right;">Dibayarkan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($realisasis as $index => $pegawai)
                @php 
                    $tpp = $pegawai->realisasiTpps->first();
                    $totalPotongan = $tpp ? ($tpp->pph_21 + $tpp->potongan_lainnya + $tpp->iuran_iwp) : 0;
                @endphp
                <tr style="{{ !$tpp ? 'background-color: #fef2f2;' : '' }}">
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $periode ?: 'Semua Periode' }}</td>
                    <td>{{ $pegawai->nip ?? '-' }}</td>
                    <td>{{ $pegawai->nama ?? 'Tidak Diketahui' }}</td>
                    <td>{{ $pegawai->status_pegawai ?? '-' }}</td>
                    <td>
                        {{ $pegawai->unitKerja?->skpd ?? '-' }}<br>
                        <span style="font-size: 8px; color: #64748b;">{{ $pegawai->jabatan?->nama ?? '-' }}</span>
                    </td>
                    @if($tpp)
                        <td class="money">{{ number_format($tpp->tpp_bruto, 0, ',', '.') }}</td>
                        <td class="money" style="color: #0284c7;">{{ number_format($tpp->nominal_plt, 0, ',', '.') }}</td>
                        <td class="money">{{ number_format($totalPotongan, 0, ',', '.') }}</td>
                        <td class="money" style="font-weight: bold;">{{ number_format($tpp->total_dibayarkan, 0, ',', '.') }}</td>
                    @else
                        <td class="center" colspan="4">Belum Dibayarkan</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="center">Data tidak tersedia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
