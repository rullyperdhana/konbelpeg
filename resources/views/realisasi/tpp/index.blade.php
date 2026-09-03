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
                    $tpp = $pegawai->realisasiTpps->first();
                    $totalPotongan = $tpp ? ($tpp->pph_21 + $tpp->potongan_lainnya + $tpp->iuran_iwp) : 0;
                @endphp
                <tr style="{{ !$tpp ? 'background-color: #fef2f2;' : '' }}">
                    <td class="center">{{ $realisasis->firstItem() + $index }}</td>
                    <td class="center" style="font-weight: 600;">{{ $periode ?: 'Semua Periode' }}</td>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">{{ $pegawai->nama ?? 'Tidak Diketahui' }}</div>
                        <div style="font-size: 11px; color: #64748b;">NIP: {{ $pegawai->nip ?? '-' }} <span style="display:inline-block; margin-left: 6px; padding: 2px 6px; background: #e2e8f0; border-radius: 4px; font-size: 10px;">{{ $pegawai->status_pegawai ?? '-' }}</span></div>
                    </td>
                    <td>
                        <div style="font-weight: 500; font-size: 11px;">{{ $pegawai->unitKerja?->skpd ?? '-' }}</div>
                        <div style="font-size: 10px; color: #64748b;">{{ $pegawai->jabatan?->nama ?? '-' }}</div>
                    </td>
                    @if($tpp)
                        <td class="money">Rp {{ number_format($tpp->tpp_bruto, 0, ',', '.') }}</td>
                        <td class="money" style="color: #0284c7;">Rp {{ number_format($tpp->nominal_plt, 0, ',', '.') }}</td>
                        <td class="money" style="color: #ef4444;">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                        <td class="money" style="color: #16a34a;">Rp {{ number_format($tpp->total_dibayarkan, 0, ',', '.') }}</td>
                    @else
                        <td class="center" colspan="3">
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Rp 0 (Belum Dibayarkan)</span>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="center" style="padding: 40px; color: #64748b;">Data belum ada atau tidak ditemukan.</td>
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
            <h3>Upload Data Realisasi TPP</h3>
            <button class="btn-close" onclick="closeModal('uploadModal')">&times;</button>
        </div>
        <div class="card" style="padding: 20px;">
            <h2 style="margin-top: 0; font-size: 16px; margin-bottom: 16px;">Import Data Realisasi TPP (Excel)</h2>
            <form id="importTppForm" action="/realisasi/tpp/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-size: 12px; color: #64748b;">Pilih File Excel / CSV / DBF</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls,.dbf" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnUploadTpp">
                        <i class="ph ph-upload"></i>
                        Upload & Proses Data
                    </button>
                </div>
                <p style="margin-top: 8px; font-size: 12px; color: #94a3b8;">Format kolom yang didukung: NIP, Periode, Jabatan, TPP Bruto, TPP Netto, PPh 21, Potongan TPP (Lainnya), Iuran IWP, Yang Dibayarkan (Transfer).</p>
            </form>
        </div>

        <!-- Script for Progress Bar -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
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
                    title: 'Mengunggah & Memproses Data',
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
                                    // Indeterminate width behavior
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
                            text: 'Data berhasil diimpor.',
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
                    // Jika server tidak membalas JSON tapi redirect (fallback HTML)
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
