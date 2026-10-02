@extends('layouts.app')

@section('title', 'Trace Daftar Penggajian Pegawai - Sistem Realisasi Belanja Pegawai')

@section('content')
<style>
    /* ===== STYLING TRACE PENGGAJIAN PEGAWAI (LIGHT & DARK MODE COMPATIBLE) ===== */
    .trace-container {
        padding-bottom: 50px;
    }

    /* Hero Banner */
    .trace-hero-card {
        background: linear-gradient(135deg, #4C35DE 0%, #2A1A9A 100%);
        border-radius: 16px;
        padding: 32px 36px;
        color: #ffffff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(76, 53, 222, 0.35);
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    :root.dark-mode .trace-hero-card {
        background: linear-gradient(135deg, #241b6b 0%, #130f35 100%);
        border-color: rgba(92, 71, 234, 0.35);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6);
    }

    .trace-hero-card::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    /* Search Bar */
    .trace-search-bar {
        background: var(--bg-surface);
        border-radius: 14px;
        padding: 6px 8px 6px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid var(--border-color);
        transition: all 0.25s ease;
        margin-top: 20px;
    }

    :root.dark-mode .trace-search-bar {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        border-color: var(--border-color);
    }

    .trace-search-bar:focus-within {
        border-color: var(--luno-primary);
        box-shadow: 0 10px 25px rgba(76, 53, 222, 0.25);
    }

    .trace-search-bar input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 15px;
        font-weight: 500;
        color: var(--text-main);
        background: transparent;
    }

    .trace-search-bar input::placeholder {
        color: var(--text-muted);
    }

    .trace-hero-select {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 12.5px;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .trace-hero-select:focus {
        background: rgba(255, 255, 255, 0.28);
        border-color: #ffffff;
    }

    .trace-hero-select option {
        background: var(--bg-surface);
        color: var(--text-main);
    }

    :root.dark-mode .trace-hero-select option {
        background: #151d30;
        color: #f1f5f9;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }

    .kpi-card.highlight {
        background: linear-gradient(135deg, rgba(76, 53, 222, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);
        border: 1.5px solid rgba(76, 53, 222, 0.35);
    }

    :root.dark-mode .kpi-card.highlight {
        background: linear-gradient(135deg, rgba(92, 71, 234, 0.16) 0%, rgba(16, 185, 129, 0.12) 100%);
        border: 1.5px solid rgba(92, 71, 234, 0.45);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .kpi-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
    }

    .kpi-subtitle {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Pegawai Profile Sheet Header */
    .profile-banner {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
    }

    .pegawai-avatar-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4C35DE 0%, #7c3aed 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(76, 53, 222, 0.25);
        flex-shrink: 0;
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-pns {
        background: rgba(76, 53, 222, 0.12);
        color: #4C35DE;
        border: 1px solid rgba(76, 53, 222, 0.25);
    }
    :root.dark-mode .badge-pns {
        background: rgba(92, 71, 234, 0.2);
        color: #a594fd;
        border-color: rgba(92, 71, 234, 0.4);
    }

    .badge-pppk {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    :root.dark-mode .badge-pppk {
        background: rgba(16, 185, 129, 0.2);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.4);
    }

    .badge-paruh {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    :root.dark-mode .badge-paruh {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
        border-color: rgba(245, 158, 11, 0.4);
    }

    .badge-golru {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .profile-meta-box {
        background: var(--bg-surface-subtle);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 12.5px;
        min-width: 250px;
    }

    /* Candidate Cards Grid */
    .candidate-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .candidate-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: var(--card-shadow);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .candidate-card:hover {
        transform: translateY(-2px);
        border-color: var(--luno-primary);
        box-shadow: var(--card-shadow-hover);
    }

    .candidate-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--luno-primary-light);
        color: var(--luno-primary-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* Interactive Table Card */
    .trace-table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .trace-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .trace-table th {
        background: var(--bg-surface-subtle);
        padding: 12px 14px;
        font-weight: 700;
        color: var(--text-main);
        border-bottom: 1.5px solid var(--border-color);
        text-align: left;
        white-space: nowrap;
    }

    .trace-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
        vertical-align: middle;
    }

    .trace-table tbody tr:hover {
        background-color: var(--bg-surface-hover);
    }

    .trace-table tfoot td {
        background: var(--bg-surface-subtle);
        font-weight: 800;
        border-top: 2px solid var(--border-color);
        color: var(--text-main);
        padding: 14px;
    }

    .td-kotor {
        background: var(--bg-surface-subtle);
        font-weight: 700;
    }

    .td-bersih {
        background: rgba(16, 185, 129, 0.08);
        color: var(--success-text);
        font-weight: 800;
    }
    :root.dark-mode .td-bersih {
        background: rgba(16, 185, 129, 0.16);
        color: #34d399;
    }

    .badge-kriteria {
        background: var(--bg-surface-subtle);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .btn-slip-action {
        background: var(--bg-surface-subtle);
        color: var(--luno-primary-text);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 11.5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .btn-slip-action:hover {
        background: var(--bg-surface-hover);
        color: var(--luno-primary);
    }

    .btn-custom-secondary {
        background: var(--bg-surface-subtle);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 7px 14px;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        transition: all 0.15s ease;
        font-weight: 500;
    }
    .btn-custom-secondary:hover {
        background: var(--bg-surface-hover);
        border-color: var(--luno-primary);
        color: var(--luno-primary-text);
    }

    /* Modal Styling */
    .trace-modal {
        position: fixed;
        inset: 0;
        background: rgba(10, 15, 28, 0.7);
        backdrop-filter: blur(5px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .trace-modal.show {
        display: flex;
    }

    .trace-modal-box {
        background: var(--bg-surface);
        border-radius: 16px;
        max-width: 780px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .modal-header-styled {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-surface-subtle);
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .modal-footer-styled {
        padding: 14px 24px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-surface-subtle);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
    }

    .empty-state-box {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 48px 24px;
        text-align: center;
        box-shadow: var(--card-shadow);
    }
</style>

<div class="trace-container">

    <!-- Top Banner & Quick Search Section -->
    <div class="trace-hero-card">
        <div style="max-width: 720px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #cbd5e1; margin-bottom: 8px;">
                <i class="ph ph-user-focus" style="font-size: 18px; color: #a5b4fc;"></i>
                Audit & Pelacakan Keuangan Pegawai
            </div>
            <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 8px 0; letter-spacing: -0.02em; color: #ffffff;">
                Trace Riwayat & Daftar Penggajian Per Orang
            </h1>
            <p style="margin: 0; font-size: 14px; color: #e2e8f0; line-height: 1.5;">
                Telusuri riwayat pembayaran gaji ASN (PNS, PPPK & PPPK Paruh Waktu) secara rinci per orang. Lakukan pencarian instan berdasarkan <strong>NIP</strong> atau <strong>Nama Pegawai</strong> untuk melihat rincian slip, tunjangan, potongan IWP/pajak, serta cetak lembar trace.
            </p>

            <!-- Search Form -->
            <form action="{{ route('laporan.trace_gaji.index') }}" method="GET">
                <div class="trace-search-bar">
                    <i class="ph ph-magnifying-glass" style="font-size: 22px; color: var(--luno-primary);"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ $search }}" 
                           placeholder="Ketik NIP (contoh: 198203...) atau Nama Pegawai (contoh: RINI SETIASIH)..." 
                           autocomplete="off" 
                           autofocus>
                    @if($search || $skpdFilter || $statusFilter)
                        <a href="{{ route('laporan.trace_gaji.index') }}" 
                           title="Hapus pencarian" 
                           style="color: var(--text-muted); font-size: 18px; text-decoration: none; padding: 4px;">
                            <i class="ph ph-x-circle"></i>
                        </a>
                    @endif
                    <button type="submit" 
                            style="background: var(--luno-primary); color: #ffffff; border: none; padding: 10px 22px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: opacity 0.2s;">
                        <i class="ph ph-magnifying-glass"></i>
                        Cari
                    </button>
                </div>

                <!-- Secondary Quick Filter Row -->
                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; align-items: center;">
                    <span style="font-size: 12px; color: #e2e8f0; font-weight: 500;">Filter Tambahan:</span>
                    
                    <select name="status_pegawai" onchange="this.form.submit()" class="trace-hero-select">
                        <option value="">-- Semua Status Pegawai --</option>
                        @foreach($statusOptions as $st)
                            <option value="{{ $st }}" {{ $statusFilter == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>

                    <select name="skpd" onchange="this.form.submit()" class="trace-hero-select" style="max-width: 340px;">
                        <option value="">-- Semua SKPD / Unit Kerja --</option>
                        @foreach($skpdOptions as $skpd)
                            <option value="{{ $skpd }}" {{ $skpdFilter == $skpd ? 'selected' : '' }}>{{ $skpd }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- KONDISI 1: ADA SEORANG PEGAWAI YANG SEDANG DIPILIH UNTUK DI-TRACE --}}
    @if($selectedPegawai && $traceData)
        <!-- Tombol Kembali / Navigasi Hasil Pencarian -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($search !== '' || $skpdFilter || $statusFilter)
                    <a href="{{ route('laporan.trace_gaji.index', ['q' => $search, 'skpd' => $skpdFilter, 'status_pegawai' => $statusFilter]) }}" 
                       class="btn-custom-secondary">
                        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Hasil
                    </a>
                @else
                    <a href="{{ route('laporan.trace_gaji.index') }}" 
                       class="btn-custom-secondary">
                        <i class="ph ph-arrow-left"></i> Kembali ke Beranda Trace
                    </a>
                @endif
                <span style="font-size: 13px; color: var(--text-muted);">
                    Menampilkan trace penggajian: <strong style="color: var(--text-main);">{{ $selectedPegawai->nama }}</strong>
                </span>
            </div>

            <!-- Tombol Cetak / Export Rekap Trace PDF -->
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('laporan.trace_gaji.export_pdf', $selectedPegawai->id) }}" 
                   target="_blank"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: 8px; background: var(--luno-primary); color: #ffffff; font-weight: 600;">
                    <i class="ph ph-file-pdf" style="font-size: 16px;"></i> Cetak Lembar Trace (PDF)
                </a>
            </div>
        </div>

        <!-- Sheet Header Profil Pegawai -->
        <div class="profile-banner">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div style="display: flex; gap: 18px; align-items: center;">
                    <div class="pegawai-avatar-circle">
                        {{ strtoupper(substr($selectedPegawai->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-main); margin: 0;">
                                {{ $selectedPegawai->nama }}
                            </h2>
                            @if($selectedPegawai->status_pegawai == 'PNS')
                                <span class="badge-status badge-pns"><i class="ph ph-shield-check"></i> PNS</span>
                            @elseif($selectedPegawai->status_pegawai == 'PPPK')
                                <span class="badge-status badge-pppk"><i class="ph ph-identification-badge"></i> PPPK</span>
                            @else
                                <span class="badge-status badge-paruh"><i class="ph ph-clock"></i> {{ $selectedPegawai->status_pegawai }}</span>
                            @endif

                            @if($selectedPegawai->golru)
                                <span class="badge-golru">
                                    Gol. {{ $selectedPegawai->golru }}
                                </span>
                            @endif

                            @if($selectedPegawai->jenis_pegawai)
                                <span style="background: var(--info-light); color: var(--info-text); padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; border: 1px solid rgba(6, 182, 212, 0.25);">
                                    {{ $selectedPegawai->jenis_pegawai }}
                                </span>
                            @endif
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: var(--text-muted);">
                            <span>
                                <strong style="color: var(--text-main);">NIP:</strong> 
                                <span style="font-family: monospace; font-size: 13.5px; font-weight: 700; color: var(--text-main);">{{ $selectedPegawai->nip }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $selectedPegawai->nip }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'NIP berhasil disalin', showConfirmButton: false, timer: 1500});" title="Salin NIP" style="background: none; border: none; cursor: pointer; color: var(--luno-primary-text); padding: 0 4px;">
                                    <i class="ph ph-copy"></i>
                                </button>
                            </span>
                            @if(!empty($traceData['latestRawMeta']['niplama']))
                                <span><strong style="color: var(--text-main);">NIP Lama:</strong> {{ $traceData['latestRawMeta']['niplama'] }}</span>
                            @endif
                            <span><strong style="color: var(--text-main);">Jabatan:</strong> {{ $selectedPegawai->jabatan?->nama ?? '-' }}</span>
                            <span><strong style="color: var(--text-main);">SKPD:</strong> {{ $selectedPegawai->unitKerja?->skpd ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Meta Tambahan SIMGAJI -->
                <div class="profile-meta-box">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="profile-meta-label">NIK (No. KTP):</span>
                        <strong class="profile-meta-val" style="font-family: monospace;">{{ $selectedPegawai->nik ?? ($traceData['latestRawMeta']['noktp'] ?? '-') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="profile-meta-label">No. Rekening:</span>
                        <strong class="profile-meta-val" style="font-family: monospace;">
                            {{ $selectedPegawai->no_rekening ?? ($traceData['latestRawMeta']['norek'] ?? '-') }}
                            @if(!empty($selectedPegawai->nama_bank))
                                <span style="font-size: 11px; color: var(--text-muted); font-weight: normal;">({{ $selectedPegawai->nama_bank }})</span>
                            @endif
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="profile-meta-label">NPWP:</span>
                        <strong class="profile-meta-val" style="font-family: monospace;">{{ $selectedPegawai->npwp ?? ($traceData['latestRawMeta']['npwp'] ?? '-') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="profile-meta-label">Tanggungan Gaji:</span>
                        <strong class="profile-meta-val">
                            {{ ($traceData['latestRawMeta']['jistri'] ?? 0) }} Pasangan, {{ ($traceData['latestRawMeta']['janak'] ?? 0) }} Anak
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span class="profile-meta-label">Masa Kerja (SIMGAJI):</span>
                        <strong class="profile-meta-val">{{ $traceData['latestRawMeta']['masker'] ?? '-' }} Tahun</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards Akumulasi Penghasilan -->
        <div class="kpi-grid">
            <div class="kpi-card highlight">
                <div class="kpi-header">
                    <span class="kpi-title">Total Gaji Bersih Diterima</span>
                    <div class="kpi-icon" style="background: var(--success-light); color: var(--success-text);">
                        <i class="ph ph-wallet"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: var(--success-text);">
                    Rp {{ number_format($traceData['totals']['gaji_bersih'], 0, ',', '.') }}
                </div>
                <div class="kpi-subtitle">
                    Rata-rata: Rp {{ number_format($traceData['rataRataBersih'], 0, ',', '.') }} / Periode
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Gaji Pokok (GAPOK)</span>
                    <div class="kpi-icon" style="background: var(--luno-primary-light); color: var(--luno-primary-text);">
                        <i class="ph ph-bank"></i>
                    </div>
                </div>
                <div class="kpi-value">
                    Rp {{ number_format($traceData['totals']['gaji_pokok'], 0, ',', '.') }}
                </div>
                <div class="kpi-subtitle">
                    Dari {{ $traceData['periodeCount'] }} pembayaran gaji tercatat
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Semua Tunjangan</span>
                    <div class="kpi-icon" style="background: var(--info-light); color: var(--info-text);">
                        <i class="ph ph-gift"></i>
                    </div>
                </div>
                @php
                    $totalTunjSemua = $traceData['totals']['tj_keluarga'] + $traceData['totals']['tj_jabatan'] + $traceData['totals']['tj_fungsional'] + $traceData['totals']['tj_umum'] + $traceData['totals']['tj_beras'] + $traceData['totals']['tj_pajak'] + $traceData['totals']['tj_pembulatan'] + $traceData['totals']['tj_lain'];
                @endphp
                <div class="kpi-value" style="color: var(--info-text);">
                    Rp {{ number_format($totalTunjSemua, 0, ',', '.') }}
                </div>
                <div class="kpi-subtitle">
                    Keluarga, Jab/Fung, Beras & Lainnya
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Potongan (IWP & Pajak)</span>
                    <div class="kpi-icon" style="background: var(--danger-light); color: var(--danger-text);">
                        <i class="ph ph-scissors"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: var(--danger-text);">
                    Rp {{ number_format($traceData['totals']['total_potongan'], 0, ',', '.') }}
                </div>
                <div class="kpi-subtitle">
                    IWP BPJS: Rp {{ number_format($traceData['totals']['pot_iwp2'], 0, ',', '.') }} | Taspen: Rp {{ number_format($traceData['totals']['pot_iwp8'], 0, ',', '.') }}
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Penghasilan Kotor</span>
                    <div class="kpi-icon" style="background: var(--warning-light); color: var(--warning-text);">
                        <i class="ph ph-chart-line-up"></i>
                    </div>
                </div>
                <div class="kpi-value">
                    Rp {{ number_format($traceData['totals']['gaji_kotor'], 0, ',', '.') }}
                </div>
                <div class="kpi-subtitle">
                    Akumulasi bruto sebelum potongan
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Daftar Penggajian -->
        <div class="trace-table-card">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-list-numbers" style="color: var(--luno-primary);"></i>
                        Daftar Riwayat Penggajian Terperinci
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        Rincian penerimaan gaji periode demi periode diurutkan secara kronologis (dari pembayaran terbaru).
                    </p>
                </div>
                <span style="background: var(--luno-primary-light); color: var(--luno-primary-text); border: 1px solid var(--border-color); padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700;">
                    {{ count($traceData['records']) }} Kali Pembayaran Gaji
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="trace-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Periode Gaji</th>
                            <th>Kriteria Gaji</th>
                            <th style="text-align: right;">Gaji Pokok</th>
                            <th style="text-align: right;">Tunj. Keluarga</th>
                            <th style="text-align: right;">Tunj. Jab/Fung</th>
                            <th style="text-align: right;">Tunj. Beras</th>
                            <th style="text-align: right;">Tunj. Lain/PPh</th>
                            <th style="text-align: right;" class="td-kotor">Gaji Kotor</th>
                            <th style="text-align: right;">IWP BPJS (2%)</th>
                            <th style="text-align: right;">IWP Taspen (8%)</th>
                            <th style="text-align: right;">Pajak (PPh)</th>
                            <th style="text-align: right; color: var(--danger-text);">Total Potongan</th>
                            <th style="text-align: right;" class="td-bersih">Gaji Bersih</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($traceData['records'] as $idx => $rec)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 12px;">{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $rec['periode'] ?? '-' }}</strong>
                            </td>
                            <td>
                                <span class="badge-kriteria">
                                    {{ $rec['jenis_gaji'] }}
                                </span>
                            </td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['gaji_pokok'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['tj_keluarga'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['tj_jabatan'] + $rec['tj_fungsional'] + $rec['tj_umum'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['tj_beras'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['tj_pajak'] + $rec['tj_pembulatan'] + $rec['tj_lain'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;" class="td-kotor">
                                Rp {{ number_format($rec['gaji_kotor'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['pot_iwp2'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['pot_iwp8'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($rec['pot_pajak'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace; color: var(--danger-text); font-weight: 600;">
                                Rp {{ number_format($rec['total_potongan'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace;" class="td-bersih">
                                Rp {{ number_format($rec['gaji_bersih'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" 
                                        onclick="showSlipModal({{ json_encode($rec) }}, '{{ addslashes($selectedPegawai->nama) }}', '{{ $selectedPegawai->nip }}')"
                                        class="btn-slip-action" 
                                        title="Lihat Slip Rincian">
                                    <i class="ph ph-receipt"></i> Slip
                                </button>
                                <a href="{{ route('laporan.trace_gaji.slip_pdf', ['pegawai' => $selectedPegawai->id, 'gaji' => $rec['id']]) }}" 
                                   target="_blank" 
                                   style="background: var(--luno-primary); color: #ffffff; text-decoration: none; border-radius: 6px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; display: inline-block;" 
                                   title="Unduh Slip PDF">
                                    <i class="ph ph-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="ph ph-receipt-x" style="font-size: 36px; color: var(--text-subtle); display: block; margin-bottom: 8px;"></i>
                                Belum ada catatan realisasi gaji yang ditemukan untuk pegawai ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($traceData['records']) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: center; font-size: 13px;">TOTAL KESELURUHAN</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['gaji_pokok'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['tj_keluarga'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['tj_jabatan'] + $traceData['totals']['tj_fungsional'] + $traceData['totals']['tj_umum'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['tj_beras'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['tj_pajak'] + $traceData['totals']['tj_pembulatan'] + $traceData['totals']['tj_lain'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace; background: var(--bg-surface-hover);">
                                Rp {{ number_format($traceData['totals']['gaji_kotor'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['pot_iwp2'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['pot_iwp8'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($traceData['totals']['pot_pajak'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace; color: var(--danger-text);">
                                Rp {{ number_format($traceData['totals']['total_potongan'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 14px;" class="td-bersih">
                                Rp {{ number_format($traceData['totals']['gaji_bersih'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Section Realisasi TPP Jika Pegawai Memiliki Catatan TPP --}}
        @if($traceData['tppRecords']->count() > 0)
        <div class="trace-table-card" style="border-left: 4px solid var(--success);">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-coins" style="color: var(--success); font-size: 20px;"></i>
                    Riwayat Pembayaran Tambahan Penghasilan Pegawai (TPP)
                </h4>
                <span style="font-size: 13px; font-weight: 700; color: var(--success-text);">
                    Total TPP Diterima: Rp {{ number_format($traceData['totalTppBersih'], 0, ',', '.') }}
                </span>
            </div>
            <div style="overflow-x: auto;">
                <table class="trace-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Periode Kas / TPP</th>
                            <th>Bulan Kinerja</th>
                            <th style="text-align: right;">TPP Dinamis</th>
                            <th style="text-align: right;">TPP Statis</th>
                            <th style="text-align: right;">Pajak (PPh 21)</th>
                            <th style="text-align: right;">Iuran BPJS (1%)</th>
                            <th style="text-align: right; font-weight: 800; color: var(--success-text);">Total TPP Dibayarkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($traceData['tppRecords'] as $idxTpp => $tpp)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">{{ $idxTpp + 1 }}</td>
                            <td><strong>{{ $tpp->periode_kas ?? $tpp->periode ?? '-' }}</strong></td>
                            <td>{{ $tpp->bulan_kinerja ?? '-' }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($tpp->nominal_dinamis, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($tpp->nominal_statis, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($tpp->pajak, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($tpp->iuran_iwp, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: var(--success-text);">
                                Rp {{ number_format($tpp->total_dibayarkan, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Section Riwayat Anggota Keluarga & Tanggungan (SIMGAJI Taspen) --}}
        @php
            $keluargaList = $traceData['keluargas'] ?? collect();
            $totalAnggota = $keluargaList->count();
            $totalTertunjang = $keluargaList->where('is_tertunjang', true)->count();
        @endphp
        <div class="trace-table-card" style="border-left: 4px solid #059669; margin-top: 24px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-users-four" style="color: #059669; font-size: 20px;"></i>
                    Daftar Anggota Keluarga & Tanggungan (SIMGAJI Taspen)
                    <span class="badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 999px;">
                        {{ $totalAnggota }} Anggota Terdata ({{ $totalTertunjang }} Tertunjang)
                    </span>
                </h4>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Sumber: Berkas Master KEL SIMGAJI Taspen
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="trace-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>Nama Anggota Keluarga</th>
                            <th style="width: 170px;">Hubungan Keluarga</th>
                            <th style="width: 120px;">Jenis Kelamin</th>
                            <th style="width: 160px;">Tgl Lahir / Usia</th>
                            <th style="width: 170px; text-align: center;">Status Tunjangan</th>
                            <th>Keterangan / Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keluargaList as $kIdx => $kel)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">{{ $kIdx + 1 }}</td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13.5px;">{{ $kel->nmkel }}</strong>
                                @if(!empty($kel->nipsuamiis))
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                        NIP Pasangan: <span style="font-family: monospace;">{{ $kel->nipsuamiis }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-surface-subtle); color: var(--text-main); border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11.5px;">
                                    {{ $kel->hubungan ?? 'Keluarga' }}
                                </span>
                            </td>
                            <td>{{ $kel->jenis_kelamin ?? '-' }}</td>
                            <td>
                                <div>{{ $kel->tgllhr ? $kel->tgllhr->format('d-m-Y') : '-' }}</div>
                                @if($kel->usia !== null)
                                    <small style="color: var(--text-muted); font-size: 11.5px;">({{ $kel->usia }} Tahun)</small>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($kel->is_tertunjang)
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 11px;">
                                        <i class="ph-bold ph-check-circle"></i> Tertunjang
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 10px; border-radius: 999px; font-weight: 600; font-size: 11px;">
                                        Tidak Tertunjang
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                @if(!empty($kel->nosks))
                                    <div><i class="ph ph-certificate"></i> No SKS: {{ $kel->nosks }}</div>
                                @endif
                                @if(!empty($kel->noaktalahi))
                                    <div><i class="ph ph-file-text"></i> Akta: {{ $kel->noaktalahi }}</div>
                                @endif
                                @if(!empty($kel->pekerjaan))
                                    <div><i class="ph ph-briefcase"></i> {{ $kel->pekerjaan }}</div>
                                @endif
                                @if(empty($kel->nosks) && empty($kel->noaktalahi) && empty($kel->pekerjaan))
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                <i class="ph ph-users" style="font-size: 32px; color: var(--text-subtle); display: block; margin-bottom: 6px;"></i>
                                Belum ada riwayat anggota keluarga pada database SIMGAJI untuk NIP ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- KONDISI 2: HASIL PENCARIAN MENEMUKAN BEBERAPA PEGAWAI --}}
    @elseif($pegawaiList && $pegawaiList->count() > 0)
        <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0;">
                    Hasil Pencarian Pegawai: "{{ $search }}"
                </h2>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                    Ditemukan <strong>{{ $pegawaiList->total() }}</strong> pegawai yang cocok. Pilih salah satu pegawai untuk melihat lembar trace penggajiannya.
                </p>
            </div>
        </div>

        <div class="candidate-grid">
            @foreach($pegawaiList as $p)
            <div class="candidate-card">
                <div>
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div class="candidate-avatar">
                                {{ strtoupper(substr($p->nama, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="candidate-name">
                                    {{ $p->nama }}
                                </h4>
                                <span style="font-family: monospace; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                    {{ $p->nip }}
                                </span>
                            </div>
                        </div>
                        @if($p->status_pegawai == 'PNS')
                            <span class="badge-status badge-pns">PNS</span>
                        @elseif($p->status_pegawai == 'PPPK')
                            <span class="badge-status badge-pppk">PPPK</span>
                        @else
                            <span class="badge-status badge-paruh">{{ $p->status_pegawai }}</span>
                        @endif
                    </div>

                    <div style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 14px;">
                        <div><strong style="color: var(--text-main);">Jabatan:</strong> {{ $p->jabatan?->nama ?? '-' }}</div>
                        <div><strong style="color: var(--text-main);">Unit Kerja:</strong> {{ $p->unitKerja?->skpd ?? '-' }}</div>
                        @if($p->golru)
                            <div><strong style="color: var(--text-main);">Golongan:</strong> {{ $p->golru }}</div>
                        @endif
                    </div>
                </div>

                <div style="padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 12px; color: var(--luno-primary-text); font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <i class="ph ph-receipt"></i> {{ $p->realisasi_gajis_count }} Catatan Gaji
                    </span>
                    <a href="{{ route('laporan.trace_gaji.index', array_merge(request()->query(), ['pegawai_id' => $p->id])) }}" 
                       style="background: var(--luno-primary); color: #ffffff; padding: 6px 14px; font-size: 12.5px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                        Trace Gaji <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $pegawaiList->links() }}
        </div>

    {{-- KONDISI 3: PENCARIAN TIDAK MENEMUKAN HASIL --}}
    @elseif($search !== '' || $skpdFilter || $statusFilter)
        <div class="empty-state-box">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--danger-light); color: var(--danger-text); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
                <i class="ph ph-user-minus"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 8px;">
                Pegawai Tidak Ditemukan
            </h3>
            <p style="font-size: 14px; color: var(--text-muted); max-width: 480px; margin: 0 auto 20px;">
                Tidak ada pegawai yang cocok dengan kata kunci <strong>"{{ $search }}"</strong> pada filter yang dipilih. Silakan periksa kembali penulisan NIP atau Nama pegawai.
            </p>
            <a href="{{ route('laporan.trace_gaji.index') }}" class="btn-custom-secondary">
                Reset Pencarian
            </a>
        </div>

    {{-- KONDISI 4: TAMPILAN AWAL / DEFAULT SEBELUM PENCARIAN --}}
    @else
        <!-- Global Stats Row -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Master Pegawai</span>
                    <div class="kpi-icon" style="background: var(--luno-primary-light); color: var(--luno-primary-text);">
                        <i class="ph ph-users"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($globalStats['total_pegawai'], 0, ',', '.') }}</div>
                <div class="kpi-subtitle">Terdaftar di SIMPEG Master Data</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Catatan Realisasi Gaji</span>
                    <div class="kpi-icon" style="background: var(--success-light); color: var(--success-text);">
                        <i class="ph ph-files"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: var(--success-text);">{{ number_format($globalStats['total_gaji_records'], 0, ',', '.') }}</div>
                <div class="kpi-subtitle">Total transaksi pembayaran terarsip</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Periode Penggajian Aktif</span>
                    <div class="kpi-icon" style="background: var(--warning-light); color: var(--warning-text);">
                        <i class="ph ph-calendar-check"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ $globalStats['total_periode'] }} Bulan</div>
                <div class="kpi-subtitle">{{ $availablePeriodes->implode(', ') }}</div>
            </div>
        </div>

        <!-- Contoh Pegawai Cepat (Quick Sample Pegawais) -->
        @if($samplePegawais->count() > 0)
        <div style="margin-top: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-sparkle" style="color: var(--warning-text);"></i>
                    Pegawai dengan Riwayat Realisasi Terbaru
                </h3>
                <span style="font-size: 13px; color: var(--text-muted);">
                    Klik untuk melihat trace instan
                </span>
            </div>

            <div class="candidate-grid">
                @foreach($samplePegawais as $p)
                <div class="candidate-card">
                    <div>
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div class="candidate-avatar">
                                    {{ strtoupper(substr($p->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="candidate-name" style="font-size: 14px;">
                                        {{ $p->nama }}
                                    </h4>
                                    <span style="font-family: monospace; font-size: 12px; color: var(--text-muted);">
                                        {{ $p->nip }}
                                    </span>
                                </div>
                            </div>
                            @if($p->status_pegawai == 'PNS')
                                <span class="badge-status badge-pns">PNS</span>
                            @else
                                <span class="badge-status badge-pppk">PPPK</span>
                            @endif
                        </div>

                        <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4; margin-bottom: 12px;">
                            <div><strong style="color: var(--text-main);">Unit:</strong> {{ $p->unitKerja?->skpd ?? '-' }}</div>
                            <div><strong style="color: var(--text-main);">Jabatan:</strong> {{ $p->jabatan?->nama ?? '-' }}</div>
                        </div>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11.5px; color: var(--luno-primary-text); font-weight: 600;">
                            {{ $p->realisasi_gajis_count }} Periode Gaji
                        </span>
                        <a href="{{ route('laporan.trace_gaji.index', ['pegawai_id' => $p->id]) }}" 
                           style="background: var(--luno-primary); color: #ffffff; padding: 5px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                            Lihat Trace <i class="ph ph-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif

</div>

<!-- Modal Interaktif Slip Gaji Rinci (Dark Mode Compatible) -->
<div class="trace-modal" id="slipModal">
    <div class="trace-modal-box">
        <div class="modal-header-styled">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-receipt" style="color: var(--luno-primary);"></i>
                    Rincian Slip Gaji Bulanan
                </h3>
                <span id="slipModalSubtitle" style="font-size: 12.5px; color: var(--text-muted);"></span>
            </div>
            <button type="button" onclick="closeSlipModal()" style="background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer; padding: 4px;">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div style="padding: 24px;" id="slipModalContent">
            <!-- Disuntikkan via Javascript -->
        </div>

        <div class="modal-footer-styled">
            <button type="button" onclick="closeSlipModal()" class="btn-custom-secondary">
                Tutup
            </button>
            <a href="#" id="btnDownloadSlipPdf" target="_blank" style="background: var(--luno-primary); color: #ffffff; padding: 8px 18px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                <i class="ph ph-file-pdf"></i> Unduh Slip (PDF)
            </a>
        </div>
    </div>
</div>

<script>
    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function showSlipModal(rec, nama, nip) {
        document.getElementById('slipModalSubtitle').textContent = nama + ' (NIP: ' + nip + ') - Periode: ' + rec.periode + ' (' + rec.jenis_gaji + ')';
        
        let pdfUrl = "{{ url('/laporan/trace-gaji') }}/" + (rec.pegawai_id || '{{ $selectedPegawai->id ?? 0 }}') + "/slip-pdf/" + rec.id;
        document.getElementById('btnDownloadSlipPdf').setAttribute('href', pdfUrl);

        let content = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Kolom Kiri: Penghasilan -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--luno-primary-text); text-transform: uppercase; margin-bottom: 12px; border-bottom: 2px solid var(--luno-primary-border); padding-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="ph ph-plus-circle"></i> Komponen Penghasilan</span>
                    </div>
                    <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Gaji Pokok</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.gaji_pokok)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Istri / Suami</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_istri)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Anak</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_anak)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Jabatan / Struktural</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_jabatan)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Fungsional</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_fungsional)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Umum</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_umum)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Beras</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_beras)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan PPh / Pajak</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_pajak)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Pembulatan</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_pembulatan)}</td></tr>
                        ${rec.tj_lain > 0 ? `<tr><td style="padding: 6px 0; color: var(--text-muted);">Tunjangan Lainnya</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.tj_lain)}</td></tr>` : ''}
                        <tr style="border-top: 1.5px solid var(--border-color); font-weight: 700;">
                            <td style="padding: 10px 0 0 0; color: var(--text-main);">TOTAL PENGHASILAN KOTOR</td>
                            <td style="text-align: right; padding: 10px 0 0 0; font-family: monospace; font-size: 13.5px; color: var(--luno-primary-text);">${formatRupiah(rec.gaji_kotor)}</td>
                        </tr>
                    </table>
                </div>

                <!-- Kolom Kanan: Potongan -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--danger-text); text-transform: uppercase; margin-bottom: 12px; border-bottom: 2px solid var(--danger-light); padding-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="ph ph-minus-circle"></i> Komponen Potongan</span>
                    </div>
                    <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">Potongan Pajak (PPh)</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.pot_pajak)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">IWP 2% (BPJS Kesehatan / Jamkes)</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.pot_iwp2)}</td></tr>
                        <tr><td style="padding: 6px 0; color: var(--text-muted);">IWP 8% (Taspen / Pensiun & THT)</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.pot_iwp8)}</td></tr>
                        ${rec.pot_taperum > 0 ? `<tr><td style="padding: 6px 0; color: var(--text-muted);">Potongan Taperum</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.pot_taperum)}</td></tr>` : ''}
                        ${rec.pot_lain > 0 ? `<tr><td style="padding: 6px 0; color: var(--text-muted);">Potongan Lain-lain</td><td style="text-align: right; font-weight: 600; font-family: monospace; color: var(--text-main);">${formatRupiah(rec.pot_lain)}</td></tr>` : ''}
                        <tr style="border-top: 1.5px solid var(--border-color); font-weight: 700;">
                            <td style="padding: 10px 0 0 0; color: var(--text-main);">TOTAL POTONGAN</td>
                            <td style="text-align: right; padding: 10px 0 0 0; font-family: monospace; font-size: 13.5px; color: var(--danger-text);">${formatRupiah(rec.total_potongan)}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Box Gaji Bersih Diterima -->
            <div style="margin-top: 20px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.14) 0%, rgba(5, 150, 105, 0.06) 100%); border: 2px solid rgba(16, 185, 129, 0.35); border-radius: 12px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 12px; font-weight: 700; color: var(--success-text); text-transform: uppercase; letter-spacing: 0.5px;">Jumlah Bersih Diterima (Take Home Pay)</span>
                    <div style="font-size: 24px; font-weight: 800; color: var(--success-text); font-family: monospace; margin-top: 2px;">
                        ${formatRupiah(rec.gaji_bersih)}
                    </div>
                </div>
                <div style="text-align: right; font-size: 12px; color: var(--text-muted);">
                    <div>Periode: <strong style="color: var(--text-main);">${rec.periode}</strong></div>
                    <div>Kriteria: <strong style="color: var(--text-main);">${rec.jenis_gaji}</strong></div>
                </div>
            </div>
        `;

        document.getElementById('slipModalContent').innerHTML = content;
        document.getElementById('slipModal').classList.add('show');
    }

    function closeSlipModal() {
        document.getElementById('slipModal').classList.remove('show');
    }

    // Close on outside click
    window.addEventListener('click', function(e) {
        let modal = document.getElementById('slipModal');
        if (e.target === modal) {
            closeSlipModal();
        }
    });
</script>
@endsection
