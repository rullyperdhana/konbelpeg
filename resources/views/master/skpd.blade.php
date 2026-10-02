@extends('layouts.app')

@section('title', 'Master Data Unit Kerja (SKPD)')
@section('page_title', 'Master Data SKPD')

@section('content')
<style>
    /* Tabs Navigation */
    .skpd-tab-pills {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .skpd-tab-pill {
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

    .skpd-tab-pill:hover {
        color: var(--text-main);
        background: var(--bg-surface-hover);
    }

    .skpd-tab-pill.active {
        background: var(--luno-primary);
        color: #ffffff;
        border-color: var(--luno-primary);
        box-shadow: 0 4px 12px rgba(76, 53, 222, 0.25);
    }

    .badge-count {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-upt-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
        background: rgba(76, 53, 222, 0.08);
        color: var(--luno-primary);
        text-decoration: none;
        border: 1px solid rgba(76, 53, 222, 0.2);
        transition: all 0.15s;
    }

    .btn-upt-link:hover {
        background: var(--luno-primary);
        color: #ffffff;
    }

    /* Tree View Styling */
    .tree-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tree-toolbar-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 16px;
    }

    .tree-ctrl-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: var(--bg-surface);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.15s;
    }

    .tree-ctrl-btn:hover {
        background: var(--bg-surface-hover);
        border-color: var(--text-muted);
    }

    /* Node Level 1: SKPD Induk Card */
    .tree-node-skpd {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .tree-node-skpd:hover {
        border-color: rgba(76, 53, 222, 0.35);
    }

    .tree-node-skpd.open {
        border-color: rgba(76, 53, 222, 0.4);
        box-shadow: 0 4px 14px rgba(76, 53, 222, 0.05);
    }

    .tree-skpd-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        background: var(--bg-surface);
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
        gap: 12px;
    }

    .tree-skpd-header:hover {
        background: var(--bg-surface-hover);
    }

    .tree-toggle-caret {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        color: var(--text-muted);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 14px;
        flex-shrink: 0;
    }

    .tree-node-skpd.open > .tree-skpd-header .tree-toggle-caret {
        transform: rotate(90deg);
        color: var(--luno-primary);
    }

    .tree-icon-box {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .tree-skpd-body {
        display: none;
        border-top: 1px solid var(--border-color);
        background: rgba(0, 0, 0, 0.015);
        padding: 14px 20px 18px 24px;
    }

    .tree-node-skpd.open > .tree-skpd-body {
        display: block;
    }

    /* Node Level 2: UPT / Cabang Group */
    .tree-upt-group {
        position: relative;
        margin-top: 10px;
        margin-bottom: 10px;
        padding-left: 20px;
        border-left: 2px solid rgba(76, 53, 222, 0.25);
    }

    .tree-upt-group::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 0;
        width: 14px;
        height: 2px;
        background: rgba(76, 53, 222, 0.25);
    }

    .tree-upt-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s;
        gap: 10px;
    }

    .tree-upt-header:hover {
        border-color: rgba(76, 53, 222, 0.4);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .tree-upt-group.open > .tree-upt-header .tree-toggle-caret {
        transform: rotate(90deg);
        color: var(--luno-primary);
    }

    .tree-upt-body {
        display: none;
        margin-top: 8px;
        padding-left: 18px;
        position: relative;
    }

    .tree-upt-group.open > .tree-upt-body {
        display: block;
    }

    /* Node Level 3: Satker Leaf Item */
    .tree-satker-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 14px;
        margin-bottom: 6px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-left: 3px solid #0891b2;
        border-radius: 6px;
        transition: all 0.12s;
        gap: 8px;
    }

    .tree-satker-item:hover {
        background: var(--bg-surface-hover);
        border-color: #0891b2;
    }

    /* Badges */
    .badge-pill-upt {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(6, 182, 212, 0.08);
        color: #0891b2;
        border: 1px solid rgba(6, 182, 212, 0.2);
    }

    .badge-pill-satker {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(100, 116, 139, 0.08);
        color: #475569;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    .badge-pill-pegawai {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(16, 185, 129, 0.08);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-tree-add {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(76, 53, 222, 0.08);
        color: var(--luno-primary);
        border: 1px solid rgba(76, 53, 222, 0.2);
        cursor: pointer;
        transition: all 0.12s;
    }

    .btn-tree-add:hover {
        background: var(--luno-primary);
        color: #ffffff;
    }
