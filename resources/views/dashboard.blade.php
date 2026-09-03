@extends('layouts.app')

@section('title', 'Dashboard - Realisasi Belanja Pegawai')
@section('page_title', 'Dashboard Overview')

@section('content')
<!-- LUNO Page Header -->
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

<!-- LUNO KPI Widgets Grid -->
<div class="stats-grid">
    <!-- Widget 1: Total Pegawai -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Total Pegawai</p>
                <h3 class="luno-widget-value">{{ number_format($totalPegawai, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-primary">
                <i class="ph-bold ph-users-three"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-check-circle"></i> Terdata</span>
            <span>di Master Data Pegawai & UPTD</span>
        </div>
    </div>
    
    <!-- Widget 2: Realisasi Gaji -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Realisasi Gaji (Bulan Ini)</p>
                <h3 class="luno-widget-value">Rp {{ number_format($totalRealisasiGaji, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-success">
                <i class="ph-bold ph-coins"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-trend-up"></i> Gaji Bersih</span>
            <span>Total Pengeluaran SP2D Gaji</span>
        </div>
    </div>
    
    <!-- Widget 3: Realisasi TPP -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Realisasi TPP (Bulan Ini)</p>
                <h3 class="luno-widget-value">Rp {{ number_format($totalRealisasiTpp, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-info">
                <i class="ph-bold ph-wallet"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-up"><i class="ph-bold ph-arrow-circle-up-right"></i> Terbayarkan</span>
            <span>TPP Bruto, Netto & Potongan</span>
        </div>
    </div>
    
    <!-- Widget 4: Total SKPD -->
    <div class="luno-widget">
        <div class="luno-widget-top">
            <div>
                <p class="luno-widget-title">Total SKPD & UPTD</p>
                <h3 class="luno-widget-value">{{ number_format($totalSkpd, 0, ',', '.') }}</h3>
            </div>
            <div class="luno-widget-icon icon-warning">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div class="luno-widget-bottom">
            <span class="luno-trend-neutral"><i class="ph-bold ph-shield-check"></i> 100% Terintegrasi</span>
            <span>Struktur Induk, UPT & Satker</span>
        </div>
    </div>
</div>

<!-- LUNO Featured Overview Card -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: var(--luno-primary-light); color: var(--luno-primary); font-size: 11px; font-weight: 700; margin-bottom: 12px; border: 1px solid var(--luno-primary-border);">
                <i class="ph-fill ph-sparkle"></i> Sistem Informasi Belanja Pegawai
            </div>
            <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">
                Sistem Terpadu Rekonsiliasi & Pelaporan Belanja Pegawai
            </h3>
            <p style="color: var(--text-muted); line-height: 1.6; font-size: 13px; margin-bottom: 16px;">
                Aplikasi ini dirancang untuk mempermudah monitoring pengeluaran belanja pegawai daerah, rekonsiliasi data penggajian berbasis file DBF dan Excel, serta otomatisasi pembuatan laporan konsolidasian (Laporan Gabungan, SIKD Core, dan Laporan PPPK Guru).
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="/laporan/gabungan" class="btn btn-primary">
                    <i class="ph-bold ph-chart-pie-slice"></i> Buka Laporan Gabungan
                </a>
                <a href="/laporan/unmatched-nip" class="btn btn-export">
                    <i class="ph-bold ph-warning-circle"></i> Cek Log Gagal Upload
                </a>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; min-width: 250px;">
            <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; margin: 0 0 2px 0;">Akses Cepat Modul</h4>
            
            <a href="/pegawai" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-users" style="color: var(--luno-primary); font-size: 17px;"></i> Master Data Pegawai
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/realisasi/gaji" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-upload-simple" style="color: var(--success); font-size: 17px;"></i> Upload DBF / Gaji
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>

            <a href="/laporan/sikd-core" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; background: var(--bg-surface-subtle); color: var(--text-main); text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid var(--border-color); transition: all 0.15s ease;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-file-text" style="color: var(--info); font-size: 17px;"></i> Laporan SIKD Core
                </span>
                <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
            </a>
        </div>
    </div>
</div>
@endsection
