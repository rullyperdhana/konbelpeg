@extends('layouts.app')

@section('title', 'Laporan Daftar Pegawai per SKPD/UPT/Satker')
@section('page_title', 'Laporan Daftar Pegawai')

@section('content')
<style>
    /* Tab Navigation */
    .rep-tab-pills {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .rep-tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        transition: all 0.15s ease;
    }

    .rep-tab-pill:hover {
        color: var(--text-main);
        background: var(--bg-surface-hover);
    }

    .rep-tab-pill.active {
        background: var(--luno-primary);
        color: #ffffff;
        border-color: var(--luno-primary);
        box-shadow: 0 4px 12px rgba(76, 53, 222, 0.25);
    }

    /* Filter Box */
    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .filter-label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .filter-select, .filter-input {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: var(--luno-primary);
        box-shadow: 0 0 0 3px rgba(76, 53, 222, 0.15);
    }

    /* Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }
    .badge-pns {
        background: rgba(76, 53, 222, 0.1);
        color: var(--luno-primary);
        border: 1px solid rgba(76, 53, 222, 0.25);
    }
    .badge-pppk {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .badge-pppk-pw {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .badge-golru {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: var(--bg-surface-subtle, rgba(0,0,0,0.05));
        color: var(--text-main);
        border: 1px solid var(--border-color);
    }

    /* Tree View for Pegawai */
    .tree-pegawai-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .tree-pegawai-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        background: var(--bg-surface);
        cursor: pointer;
        user-select: none;
        gap: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .tree-pegawai-header:hover {
        background: var(--bg-surface-hover);
    }

    .tree-caret {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        transition: transform 0.2s ease;
        color: var(--text-muted);
    }

    .tree-pegawai-card.open .tree-pegawai-header .tree-caret {
        transform: rotate(90deg);
        color: var(--luno-primary);
    }

    .tree-pegawai-body {
        display: none;
        padding: 14px 18px;
        background: rgba(0, 0, 0, 0.015);
    }

    .tree-pegawai-card.open .tree-pegawai-body {
        display: block;
    }

    .tree-upt-section {
        margin-bottom: 16px;
        padding-left: 14px;
        border-left: 2px solid rgba(76, 53, 222, 0.25);
    }

    .tree-upt-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .tree-satker-section {
        margin-bottom: 12px;
        padding-left: 12px;
        border-left: 2px dashed rgba(6, 182, 212, 0.3);
    }

    .tree-satker-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
    }

    /* TomSelect Custom Styling for LUNO Theme */
    .ts-wrapper {
        width: 100%;
    }
    .ts-wrapper .ts-control {
        min-height: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface) !important;
        color: var(--text-main) !important;
        font-size: 13px;
        padding: 0 12px !important;
        box-shadow: none !important;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .ts-wrapper.focus .ts-control {
        border-color: var(--luno-primary) !important;
        box-shadow: 0 0 0 3px rgba(76, 53, 222, 0.15) !important;
    }
    .ts-wrapper .ts-control input {
        color: var(--text-main) !important;
        font-size: 13px;
        background: transparent !important;
    }
    .ts-dropdown {
        border-radius: 8px !important;
        background: var(--bg-surface) !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        color: var(--text-main) !important;
        margin-top: 4px !important;
        z-index: 1000 !important;
    }
    .ts-dropdown .option {
        padding: 8px 12px !important;
        font-size: 12.5px !important;
        color: var(--text-main) !important;
    }
    .ts-dropdown .active {
        background: rgba(76, 53, 222, 0.1) !important;
        color: var(--luno-primary) !important;
        font-weight: 600;
    }
    .ts-dropdown .selected {
        background: rgba(76, 53, 222, 0.15) !important;
        color: var(--luno-primary) !important;
        font-weight: 700;
    }
</style>