</style>

<div class="app-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2>Master Data Unit Kerja (SKPD)</h2>
        <p>Pengelolaan hierarki SKPD Induk, Unit Pelaksana Teknis (UPT), dan Satuan Kerja.</p>
        <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(76, 53, 222, 0.2);">
                <i class="ph ph-buildings"></i> {{ number_format($totalSkpdInduk) }} SKPD Induk
            </span>
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(6, 182, 212, 0.08); color: #0891b2; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(6, 182, 212, 0.2);">
                <i class="ph ph-tree-structure"></i> {{ number_format($totalUnitKerja) }} Total Unit Kerja / UPT
            </span>
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(16, 185, 129, 0.08); color: #059669; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="ph ph-users"></i> {{ number_format($totalPegawai) }} Total Pegawai Terdata
            </span>
        </div>
    </div>
    
    <!-- Action & Print Menu Buttons -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Cetak PDF -->
        <a href="{{ route('master.skpd.export_pdf', request()->query()) }}" target="_blank" class="btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #ef4444; color: white; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.25);" title="Buka dan cetak dokumen PDF">
            <i class="ph ph-printer" style="font-size: 16px;"></i>
            <span>Cetak PDF</span>
        </a>

        <!-- Ekspor Excel -->
        <a href="{{ route('master.skpd.export_excel', request()->query()) }}" class="btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #10b981; color: white; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);" title="Unduh data ke Microsoft Excel">
            <i class="ph ph-file-xls" style="font-size: 16px;"></i>
            <span>Ekspor Excel</span>
        </a>

        <!-- Tambah Unit Kerja -->
        <button class="btn btn-primary" onclick="openAddModal()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-plus-circle" style="font-size: 16px;"></i>
            <span>Tambah Unit Kerja</span>
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if(isset($errors) && $errors->any())
    <div class="alert alert-error">Terjadi kesalahan pada input form.</div>
@endif

<!-- Tab Navigation (3 Pilihan Tampilan) -->
<div class="skpd-tab-pills">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'tree', 'page' => 1]) }}" class="skpd-tab-pill {{ $tab === 'tree' ? 'active' : '' }}">
        <i class="ph ph-tree-structure" style="font-size: 17px;"></i>
        <span>Pohon Hierarki (Tree View)</span>
        <span class="badge-count" style="background: {{ $tab === 'tree' ? 'rgba(255,255,255,0.25)' : 'rgba(76,53,222,0.1)' }}; color: {{ $tab === 'tree' ? '#fff' : 'var(--luno-primary)' }};">
            42 SKPD
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'rekap', 'page' => 1]) }}" class="skpd-tab-pill {{ $tab === 'rekap' ? 'active' : '' }}">
        <i class="ph ph-buildings" style="font-size: 17px;"></i>
        <span>Ringkasan 42 SKPD Induk</span>
        <span class="badge-count" style="background: {{ $tab === 'rekap' ? 'rgba(255,255,255,0.25)' : 'rgba(76,53,222,0.1)' }}; color: {{ $tab === 'rekap' ? '#fff' : 'var(--luno-primary)' }};">
            42
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'rinci', 'page' => 1]) }}" class="skpd-tab-pill {{ $tab === 'rinci' ? 'active' : '' }}">
        <i class="ph ph-table" style="font-size: 17px;"></i>
        <span>Rincian 1.638 Unit Kerja</span>
        <span class="badge-count" style="background: {{ $tab === 'rinci' ? 'rgba(255,255,255,0.25)' : 'rgba(6,182,212,0.1)' }}; color: {{ $tab === 'rinci' ? '#fff' : '#0891b2' }};">
            1.638
        </span>
    </a>
</div>

