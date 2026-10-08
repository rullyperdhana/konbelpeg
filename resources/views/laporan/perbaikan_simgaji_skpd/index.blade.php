@extends('layouts.app')

@section('title', 'Laporan BNBA Perbaikan SKPD SIMGAJI')
@section('page_title', 'BNBA Perbaikan SKPD SIMGAJI (Acuan SIMPEG)')

@section('content')
<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <span class="badge" style="background: rgba(30, 64, 175, 0.1); color: #1e40af; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-shield-check"></i> ACUAN RESMI: SIMPEG / KONBELPEG
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                <i class="ph-bold ph-user-list"></i> FORMAT BNBA PER PEGAWAI
            </span>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">
            Laporan BNBA Perbaikan Kode & Nama SKPD SIMGAJI
        </h2>
        <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">
            Daftar nominatif perseorangan (By Name By Address) untuk perbaikan dan sinkronisasi penempatan SKPD/Satker pada SIMGAJI Taspen berdasarkan data master SIMPEG.
        </p>
        <div style="margin-top: 10px; display: inline-flex; align-items: center; gap: 8px; padding: 5px 14px; background: rgba(76, 53, 222, 0.06); border-radius: 999px; font-size: 12px; color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-database"></i> Database Acuan: <strong>{{ $activeDbf['filename'] ?? '-' }}</strong> &bull; Pembaruan Terakhir: {{ $cachedAt ?? '-' }}
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.refresh') }}" class="btn btn-export" title="Kalkulasi ulang pemetaan data dari DBF aktif">
            <i class="ph-bold ph-arrows-clockwise"></i> Refresh Cache
        </a>

        <!-- Separator -->
        <div style="width: 1px; height: 26px; background: var(--border-color); margin: 0 4px;"></div>

        <!-- Mode Export -->
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_excel', ['kategori' => $kategori, 'skpd' => $skpdFilter, 'skpd_simgaji' => $simgajiFilter, 'search' => $search]) }}" class="btn btn-export" style="color: #059669; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;" title="Unduh data BNBA ini ke format Excel (.xlsx) untuk lampiran resmi Taspen">
            <i class="ph-bold ph-file-xls" style="font-size: 16px; color: #10b981;"></i> Unduh Excel (.xlsx)
        </a>
        <a href="{{ route('laporan.perbaikan_simgaji_skpd.export_pdf', ['kategori' => $kategori, 'skpd' => $skpdFilter, 'skpd_simgaji' => $simgajiFilter, 'search' => $search]) }}" target="_blank" class="btn btn-export" style="color: #dc2626; border-color: rgba(239, 68, 68, 0.4); font-weight: 600;" title="Buka / Cetak dokumen PDF resmi A4 Landscape">
            <i class="ph-bold ph-file-pdf" style="font-size: 16px; color: #ef4444;"></i> Unduh PDF Resmi
        </a>
        <a href="{{ route('laporan.penyelarasan_unit.index') }}" class="btn btn-export" style="color: var(--text-main);" title="Kembali ke matriks penyelarasan hierarki">
            <i class="ph-bold ph-git-branch"></i> Penyelarasan Hierarki
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(!empty($error))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <div>{{ $error }}</div>
    </div>
@endif

<!-- Petunjuk Operasional Resmi -->
<div style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05), rgba(76, 53, 222, 0.05)); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px;">
    <div style="width: 36px; height: 36px; border-radius: 8px; background: #1e40af; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
        <i class="ph-bold ph-info"></i>
    </div>
    <div style="font-size: 13px; line-height: 1.55; color: var(--text-main);">
        <strong style="color: #1e40af; font-size: 13.5px;">Ketentuan Acuan Perbaikan Data SIMGAJI Taspen:</strong><br>
        1. <strong>Master SIMPEG / KONBELPEG</strong> adalah acuan tunggal kebenaran penempatan SKPD, UPTD, dan Jabatan pegawai Pemerintah Daerah.<br>
        2. Pegawai pada daftar BNBA di bawah ini terdeteksi memiliki perbedaan kode/nama SKPD atau satker di SIMGAJI dan <strong>harus diperbarui pada aplikasi SIMGAJI Taspen</strong> agar laporan keuangan, pemotongan gaji, dan rekonsiliasi belanja pegawai akurat.<br>
        3. Berkas ekspor Excel dapat langsung dilampirkan sebagai <strong>Lampiran Berita Acara Usulan Perbaikan Data Penggajian ke PT Taspen / Bank Persepsi</strong>.
    </div>
</div>

