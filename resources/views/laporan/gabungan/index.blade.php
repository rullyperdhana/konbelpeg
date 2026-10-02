@extends('layouts.app')

@section('title', 'Laporan Gabungan (Gaji & TPP)')
@section('page_title', 'Laporan Gabungan Gaji & TPP')

@section('content')
<div class="app-header">
    <div>
        <h2>Laporan Gabungan (Realisasi Gaji & TPP)</h2>
        <p>Konsolidasi jumlah pegawai, status pembayaran, dan total realisasi pengeluaran per SKPD.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form action="" method="GET" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <select name="periode" onchange="this.form.submit()" style="width: auto; min-width: 160px;">
                <option value="Semua Periode" {{ $periode == 'Semua Periode' ? 'selected' : '' }}>Semua Periode</option>
                @foreach($allPeriodes as $p)
                    <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            <select name="jenis_gaji" onchange="this.form.submit()" style="width: auto; min-width: 160px;">
                <option value="Semua" {{ ($jenisGaji ?? 'Semua') == 'Semua' ? 'selected' : '' }}>Semua Kriteria Gaji</option>
                @foreach($daftarJenisGaji as $jg)
                    <option value="{{ $jg }}" {{ ($jenisGaji ?? '') == $jg ? 'selected' : '' }}>{{ $jg }}</option>
                @endforeach
            </select>
        </form>
        <a href="/laporan/gabungan/export/pdf?periode={{ urlencode($periode) }}&jenis_gaji={{ urlencode($jenisGaji ?? 'Semua') }}" class="btn btn-export" style="color: var(--danger-text);">
            <i class="ph ph-file-pdf"></i> Export PDF
        </a>
        <a href="/laporan/gabungan/export/excel?periode={{ urlencode($periode) }}&jenis_gaji={{ urlencode($jenisGaji ?? 'Semua') }}" class="btn btn-export" style="color: var(--success-text);">
            <i class="ph ph-file-xls"></i> Export Excel
        </a>
    </div>
</div>