<!-- Toolbar & Search Filters -->
<div class="toolbar" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <form action="/master/skpd" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; width: 100%; max-width: 820px;">
        <input type="hidden" name="tab" value="{{ $tab }}">

        @if($tab === 'rinci')
            <!-- Dropdown Filter SKPD Induk -->
            <select name="skpd_filter" onchange="this.form.submit()" style="height: 38px; padding: 0 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-main); font-size: 13px; max-width: 340px;">
                <option value="">Semua SKPD Induk (42 SKPD)</option>
                @foreach($allSkpd as $skpdName)
                    <option value="{{ $skpdName }}" {{ $skpdFilter == $skpdName ? 'selected' : '' }}>
                        {{ $skpdName }}
                    </option>
                @endforeach
            </select>
        @endif

        <div class="search-box" style="margin: 0; flex: 1; min-width: 250px;">
            <input type="text" name="search" id="skpdSearchInput" placeholder="{{ $tab === 'rekap' ? 'Cari nama SKPD Induk...' : ($tab === 'tree' ? 'Cari SKPD, Cabang/UPT, atau Satker...' : 'Cari nama SKPD, UPT, atau Satker...') }}" value="{{ request('search') }}">
            <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
        </div>

        @if(request('search') || request('skpd_filter'))
            <a href="/master/skpd?tab={{ $tab }}" class="btn" style="padding: 7px 12px; border-radius: 8px; font-size: 12px; background: var(--bg-surface-hover); color: var(--text-main); text-decoration: none; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 5px;">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </a>
        @endif
    </form>
</div>