<div class="app-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2>Laporan Daftar Pegawai per SKPD / UPT / Satker</h2>
        <p>Laporan rincian, hierarki, dan rekapitulasi pegawai berdasarkan struktur unit kerja daerah.</p>
        <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(76, 53, 222, 0.2);">
                <i class="ph ph-users"></i> {{ number_format($totalPegawai) }} Total Pegawai
            </span>
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(59, 130, 246, 0.08); color: #2563eb; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(59, 130, 246, 0.2);">
                <i class="ph ph-identification-card"></i> {{ number_format($totalPns) }} PNS
            </span>
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(16, 185, 129, 0.08); color: #059669; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="ph ph-briefcase"></i> {{ number_format($totalPppk) }} PPPK
            </span>
            @if($totalPppkPw > 0)
                <span style="font-size: 11.5px; font-weight: 600; background: rgba(245, 158, 11, 0.08); color: #d97706; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.2);">
                    <i class="ph ph-clock"></i> {{ number_format($totalPppkPw) }} PPPK Paruh Waktu
                </span>
            @endif
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(6, 182, 212, 0.08); color: #0891b2; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(6, 182, 212, 0.2);">
                <i class="ph ph-buildings"></i> {{ number_format($totalUnitKerja) }} Unit Terdata
            </span>
        </div>
    </div>

    <!-- Export & Print Actions -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Cetak PDF -->
        <a href="{{ route('laporan.pegawai.export_pdf', request()->query()) }}" target="_blank" class="btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #ef4444; color: white; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.25);" title="Cetak dokumen resmi PDF">
            <i class="ph ph-printer" style="font-size: 16px;"></i>
            <span>Cetak PDF</span>
        </a>

        <!-- Ekspor Excel -->
        <a href="{{ route('laporan.pegawai.export_excel', request()->query()) }}" class="btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #10b981; color: white; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);" title="Unduh ke format Microsoft Excel">
            <i class="ph ph-file-xls" style="font-size: 16px;"></i>
            <span>Ekspor Excel</span>
        </a>
    </div>
</div>

<!-- Tab Navigation -->
<div class="rep-tab-pills">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'rinci', 'page' => 1]) }}" class="rep-tab-pill {{ $tab === 'rinci' ? 'active' : '' }}">
        <i class="ph ph-table" style="font-size: 17px;"></i>
        <span>1. Rincian Pegawai (Tabel Detail)</span>
        <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 12px; background: {{ $tab === 'rinci' ? 'rgba(255,255,255,0.25)' : 'rgba(76,53,222,0.1)' }}; color: {{ $tab === 'rinci' ? '#fff' : 'var(--luno-primary)' }};">
            {{ number_format($totalPegawai) }}
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'hierarki', 'page' => 1]) }}" class="rep-tab-pill {{ $tab === 'hierarki' ? 'active' : '' }}">
        <i class="ph ph-tree-structure" style="font-size: 17px;"></i>
        <span>2. Tampilan Hierarki (Pohon Unit)</span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'rekap', 'page' => 1]) }}" class="rep-tab-pill {{ $tab === 'rekap' ? 'page' : '' }} {{ $tab === 'rekap' ? 'active' : '' }}">
        <i class="ph ph-chart-pie-slice" style="font-size: 17px;"></i>
        <span>3. Rekapitulasi per SKPD & UPT</span>
    </a>
</div>