<div class="table-container">
    <table style="min-width: 2500px;">
        <thead>
            <tr>
                <th rowspan="2" style="width: 50px;" class="center">NO</th>
                <th rowspan="2" style="width: 300px; position: sticky; left: 0; z-index: 2;">UNIT KERJA / SKPD</th>
                
                <th colspan="4" class="center" style="background: var(--bg-surface-subtle); color: var(--text-main);">TOTAL PEGAWAI</th>
                <th colspan="4" class="center" style="background: var(--danger-subtle); color: var(--danger-text);">BELUM DIBAYAR GAJI</th>
                <th colspan="4" class="center" style="background: var(--warning-subtle); color: var(--warning-text);">BELUM DIBAYAR TPP</th>
                
                <th colspan="8" class="center" style="background: var(--success-subtle); color: var(--success-text);">TOTAL NOMINAL GAJI</th>
                <th colspan="8" class="center" style="background: var(--primary-subtle); color: var(--primary-text);">TOTAL NOMINAL TPP</th>
                
                <th rowspan="2" class="center" style="background: var(--warning-subtle); color: var(--warning-text); font-weight: 700;">GRAND TOTAL (GAJI+TPP)</th>
            </tr>
            <tr>
                <!-- TOTAL PEGAWAI -->
                <th class="center">PNS</th>
                <th class="center">PPPK</th>
                <th class="center">PARUH WAKTU</th>
                <th class="center" style="font-weight: 700;">TOTAL</th>

                <!-- BELUM DIBAYAR GAJI -->
                <th class="center" style="color: var(--danger-text);">PNS</th>
                <th class="center" style="color: var(--danger-text);">PPPK</th>
                <th class="center" style="color: var(--danger-text);">PARUH WAKTU</th>
                <th class="center" style="color: var(--danger-text); font-weight: 700;">TOTAL</th>

                <!-- BELUM DIBAYAR TPP -->
                <th class="center" style="color: var(--warning-text);">PNS</th>
                <th class="center" style="color: var(--warning-text);">PPPK</th>
                <th class="center" style="color: var(--warning-text);">PARUH WAKTU</th>
                <th class="center" style="color: var(--warning-text); font-weight: 700;">TOTAL</th>

                <!-- NOMINAL GAJI -->
                <th class="center">PNS</th>
                <th class="center">PPPK GURU</th>
                <th class="center">PPPK KES</th>
                <th class="center">PPPK TEKNIS</th>
                <th class="center">PARUH GURU</th>
                <th class="center">PARUH KES</th>
                <th class="center">PARUH TEKNIS</th>
                <th class="center" style="font-weight: 700;">TOTAL GAJI</th>

                <!-- NOMINAL TPP -->
                <th class="center">PNS</th>
                <th class="center">PPPK GURU</th>
                <th class="center">PPPK KES</th>
                <th class="center">PPPK TEKNIS</th>
                <th class="center">PARUH GURU</th>
                <th class="center">PARUH KES</th>
                <th class="center">PARUH TEKNIS</th>
                <th class="center" style="font-weight: 700;">TOTAL TPP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekaps as $index => $rekap)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td style="position: sticky; left: 0; background: var(--bg-surface); z-index: 1; font-weight: 600;">{{ $rekap->skpd }}</td>
                
                <td class="center">{{ $rekap->count_pns }}</td>
                <td class="center">{{ $rekap->count_pppk }}</td>
                <td class="center">{{ $rekap->count_paruh }}</td>
                <td class="center" style="font-weight: 700;">{{ $rekap->count_total }}</td>
                
                <td class="center" style="color: var(--danger-text);">{{ $rekap->blm_gaji_pns }}</td>
                <td class="center" style="color: var(--danger-text);">{{ $rekap->blm_gaji_pppk }}</td>
                <td class="center" style="color: var(--danger-text);">{{ $rekap->blm_gaji_paruh }}</td>
                <td class="center" style="font-weight: 700; color: var(--danger-text);">{{ $rekap->blm_gaji_total }}</td>
                
                <td class="center" style="color: var(--warning-text);">{{ $rekap->blm_tpp_pns }}</td>
                <td class="center" style="color: var(--warning-text);">{{ $rekap->blm_tpp_pppk }}</td>
                <td class="center" style="color: var(--warning-text);">{{ $rekap->blm_tpp_paruh }}</td>
                <td class="center" style="font-weight: 700; color: var(--warning-text);">{{ $rekap->blm_tpp_total }}</td>
                
                <td class="money">{{ number_format($rekap->nom_gaji_pns, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_pppk_teknis, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_gaji_paruh_teknis, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: 700;">{{ number_format($rekap->nom_gaji_total, 0, ',', '.') }}</td>
                
                <td class="money">{{ number_format($rekap->nom_tpp_pns, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_pppk_teknis, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_guru, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_kes, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($rekap->nom_tpp_paruh_teknis, 0, ',', '.') }}</td>
                <td class="money" style="font-weight: 700;">{{ number_format($rekap->nom_tpp_total, 0, ',', '.') }}</td>
                
                <td class="money" style="font-weight: 700; background: var(--warning-subtle); color: var(--warning-text);">
                    {{ number_format($rekap->nom_gaji_total + $rekap->nom_tpp_total, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="31" class="center" style="padding: 40px; color: var(--text-muted);">Belum ada data realisasi untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($rekaps) > 0)
        <tfoot>
            <tr style="background: var(--bg-surface-subtle); font-weight: 700;">
                <td colspan="2" class="center" style="position: sticky; left: 0; background: var(--bg-surface-subtle); z-index: 1;">TOTAL KESELURUHAN</td>
                <td class="center">{{ $rekaps->sum('count_pns') }}</td>
                <td class="center">{{ $rekaps->sum('count_pppk') }}</td>
                <td class="center">{{ $rekaps->sum('count_paruh') }}</td>
                <td class="center">{{ $rekaps->sum('count_total') }}</td>
                
                <td class="center" style="color: var(--danger-text);">{{ $rekaps->sum('blm_gaji_pns') }}</td>
                <td class="center" style="color: var(--danger-text);">{{ $rekaps->sum('blm_gaji_pppk') }}</td>
                <td class="center" style="color: var(--danger-text);">{{ $rekaps->sum('blm_gaji_paruh') }}</td>
                <td class="center" style="color: var(--danger-text);">{{ $rekaps->sum('blm_gaji_total') }}</td>
                
                <td class="center" style="color: var(--warning-text);">{{ $rekaps->sum('blm_tpp_pns') }}</td>
                <td class="center" style="color: var(--warning-text);">{{ $rekaps->sum('blm_tpp_pppk') }}</td>
                <td class="center" style="color: var(--warning-text);">{{ $rekaps->sum('blm_tpp_paruh') }}</td>
                <td class="center" style="color: var(--warning-text);">{{ $rekaps->sum('blm_tpp_total') }}</td>
                
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
                
                <td class="money" style="background: var(--warning-subtle); color: var(--warning-text);">
                    {{ number_format($rekaps->sum('nom_gaji_total') + $rekaps->sum('nom_tpp_total'), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