<!-- Content View -->
@if($tab === 'tree')
    <!-- TAB 1: POHON HIERARKI (TREE VIEW) -->
    <div class="tree-toolbar-box">
        <div style="font-size: 13px; color: var(--text-main); font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(76, 53, 222, 0.1); color: var(--luno-primary); font-size: 12px;">
                <i class="ph ph-info"></i>
            </span>
            <span>Klik pada nama <strong>SKPD</strong> atau <strong>UPT</strong> untuk membuka & menutup cabang hierarki.</span>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" class="tree-ctrl-btn" onclick="expandAllTree()">
                <i class="ph ph-arrows-out-simple"></i> Buka Semua
            </button>
            <button type="button" class="tree-ctrl-btn" onclick="collapseAllTree()">
                <i class="ph ph-arrows-in-simple"></i> Tutup Semua
            </button>
        </div>
    </div>

    <div class="tree-wrapper" id="treeContainer">
        @forelse($tree as $skpdName => $skpdData)
            @php
                $isSkpdMatch = !empty($search) && (stripos($skpdName, $search) !== false);
                $hasMatchingChild = false;
                if (!empty($search)) {
                    foreach ($skpdData['upts'] as $uName => $uData) {
                        if (stripos($uName, $search) !== false) {
                            $hasMatchingChild = true;
                            break;
                        }
                        foreach ($uData['items'] as $sItem) {
                            if (stripos($sItem->satker ?? '', $search) !== false) {
                                $hasMatchingChild = true;
                                break 2;
                            }
                        }
                    }
                }
                // Auto-open if search matches
                $skpdOpen = !empty($search) && ($isSkpdMatch || $hasMatchingChild);
            @endphp

            <div class="tree-node-skpd {{ $skpdOpen ? 'open' : '' }}" data-skpd="{{ strtolower($skpdName) }}">
                <!-- Level 1 Header: SKPD Induk -->
                <div class="tree-skpd-header" onclick="toggleSkpdNode(this)">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                        <span class="tree-toggle-caret">
                            <i class="ph ph-caret-right" style="font-weight: bold;"></i>
                        </span>
                        <div class="tree-icon-box" style="background: rgba(76, 53, 222, 0.1); color: var(--luno-primary);">
                            <i class="ph ph-buildings"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 13.5px; font-weight: 700; color: var(--text-main); line-height: 1.3;">
                                {{ $skpdName }}
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                SKPD INDUK
                            </div>
                        </div>
                    </div>

                    <!-- Right Badges & Quick Action -->
                    <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;" onclick="event.stopPropagation();">
                        <span class="badge-pill-upt" title="Jumlah UPT / Sekolah di bawah SKPD ini">
                            <i class="ph ph-git-branch"></i> {{ number_format($skpdData['total_upt']) }} UPT
                        </span>
                        <span class="badge-pill-satker" title="Jumlah Satuan Kerja">
                            <i class="ph ph-stack"></i> {{ number_format($skpdData['total_satker']) }} Satker
                        </span>
                        <span class="badge-pill-pegawai" title="Total Pegawai terdata">
                            <i class="ph ph-users"></i> {{ number_format($skpdData['total_pegawai']) }}
                        </span>
                        <button type="button" class="btn-tree-add" onclick="openAddModal('{{ addslashes($skpdName) }}', '')" title="Tambah UPT di bawah SKPD ini">
                            <i class="ph ph-plus"></i> Tambah UPT
                        </button>
                    </div>
                </div>

                <!-- Level 1 Body: UPT Groups -->
                <div class="tree-skpd-body">
                    @foreach($skpdData['upts'] as $uptName => $uptData)
                        @php
                            $isUptMatch = !empty($search) && (stripos($uptName, $search) !== false);
                            $hasMatchingSatker = false;
                            if (!empty($search)) {
                                foreach ($uptData['items'] as $sItem) {
                                    if (stripos($sItem->satker ?? '', $search) !== false) {
                                        $hasMatchingSatker = true;
                                        break;
                                    }
                                }
                            }
                            $uptOpen = !empty($search) && ($isUptMatch || $hasMatchingSatker);
                            $itemCount = count($uptData['items']);
                            $isSingleLeaf = ($itemCount === 1) && ($uptData['items'][0]->satker === $uptName || $uptData['items'][0]->satker === '-' || empty($uptData['items'][0]->satker));
                        @endphp

                        @if($isSingleLeaf)
                            @php $singleItem = $uptData['items'][0]; @endphp
                            <!-- Single Leaf UPT (no sub-satker redundant nesting) -->
                            <div class="tree-upt-group" style="padding-left: 20px;">
                                <div class="tree-satker-item" style="border-left-color: var(--luno-primary);">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                                        <div class="tree-icon-box" style="width: 28px; height: 28px; font-size: 15px; border-radius: 6px; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary);">
                                            <i class="ph ph-buildings"></i>
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: var(--text-main);">
                                                {{ $uptName }}
                                            </div>
                                            <div style="font-size: 10.5px; color: var(--text-muted);">
                                                UPT TUNGGAL
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="badge-pill-pegawai">
                                            <i class="ph ph-users"></i> {{ number_format($singleItem->pegawais_count) }}
                                        </span>
                                        <div class="action-btns" style="margin: 0;">
                                            <button type="button" class="btn-edit" onclick="openEditModal({{ $singleItem->id }}, '{{ addslashes($singleItem->skpd) }}', '{{ addslashes($singleItem->upt ?? '') }}', '{{ addslashes($singleItem->satker ?? '') }}')">Edit</button>
                                            <form action="/master/skpd/{{ $singleItem->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Unit Kerja ini?');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Branch UPT (Contains multiple satkers or distinct satker items) -->
                            <div class="tree-upt-group {{ $uptOpen ? 'open' : '' }}" data-upt="{{ strtolower($uptName) }}">
                                <div class="tree-upt-header" onclick="toggleUptNode(this)">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                                        <span class="tree-toggle-caret">
                                            <i class="ph ph-caret-right" style="font-weight: bold;"></i>
                                        </span>
                                        <div class="tree-icon-box" style="width: 28px; height: 28px; font-size: 15px; border-radius: 6px; background: rgba(6, 182, 212, 0.1); color: #0891b2;">
                                            @if(stripos($uptName, 'SEKOLAH') !== false || stripos($uptName, 'SMAN') !== false || stripos($uptName, 'SMKN') !== false)
                                                <i class="ph ph-graduation-cap"></i>
                                            @elseif(stripos($uptName, 'KESEHATAN') !== false || stripos($uptName, 'PUSKESMAS') !== false || stripos($uptName, 'RSUD') !== false)
                                                <i class="ph ph-first-aid"></i>
                                            @else
                                                <i class="ph ph-folder-notch-open"></i>
                                            @endif
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: var(--text-main);">
                                                {{ $uptName }}
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 8px;" onclick="event.stopPropagation();">
                                        <span class="badge-pill-satker">
                                            <i class="ph ph-article"></i> {{ number_format($itemCount) }} Satker
                                        </span>
                                        <span class="badge-pill-pegawai">
                                            <i class="ph ph-users"></i> {{ number_format($uptData['total_pegawai']) }}
                                        </span>
                                        <button type="button" class="btn-tree-add" onclick="openAddModal('{{ addslashes($skpdName) }}', '{{ addslashes($uptName === 'KANTOR INDUK / SEKRETARIAT' ? '' : $uptName) }}')" title="Tambah Satker di bawah UPT ini">
                                            <i class="ph ph-plus"></i> Tambah Satker
                                        </button>
                                    </div>
                                </div>

                                <!-- Level 3: Satker Leaf Items -->
                                <div class="tree-upt-body">
                                    @foreach($uptData['items'] as $item)
                                        <div class="tree-satker-item" data-satker="{{ strtolower($item->satker ?? '') }}">
                                            <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                                <i class="ph ph-file-text" style="color: #0891b2; font-size: 15px; flex-shrink: 0;"></i>
                                                <div style="font-size: 12px; color: var(--text-main); font-weight: 500; word-break: break-word;">
                                                    {{ $item->satker && $item->satker !== '-' ? $item->satker : ($item->upt ?: 'KANTOR INDUK') }}
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                <span class="badge-pill-pegawai" style="font-size: 10.5px; padding: 2px 7px;">
                                                    <i class="ph ph-users"></i> {{ number_format($item->pegawais_count) }}
                                                </span>
                                                <div class="action-btns" style="margin: 0;">
                                                    <button type="button" class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->skpd) }}', '{{ addslashes($item->upt ?? '') }}', '{{ addslashes($item->satker ?? '') }}')">Edit</button>
                                                    <form action="/master/skpd/{{ $item->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Unit Kerja ini?');" style="margin: 0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-delete">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 50px 20px; background: var(--bg-surface); border: 1px dashed var(--border-color); border-radius: 12px;">
                <i class="ph ph-magnifying-glass" style="font-size: 40px; color: var(--text-muted); opacity: 0.5;"></i>
                <h4 style="margin: 12px 0 6px 0; color: var(--text-main);">Data Unit Kerja Tidak Ditemukan</h4>
                <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Tidak ada hasil yang sesuai dengan kata kunci pencarian "{{ request('search') }}".</p>
                <a href="/master/skpd?tab=tree" class="btn btn-primary" style="margin-top: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph ph-arrow-counter-clockwise"></i> Reset Pencarian
                </a>
            </div>
        @endforelse
    </div>