@if(!empty($summary))
<!-- KPI Widgets Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <!-- Total Perlu Perbaikan -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Total BNBA Perbaikan</p>
                <h3 class="luno-widget-value" style="color: #dc2626;">{{ number_format($summary['total_perlu_perbaikan'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-danger">
                <i class="ph-bold ph-user-list"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-danger"><i class="ph-bold ph-warning"></i> Pegawai perlu koreksi</span>
        </div>
    </div>

    <!-- Beda SKPD Induk -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Beda SKPD Induk</p>
                <h3 class="luno-widget-value" style="color: #b45309;">{{ number_format($summary['total_beda_skpd'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-warning">
                <i class="ph-bold ph-arrows-split"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-warning"><i class="ph-bold ph-arrows-clockwise"></i> Prioritas Mutasi Antar-SKPD</span>
        </div>
    </div>

    <!-- Beda Cabang Disdik -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Beda Cabang Disdik</p>
                <h3 class="luno-widget-value" style="color: #7c3aed;">{{ number_format($summary['total_beda_cabang_disdik'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon" style="background: rgba(124, 58, 237, 0.12); color: #7c3aed;">
                <i class="ph-bold ph-student"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-map-pin"></i> Beda Wilayah Kab/Kota Disdik</span>
        </div>
    </div>

    <!-- Beda UPTD / Satker -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Beda UPTD / Satker</p>
                <h3 class="luno-widget-value" style="color: #2563eb;">{{ number_format($summary['total_beda_uptd'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-check"></i> SKPD sama, satker beda</span>
        </div>
    </div>

    <!-- SKPD Terdampak -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">SKPD Terdampak</p>
                <h3 class="luno-widget-value" style="color: #059669;">{{ number_format($summary['total_skpd_terdampak'] ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-success">
                <i class="ph-bold ph-tree-structure"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-success"><i class="ph-bold ph-check-circle"></i> Unit Kerja Terpetakan</span>
        </div>
    </div>
</div>
@endif

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 24px; padding: 18px 20px;">
    <form method="GET" action="{{ route('laporan.perbaikan_simgaji_skpd.index') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Filter Kategori -->
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="ph-bold ph-funnel"></i> Kategori Perbaikan
            </label>
            <select name="kategori" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                <option value="semua" {{ $kategori === 'semua' ? 'selected' : '' }}>Semua Kategori Perbaikan ({{ $summary['total_perlu_perbaikan'] ?? 0 }})</option>
                <option value="beda_skpd" {{ $kategori === 'beda_skpd' ? 'selected' : '' }}>🚨 Beda SKPD Induk (Mutasi) ({{ $summary['total_beda_skpd'] ?? 0 }})</option>
                <option value="beda_cabang_disdik" {{ $kategori === 'beda_cabang_disdik' ? 'selected' : '' }}>📍 Beda Cabang Disdik (Kab/Kota) ({{ $summary['total_beda_cabang_disdik'] ?? 0 }})</option>
                <option value="beda_uptd" {{ $kategori === 'beda_uptd' ? 'selected' : '' }}>🏢 Beda UPTD / Satker ({{ $summary['total_beda_uptd'] ?? 0 }})</option>
            </select>
        </div>

        <!-- Filter SKPD SIMPEG (Acuan Resmi) -->
        <div style="flex: 1.5; min-width: 250px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="ph-bold ph-buildings"></i> SKPD Resmi (Acuan SIMPEG)
            </label>
            <select name="skpd" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                <option value="semua">Semua SKPD Resmi ({{ count($filterOptions['skpd_simpeg'] ?? []) }})</option>
                @foreach($filterOptions['skpd_simpeg'] ?? [] as $s)
                    <option value="{{ $s }}" {{ $skpdFilter === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter SKPD SIMGAJI (Eksisting) -->
        <div style="flex: 1.5; min-width: 250px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="ph-bold ph-database"></i> SKPD Asal SIMGAJI (Eksisting)
            </label>
            <select name="skpd_simgaji" class="form-control" style="width: 100%; height: 38px; font-size: 12.5px;" onchange="this.form.submit()">
                <option value="semua">Semua SKPD Asal SIMGAJI ({{ count($filterOptions['skpd_simgaji'] ?? []) }})</option>
                @foreach($filterOptions['skpd_simgaji'] ?? [] as $sg)
                    <option value="{{ $sg }}" {{ $simgajiFilter === $sg ? 'selected' : '' }}>{{ $sg }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Box -->
        <div style="flex: 1.2; min-width: 200px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="ph-bold ph-magnifying-glass"></i> Pencarian Pegawai
            </label>
            <input type="text" name="search" class="form-control" placeholder="Cari NIP, Nama, atau Jabatan..." value="{{ $search }}" style="width: 100%; height: 38px; font-size: 12.5px;">
        </div>

        <!-- Tombol Aksi Filter -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            @if($kategori !== 'semua' || $skpdFilter !== 'semua' || $simgajiFilter !== 'semua' || $search !== '')
                <a href="{{ route('laporan.perbaikan_simgaji_skpd.index') }}" class="btn btn-export" style="height: 38px; color: var(--danger-text); padding: 0 14px;" title="Reset filter">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabel BNBA Perbaikan SIMGAJI -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-main);">
                Daftar Nominatif BNBA Usulan Perbaikan Data SIMGAJI
            </h3>
            <p style="margin: 0; font-size: 12.5px; color: var(--text-muted);">
                Menampilkan <strong>{{ number_format($items->count(), 0, ',', '.') }}</strong> dari total <strong>{{ number_format($filteredTotal, 0, ',', '.') }}</strong> data perbaikan yang memenuhi filter.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span style="font-size: 12px; color: var(--text-muted);">
                Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" style="font-size: 12px;">
            <thead>
                <!-- Top Group Headers -->
                <tr style="background: #1e3a8a; color: #ffffff;">
                    <th colspan="4" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="ph-bold ph-user"></i> Identitas Pegawai
                    </th>
                    <th colspan="3" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); background: #0f172a; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="ph-bold ph-database"></i> Kondisi Eksisting SIMGAJI (Data Lama/Salah)
                    </th>
                    <th colspan="3" style="text-align: center; border-right: 1px solid rgba(255,255,255,0.2); background: #065f46; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="ph-bold ph-shield-check"></i> Acuan Resmi SIMPEG (Seharusnya)
                    </th>
                    <th style="text-align: center; background: #831843; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="ph-bold ph-wrench"></i> Tindakan Perbaikan
                    </th>
                </tr>
                <!-- Sub Column Headers -->
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 170px;">NIP / Golru</th>
                    <th style="min-width: 190px;">Nama Pegawai</th>
                    <th style="min-width: 180px;">Jabatan Resmi (SIMPEG)</th>
                    <!-- SIMGAJI -->
                    <th style="min-width: 90px; text-align: center; background: rgba(15, 23, 42, 0.04);">Kode SIMGAJI</th>
                    <th style="min-width: 200px; background: rgba(15, 23, 42, 0.04);">SKPD SIMGAJI</th>
                    <th style="min-width: 160px; background: rgba(15, 23, 42, 0.04);">Satker / Inputer</th>
                    <!-- SIMPEG -->
                    <th style="min-width: 210px; background: rgba(16, 185, 129, 0.04);">SKPD Resmi (SIMPEG)</th>
                    <th style="min-width: 85px; text-align: center; background: rgba(16, 185, 129, 0.04);">Kode Rekom.</th>
                    <th style="min-width: 220px; background: rgba(16, 185, 129, 0.04);">UPTD / Sekolah Resmi</th>
                    <!-- Tindakan -->
                    <th style="min-width: 260px; background: rgba(239, 68, 68, 0.04);">Rekomendasi Tindakan SIMGAJI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $row)
                    @php
                        $nomor = ($items->currentPage() - 1) * $items->perPage() + $loop->iteration;
                        $jenis = $row['jenis_selisih'] ?? '';
                    @endphp
                    <tr style="{{ $jenis === 'Beda SKPD Induk' ? 'background: rgba(239, 68, 68, 0.03);' : '' }}">
                        <!-- No -->
                        <td style="text-align: center; font-weight: 600; color: var(--text-muted);">
                            {{ $nomor }}
                        </td>

                        <!-- NIP / Golru -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <strong style="font-family: monospace; font-size: 12.5px; color: var(--text-main);">
                                    {{ $row['nip'] }}
                                </strong>
                                <button type="button" class="btn-copy" onclick="copyNip('{{ $row['nip'] }}', this)" title="Salin NIP" style="background: none; border: none; cursor: pointer; color: var(--text-light); padding: 2px;">
                                    <i class="ph-bold ph-copy" style="font-size: 13px;"></i>
                                </button>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                <span class="badge" style="background: var(--bg-canvas); color: var(--text-main); padding: 1px 6px; font-size: 10px;">
                                    Gol: {{ $row['golru'] }}
                                </span>
                                <span style="margin-left: 4px;">{{ $row['status_pegawai'] }}</span>
                            </div>
                        </td>

                        <!-- Nama Pegawai -->
                        <td>
                            <strong style="color: var(--text-main); font-size: 12.5px;">
                                {{ $row['nama'] }}
                            </strong>
                        </td>

                        <!-- Jabatan Resmi (SIMPEG) -->
                        <td style="color: var(--text-muted); font-size: 11.5px;">
                            {{ $row['jabatan'] }}
                        </td>

                        <!-- Kode SKPD SIMGAJI -->
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #dc2626; background: rgba(15, 23, 42, 0.02);">
                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; font-size: 11px;">
                                {{ $row['kdskpd_simgaji'] }}
                            </span>
                        </td>

                        <!-- SKPD SIMGAJI -->
                        <td style="background: rgba(15, 23, 42, 0.02);">
                            <span style="font-weight: 600; color: var(--text-main);">
                                {{ $row['skpd_simgaji'] }}
                            </span>
                        </td>

                        <!-- Satker / Inputer SIMGAJI -->
                        <td style="background: rgba(15, 23, 42, 0.02); font-size: 11px; color: var(--text-muted);">
                            <div><strong>Satker:</strong> {{ $row['kdsatker_simgaji'] }}</div>
                            <div><strong>Inputer:</strong> {{ $row['inputer_simgaji'] }}</div>
                        </td>

                        <!-- SKPD Acuan Resmi (SIMPEG) -->
                        <td style="background: rgba(16, 185, 129, 0.02);">
                            <strong style="color: #065f46; font-size: 12px;">
                                {{ $row['skpd_simpeg'] }}
                            </strong>
                        </td>

                        <!-- Kode Rekomendasi -->
                        <td style="text-align: center; background: rgba(16, 185, 129, 0.02); font-family: monospace;">
                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #047857; font-size: 11px; font-weight: 800;">
                                {{ $row['kdskpd_rekomendasi'] }}
                            </span>
                        </td>

                        <!-- UPTD / Sekolah Resmi (SIMPEG) -->
                        <td style="background: rgba(16, 185, 129, 0.02); font-size: 11.5px; color: var(--text-main);">
                            @if($row['upt_simpeg'] !== '-' && $row['upt_simpeg'] !== '')
                                <div style="font-weight: 600; color: #1e40af;">
                                    {{ $row['upt_simpeg'] }}
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Induk SKPD (Tidak Ber-UPTD)</span>
                            @endif
                        </td>

                        <!-- Rekomendasi Tindakan SIMGAJI -->
                        <td style="background: rgba(239, 68, 68, 0.02);">
                            <div style="margin-bottom: 4px;">
                                @if($jenis === 'Beda SKPD Induk')
                                    <span class="badge" style="background: #fee2e2; color: #dc2626; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                        <i class="ph-bold ph-warning"></i> BEDA SKPD INDUK
                                    </span>
                                @elseif($jenis === 'Beda Cabang Disdik')
                                    <span class="badge" style="background: #f3e8ff; color: #7c3aed; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                        <i class="ph-bold ph-map-pin"></i> BEDA CABANG DISDIK
                                    </span>
                                @elseif($jenis === 'Beda UPTD / Satker')
                                    <span class="badge" style="background: #dbeafe; color: #1d4ed8; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                        <i class="ph-bold ph-buildings"></i> BEDA UPTD / SATKER
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10px; padding: 2px 7px;">
                                        {{ $jenis }}
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 11.5px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
                                {{ $row['rekomendasi'] }}
                            </div>
                            <div style="font-size: 10.5px; color: var(--text-light); margin-top: 3px;">
                                {{ $row['keterangan'] }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                            <div style="font-size: 36px; margin-bottom: 8px; color: #10b981;">
                                <i class="ph-bold ph-check-circle"></i>
                            </div>
                            <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                                Tidak Ada Data Selisih yang Ditemukan
                            </div>
                            <p style="margin: 0; font-size: 13px;">
                                Seluruh data penempatan pegawai antara SIMGAJI dan SIMPEG telah sesuai, atau tidak ada data yang cocok dengan kriteria filter saat ini.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    @if($items->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
            <div style="font-size: 12.5px; color: var(--text-muted);">
                Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ number_format($filteredTotal, 0, ',', '.') }} data perbaikan
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    @endif
</div>

<script>
    function copyNip(nip, btn) {
        navigator.clipboard.writeText(nip).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="ph-bold ph-check" style="font-size: 13px; color: #10b981;"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        });
    }
</script>
@endsection
