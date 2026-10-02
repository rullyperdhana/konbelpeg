@extends('layouts.app')

@section('title', 'Realisasi Gaji')
@section('page_title', 'Realisasi Gaji Pegawai')

@section('content')
<div class="app-header">
    <div>
        <h2>Laporan & Realisasi Gaji</h2>
        <p>Laporan komprehensif realisasi gaji pegawai, pemotongan, dan rekonsiliasi SKPD.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openModal('uploadModal')">
            <i class="ph ph-upload-simple"></i> Upload DBF / Excel
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
            <h4>Total Gaji Pokok (Sesuai Filter)</h4>
            <div class="amount">Rp {{ number_format($totalGajiPokok, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon green"><i class="ph ph-hand-coins"></i></div>
        <div class="summary-info">
            <h4>Total Dibayarkan (Gaji Bersih)</h4>
            <div class="amount">Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<form action="/realisasi/gaji" method="GET" class="toolbar" id="filterForm">
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

    <select name="jenis_gaji_filter">
        <option value="">Semua Kriteria Gaji</option>
        @foreach($daftarJenisGaji as $jg)
            <option value="{{ $jg }}" {{ ($jenisGajiFilter ?? '') == $jg ? 'selected' : '' }}>{{ $jg }}</option>
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
    <a href="/laporan/trace-gaji" class="btn-export" style="color: #4C35DE; border-color: #c7d2fe; background: #eef2ff; font-weight: 600;">
        <i class="ph ph-user-focus"></i> Trace Penggajian Per Orang
    </a>
</form>

<script>
function exportData(type) {
    const form = document.getElementById('filterForm');
    const oldAction = form.action;
    form.action = '/realisasi/gaji/export/' + type;
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
                    <th colspan="8" style="background: rgba(76, 53, 222, 0.08) !important; color: var(--luno-primary) !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(76, 53, 222, 0.2) !important;">JUMLAH PEGAWAI (YANG DIBAYARKAN GAJI)</th>
                    <th colspan="4" style="background: rgba(239, 68, 68, 0.08) !important; color: #dc2626 !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(239, 68, 68, 0.2) !important;">BELUM DIBAYAR</th>
                    <th colspan="8" style="background: rgba(16, 185, 129, 0.08) !important; color: #059669 !important; font-weight: 800; font-size: 11px; text-align: center; border-bottom: 2px solid rgba(16, 185, 129, 0.2) !important;">TOTAL REALISASI GAJI</th>
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
                    
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 105px;">PNS</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Guru</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Kesehatan</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 110px;">PPPK<br>Teknis</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Guru</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Kesehatan</th>
                    <th style="background: rgba(16, 185, 129, 0.04) !important; font-size: 10px; text-align: center; min-width: 115px;">Paruh Waktu<br>Teknis</th>
                    <th style="background: rgba(16, 185, 129, 0.14) !important; color: #059669 !important; font-weight: 800; font-size: 10px; text-align: center; min-width: 120px;">Jumlah<br>Gaji</th>
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
                    
                    <td class="money">{{ number_format($rekap->nominal_pns, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_pppk_teknis, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_guru, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_kes, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekap->nominal_paruh_teknis, 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: 800; background: rgba(16, 185, 129, 0.06); color: #059669;">{{ number_format($rekap->nominal_total, 0, ',', '.') }}</td>
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
                    
                    <td class="money">{{ number_format($rekaps->sum('nominal_pns'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_pppk_teknis'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_guru'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_kes'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($rekaps->sum('nominal_paruh_teknis'), 0, ',', '.') }}</td>
                    <td class="money" style="font-weight: 800; background: rgba(16, 185, 129, 0.06); color: #059669;">{{ number_format($rekaps->sum('nominal_total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Periode</th>
                    <th style="text-align: left;">Pegawai</th>
                    <th style="text-align: left;">SKPD / Jabatan</th>
                    <th style="text-align: center;">Kriteria Gaji</th>
                    <th style="text-align: right;">Gaji Pokok</th>
                    <th style="text-align: right;">Potongan</th>
                    <th style="text-align: right;">Gaji Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($realisasis as $index => $pegawai)
                @php 
                    $hasGaji = $pegawai->realisasiGajis->isNotEmpty();
                    $totalGajiPokok = $hasGaji ? $pegawai->realisasiGajis->sum('gaji_pokok') : 0;
                    $totalPotongan = $hasGaji ? $pegawai->realisasiGajis->sum(fn ($g) => $g->pajak + $g->potongan_lain + $g->iwp) : 0;
                    $totalGajiBersih = $hasGaji ? $pegawai->realisasiGajis->sum('gaji_bersih') : 0;
                @endphp
                <tr style="{{ ! $hasGaji ? 'background-color: #fef2f2;' : '' }}">
                    <td class="center">{{ $realisasis->firstItem() + $index }}</td>
                    <td class="center" style="font-weight: 600;">{{ $periode ?: 'Semua Periode' }}</td>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">{{ $pegawai->nama ?? 'Tidak Diketahui' }}</div>
                        <div style="font-size: 11px; color: #64748b;">
                            NIP: {{ $pegawai->nip ?? '-' }} 
                            <span style="display:inline-block; margin-left: 6px; padding: 2px 6px; background: #e2e8f0; border-radius: 4px; font-size: 10px;">{{ $pegawai->status_pegawai ?? '-' }}</span>
                            <a href="/laporan/trace-gaji/{{ $pegawai->id }}" style="margin-left: 6px; color: #4C35DE; text-decoration: none; font-weight: 600;" title="Trace riwayat penggajian pegawai ini">
                                <i class="ph ph-user-focus"></i> Trace Gaji
                            </a>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 500; font-size: 11px;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</div>
                        <div style="font-size: 10px; color: #64748b;">{{ $pegawai->jabatan?->nama ?? '-' }}</div>
                    </td>
                    @if($hasGaji)
                        <td class="center">
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                @foreach($pegawai->realisasiGajis as $g)
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10.5px; font-weight: 600; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                                        {{ $g->jenis_gaji ?? 'Gaji Induk' }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="money">Rp {{ number_format($totalGajiPokok, 0, ',', '.') }}</td>
                        <td class="money" style="color: #ef4444;">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                        <td class="money" style="color: #16a34a; font-weight: 600;">Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}</td>
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
    <div class="modal-content">
        <div class="modal-header">
            <h3>Upload Data Realisasi Gaji</h3>
            <button class="btn-close" onclick="closeModal('uploadModal')">&times;</button>
        </div>
        <div class="card" style="padding: 20px;">
            <form id="importGajiForm" action="/realisasi/gaji/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Pilih Periode Laporan:</label>
                    <select name="periode_import" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 16px; outline: none; background: white;">
                        <option value="">-- Pilih Periode --</option>
                        <option value="Januari 2026">Januari 2026</option>
                        <option value="Februari 2026">Februari 2026</option>
                        <option value="Maret 2026">Maret 2026</option>
                        <option value="April 2026">April 2026</option>
                        <option value="Mei 2026">Mei 2026</option>
                        <option value="Juni 2026">Juni 2026</option>
                        <option value="Juli 2026">Juli 2026</option>
                        <option value="Agustus 2026">Agustus 2026</option>
                        <option value="September 2026">September 2026</option>
                        <option value="Oktober 2026">Oktober 2026</option>
                        <option value="November 2026">November 2026</option>
                        <option value="Desember 2026">Desember 2026</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Kriteria Gaji (SIMGAJI DBF / Excel):</label>
                    <select name="jenis_gaji" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 6px; outline: none; background: white;">
                        @foreach(\App\Models\RealisasiGaji::DAFTAR_JENIS_GAJI as $jg)
                            <option value="{{ $jg }}" {{ $jg == 'Gaji Induk' ? 'selected' : '' }}>{{ $jg }}</option>
                        @endforeach
                    </select>
                    <small style="display: block; color: #64748b; font-size: 11.5px; margin-bottom: 16px;">
                        Pilih kriteria yang sesuai dengan file DBF/Excel yang diunggah. Data dengan kriteria berbeda pada bulan yang sama tidak akan saling menimpa.
                    </small>
                </div>
                <div class="mb-4">
                    <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Kelompok Pegawai (Opsional - Penanda File):</label>
                    <select name="kelompok_pegawai" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 16px; outline: none; background: white;">
                        <option value="">-- Semua / Gabungan --</option>
                        <option value="PNS">PNS & CPNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="PPPK PARUH WAKTU">PPPK Paruh Waktu</option>
                    </select>
                </div>
                <div class="upload-box" style="margin-bottom: 20px;">
                    <i class="ph ph-file-xls" style="font-size: 32px; color: #3b82f6; margin-bottom: 12px; display: block;"></i>
                    <p style="margin-bottom: 12px;">Pilih file Excel (<strong>.xlsx</strong>) atau <strong>.dbf</strong> dari komputer Anda.</p>
                    <input type="file" name="file" accept=".xlsx, .xls, .csv, .dbf" required style="display: block; margin: 0 auto;">
                </div>
                <div style="text-align: right;">
                    <button type="button" class="btn-close" onclick="closeModal('uploadModal')" style="font-size: 14px; background: #f1f5f9; padding: 10px 16px; border-radius: 8px; margin-right: 8px;">Batal</button>
                    <button type="submit" class="btn-primary" id="btnUploadGaji">Mulai Impor Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script for Progress Bar -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('importGajiForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const fileInput = form.querySelector('input[name="file"]');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire('Peringatan', 'Silakan pilih file terlebih dahulu.', 'warning');
            return;
        }

        const fileName = fileInput.files[0].name;
        const periodeSelect = form.querySelector('select[name="periode_import"]');
        const periodeVal = periodeSelect ? (periodeSelect.options[periodeSelect.selectedIndex]?.text || periodeSelect.value) : '';
        const jenisGajiSelect = form.querySelector('select[name="jenis_gaji"]');
        const jenisGajiVal = jenisGajiSelect ? (jenisGajiSelect.options[jenisGajiSelect.selectedIndex]?.text || jenisGajiSelect.value) : 'Gaji Induk';

        const formData = new FormData(form);
        const uploadId = Date.now().toString() + Math.random().toString(36).substring(2, 7);
        formData.append('upload_id', uploadId);
        
        const btn = document.getElementById('btnUploadGaji');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memproses...';
        closeModal('uploadModal');
        
        // Tampilkan SweetAlert Progress yang Informatif & Elegan
        Swal.fire({
            title: 'Memproses Data Realisasi Gaji',
            html: `
                <div style="text-align: left; font-family: inherit;">
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; font-size: 11.5px;">
                        <span style="background: #eff6ff; color: #1d4ed8; padding: 3px 8px; border-radius: 6px; font-weight: 600; border: 1px solid #bfdbfe;">
                            <i class="ph ph-calendar"></i> ${periodeVal}
                        </span>
                        <span style="background: #f0fdf4; color: #15803d; padding: 3px 8px; border-radius: 6px; font-weight: 600; border: 1px solid #bbf7d0;">
                            <i class="ph ph-tag"></i> ${jenisGajiVal}
                        </span>
                        <span style="background: #f8fafc; color: #475569; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${fileName}">
                            <i class="ph ph-file"></i> ${fileName}
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                        <div id="progress-status" style="font-size: 13px; font-weight: 600; color: #334155; max-width: 75%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            Menyiapkan dan membaca file...
                        </div>
                        <div id="progress-percent" style="font-size: 16px; font-weight: 800; color: #2563eb;">
                            0%
                        </div>
                    </div>

                    <div style="width: 100%; background-color: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; height: 14px; overflow: hidden; padding: 2px; margin-bottom: 14px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);">
                        <div id="progress-bar" style="width: 0%; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #3b82f6, #06b6d4, #10b981); transition: width 0.25s ease;"></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; text-align: center;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 4px;">
                            <div style="font-size: 11px; color: #64748b; font-weight: 500;">Diproses</div>
                            <div id="progress-count" style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin-top: 2px;">0 / 0</div>
                        </div>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 8px 4px;">
                            <div style="font-size: 11px; color: #166534; font-weight: 500;">Berhasil</div>
                            <div id="progress-success" style="font-size: 13.5px; font-weight: 700; color: #15803d; margin-top: 2px;">0</div>
                        </div>
                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 4px;">
                            <div style="font-size: 11px; color: #92400e; font-weight: 500;">NIP Gagal</div>
                            <div id="progress-failed" style="font-size: 13.5px; font-weight: 700; color: #b45309; margin-top: 2px;">0</div>
                        </div>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                // UI ready
            }
        });

        let finished = false;
        let maxProgress = 0;

        function updateUI(data) {
            if (!data || finished) return;
            const total = Number(data.total) || 0;
            const progress = Number(data.progress) || 0;
            if (progress > maxProgress) {
                maxProgress = progress;
            }
            const currentProgress = Math.max(progress, maxProgress);
            const percent = total > 0 ? Math.min(100, Math.round((currentProgress / total) * 100)) : (Number(data.percent) || 0);
            const failed = Number(data.failed) || 0;
            const success = data.success !== undefined ? Number(data.success) : Math.max(0, currentProgress - failed);

            const barEl = document.getElementById('progress-bar');
            const percentEl = document.getElementById('progress-percent');
            const countEl = document.getElementById('progress-count');
            const statusEl = document.getElementById('progress-status');
            const successEl = document.getElementById('progress-success');
            const failedEl = document.getElementById('progress-failed');

            if (barEl) barEl.style.width = percent + '%';
            if (percentEl) percentEl.innerText = percent + '%';
            if (countEl) countEl.innerText = total > 0 ? `${currentProgress.toLocaleString()} / ${total.toLocaleString()}` : `${currentProgress.toLocaleString()}`;
            if (successEl) successEl.innerText = success.toLocaleString();
            if (failedEl) failedEl.innerText = failed.toLocaleString();

            if (statusEl) {
                if (total > 0 && currentProgress >= total) {
                    statusEl.innerText = 'Menyimpan perubahan ke database...';
                } else if (total > 0) {
                    statusEl.innerText = `Memproses data (${currentProgress.toLocaleString()} / ${total.toLocaleString()})...`;
                } else if (data.message) {
                    statusEl.innerText = data.message;
                }
            }
        }

        function showSuccessModal(data) {
            finished = true;
            clearInterval(pollInterval);
            const barEl = document.getElementById('progress-bar');
            const percentEl = document.getElementById('progress-percent');
            if (barEl) barEl.style.width = '100%';
            if (percentEl) percentEl.innerText = '100%';

            let detailHtml = `
                <div style="font-size: 14px; color: #475569; margin-top: 8px; line-height: 1.5;">
                    ${data.message || 'Data realisasi gaji berhasil diimpor.'}
                </div>
            `;

            if (data.failed && data.failed > 0) {
                detailHtml += `
                    <div style="margin-top: 14px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 12.5px; color: #92400e; text-align: left; line-height: 1.4;">
                        <strong>Perhatian:</strong> Terdapat <strong>${data.failed} data</strong> yang NIP-nya belum terdaftar di Data Pegawai. Rincian telah otomatis dicatat di menu <strong>Laporan &gt; NIP Belum Terdaftar</strong>.
                    </div>
                `;
            }

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Impor Selesai!',
                    html: detailHtml,
                    confirmButtonText: 'Tutup & Lihat Data',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false,
                }).then(() => {
                    window.location.reload();
                });
            }, 300);
        }

        function showErrorModal(message) {
            finished = true;
            clearInterval(pollInterval);
            btn.disabled = false;
            btn.innerHTML = 'Mulai Impor Data';
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengimpor Data',
                text: message || 'Terjadi kesalahan saat memproses data.',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#ef4444'
            });
        }

        // Concurrent fallback polling interval
        const pollInterval = setInterval(() => {
            if (finished) {
                clearInterval(pollInterval);
                return;
            }
            fetch('/upload/progress?id=' + uploadId)
                .then(res => res.json())
                .then(data => {
                    if (!finished && data && (data.total > 0 || data.progress > 0)) {
                        updateUI(data);
                    }
                })
                .catch(err => console.debug('Progress poll error:', err));
        }, 500);

        // Upload Request via Fetch
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                let errMsg = 'Terjadi kesalahan pada server (Status: ' + response.status + ')';
                try {
                    const errJson = await response.json();
                    if (errJson.message) errMsg = errJson.message;
                } catch(e) {}
                showErrorModal(errMsg);
                return;
            }

            if (contentType.includes('text/event-stream') && response.body && response.body.getReader) {
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    buffer += decoder.decode(value, { stream: true });
                    
                    const lines = buffer.split('\n\n');
                    buffer = lines.pop(); // simpan chunk sisa yang belum lengkap
                    
                    for (const line of lines) {
                        const trimmed = line.trim();
                        if (trimmed.startsWith('data: ')) {
                            try {
                                const payload = JSON.parse(trimmed.substring(6));
                                if (payload.type === 'start') {
                                    updateUI({ total: payload.total, progress: 0, percent: 0, message: payload.message });
                                } else if (payload.type === 'progress') {
                                    updateUI(payload);
                                } else if (payload.type === 'done') {
                                    showSuccessModal(payload);
                                    return;
                                } else if (payload.type === 'error') {
                                    showErrorModal(payload.message);
                                    return;
                                }
                            } catch(err) {
                                console.debug('SSE parse error:', err);
                            }
                        }
                    }
                }

                if (!finished) {
                    showSuccessModal({ message: 'Proses impor data telah selesai.' });
                }
            } else {
                const data = await response.json();
                if (data.success) {
                    showSuccessModal(data);
                } else {
                    showErrorModal(data.message || 'Gagal memproses file.');
                }
            }
        } catch (error) {
            console.error('Upload error:', error);
            showErrorModal('Koneksi terputus atau terjadi kesalahan saat mengunggah file.');
        }
    });
</script>

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