@elseif($tab === 'rekap')
    <!-- TAB 2: REKAP 42 SKPD INDUK -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Nama SKPD (Induk)</th>
                    <th style="text-align: center; width: 140px;">Jumlah UPT / Sekolah</th>
                    <th style="text-align: center; width: 120px;">Satuan Kerja</th>
                    <th style="text-align: center; width: 140px;">Total Pegawai</th>
                    <th style="text-align: center; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekaps as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: 600; color: var(--text-main);">
                        <a href="/master/skpd?tab=tree&search={{ urlencode($item->skpd) }}" style="color: var(--text-main); text-decoration: none;" title="Buka pohon hierarki {{ $item->skpd }}">
                            {{ $item->skpd }}
                        </a>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary);">
                            {{ number_format($item->total_upt) }} Unit
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: rgba(6, 182, 212, 0.08); color: #0891b2;">
                            {{ number_format($item->total_satker) }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: 700; color: #059669;">
                        {{ number_format($item->total_pegawai) }} Orang
                    </td>
                    <td style="text-align: center;">
                        <a href="/master/skpd?tab=tree&search={{ urlencode($item->skpd) }}" class="btn-upt-link" title="Buka pohon hierarki {{ $item->skpd }}">
                            <i class="ph ph-tree-structure"></i>
                            <span>Buka Pohon</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Data SKPD tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($rekaps) > 0)
            <tfoot>
                <tr style="background: var(--bg-surface-subtle); font-weight: 800;">
                    <td colspan="2" style="text-align: center;">TOTAL KESELURUHAN ({{ count($rekaps) }} SKPD INDUK)</td>
                    <td style="text-align: center; color: var(--luno-primary);">{{ number_format($rekaps->sum('total_upt')) }} Unit</td>
                    <td style="text-align: center; color: #0891b2;">{{ number_format($rekaps->sum('total_satker')) }}</td>
                    <td style="text-align: center; color: #059669;">{{ number_format($rekaps->sum('total_pegawai')) }} Orang</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

