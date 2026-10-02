@extends('layouts.app')

@section('title', 'Realisasi TPP')
@section('page_title', 'Realisasi Tambahan Penghasilan Pegawai (TPP)')

@section('content')
<div class="app-header">
    <div>
        <h2>Laporan & Realisasi Belanja TPP</h2>
        <p>Laporan komprehensif realisasi Tambahan Penghasilan Pegawai (TPP), potongan iuran/pajak, dan rincian SKPD.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openModal('uploadModal')">
            <i class="ph ph-upload-simple"></i> Upload Excel
        </button>
    </div>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
@if($errors->any()) <div class="alert alert-error">Terjadi kesalahan saat upload file.</div> @endif

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon"><i class="ph ph-coins"></i></div>
        <div class="summary-info">
            <h4>Total TPP Bruto (Sesuai Filter)</h4>
            <div class="amount">Rp {{ number_format($totalTppBruto, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon green"><i class="ph ph-hand-coins"></i></div>
        <div class="summary-info">
            <h4>Total Dibayarkan (Netto)</h4>
            <div class="amount">Rp {{ number_format($totalDibayarkan, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<form action="/realisasi/tpp" method="GET" class="toolbar" id="filterForm">
    <select name="tipe_laporan">
        <option value="rekap" {{ request('tipe_laporan') == 'rekap' ? 'selected' : '' }}>Tampilan: Rekapitulasi SKPD</option>
        <option value="rinci" {{ request('tipe_laporan') == 'rinci' ? 'selected' : '' }}>Tampilan: Rincian Pegawai</option>
    </select>

    <select name="periode_filter">
        <option value="">Semua Periode</option>
        @foreach($periodes as $p)
            <option value="{{ $p }}" {{ request('periode_filter') == $p ? 'selected' : '' }}>{{ $p }}</option>
        @endforeach
    </select>

    <div style="flex-grow: 1; min-width: 250px;">
        <select name="skpd_filter" id="skpd-filter">
            <option value="">Semua SKPD</option>
            @foreach($filterUnitKerjas as $uk)
                <option value="{{ $uk }}" {{ request('skpd_filter') == $uk ? 'selected' : '' }}>
                    {{ $uk }}
                </option>
            @endforeach
        </select>
    </div>
    
    <button type="submit"><i class="ph ph-funnel"></i> Terapkan Filter</button>

    <div style="width: 1px; height: 30px; background: #e2e8f0; margin: 0 8px;"></div>

    <a href="javascript:void(0)" onclick="exportData('pdf')" class="btn-export" style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
        <i class="ph ph-file-pdf"></i> Export PDF
    </a>
    <a href="javascript:void(0)" onclick="exportData('excel')" class="btn-export" style="color: #16a34a; border-color: #bbf7d0; background: #f0fdf4;">
        <i class="ph ph-file-xls"></i> Export Excel
    </a>
</form>

<script>
function exportData(type) {
    const form = document.getElementById('filterForm');
    const oldAction = form.action;
    form.action = '/realisasi/tpp/export/' + type;
    form.submit();
    form.action = oldAction; // restore
}
</script>

<div class="table-container">
    @if($tipeLaporan == 'rekap')
        <table class="table-rekap">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col-1" style="width: 42px; text-align: center; vertical-align: middle;">NO</th>
                    <th rowspan="2" class="sticky-col-2" style="min-width: 220px; text-align: left; vertical-align: middle;">SKPD / UNIT KERJA</th>
                    <th colspan="8" style="background: rgba(76, 53, 222, 0.08) !important; color: var(--luno-primary) !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(76, 53, 222, 0.2) !important;">JUMLAH PEGAWAI (YANG DIBAYARKAN TPP)</th>
                    <th colspan="4" style="background: rgba(239, 68, 68, 0.08) !important; color: #dc2626 !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(239, 68, 68, 0.2) !important;">BELUM DIBAYAR</th>
                    <th colspan="8" style="background: rgba(6, 182, 212, 0.08) !important; color: #0891b2 !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(6, 182, 212, 0.2) !important;">TOTAL REALISASI TPP</th>
                </tr>
                <tr>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 65px;">PNS</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 85px;">PPPK<br>Guru</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 85px;">PPPK<br>Kesehatan</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 85px;">PPPK<br>Teknis</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 95px;">Paruh Waktu<br>Guru</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 95px;">Paruh Waktu<br>Kesehatan</th>
                    <th style="background: rgba(76, 53, 222, 0.04) !important; font-size: 10px; text-align: center; min-width: 95px;">Paruh Waktu<br>Teknis</th>
                    <th style="background: rgba(76, 53, 222, 0.12) !important; color: var(--luno-primary) !important; font-weight: 800; font-size: 10px; text-align: center; min-width: 75px;">Jumlah<br>Pegawai</th>
                    
                    <th style="background: rgba(239, 68, 68, 0.04) !important; color: #dc2626 !important; font-size: 10px; text-align: center; min-width: 60px;">PNS</th>
                    <th style="background: rgba(239, 68, 68, 0.04) !important; color: #dc2626 !important; font-size: 10px; text-align: center; min-width: 60px;">PPPK</th>
                    <th style="background: rgba(239, 68, 68, 0.04) !important; color: #dc2626 !important; font-size: 10px; text-align: center; min-width: 75px;">Paruh<br>Waktu</th>
                    <th style="background: rgba(239, 68, 68, 0.12) !important; color: #dc2626 !important; font-weight: 800; font-size: 10px; text-align: center; min-width: 65px;">Total</th>
                    
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 105px;">PNS</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Guru</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Kesehatan</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Teknis</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Guru</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Kesehatan</th>
                    <th style="background: rgba(6, 182, 212, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Teknis</th>
                    <th style="background: rgba(6, 182, 212, 0.14) !important; color: #0891b2 !important; font-weight: 800; font-size: 10px; text-align: center; min-width: 120px;">Jumlah<br>TPP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $rekap)
                <tr>
                    <td class="center sticky-col-1">{{ $index + 1 }}</td>
                    <td class="sticky-col-2" style="font-weight: 600; min-width: 220px; font-size: 11.5px;">{{ $rekap->skpd }}</td>
                    
                    <td class="center">{{ $rekap->count_pns }}</td>
                    <td class="center">{{ $rekap->count_pppk_guru }}</td>
                    <td class="center">{{ $rekap->count_pppk_kes }}</td>
                    <td class="center">{{ $rekap->count_pppk_teknis }}</td>
                    <td class="center">{{ $rekap->count_paruh_guru }}</td>
                    <td class="center">{{ $rekap->count_paruh_kes }}</td>
                    <td class="center">{{ $rekap->count_paruh_teknis }}</td>
                    <td class="center" style="font-weight: 800; background: rgba(76, 53, 222, 0.06);">{{ $rekap->count_total }}</td>
                    
                    <td class="center" style="background: rgba(239, 68, 68, 0.04); color: #dc2626;">{{ $rekap->count_belum_dibayar_pns }}</td>
                    <td class="center" style="background: rgba(239, 68, 68, 0.04); color: #dc2626;">{{ $rekap->count_belum_dibayar_pppk }}</td>
                    <td class="center" style="background: rgba(239, 68, 68, 0.04); color: #dc2626;">{{ $rekap->count_belum_dibayar_paruh }}</td>
                    <td class="center" style="font-weight: 800; background: rgba(239, 68, 68, 0.1); color: #dc2626;">{{ $rekap->count_belum_dibayar_total }}</td>
                    
                    <td class="money">{{ number_format($rekap->tpp_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_pppk_teknis, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->tpp_paruh_teknis, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: 800; background: rgba(6, 182, 212, 0.06); color: #0891b2;">{{ number_format($rekap->tpp_total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="22" class="center" style="padding: 40px; color: var(--text-muted);">Belum ada data realisasi.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr style="background: var(--bg-surface-subtle); font-weight: bold;">
                    <td colspan="2" class="center sticky-col-1" style="font-weight: 800;">TOTAL KESELURUHAN</td>
                    <td class="center">{{ $rekaps->sum('count_pns') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_guru') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_kes') }}</td>
                    <td class="center">{{ $rekaps->sum('count_pppk_teknis') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_guru') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_kes') }}</td>
                    <td class="center">{{ $rekaps->sum('count_paruh_teknis') }}</td>
                    <td class="center" style="font-weight: 800; background: rgba(76, 53, 222, 0.06);">{{ $rekaps->sum('count_total') }}</td>
                    
                    <td class="center" style="color: #dc2626;">{{ $rekaps->sum('count_belum_dibayar_pns') }}</td>
                    <td class="center" style="color: #dc2626;">{{ $rekaps->sum('count_belum_dibayar_pppk') }}</td>
                    <td class="center" style="color: #dc2626;">{{ $rekaps->sum('count_belum_dibayar_paruh') }}</td>
                    <td class="center" style="font-weight: 800; background: rgba(239, 68, 68, 0.1); color: #dc2626;">{{ $rekaps->sum('count_belum_dibayar_total') }}</td>
                    
                    <td class="money">{{ number_format($rekaps->sum('tpp_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_pppk_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('tpp_paruh_teknis'), 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: 800; background: rgba(6, 182, 212, 0.06); color: #0891b2;">{{ number_format($rekaps->sum('tpp_total'), 0, ',', '.') }}</td>
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
                    <th style="text-align: left;">Pegawai</th>
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
                    $tppRecords = $pegawai->realisasiTpps;
                    $hasTpp = $tppRecords->isNotEmpty();
                    $tppBrutoTotal = $tppRecords->sum('tpp_bruto');
                    $nominalPltTotal = $tppRecords->sum('nominal_plt');
                    $totalPotongan = $tppRecords->sum(fn ($t) => $t->pph_21 + $t->potongan_lainnya + $t->iuran_iwp);
                    $totalDibayarkan = $tppRecords->sum('total_dibayarkan');
                @endphp
                <tr style="{{ !$hasTpp ? 'background-color: #fef2f2;' : '' }}">
                    <td class="center">{{ $realisasis->firstItem() + $index }}</td>
                    <td class="center" style="font-size: 11px;">
                        @if(!$hasTpp)
                            <span style="font-weight: 600; color: #64748b;">{{ $periode ?: 'Semua Periode' }}</span>
                        @elseif($tppRecords->count() === 1)
                            <div style="font-weight: 700; color: #1e293b;">{{ $tppRecords[0]->periode_kas ?: $tppRecords[0]->periode }}</div>
                            @if($tppRecords[0]->bulan_kinerja)
                                <div style="font-size: 10px; color: #0284c7; margin-top: 2px;">
                                    <i class="ph ph-briefcase"></i> {{ $tppRecords[0]->bulan_kinerja }} ({{ $tppRecords[0]->tahap_bayar ?: 'Reguler' }})
                                </div>
                            @endif
                        @else
                            <div style="font-weight: 700; color: #1e293b;">{{ $tppRecords[0]->periode_kas ?: $periode }}</div>
                            <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; margin-top: 2px;">
                                {{ $tppRecords->count() }}x Pencairan Kas
                            </span>
                            <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">
                                @foreach($tppRecords as $t)
                                    <div>&bull; {{ $t->tahap_bayar ?: 'Tahap' }}: {{ $t->bulan_kinerja }} (Rp {{ number_format($t->total_dibayarkan, 0, ',', '.') }})</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">{{ $pegawai->nama ?? 'Tidak Diketahui' }}</div>
                        <div style="font-size: 11px; color: #64748b;">NIP: {{ $pegawai->nip ?? '-' }} <span style="display:inline-block; margin-left: 6px; padding: 2px 6px; background: #e2e8f0; border-radius: 4px; font-size: 10px;">{{ $pegawai->status_pegawai ?? '-' }}</span></div>
                    </td>
                    <td>
                        <div style="font-weight: 500; font-size: 11px;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</div>
                        <div style="font-size: 10px; color: #64748b;">{{ $pegawai->jabatan?->nama ?? '-' }}</div>
                    </td>
                    @if($hasTpp)
                        <td class="money">Rp {{ number_format($tppBrutoTotal, 0, ',', '.') }}</td>
                        <td class="money" style="color: #0284c7;">Rp {{ number_format($nominalPltTotal, 0, ',', '.') }}</td>
                        <td class="money" style="color: #ef4444;">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                        <td class="money" style="color: #16a34a; font-weight: 700;">Rp {{ number_format($totalDibayarkan, 0, ',', '.') }}</td>
                    @else
                        <td class="center" colspan="4">
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Rp 0 (Belum Dibayarkan)</span>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 40px; color: #64748b;">Data belum ada atau tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            <div style="font-size: 13px; color: #64748b;">
                Menampilkan {{ $realisasis->firstItem() ?? 0 }} - {{ $realisasis->lastItem() ?? 0 }} dari {{ $realisasis->total() }} pegawai
            </div>
            <div style="display: flex; gap: 8px;">
                @if(!$realisasis->onFirstPage())
                    <a href="{{ $realisasis->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
                @endif
                @if($realisasis->hasMorePages())
                    <a href="{{ $realisasis->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Modal Upload -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal-content" style="max-width: 680px; width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-file-arrow-up" style="font-size: 20px; color: var(--luno-primary);"></i>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700;">Upload & Pencatatan Realisasi TPP</h3>
            </div>
            <button class="btn-close" onclick="closeModal('uploadModal')">&times;</button>
        </div>
        <div class="card" style="padding: 24px; border: none; box-shadow: none;">
            <div style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <i class="ph ph-info" style="font-size: 20px; color: #3b82f6; margin-top: 2px;"></i>
                    <div style="font-size: 12.5px; color: #475569; line-height: 1.5;">
                        <strong style="color: #0f172a;">Pencatatan Kas Berbasis Dual-Field:</strong><br>
                        Realisasi kas dicatat berdasarkan <strong>Bulan Realisasi Kas (SP2D)</strong> untuk kepatuhan laporan kas daerah/BPKAD, dan dipadukan dengan <strong>Bulan Hak Kinerja</strong> ASN. Untuk bulan Desember yang memiliki 2x pencairan (Kinerja Nov & Kinerja Des), silakan unggah bertahap tanpa saling menimpa.
                    </div>
                </div>
            </div>

            <form id="importTppForm" action="/realisasi/tpp/import" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- File Input -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #334155;">Pilih File Excel / CSV (.xlsx, .csv, .xls)</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <p style="margin: 6px 0 0 0; font-size: 11px; color: #94a3b8;">Format kolom yang didukung: NIP, Periode, Jabatan, TPP Bruto, TPP Netto, PPh 21, Potongan TPP (Lainnya), Iuran IWP, Yang Dibayarkan (Transfer).</p>
                </div>

                <!-- Dual Field Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #334155;">
                            <i class="ph ph-calendar-check" style="color: #2563eb;"></i> Bulan Realisasi Kas (SP2D)
                        </label>
                        <select name="periode_kas" id="modal_periode_kas" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            @php
                                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $currentYear = date('Y');
                            @endphp
                            @foreach($months as $m)
                                <option value="{{ $m }} {{ $currentYear }}" {{ $m == 'Februari' ? 'selected' : '' }}>{{ $m }} {{ $currentYear }}</option>
                            @endforeach
                            @foreach($months as $m)
                                <option value="{{ $m }} {{ $currentYear - 1 }}">{{ $m }} {{ $currentYear - 1 }}</option>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">Bulan terbitnya SP2D / uang keluar dari kas daerah.</small>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #334155;">
                            <i class="ph ph-briefcase" style="color: #059669;"></i> Bulan Hak Kinerja Pegawai
                        </label>
                        <select name="bulan_kinerja" id="modal_bulan_kinerja" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            @foreach($months as $m)
                                <option value="{{ $m }} {{ $currentYear }}" {{ $m == 'Januari' ? 'selected' : '' }}>{{ $m }} {{ $currentYear }}</option>
                            @endforeach
                            <option value="THR {{ $currentYear }}">THR {{ $currentYear }}</option>
                            <option value="Gaji 13 {{ $currentYear }}">Gaji 13 {{ $currentYear }}</option>
                            @foreach($months as $m)
                                <option value="{{ $m }} {{ $currentYear - 1 }}">{{ $m }} {{ $currentYear - 1 }}</option>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">Bulan capaian kerja / hak kinerja ASN yang dibayar.</small>
                    </div>
                </div>

                <!-- Tahap Pencairan & Keterangan -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #334155;">
                            <i class="ph ph-steps" style="color: #d97706;"></i> Tahap Pencairan
                        </label>
                        <select name="tahap_bayar" id="modal_tahap_bayar" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="Reguler" selected>Reguler (Pencairan Bulanan Biasa)</option>
                            <option value="Tahap 1">Tahap 1 (Awal Desember - Kinerja Nov)</option>
                            <option value="Tahap 2">Tahap 2 (Akhir Desember - Kinerja Des)</option>
                            <option value="Susulan">Susulan</option>
                            <option value="THR">THR</option>
                            <option value="Gaji 13">Gaji Ke-13</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #334155;">
                            Catatan / No. SP2D (Opsional)
                        </label>
                        <input type="text" name="keterangan_bayar" id="modal_keterangan_bayar" class="form-control" placeholder="Contoh: SP2D No. 012/TPP/2026" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <!-- Alert Khusus Desember -->
                <div id="desemberNotice" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 12px; color: #1e40af;">
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <i class="ph ph-info" style="font-size: 18px; color: #2563eb; margin-top: 1px;"></i>
                        <div>
                            <strong>Pencairan Bulan Desember (2 Tahap):</strong><br>
                            Bulan Desember memiliki 2 pencairan terpisah:<br>
                            &bull; <strong>Tahap 1</strong>: Pembayaran Kinerja November (dicairkan awal Desember)<br>
                            &bull; <strong>Tahap 2</strong>: Pembayaran Kinerja Desember (dicairkan akhir Desember)<br>
                            Keduanya tersimpan terpisah dan aman dari saling menimpa.
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
                    <button type="button" class="btn" onclick="closeModal('uploadModal')" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnUploadTpp">
                        <i class="ph ph-upload"></i> Upload & Proses Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Script for Progress Bar & Auto-Sync -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Dual-field Auto Sync
            const selKas = document.getElementById('modal_periode_kas');
            const selKinerja = document.getElementById('modal_bulan_kinerja');
            const selTahap = document.getElementById('modal_tahap_bayar');
            const desNotice = document.getElementById('desemberNotice');

            const monthsOrder = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            function handleKasChange() {
                if (!selKas || !selKinerja || !selTahap) return;
                const val = selKas.value;
                const parts = val.split(' ');
                const m = parts[0];
                const y = parts[1] || new Date().getFullYear();

                if (m === 'Desember') {
                    if (desNotice) desNotice.style.display = 'block';
                    if (selTahap.value === 'Reguler') {
                        selTahap.value = 'Tahap 1';
                    }
                    if (selTahap.value === 'Tahap 1') {
                        selKinerja.value = 'November ' + y;
                    } else if (selTahap.value === 'Tahap 2') {
                        selKinerja.value = 'Desember ' + y;
                    }
                } else {
                    if (desNotice) desNotice.style.display = 'none';
                    selTahap.value = 'Reguler';
                    const idx = monthsOrder.indexOf(m);
                    if (idx > 0) {
                        selKinerja.value = monthsOrder[idx - 1] + ' ' + y;
                    } else if (idx === 0) {
                        selKinerja.value = 'Desember ' + (parseInt(y) - 1);
                    }
                }
            }

            function handleTahapChange() {
                if (!selKas || !selKinerja || !selTahap) return;
                const tahap = selTahap.value;
                const val = selKas.value;
                const parts = val.split(' ');
                const y = parts[1] || new Date().getFullYear();

                if (tahap === 'Tahap 1') {
                    selKas.value = 'Desember ' + y;
                    selKinerja.value = 'November ' + y;
                    if (desNotice) desNotice.style.display = 'block';
                } else if (tahap === 'Tahap 2') {
                    selKas.value = 'Desember ' + y;
                    selKinerja.value = 'Desember ' + y;
                    if (desNotice) desNotice.style.display = 'block';
                }
            }

            if (selKas) selKas.addEventListener('change', handleKasChange);
            if (selTahap) selTahap.addEventListener('change', handleTahapChange);

            document.getElementById('importTppForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);
                const uploadId = Date.now().toString() + Math.random().toString(36).substring(2, 7);
                formData.append('upload_id', uploadId);
                
                const btn = document.getElementById('btnUploadTpp');
                btn.disabled = true;
                btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memproses...';
                
                // Tampilkan SweetAlert Progress
                Swal.fire({
                    title: 'Mengunggah & Memproses Data Realisasi TPP',
                    html: `
                        <div style="margin-top: 15px; margin-bottom: 10px; text-align: left; font-size: 13px; color: #64748b;" id="progress-text">Menyiapkan file...</div>
                        <div style="width: 100%; background-color: #e2e8f0; border-radius: 999px; height: 12px; overflow: hidden;">
                            <div id="progress-bar" style="width: 0%; height: 100%; background-color: #3b82f6; transition: width 0.3s ease;"></div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Mulai polling
                const pollInterval = setInterval(() => {
                    fetch('/upload/progress?id=' + uploadId)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.progress > 0) {
                                let percent = data.total > 0 ? Math.round((data.progress / data.total) * 100) : 0;
                                if (percent > 100) percent = 100;
                                
                                const text = data.total > 0 
                                    ? `Memproses baris ke-${data.progress.toLocaleString()} dari ${data.total.toLocaleString()} (${percent}%)`
                                    : `Memproses baris ke-${data.progress.toLocaleString()}...`;
                                    
                                document.getElementById('progress-text').innerText = text;
                                if (data.total > 0) {
                                    document.getElementById('progress-bar').style.width = percent + '%';
                                } else {
                                    let currWidth = parseInt(document.getElementById('progress-bar').style.width) || 0;
                                    let newWidth = (currWidth + 5) % 100;
                                    document.getElementById('progress-bar').style.width = newWidth + '%';
                                }
                            }
                        }).catch(err => console.error(err));
                }, 1000);
                
                // Lakukan upload via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    clearInterval(pollInterval);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Selesai!',
                            text: 'Data Realisasi TPP berhasil diimpor.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat mengimpor data.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ph ph-upload"></i> Upload & Proses Data';
                    }
                })
                .catch(error => {
                    clearInterval(pollInterval);
                    console.error(error);
                    window.location.reload(); 
                });
            });
        </script>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('skpd-filter')) {
            new TomSelect("#skpd-filter",{ create: false, sortField: { field: "text", direction: "asc" } });
        }
    });

    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
@endsection