<!-- Multi-level Filter Card -->
<div class="filter-card">
    <form action="/laporan/pegawai" method="GET" id="filterForm">
        <input type="hidden" name="tab" value="{{ $tab }}">
        
        <div class="filter-grid">
            <!-- Filter SKPD Induk (Searchable / Ketik) -->
            <div>
                <label class="filter-label">SKPD (Induk)</label>
                <select name="skpd" id="select_skpd" placeholder="Ketik untuk mencari SKPD..." autocomplete="off">
                    <option value="">Semua SKPD (42 SKPD)</option>
                    @foreach($allSkpd as $s)
                        <option value="{{ $s }}" {{ $skpd === $s ? 'selected' : '' }}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter UPT / Cabang -->
            <div>
                <label class="filter-label">UPT / Cabang / Sekolah</label>
                <select name="upt" id="select_upt" class="filter-select" onchange="onUptChange()">
                    <option value="">Semua UPT / Cabang</option>
                    @foreach($availableUpts as $u)
                        <option value="{{ $u }}" {{ $upt === $u ? 'selected' : '' }}>
                            {{ $u }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Satker -->
            <div>
                <label class="filter-label">Satuan Kerja (Satker)</label>
                <select name="satker" id="select_satker" class="filter-select">
                    <option value="">Semua Satuan Kerja</option>
                    @foreach($availableSatkers as $sat)
                        <option value="{{ $sat }}" {{ $satker === $sat ? 'selected' : '' }}>
                            {{ $sat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pegawai -->
            <div>
                <label class="filter-label">Status Pegawai</label>
                <select name="status_pegawai" class="filter-select">
                    <option value="">Semua Status</option>
                    @foreach($allStatus as $st)
                        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenis Pegawai -->
            <div>
                <label class="filter-label">Jenis Pegawai</label>
                <select name="jenis_pegawai" class="filter-select">
                    <option value="">Semua Jenis</option>
                    @foreach($allJenis as $jn)
                        <option value="{{ $jn }}" {{ $jenis === $jn ? 'selected' : '' }}>
                            {{ $jn }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cari NIP / Nama / Jabatan -->
            <div>
                <label class="filter-label">Cari Pegawai / Jabatan</label>
                <input type="text" name="search" class="filter-input" placeholder="Ketik NIP, Nama, atau Jabatan..." value="{{ $search }}">
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 12.5px; color: var(--text-muted);">Tampilkan:</label>
                <select name="per_page" class="filter-select" style="width: auto; height: 32px; padding: 0 8px; font-size: 12px;">
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 baris</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 baris</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 baris</option>
                    <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200 baris</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                @if($skpd || $upt || $satker || $status || $jenis || $search)
                    <a href="/laporan/pegawai?tab={{ $tab }}" class="btn" style="padding: 7px 14px; border-radius: 8px; font-size: 12.5px; background: var(--bg-surface-hover); color: var(--text-main); text-decoration: none; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 5px;">
                        <i class="ph ph-arrow-counter-clockwise"></i> Reset Filter
                    </a>
                @endif
                <button type="submit" class="btn btn-primary" style="padding: 7px 18px; border-radius: 8px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph ph-funnel"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Content View Based on Active Tab -->
@if($tab === 'rinci')
    <!-- TAB 1: RINCIAN TABEL PEGAWAI -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 140px;">NIP</th>
                    <th>Nama Pegawai & Golru</th>
                    <th>Jabatan & Jenis</th>
                    <th>Status</th>
                    <th>SKPD (Induk)</th>
                    <th>UPT / Cabang</th>
                    <th>Satuan Kerja (Satker)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pegawais as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $pegawais->firstItem() + $index }}</td>
                    <td style="font-family: monospace; font-size: 12px; font-weight: 600; color: var(--text-main);">
                        {{ $item->nip }}
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main); line-height: 1.3;">
                            {{ $item->nama }}
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; margin-top: 3px;">
                            <span class="badge-golru">{{ $item->golru ?: '-' }}</span>
                            @if($item->jk)
                                <span style="font-size: 10.5px; color: var(--text-muted);">
                                    &bull; {{ $item->jk === 'LAKI-LAKI' ? 'L' : 'P' }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 500; color: var(--text-main); line-height: 1.3;">
                            {{ $item->jabatan?->nama ?? '-' }}
                        </div>
                        @if($item->jenis_pegawai)
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                {{ $item->jenis_pegawai }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($item->status_pegawai === 'PNS')
                            <span class="badge-status badge-pns">PNS</span>
                        @elseif($item->status_pegawai === 'PPPK')
                            <span class="badge-status badge-pppk">PPPK</span>
                        @else
                            <span class="badge-status badge-pppk-pw">{{ $item->status_pegawai ?? '-' }}</span>
                        @endif
                    </td>
                    <td style="font-weight: 600; color: var(--text-main); font-size: 12px;">
                        {{ $item->unitKerja?->skpd ?? '-' }}
                    </td>
                    <td style="font-size: 12px; color: var(--text-main);">
                        {{ $item->unitKerja?->upt && $item->unitKerja->upt !== '-' ? $item->unitKerja->upt : '-' }}
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ $item->unitKerja?->satker && $item->unitKerja->satker !== '-' ? $item->unitKerja->satker : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 45px 20px; color: var(--text-muted);">
                        <i class="ph ph-magnifying-glass" style="font-size: 36px; opacity: 0.4; display: block; margin-bottom: 8px;"></i>
                        <strong>Data Pegawai Tidak Ditemukan</strong>
                        <div style="font-size: 12.5px; margin-top: 4px;">Silakan sesuaikan kriteria filter SKPD, UPT, atau kata kunci pencarian Anda.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrapper">
            <div style="font-size: 13px; color: var(--text-muted);">
                Menampilkan {{ $pegawais->firstItem() ?? 0 }} - {{ $pegawais->lastItem() ?? 0 }} dari {{ number_format($pegawais->total()) }} data pegawai
            </div>
            <div style="display: flex; gap: 8px;">
                @if(!$pegawais->onFirstPage())
                    <a href="{{ $pegawais->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">&laquo; Prev</a>
                @endif
                @if($pegawais->hasMorePages())
                    <a href="{{ $pegawais->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">Next &raquo;</a>
                @endif
            </div>
        </div>
    </div>

@elseif($tab === 'hierarki')
    <!-- TAB 2: POHON HIERARKI PEGAWAI PER SKPD / UPT / SATKER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 13px; color: var(--text-muted);">
            Menampilkan struktur pohon hierarki pegawai untuk <strong>{{ count($treeData) }}</strong> SKPD Induk terpilih.
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn" onclick="expandAllPegawaiTree()" style="font-size: 12px; padding: 6px 12px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-main); cursor: pointer;">
                <i class="ph ph-arrows-out-simple"></i> Buka Semua
            </button>
            <button type="button" class="btn" onclick="collapseAllPegawaiTree()" style="font-size: 12px; padding: 6px 12px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-main); cursor: pointer;">
                <i class="ph ph-arrows-in-simple"></i> Tutup Semua
            </button>
        </div>
    </div>

    @forelse($treeData as $skpdName => $skpdGroup)
        <div class="tree-pegawai-card open">
            <div class="tree-pegawai-header" onclick="this.parentElement.classList.toggle('open')">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="tree-caret"><i class="ph ph-caret-right" style="font-weight: bold;"></i></span>
                    <i class="ph ph-buildings" style="font-size: 20px; color: var(--luno-primary);"></i>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-main);">
                            {{ $skpdName }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            SKPD INDUK &bull; {{ count($skpdGroup['upts']) }} UPT / Cabang
                        </div>
                    </div>
                </div>
                <div>
                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: rgba(76, 53, 222, 0.1); color: var(--luno-primary);">
                        {{ number_format($skpdGroup['total_pegawai']) }} Pegawai
                    </span>
                </div>
            </div>

            <div class="tree-pegawai-body">
                @foreach($skpdGroup['upts'] as $uptName => $uptGroup)
                    <div class="tree-upt-section">
                        <div class="tree-upt-title">
                            <i class="ph ph-folder-notch-open" style="color: #0891b2; font-size: 16px;"></i>
                            <span>{{ $uptName }}</span>
                            <span style="font-size: 11px; font-weight: 600; color: #0891b2; background: rgba(6, 182, 212, 0.08); padding: 1px 7px; border-radius: 10px;">
                                {{ number_format($uptGroup['total_pegawai']) }} Pegawai
                            </span>
                        </div>

                        @foreach($uptGroup['satkers'] as $satkerName => $satkerGroup)
                            <div class="tree-satker-section">
                                <div class="tree-satker-title">
                                    <i class="ph ph-stack" style="font-size: 14px; color: var(--text-muted);"></i>
                                    <span>{{ $satkerName }}</span>
                                    <span style="font-size: 10.5px; color: var(--text-muted);">
                                        ({{ count($satkerGroup['items']) }} pegawai)
                                    </span>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 4px; margin-bottom: 8px;">
                                    @foreach($satkerGroup['items'] as $peg)
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                                <span style="font-family: monospace; font-size: 11px; font-weight: 600; color: var(--text-muted);">{{ $peg->nip }}</span>
                                                <span style="font-weight: 600; color: var(--text-main);">{{ $peg->nama }}</span>
                                                <span class="badge-golru">{{ $peg->golru ?: '-' }}</span>
                                                <span style="color: var(--text-muted); font-size: 11.5px;">&bull; {{ $peg->jabatan?->nama ?? '-' }}</span>
                                            </div>
                                            <div style="flex-shrink: 0;">
                                                @if($peg->status_pegawai === 'PNS')
                                                    <span class="badge-status badge-pns" style="font-size: 10px;">PNS</span>
                                                @elseif($peg->status_pegawai === 'PPPK')
                                                    <span class="badge-status badge-pppk" style="font-size: 10px;">PPPK</span>
                                                @else
                                                    <span class="badge-status badge-pppk-pw" style="font-size: 10px;">{{ $peg->status_pegawai }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 45px 20px; background: var(--bg-surface); border: 1px dashed var(--border-color); border-radius: 12px; color: var(--text-muted);">
            <i class="ph ph-tree-structure" style="font-size: 36px; opacity: 0.4; display: block; margin-bottom: 8px;"></i>
            <strong>Tidak Ada Data Hierarki yang Sesuai</strong>
            <div style="font-size: 12.5px; margin-top: 4px;">Pilih salah satu SKPD pada filter di atas untuk menelusuri hierarki pegawai.</div>
        </div>
    @endforelse

@else
    <!-- TAB 3: REKAPITULASI JUMLAH PEGAWAI PER SKPD & UPT -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>{{ $skpd ? 'Nama UPT / Cabang' : 'Nama SKPD (Induk)' }}</th>
                    <th style="text-align: center; width: 130px;">Jumlah Satker</th>
                    <th style="text-align: center; width: 110px;">PNS</th>
                    <th style="text-align: center; width: 110px;">PPPK</th>
                    <th style="text-align: center; width: 130px;">PPPK Paruh Waktu</th>
                    <th style="text-align: center; width: 130px;">Total Pegawai</th>
                    <th style="text-align: center; width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: 600; color: var(--text-main);">
                        @if($skpd)
                            {{ $item->nama_grup }}
                        @else
                            <a href="/laporan/pegawai?tab=rinci&skpd={{ urlencode($item->nama_grup) }}" style="color: var(--text-main); text-decoration: none;">
                                {{ $item->nama_grup }}
                            </a>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: rgba(6, 182, 212, 0.08); color: #0891b2;">
                            {{ number_format($item->total_satker) }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #2563eb;">
                        {{ number_format($item->total_pns) }}
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #059669;">
                        {{ number_format($item->total_pppk) }}
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #d97706;">
                        {{ number_format($item->total_pppk_pw) }}
                    </td>
                    <td style="text-align: center; font-weight: 700; color: var(--luno-primary); font-size: 13.5px;">
                        {{ number_format($item->total_pegawai) }}
                    </td>
                    <td style="text-align: center;">
                        @if($skpd)
                            <a href="/laporan/pegawai?tab=rinci&skpd={{ urlencode($skpd) }}&upt={{ urlencode($item->nama_grup) }}" class="btn" style="padding: 4px 10px; font-size: 11.5px; border-radius: 6px; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); text-decoration: none; border: 1px solid rgba(76, 53, 222, 0.2);">
                                Rincian &raquo;
                            </a>
                        @else
                            <a href="/laporan/pegawai?tab=rinci&skpd={{ urlencode($item->nama_grup) }}" class="btn" style="padding: 4px 10px; font-size: 11.5px; border-radius: 6px; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); text-decoration: none; border: 1px solid rgba(76, 53, 222, 0.2);">
                                Rincian &raquo;
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Data rekapitulasi tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr style="background: var(--bg-surface-subtle); font-weight: 800;">
                    <td colspan="2" style="text-align: center;">TOTAL KESELURUHAN</td>
                    <td style="text-align: center; color: #0891b2;">{{ number_format($rekaps->sum('total_satker')) }}</td>
                    <td style="text-align: center; color: #2563eb;">{{ number_format($rekaps->sum('total_pns')) }}</td>
                    <td style="text-align: center; color: #059669;">{{ number_format($rekaps->sum('total_pppk')) }}</td>
                    <td style="text-align: center; color: #d97706;">{{ number_format($rekaps->sum('total_pppk_pw')) }}</td>
                    <td style="text-align: center; color: var(--luno-primary);">{{ number_format($rekaps->sum('total_pegawai')) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
@endif

<script>
    let skpdTomSelect = null;

    document.addEventListener("DOMContentLoaded", function() {
        const skpdEl = document.getElementById('select_skpd');
        if (skpdEl && typeof TomSelect !== 'undefined') {
            skpdTomSelect = new TomSelect('#select_skpd', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Ketik untuk mencari SKPD Induk...',
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            skpdTomSelect.on('change', function(value) {
                onSkpdChange(value);
            });
        }
    });

    function onSkpdChange(skpd) {
        if (skpd === undefined) {
            skpd = document.getElementById('select_skpd').value;
        }
        const uptSelect = document.getElementById('select_upt');
        const satkerSelect = document.getElementById('select_satker');

        uptSelect.innerHTML = '<option value="">Memuat UPT...</option>';
        satkerSelect.innerHTML = '<option value="">Semua Satuan Kerja</option>';

        if (!skpd) {
            uptSelect.innerHTML = '<option value="">Semua UPT / Cabang</option>';
            return;
        }

        fetch('/laporan/pegawai/filter-options?skpd=' + encodeURIComponent(skpd))
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">Semua UPT / Cabang</option>';
                if (data.upts && data.upts.length > 0) {
                    data.upts.forEach(u => {
                        html += `<option value="${u}">${u}</option>`;
                    });
                }
                uptSelect.innerHTML = html;
            })
            .catch(() => {
                uptSelect.innerHTML = '<option value="">Semua UPT / Cabang</option>';
            });
    }

    function onUptChange() {
        const skpd = document.getElementById('select_skpd').value;
        const upt = document.getElementById('select_upt').value;
        const satkerSelect = document.getElementById('select_satker');

        if (!skpd || !upt) {
            satkerSelect.innerHTML = '<option value="">Semua Satuan Kerja</option>';
            return;
        }

        satkerSelect.innerHTML = '<option value="">Memuat Satker...</option>';

        fetch('/laporan/pegawai/filter-options?skpd=' + encodeURIComponent(skpd) + '&upt=' + encodeURIComponent(upt))
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">Semua Satuan Kerja</option>';
                if (data.satkers && data.satkers.length > 0) {
                    data.satkers.forEach(s => {
                        html += `<option value="${s}">${s}</option>`;
                    });
                }
                satkerSelect.innerHTML = html;
            })
            .catch(() => {
                satkerSelect.innerHTML = '<option value="">Semua Satuan Kerja</option>';
            });
    }

    function expandAllPegawaiTree() {
        document.querySelectorAll('.tree-pegawai-card').forEach(el => el.classList.add('open'));
    }

    function collapseAllPegawaiTree() {
        document.querySelectorAll('.tree-pegawai-card').forEach(el => el.classList.remove('open'));
    }
</script>
@endsection