@else
    <!-- TAB 3: RINCIAN FLAT 1.638 UNIT KERJA (UPT / SATKER) -->
    @if($skpdFilter)
        <div style="background: rgba(76, 53, 222, 0.06); border: 1px solid rgba(76, 53, 222, 0.15); border-radius: 10px; padding: 10px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 13px; color: var(--text-main);">
                Menampilkan UPT di bawah: <strong>{{ $skpdFilter }}</strong> ({{ $unitKerjas->total() }} unit kerja)
            </div>
            <a href="/master/skpd?tab=rinci" style="font-size: 12px; color: var(--luno-primary); font-weight: 600; text-decoration: none;">
                Tampilkan Semua SKPD &raquo;
            </a>
        </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>SKPD (Induk)</th>
                    <th>UPT / Cabang</th>
                    <th>Satuan Kerja (Satker)</th>
                    <th style="text-align: center; width: 90px;">Pegawai</th>
                    <th style="text-align: right; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unitKerjas as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $unitKerjas->firstItem() + $index }}</td>
                    <td style="font-weight: 600; color: var(--text-main);">{{ $item->skpd ?? '-' }}</td>
                    <td>{{ $item->upt ?? '-' }}</td>
                    <td>{{ $item->satker ?? '-' }}</td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary);">
                            {{ number_format($item->pegawais_count ?? 0) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <button class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->skpd) }}', '{{ addslashes($item->upt ?? '') }}', '{{ addslashes($item->satker ?? '') }}')">Edit</button>
                            <form action="/master/skpd/{{ $item->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Unit Kerja ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Data Unit Kerja tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            <div style="font-size: 13px; color: var(--text-muted);">
                Menampilkan {{ $unitKerjas->firstItem() ?? 0 }} - {{ $unitKerjas->lastItem() ?? 0 }} dari {{ $unitKerjas->total() }} data
            </div>
            <div style="display: flex; gap: 8px;">
                @if(!$unitKerjas->onFirstPage())
                    <a href="{{ $unitKerjas->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">&laquo; Prev</a>
                @endif
                @if($unitKerjas->hasMorePages())
                    <a href="{{ $unitKerjas->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">Next &raquo;</a>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- Modal Tambah -->
<div class="modal-overlay" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Unit Kerja</h3>
            <button class="btn-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="/master/skpd" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama SKPD (Induk) *</label>
                <input type="text" name="skpd" id="add_skpd" required placeholder="Contoh: DINAS PENDIDIKAN DAN KEBUDAYAAN">
            </div>
            <div class="form-group">
                <label>Nama UPT</label>
                <input type="text" name="upt" id="add_upt" placeholder="Kosongkan atau isi '-' jika tidak ada">
            </div>
            <div class="form-group">
                <label>Nama Satker</label>
                <input type="text" name="satker" id="add_satker" placeholder="Kosongkan atau isi '-' jika tidak ada">
            </div>
            <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn-edit" onclick="closeModal('addModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Unit Kerja</h3>
            <button class="btn-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama SKPD (Induk) *</label>
                <input type="text" name="skpd" id="edit_skpd" required>
            </div>
            <div class="form-group">
                <label>Nama UPT</label>
                <input type="text" name="upt" id="edit_upt">
            </div>
            <div class="form-group">
                <label>Nama Satker</label>
                <input type="text" name="satker" id="edit_satker">
            </div>
            <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn-edit" onclick="closeModal('editModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn btn-primary">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openAddModal(skpd = '', upt = '') {
        document.getElementById('add_skpd').value = skpd;
        document.getElementById('add_upt').value = upt;
        document.getElementById('add_satker').value = '';
        openModal('addModal');
        
        // Auto-focus next empty input
        setTimeout(() => {
            if (!skpd) {
                document.getElementById('add_skpd').focus();
            } else if (!upt) {
                document.getElementById('add_upt').focus();
            } else {
                document.getElementById('add_satker').focus();
            }
        }, 150);
    }

    function openEditModal(id, skpd, upt, satker) {
        document.getElementById('editForm').action = '/master/skpd/' + id;
        document.getElementById('edit_skpd').value = skpd !== '-' ? skpd : '';
        document.getElementById('edit_upt').value = upt !== '-' ? upt : '';
        document.getElementById('edit_satker').value = satker !== '-' ? satker : '';
        openModal('editModal');
    }

    /* Tree View Interaction Functions */
    function toggleSkpdNode(headerElement) {
        const node = headerElement.closest('.tree-node-skpd');
        if (node) {
            node.classList.toggle('open');
        }
    }

    function toggleUptNode(headerElement) {
        const group = headerElement.closest('.tree-upt-group');
        if (group) {
            group.classList.toggle('open');
        }
    }

    function expandAllTree() {
        document.querySelectorAll('.tree-node-skpd').forEach(node => node.classList.add('open'));
        document.querySelectorAll('.tree-upt-group').forEach(group => group.classList.add('open'));
    }

    function collapseAllTree() {
        document.querySelectorAll('.tree-node-skpd').forEach(node => node.classList.remove('open'));
        document.querySelectorAll('.tree-upt-group').forEach(group => group.classList.remove('open'));
    }
</script>
@endsection
