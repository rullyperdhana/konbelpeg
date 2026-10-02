<!DOCTYPE html>
<html>
<head>
    <title>Laporan Realisasi Gaji</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0 0 0; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .money { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN REALISASI GAJI</h2>
        <p>PERIODE: {{ $periode ?: 'SEMUA PERIODE' }} @if(!empty($jenisGajiFilter) && $jenisGajiFilter !== 'Semua') | KRITERIA: {{ strtoupper($jenisGajiFilter) }} @endif</p>
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
                    <th colspan="9">JUMLAH PEGAWAI (YANG DIBAYARKAN GAJI)</th>
                    <th colspan="4">BELUM DIBAYAR</th>
                    <th colspan="8">TOTAL REALISASI GAJI</th>
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
                    <th style="font-size: 7px; vertical-align: top;">JUMLAH GAJI</th>
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
                    
                    <td class="money">{{ number_format($rekap->nominal_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_teknis, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_teknis, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight:bold;">{{ number_format($rekap->nominal_total, 0, ',', '.') }}</td>
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
                    
                    <td class="money">{{ number_format($rekaps->sum('nominal_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">NO</th>
                    <th>PERIODE</th>
                    <th>NIP</th>
                    <th>NAMA PEGAWAI</th>
                    <th>STATUS</th>
                    <th>UNIT KERJA</th>
                    <th>KRITERIA GAJI</th>
                    <th>GAJI POKOK</th>
                    <th>TOTAL DIBAYARKAN</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @forelse($realisasis as $pegawai)
                    @if($pegawai->realisasiGajis->isEmpty())
                        <tr style="background-color: #fef2f2;">
                            <td class="center">{{ $no++ }}</td>
                            <td class="center">{{ $periode ?: 'SEMUA PERIODE' }}</td>
                            <td>{{ $pegawai->nip ?? '-' }}</td>
                            <td>{{ $pegawai->nama ?? '-' }}</td>
                            <td class="center">{{ $pegawai->status_pegawai ?? '-' }}</td>
                            <td style="font-size: 8px;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</td>
                            <td class="center">-</td>
                            <td colspan="2" class="center" style="color: #991b1b; font-weight: bold;">Rp 0 (Belum Dibayarkan)</td>
                        </tr>
                    @else
                        @foreach($pegawai->realisasiGajis as $gaji)
                        <tr>
                            <td class="center">{{ $no++ }}</td>
                            <td class="center">{{ $gaji->periode ?? $periode }}</td>
                            <td>{{ $pegawai->nip ?? '-' }}</td>
                            <td>{{ $pegawai->nama ?? '-' }}</td>
                            <td class="center">{{ $pegawai->status_pegawai ?? '-' }}</td>
                            <td style="font-size: 8px;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</td>
                            <td class="center" style="font-size: 8px;">{{ $gaji->jenis_gaji ?? 'Gaji Induk' }}</td>
                            <td class="money">{{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="money">{{ number_format($gaji->gaji_bersih, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @endif
                @empty
                <tr>
                    <td colspan="9" class="center">Data tidak tersedia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
