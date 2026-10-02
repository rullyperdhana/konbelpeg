@extends('layouts.app')

@section('title', 'Dashboard - Realisasi Belanja Pegawai')
@section('page_title', 'Dashboard Overview')

@section('content')
<style>
    /* Custom styles for enhanced dashboard */
    .dashboard-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .kpi-title {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin: 0;
    }

    .kpi-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        margin: 4px 0 0 0;
        font-variant-numeric: tabular-nums;
    }

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .kpi-bottom {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        padding-top: 10px;
        border-top: 1px solid var(--border-subtle);
        margin-top: 8px;
    }

    /* Financial Breakdown Section */
    .financial-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 992px) {
        .financial-grid {
            grid-template-columns: 1fr;
        }
    }

    .fin-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        border-radius: 8px;
        background: var(--bg-surface-subtle);
        margin-bottom: 8px;
        border: 1px solid var(--border-subtle);
        font-size: 13px;
    }

    .fin-label {
        font-weight: 500;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fin-val {
        font-weight: 700;
        color: var(--text-main);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    /* Chart Grid */
    .chart-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1024px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Progress bar helper */
    .custom-progress {
        width: 100%;
        background-color: #f1f5f9;
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
        margin-top: 6px;
    }

    .custom-progress-bar {
        height: 100%;
        border-radius: 999px;
    }

    /* Rank Badge */
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
    }
    .rank-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .rank-2 { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .rank-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .rank-other { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
</style>

<!-- Page Header -->
<div class="app-header">
    <div>
        <h2>Dashboard Analisis Realisasi Belanja Pegawai</h2>
        <p>Ringkasan eksekutif alokasi dan realisasi anggaran belanja pegawai, gaji pokok, dan TPP pemerintah provinsi.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 11.5px; font-weight: 700; padding: 6px 12px; border-radius: 999px; background: var(--luno-primary-light); color: var(--luno-primary); border: 1px solid var(--luno-primary-border);">
            <i class="ph-bold ph-calendar"></i> Tahun Anggaran 2026
        </span>
        <a href="/realisasi/gaji" class="btn btn-export">
            <i class="ph ph-receipt"></i> Gaji Pegawai
        </a>
        <a href="/realisasi/tpp" class="btn btn-primary">
            <i class="ph ph-wallet"></i> Realisasi TPP
        </a>
    </div>
</div>

<!-- KPI Widgets Grid (6 Cards) -->
<div class="dashboard-kpi-grid">
    <!-- 1. Total Belanja Pegawai (Konsolidasian Gaji + TPP) -->
    <div class="kpi-card" style="border-left: 4px solid var(--luno-primary);">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">Total Belanja Pegawai (YTD)</p>
                <h3 class="kpi-value" style="color: var(--luno-primary);">Rp {{ number_format($totalBelanjaPegawai, 0, ',', '.') }}</h3>
            </div>
            <div class="kpi-icon icon-primary">
                <i class="ph-bold ph-chart-donut"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            <span style="font-weight: 600; color: var(--text-main);">Konsolidasian</span> Gaji + TPP Terbayar
        </div>
    </div>

    <!-- 2. Total Pegawai Terdaftar -->
    <div class="kpi-card">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">Total Pegawai Terdata</p>
                <h3 class="kpi-value">{{ number_format($totalPegawai, 0, ',', '.') }}</h3>
            </div>
            <div class="kpi-icon icon-success">
                <i class="ph-bold ph-users-three"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            <span>PNS: <strong>{{ number_format($pnsCount, 0, ',', '.') }}</strong></span> | 
            <span>PPPK: <strong>{{ number_format($pppkCount, 0, ',', '.') }}</strong></span> | 
            <span>PW: <strong>{{ number_format($paruhWaktuCount, 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <!-- 3. Realisasi Gaji Bersih -->
    <div class="kpi-card">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">Realisasi Gaji (Netto)</p>
                <h3 class="kpi-value">Rp {{ number_format($totalRealisasiGaji, 0, ',', '.') }}</h3>
            </div>
            <div class="kpi-icon icon-info">
                <i class="ph-bold ph-coins"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-check"></i> {{ number_format($gajiSummary->total_transaksi ?? 0, 0, ',', '.') }}</span>
            <span>Rekord Pembayaran Gaji</span>
        </div>
    </div>

    <!-- 4. Realisasi TPP Terbayarkan -->
    <div class="kpi-card">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">Realisasi TPP (Netto)</p>
                <h3 class="kpi-value">Rp {{ number_format($totalRealisasiTpp, 0, ',', '.') }}</h3>
            </div>
            <div class="kpi-icon icon-warning">
                <i class="ph-bold ph-wallet"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-arrow-circle-up-right"></i> {{ number_format($tppSummary->total_transaksi ?? 0, 0, ',', '.') }}</span>
            <span>Pembayaran TPP ASN</span>
        </div>
    </div>

    <!-- 5. Total SKPD & Satker -->
    <div class="kpi-card">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">Struktur Organisasi</p>
                <h3 class="kpi-value">{{ number_format($totalSkpd, 0, ',', '.') }} <span style="font-size: 15px; font-weight: 600; color: var(--text-muted);">SKPD</span></h3>
            </div>
            <div class="kpi-icon icon-primary">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            <span>Mencakup <strong>{{ number_format($totalUnitKerja, 0, ',', '.') }}</strong> UPTD & Satuan Kerja</span>
        </div>
    </div>

    <!-- 6. Status Audit & NIP Belum Terdaftar -->
    <div class="kpi-card" style="{{ $unmatchedCount > 0 ? 'border-left: 4px solid var(--warning);' : 'border-left: 4px solid var(--success);' }}">
        <div class="kpi-top">
            <div>
                <p class="kpi-title">NIP Belum Terdaftar</p>
                <h3 class="kpi-value" style="{{ $unmatchedCount > 0 ? 'color: var(--warning);' : 'color: var(--success);' }}">
                    {{ number_format($unmatchedCount, 0, ',', '.') }}
                </h3>
            </div>
            <div class="kpi-icon {{ $unmatchedCount > 0 ? 'icon-warning' : 'icon-success' }}">
                <i class="ph-bold {{ $unmatchedCount > 0 ? 'ph-warning' : 'ph-shield-check' }}"></i>
            </div>
        </div>
        <div class="kpi-bottom">
            @if($unmatchedCount > 0)
                <a href="/laporan/unmatched-nip" style="color: var(--warning); font-weight: 600; text-decoration: none;">
                    <i class="ph-bold ph-arrow-right"></i> Cek Log & Selaraskan Data
                </a>
            @else
                <span class="luno-trend-up"><i class="ph-bold ph-check-circle"></i> 100% Valid & Terdaftar</span>
            @endif
        </div>
    </div>
</div>

<!-- Financial Breakdown Section -->
<div class="financial-grid">
    <!-- Rincian Komponen Gaji -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    <i class="ph-bold ph-receipt" style="color: var(--luno-primary); margin-right: 6px;"></i> Komposisi Realisasi Gaji
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Akumulasi belanja gaji dari berkas SIMGAJI DBF & Excel</p>
            </div>
            <a href="/realisasi/gaji" class="badge badge-gaji" style="text-decoration: none;">Lihat Rincian</a>
        </div>

        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-wallet"></i> Total Gaji Pokok (GAPOK)</span>
            <span class="fin-val">Rp {{ number_format($gajiSummary->total_gapok ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-percent"></i> Tunjangan Pajak (PPAJAK)</span>
            <span class="fin-val" style="color: var(--info);">Rp {{ number_format($gajiSummary->total_pajak ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-shield"></i> Potongan Iuran Wajib Pegawai (IWP)</span>
            <span class="fin-val" style="color: var(--warning);">Rp {{ number_format($gajiSummary->total_iwp ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-scissors"></i> Potongan Lain-lain</span>
            <span class="fin-val" style="color: var(--danger);">Rp {{ number_format($gajiSummary->total_potongan ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item" style="background: var(--luno-primary-light); border-color: var(--luno-primary-border);">
            <span class="fin-label" style="color: var(--luno-primary); font-weight: 700;">
                <i class="ph-bold ph-check-circle"></i> Gaji Bersih Dibayarkan (SP2D)
            </span>
            <span class="fin-val" style="color: var(--luno-primary); font-size: 14.5px;">
                Rp {{ number_format($gajiSummary->total_bersih ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Rincian Komponen TPP -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    <i class="ph-bold ph-wallet" style="color: var(--info); margin-right: 6px;"></i> Komposisi Realisasi TPP
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Akumulasi Tambahan Penghasilan Pegawai (Beban Kerja & Prestasi)</p>
            </div>
            <a href="/realisasi/tpp" class="badge badge-tpp" style="text-decoration: none;">Lihat Rincian</a>
        </div>

        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-trend-up"></i> Total TPP Bruto</span>
            <span class="fin-val">Rp {{ number_format($tppSummary->total_bruto ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-receipt"></i> Potongan PPh 21</span>
            <span class="fin-val" style="color: var(--danger);">Rp {{ number_format($tppSummary->total_pph ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-first-aid"></i> Iuran BPJS / IWP TPP</span>
            <span class="fin-val" style="color: var(--warning);">Rp {{ number_format($tppSummary->total_iwp ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item">
            <span class="fin-label"><i class="ph ph-scissors"></i> Potongan Lainnya TPP</span>
            <span class="fin-val" style="color: var(--danger);">Rp {{ number_format($tppSummary->total_potongan ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="fin-item" style="background: var(--info-light); border-color: rgba(6, 182, 212, 0.3);">
            <span class="fin-label" style="color: var(--info-text); font-weight: 700;">
                <i class="ph-bold ph-check-circle"></i> TPP Bersih Dibayarkan
            </span>
            <span class="fin-val" style="color: var(--info-text); font-size: 14.5px;">
                Rp {{ number_format($tppSummary->total_dibayarkan ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

<!-- Charts & Visual Analytics Grid (Chart.js) -->
<div class="chart-grid">
    <!-- Chart 1: Tren Realisasi Gaji per Periode -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    Tren Realisasi Belanja Gaji per Periode
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Perbandingan Gaji Pokok vs Gaji Bersih per bulan anggaran</p>
            </div>
            <span class="badge badge-gaji"><i class="ph ph-chart-bar"></i> Statistik Bulanan</span>
        </div>
        
        <div style="height: 270px; position: relative;">
            <canvas id="chartGajiPeriode"></canvas>
        </div>
    </div>

    <!-- Chart 2: Komposisi & Distribusi Pegawai -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    Komposisi Pegawai
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Distribusi PNS, PPPK & Paruh Waktu</p>
            </div>
            <a href="/pegawai" class="badge badge-pns" style="text-decoration: none;">Master Pegawai</a>
        </div>

        <div style="height: 180px; position: relative; margin-bottom: 14px;">
            <canvas id="chartStatusPegawai"></canvas>
        </div>

        <!-- Breakdown Profesi / Kelompok Pegawai -->
        <div style="border-top: 1px solid var(--border-subtle); padding-top: 12px;">
            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                <span style="color: var(--text-muted);"><i class="ph ph-chalkboard-teacher"></i> Guru</span>
                <span style="font-weight: 700;">{{ number_format($pegawaiByJenis['GURU'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="custom-progress" style="margin-bottom: 8px;">
                <div class="custom-progress-bar" style="width: {{ $totalPegawai > 0 ? round((($pegawaiByJenis['GURU'] ?? 0) / $totalPegawai) * 100) : 0 }}%; background: #3b82f6;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                <span style="color: var(--text-muted);"><i class="ph ph-gear"></i> Tenaga Teknis</span>
                <span style="font-weight: 700;">{{ number_format($pegawaiByJenis['TEKNIS'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="custom-progress" style="margin-bottom: 8px;">
                <div class="custom-progress-bar" style="width: {{ $totalPegawai > 0 ? round((($pegawaiByJenis['TEKNIS'] ?? 0) / $totalPegawai) * 100) : 0 }}%; background: #10b981;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                <span style="color: var(--text-muted);"><i class="ph ph-heartbeat"></i> Tenaga Kesehatan</span>
                <span style="font-weight: 700;">{{ number_format($pegawaiByJenis['KESEHATAN'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="custom-progress" style="margin-bottom: 8px;">
                <div class="custom-progress-bar" style="width: {{ $totalPegawai > 0 ? round((($pegawaiByJenis['KESEHATAN'] ?? 0) / $totalPegawai) * 100) : 0 }}%; background: #06b6d4;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                <span style="color: var(--text-muted);"><i class="ph ph-book-open"></i> Tenaga Kependidikan (Tendik)</span>
                <span style="font-weight: 700;">{{ number_format($pegawaiByJenis['TENDIK'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="custom-progress">
                <div class="custom-progress-bar" style="width: {{ $totalPegawai > 0 ? round((($pegawaiByJenis['TENDIK'] ?? 0) / $totalPegawai) * 100) : 0 }}%; background: #f59e0b;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables & Breakdowns Section (2 Columns) -->
<div class="chart-grid" style="margin-bottom: 24px;">
    <!-- Top 5 SKPD Realisasi Belanja Terbesar -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    Top 5 SKPD Realisasi Gaji Terbesar
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Unit kerja dengan serapan belanja gaji tertinggi</p>
            </div>
            <a href="/laporan/pegawai" class="badge badge-pns" style="text-decoration: none;">Daftar Pegawai SKPD</a>
        </div>

        <div class="table-container" style="box-shadow: none; border: 1px solid var(--border-color); border-radius: 10px;">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Nama SKPD</th>
                        <th style="text-align: center;">Penerima</th>
                        <th style="text-align: right;">Total Gaji Bersih</th>
                        <th style="text-align: center; width: 80px;">Proporsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSkpdGaji as $idx => $skpd)
                        @php
                            $pct = $totalRealisasiGaji > 0 ? round(($skpd->total_gaji / $totalRealisasiGaji) * 100, 1) : 0;
                            $rankClass = $idx === 0 ? 'rank-1' : ($idx === 1 ? 'rank-2' : ($idx === 2 ? 'rank-3' : 'rank-other'));
                        @endphp
                        <tr>
                            <td style="text-align: center;">
                                <span class="rank-badge {{ $rankClass }}">{{ $idx + 1 }}</span>
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13px;">{{ $skpd->skpd }}</strong>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-gaji">{{ number_format($skpd->total_transaksi, 0, ',', '.') }}</span>
                            </td>
                            <td class="money" style="font-size: 12.5px; color: var(--luno-primary);">
                                Rp {{ number_format($skpd->total_gaji, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">{{ $pct }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">Belum ada data realisasi gaji yang diunggah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Realisasi per Kriteria & Unmatched NIP Alert -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">
                    Realisasi per Kriteria Gaji
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Gaji Induk, Susulan, THR & Gaji 13</p>
            </div>
            <a href="/realisasi/gaji" class="badge badge-gaji" style="text-decoration: none;">Filter Kriteria</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
            @forelse($gajiPerKriteria as $kriteria)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 8px; background: var(--bg-surface-subtle); border: 1px solid var(--border-subtle);">
                    <div>
                        <strong style="font-size: 13px; color: var(--text-main); display: block;">{{ $kriteria->kriteria }}</strong>
                        <span style="font-size: 11.5px; color: var(--text-muted);">{{ number_format($kriteria->total_transaksi, 0, ',', '.') }} pembayaran</span>
                    </div>
                    <span class="money" style="font-size: 13px; color: var(--success-text);">
                        Rp {{ number_format($kriteria->total_bersih, 0, ',', '.') }}
                    </span>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 16px;">Belum ada kriteria gaji terdata.</div>
            @endforelse
        </div>

        <!-- Alert Log NIP Unmatched -->
        @if($unmatchedCount > 0)
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="ph-bold ph-warning-circle" style="color: #b45309; font-size: 18px;"></i>
                    <strong style="color: #92400e; font-size: 13px;">Perhatian Sinkronisasi NIP</strong>
                </div>
                <p style="color: #78350f; font-size: 12px; margin: 0 0 8px 0; line-height: 1.4;">
                    Ditemukan <strong>{{ $unmatchedCount }} NIP</strong> pada file gaji/TPP yang belum tercatat di Master Pegawai.
                </p>
                <a href="/laporan/unmatched-nip" class="btn btn-export" style="font-size: 11.5px; padding: 6px 10px; text-decoration: none; width: 100%; justify-content: center; background: white;">
                    <i class="ph ph-arrow-right"></i> Buka Log NIP Belum Terdaftar
                </a>
            </div>
        @else
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; text-align: center;">
                <i class="ph-bold ph-shield-check" style="color: #166534; font-size: 20px;"></i>
                <p style="color: #166534; font-size: 12.5px; font-weight: 600; margin: 4px 0 0 0;">Semua NIP Terdaftar & Sinkron</p>
            </div>
        @endif
    </div>
</div>

<!-- LUNO Featured Overview Card & Quick Modules -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 320px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: var(--luno-primary-light); color: var(--luno-primary); font-size: 11.5px; font-weight: 700; margin-bottom: 12px; border: 1px solid var(--luno-primary-border);">
                <i class="ph-fill ph-sparkle"></i> Pusat Rekonsiliasi & Pelaporan
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">
                Sistem Terpadu Rekonsiliasi & Pelaporan Belanja Pegawai
            </h3>
            <p style="color: var(--text-muted); line-height: 1.6; font-size: 13px; margin-bottom: 18px;">
                Aplikasi ini dirancang untuk mempermudah monitoring pengeluaran belanja pegawai daerah, rekonsiliasi data penggajian berbasis file DBF dan Excel, serta otomatisasi pembuatan laporan konsolidasian (Laporan Gabungan, SIKD Core, dan Laporan PPPK Guru).
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="/laporan/gabungan" class="btn btn-primary">
                    <i class="ph-bold ph-chart-pie-slice"></i> Buka Laporan Gabungan
                </a>
                <a href="/laporan/sikd-core" class="btn btn-export">
                    <i class="ph-bold ph-file-text"></i> Laporan SIKD Core
                </a>
                <a href="/rekonsiliasi/simgaji" class="btn btn-secondary">
                    <i class="ph-bold ph-arrows-left-right"></i> Rekonsiliasi SIMGAJI
                </a>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; min-width: 270px;">
            <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; margin: 0 0 2px 0;">Pintasan Modul Utama</h4>
            
            <a href="/pegawai" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-users" style="color: var(--luno-primary); font-size: 17px;"></i> Master Data Pegawai
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/realisasi/gaji" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-upload-simple" style="color: var(--success); font-size: 17px;"></i> Upload DBF / Excel Gaji
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/realisasi/tpp" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-wallet" style="color: var(--info); font-size: 17px;"></i> Upload Realisasi TPP
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/laporan/pppk-guru" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-student" style="color: var(--warning); font-size: 17px;"></i> Laporan PPPK Guru
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/laporan/iwp-jamkes" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-heartbeat" style="color: var(--danger); font-size: 17px;"></i> Laporan IWP & Jamkes
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Chart 1: Tren Realisasi Gaji per Periode
        const ctxPeriode = document.getElementById('chartGajiPeriode');
        if (ctxPeriode) {
            const periodeLabels = @json($gajiPerPeriode->pluck('periode'));
            const gapokData = @json($gajiPerPeriode->pluck('gapok'));
            const bersihData = @json($gajiPerPeriode->pluck('bersih'));

            new Chart(ctxPeriode, {
                type: 'bar',
                data: {
                    labels: periodeLabels.length > 0 ? periodeLabels : ['Belum Ada Data'],
                    datasets: [
                        {
                            label: 'Gaji Pokok (GAPOK)',
                            data: gapokData.length > 0 ? gapokData : [0],
                            backgroundColor: 'rgba(59, 130, 246, 0.75)',
                            borderColor: '#3b82f6',
                            borderWidth: 1.5,
                            borderRadius: 6,
                        },
                        {
                            label: 'Gaji Bersih (SP2D)',
                            data: bersihData.length > 0 ? bersihData : [0],
                            backgroundColor: 'rgba(76, 53, 222, 0.85)',
                            borderColor: '#4C35DE',
                            borderWidth: 1.5,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { family: 'Inter', size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + Number(context.parsed.y).toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                callback: function(value) {
                                    if (value >= 1000000000) {
                                        return (value / 1000000000).toFixed(1) + ' M';
                                    } else if (value >= 1000000) {
                                        return (value / 1000000).toFixed(0) + ' Jt';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 11 } }
                        }
                    }
                }
            });
        }

        // Chart 2: Komposisi Status Pegawai (Doughnut)
        const ctxStatus = document.getElementById('chartStatusPegawai');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['PNS', 'PPPK', 'PPPK Paruh Waktu'],
                    datasets: [{
                        data: [
                            {{ $pnsCount }},
                            {{ $pppkCount }},
                            {{ $paruhWaktuCount }}
                        ],
                        backgroundColor: [
                            '#4C35DE',
                            '#10b981',
                            '#f59e0b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Inter', size: 11 },
                                usePointStyle: true,
                                padding: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return label + ': ' + Number(value).toLocaleString('id-ID') + ' (' + pct + '%)';
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }
    });
</script>
@endsection
