@extends('layouts.app')

@section('title', 'Laporan')
@section('page_title', 'Laporan')

@section('content')
<style>
    .aas-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .aas-header h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .aas-header p { color: #94a3b8; font-size: 14px; }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .report-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .report-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #eff6ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }

    .report-card h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .report-card p {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .btn-report {
        background: #3b82f6;
        color: white;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: 0.2s;
        width: 100%;
        text-align: center;
    }

    .btn-report:hover {
        background: #2563eb;
    }
</style>

<div class="aas-header">
    <h2>Pusat Laporan</h2>
    <p>Pilih jenis laporan yang ingin Anda unduh atau cetak.</p>
</div>

<div class="report-grid">
    <div class="report-card">
        <div class="report-icon">
            <i class="ph ph-users"></i>
        </div>
        <h3>Daftar Pegawai</h3>
        <p>Laporan komprehensif seluruh pegawai, dapat difilter berdasarkan SKPD dan status kepegawaian (PNS/PPPK).</p>
        <a href="/laporan/pegawai" class="btn-report">Buka Laporan</a>
    </div>
    
    <div class="report-card">
        <div class="report-icon">
            <i class="ph ph-money"></i>
        </div>
        <h3>Realisasi Belanja TPP</h3>
        <p>Laporan rekapan Gaji, TPP, JKK/JKM, dan komponen belanja pegawai lainnya.</p>
        <a href="/realisasi/tpp" class="btn-report">Buka Laporan</a>
    </div>

    <div class="report-card">
        <div class="report-icon" style="background: rgba(76, 53, 222, 0.1); color: #4C35DE;">
            <i class="ph ph-git-branch"></i>
        </div>
        <h3>Penyelarasan SKPD & UPTD</h3>
        <p>Matriks perbandingan dan penyelarasan hierarki SKPD, UPTD, dan Satker antara data SIMGAJI (Taspen) dengan SIMPEG (Master BKD).</p>
        <a href="/laporan/penyelarasan-unit-kerja" class="btn-report" style="background: #4C35DE;">Buka Laporan</a>
    </div>

    <div class="report-card">
        <div class="report-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="ph ph-heartbeat"></i>
        </div>
        <h3>Laporan IWP & Jamkes BPJS</h3>
        <p>Rekonsiliasi Iuran Wajib Pegawai (IWP 10%) dan pemotongan Jaminan Kesehatan BPJS dari Gaji Reguler (2%) dan TPP (1%) per SKPD & Pegawai.</p>
        <a href="/laporan/iwp-jamkes" class="btn-report" style="background: #10b981;">Buka Laporan</a>
    </div>
</div>
@endsection
