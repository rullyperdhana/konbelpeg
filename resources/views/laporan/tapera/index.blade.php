@extends('layouts.app')

@section('title', 'Simulasi Perhitungan Tapera (ASN & Pemda)')
@section('page_title', 'Simulasi Tapera (PP 21/2024)')

@section('content')
<style>
    /* Hero Header */
    .tapera-hero {
        background: linear-gradient(135deg, #09203f 0%, #1e3a5f 50%, #295270 100%);
        color: #ffffff;
        padding: 26px 30px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 12px 28px -6px rgba(15, 32, 63, 0.35);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .tapera-hero-content h2 {
        font-size: 23px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tapera-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tapera-hero-content p {
        font-size: 13.5px;
        color: #cbd5e1;
        max-width: 780px;
        line-height: 1.55;
        margin: 0;
    }

    /* KPI Summary Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    .kpi-card.highlight-pemda {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.06) 0%, rgba(37, 99, 235, 0.03) 100%);
        border: 1.5px solid rgba(59, 130, 246, 0.35);
    }

    .kpi-card.highlight-asn {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, rgba(217, 119, 6, 0.03) 100%);
        border: 1.5px solid rgba(245, 158, 11, 0.35);
    }

    .kpi-card.highlight-total {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.06) 0%, rgba(5, 150, 105, 0.03) 100%);
        border: 1.5px solid rgba(16, 185, 129, 0.35);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-muted, #64748b);
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
        font-size: 19px;
    }

    .kpi-value {
        font-size: 21px;
        font-weight: 800;
        color: var(--text-main, #0f172a);
        letter-spacing: -0.02em;
    }

    .kpi-subtitle {
        font-size: 11.5px;
        color: var(--text-muted, #64748b);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Tabs & Controls */
    .controls-wrapper {
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
    }

    .tab-pills {
        display: flex;
        gap: 10px;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
        padding-bottom: 14px;
        margin-bottom: 18px;
        overflow-x: auto;
    }

    .tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-muted, #64748b);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .tab-pill:hover {
        background: #f1f5f9;
        color: var(--text-main, #0f172a);
    }

    .tab-pill.active {
        background: #1e3a5f;
        color: #ffffff;
        border-color: #1e3a5f;
        box-shadow: 0 4px 10px rgba(30, 58, 95, 0.25);
    }

    .filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .form-select, .form-input {
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #cbd5e1);
        font-size: 13px;
        color: var(--text-main, #0f172a);
        background-color: var(--bg-surface, #ffffff);
        outline: none;
        transition: border-color 0.2s;
    }

    .form-select:focus, .form-input:focus {
        border-color: #2563eb;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .btn-excel {
        background: #107c41;
        color: #ffffff;
    }
    .btn-excel:hover {
        background: #0b5c30;
    }

    .btn-pdf {
        background: #b91c1c;
        color: #ffffff;
    }
    .btn-pdf:hover {
        background: #991b1b;
    }

    /* Calculator Section */
    .calculator-container {
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 24px;
    }

    @media (max-width: 992px) {
        .calculator-container {
            grid-template-columns: 1fr;
        }
    }

    .calc-card {
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
    }

    .calc-card h3 {
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--text-main, #0f172a);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .calc-card p.calc-desc {
        font-size: 13px;
        color: var(--text-muted, #64748b);
        margin-bottom: 20px;
    }

    .form-group-calc {
        margin-bottom: 16px;
    }

    .form-group-calc label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .input-rupiah-wrapper {
        position: relative;
    }

    .input-rupiah-prefix {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .input-rupiah {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        font-family: inherit;
    }

    .calc-result-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-top: 16px;
    }

    .result-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .result-row:last-child {
        border-bottom: none;
    }

    .result-row.main-total {
        border-top: 2px solid #cbd5e1;
        border-bottom: none;
        padding-top: 14px;
        margin-top: 8px;
    }

    .result-label {
        font-size: 13px;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .result-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        font-family: 'Courier New', Courier, monospace;
    }

    .result-tag {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .tag-asn { background: #fef3c7; color: #b45309; }
    .tag-pemda { background: #dbeafe; color: #1d4ed8; }
    .tag-total { background: #d1fae5; color: #047857; }

    /* Tables */
    .table-container {
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        overflow-x: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .table-custom th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .table-custom td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
    }

    .table-custom tr:hover td {
        background: #f8fafc;
    }

    .table-custom .col-number {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
    }

    .table-custom tfoot td {
        background: #f1f5f9;
        font-weight: 800;
        border-top: 2px solid #cbd5e1;
        color: #0f172a;
    }

    .progress-bar-container {
        background: #e2e8f0;
        border-radius: 999px;
        height: 12px;
        display: flex;
        overflow: hidden;
        margin: 12px 0 6px 0;
    }

    .progress-bar-asn {
        background: #f59e0b;
        height: 100%;
        width: 83.33%;
    }

    .progress-bar-pemda {
        background: #3b82f6;
        height: 100%;
        width: 16.67%;
    }
</style>

<!-- Hero Section -->
<div class="tapera-hero">
    <div class="tapera-hero-content">
        <h2>
            <i class="ph ph-house-line"></i>
            Simulasi & Proyeksi Tapera ASN & Pemberi Kerja
            <span class="tapera-badge">PP No. 21 Tahun 2024</span>
        </h2>
        <p>
            Perhitungan simulasi Tabungan Perumahan Rakyat (Tapera) total 3% ditanggung bersama antara <strong>Pekerja/ASN (2,5%)</strong> dan <strong>Pemberi Kerja/Pemda (0,5%)</strong>. Dihitung berdasarkan <em>Gaji Pokok + Tunjangan Keluarga + Tunjangan Jabatan/Umum</em>.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('laporan.tapera.export_excel', ['tab' => $tab, 'periode_filter' => $periode, 'skpd_filter' => $skpdFilter]) }}" class="btn-action btn-excel">
            <i class="ph ph-file-xls"></i> Unduh Excel
        </a>
        <a href="{{ route('laporan.tapera.export_pdf', ['tab' => $tab, 'periode_filter' => $periode, 'skpd_filter' => $skpdFilter]) }}" class="btn-action btn-pdf">
            <i class="ph ph-file-pdf"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- KPI Summary Cards (Berdasarkan Data Riil Periode Aktif) -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Pegawai Tercover</span>
            <div class="kpi-icon" style="background: rgba(100, 116, 139, 0.12); color: #475569;">
                <i class="ph ph-users"></i>
            </div>
        </div>
        <div class="kpi-value">{{ number_format($totalPegawai) }}</div>
        <div class="kpi-subtitle">
            <i class="ph ph-calendar"></i> Periode: {{ $periode ?: 'Semua' }}
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Dasar Tapera (100%)</span>
            <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                <i class="ph ph-coins"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalDasarTapera, 0, ',', '.') }}</div>
        <div class="kpi-subtitle">Gapok + Tunj. Klrg + Tunj. Jab</div>
    </div>

    <div class="kpi-card highlight-pemda">
        <div class="kpi-header">
            <span class="kpi-title">Beban Pemda / APBD (0,5%)</span>
            <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.15); color: #2563eb;">
                <i class="ph ph-buildings"></i>
            </div>
        </div>
        <div class="kpi-value" style="color: #1d4ed8;">Rp {{ number_format($totalTaperaPemda, 0, ',', '.') }}</div>
        <div class="kpi-subtitle" style="color: #2563eb;">
            Proyeksi 1 Thn: <strong>Rp {{ number_format($totalTaperaPemda * 12, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="kpi-card highlight-asn">
        <div class="kpi-header">
            <span class="kpi-title">Potongan ASN (2,5%)</span>
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                <i class="ph ph-wallet"></i>
            </div>
        </div>
        <div class="kpi-value" style="color: #b45309;">Rp {{ number_format($totalTaperaAsn, 0, ',', '.') }}</div>
        <div class="kpi-subtitle" style="color: #d97706;">
            Proyeksi 1 Thn: <strong>Rp {{ number_format($totalTaperaAsn * 12, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="kpi-card highlight-total">
        <div class="kpi-header">
            <span class="kpi-title">Total Setoran BP Tapera (3%)</span>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                <i class="ph ph-check-circle"></i>
            </div>
        </div>
        <div class="kpi-value" style="color: #047857;">Rp {{ number_format($grandTotalTapera, 0, ',', '.') }}</div>
        <div class="kpi-subtitle" style="color: #059669;">
            Proyeksi 1 Thn: <strong>Rp {{ number_format($grandTotalTapera * 12, 0, ',', '.') }}</strong>
        </div>
    </div>
</div>

<!-- Controls & Tabs -->
<div class="controls-wrapper">
    <div class="tab-pills">
        <a href="{{ route('laporan.tapera.index', ['tab' => 'kalkulator', 'periode_filter' => $periode, 'skpd_filter' => $skpdFilter]) }}" class="tab-pill {{ $tab === 'kalkulator' ? 'active' : '' }}">
            <i class="ph ph-calculator"></i> 1. Kalkulator Simulasi Interaktif (Perseorangan)
        </a>
        <a href="{{ route('laporan.tapera.index', ['tab' => 'rekap', 'periode_filter' => $periode, 'skpd_filter' => $skpdFilter]) }}" class="tab-pill {{ $tab === 'rekap' ? 'active' : '' }}">
            <i class="ph ph-chart-bar"></i> 2. Rekapitulasi per SKPD (Data Riil)
        </a>
        <a href="{{ route('laporan.tapera.index', ['tab' => 'rinci', 'periode_filter' => $periode, 'skpd_filter' => $skpdFilter]) }}" class="tab-pill {{ $tab === 'rinci' ? 'active' : '' }}">
            <i class="ph ph-identification-card"></i> 3. Daftar Nominatif ASN (Rinci)
        </a>
    </div>

    @if($tab !== 'kalkulator')
    <form method="GET" action="{{ route('laporan.tapera.index') }}" class="filter-bar">
        <input type="hidden" name="tab" value="{{ $tab }}">
        
        <div class="filter-group">
            <div>
                <label style="font-size: 11.5px; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px;">Periode Penggajian:</label>
                <select name="periode_filter" class="form-select" onchange="this.form.submit()">
                    <option value="Semua Periode" {{ $periode === 'Semua Periode' ? 'selected' : '' }}>Semua Periode</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p }}" {{ $periode === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size: 11.5px; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px;">Filter SKPD / Unit Kerja:</label>
                <select name="skpd_filter" class="form-select" onchange="this.form.submit()" style="max-width: 320px;">
                    <option value="">Semua SKPD</option>
                    @foreach($filterUnitKerjas as $u)
                        <option value="{{ $u }}" {{ $skpdFilter === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            @if($tab === 'rinci')
            <div>
                <label style="font-size: 11.5px; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px;">Cari NIP / Nama:</label>
                <div style="display: flex; gap: 4px;">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik NIP atau Nama..." class="form-input" style="width: 220px;">
                    <button type="submit" class="btn-action" style="background: #1e3a5f; color: white;">
                        <i class="ph ph-magnifying-glass"></i>
                    </button>
                </div>
            </div>
            @endif
        </div>

        <div style="margin-left: auto;">
            <a href="{{ route('laporan.tapera.index', ['tab' => $tab]) }}" class="btn-action" style="background: #f1f5f9; color: #475569;">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </a>
        </div>
    </form>
    @endif
</div>

@if($tab === 'kalkulator')
<!-- TAB 1: KALKULATOR SIMULASI INTERAKTIF -->
<div class="calculator-container">
    <!-- Form Input Parameter -->
    <div class="calc-card">
        <h3><i class="ph ph-sliders"></i> Parameter Penghasilan ASN</h3>
        <p class="calc-desc">Isi komponen gaji pokok dan tunjangan atau gunakan preset golongan untuk mensimulasikan potongan Tapera.</p>

        <!-- Preset Golongan -->
        <div class="form-group-calc">
            <label>Preset Golongan / Pangkat (PP 5/2024 & Perpres 11/2024):</label>
            <select id="presetGolongan" class="form-select" style="width: 100%;" onchange="applyPreset()">
                <option value="">-- Pilih Preset Golongan (Opsional) --</option>
                <optgroup label="PNS (PP No. 5 Tahun 2024)">
                    <option value="2184000|0|540000">PNS Gol. II/a (Masa Kerja 0 thn: Rp 2.184.000)</option>
                    <option value="2686500|0|540000">PNS Gol. II/c (Masa Kerja 0 thn: Rp 2.686.500)</option>
                    <option value="2785700|0|540000" selected>PNS Gol. III/a (Masa Kerja 0 thn: Rp 2.785.700)</option>
                    <option value="3154400|0|750000">PNS Gol. III/c (Masa Kerja 0 thn: Rp 3.154.400)</option>
                    <option value="3287800|0|900000">PNS Gol. IV/a (Masa Kerja 0 thn: Rp 3.287.800)</option>
                    <option value="3680900|0|1260000">PNS Gol. IV/c (Masa Kerja 0 thn: Rp 3.680.900)</option>
                </optgroup>
                <optgroup label="PPPK (Perpres No. 11 Tahun 2024)">
                    <option value="2511500|0|360000">PPPK Gol. V (D-III: Rp 2.511.500)</option>
                    <option value="3203600|0|540000">PPPK Gol. IX (S-1: Rp 3.203.600)</option>
                    <option value="3480300|0|750000">PPPK Gol. X (S-2: Rp 3.480.300)</option>
                </optgroup>
            </select>
        </div>

        <!-- Gaji Pokok -->
        <div class="form-group-calc">
            <label>1. Gaji Pokok (Rp):</label>
            <div class="input-rupiah-wrapper">
                <span class="input-rupiah-prefix">Rp</span>
                <input type="number" id="inputGapok" class="input-rupiah" value="2785700" min="0" step="1000" oninput="calculateTapera()">
            </div>
        </div>

        <!-- Tunjangan Suami/Istri -->
        <div class="form-group-calc">
            <label>2. Status Pasangan (Tunjangan Suami/Istri 10%):</label>
            <select id="statusPasangan" class="form-select" style="width: 100%;" onchange="calculateTapera()">
                <option value="1" selected>Kawin / Menikah (10% dari Gaji Pokok)</option>
                <option value="0">Tidak Kawin / Belum Menikah (0%)</option>
            </select>
        </div>

        <!-- Tunjangan Anak -->
        <div class="form-group-calc">
            <label>3. Tanggungan Anak (Maksimal 2 Anak, 2% per Anak):</label>
            <select id="jumlahAnak" class="form-select" style="width: 100%;" onchange="calculateTapera()">
                <option value="0">0 Anak (0%)</option>
                <option value="1" selected>1 Anak (2% dari Gaji Pokok)</option>
                <option value="2">2 Anak (4% dari Gaji Pokok)</option>
            </select>
        </div>

        <!-- Tunjangan Jabatan / Fungsional / Umum -->
        <div class="form-group-calc">
            <label>4. Tunjangan Jabatan / Fungsional / Umum (Rp):</label>
            <div class="input-rupiah-wrapper">
                <span class="input-rupiah-prefix">Rp</span>
                <input type="number" id="inputTunjJabatan" class="input-rupiah" value="540000" min="0" step="1000" oninput="calculateTapera()">
            </div>
            <span style="font-size: 11.5px; color: #64748b;">Contoh: Jabatan Struktural, Fungsional Guru/Nakes, atau Tunjangan Umum.</span>
        </div>
    </div>

    <!-- Hasil Simulasi Interaktif -->
    <div class="calc-card" style="border-top: 4px solid #1e3a5f;">
        <h3><i class="ph ph-receipt"></i> Rincian Hasil Perhitungan Simulasi</h3>
        <p class="calc-desc">Kompilasi pemotongan penghasilan ASN dan kewajiban kontribusi belanja APBD.</p>

        <!-- Proporsi Progress Bar -->
        <div style="margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
                <span style="color: #b45309;"><i class="ph ph-user"></i> Beban ASN: 2,5% (83.3%)</span>
                <span style="color: #1d4ed8;"><i class="ph ph-buildings"></i> Beban Pemda: 0,5% (16.7%)</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-asn" title="Beban Pegawai ASN: 2.5%"></div>
                <div class="progress-bar-pemda" title="Beban Pemda: 0.5%"></div>
            </div>
        </div>

        <div class="calc-result-box">
            <!-- Komponen Rinci -->
            <div class="result-row">
                <span class="result-label">Gaji Pokok:</span>
                <span class="result-value" id="resGapok">Rp 2.785.700</span>
            </div>
            <div class="result-row">
                <span class="result-label">Tunjangan Pasangan (10%):</span>
                <span class="result-value" id="resTjPasangan">Rp 278.570</span>
            </div>
            <div class="result-row">
                <span class="result-label">Tunjangan Anak:</span>
                <span class="result-value" id="resTjAnak">Rp 55.714</span>
            </div>
            <div class="result-row">
                <span class="result-label">Tunjangan Jabatan / Fungsional:</span>
                <span class="result-value" id="resTjJabatan">Rp 540.000</span>
            </div>

            <div class="result-row main-total" style="background: rgba(99, 102, 241, 0.05); padding: 12px 10px; border-radius: 8px;">
                <span class="result-label" style="font-weight: 800; color: #312e81;">
                    <i class="ph ph-equals"></i> TOTAL DASAR TAPERA (100%):
                </span>
                <span class="result-value" id="resDasarTapera" style="font-size: 17px; color: #312e81;">Rp 3.659.984</span>
            </div>

            <!-- Potongan ASN 2.5% -->
            <div class="result-row" style="margin-top: 14px; background: rgba(245, 158, 11, 0.06); padding: 12px 10px; border-radius: 8px;">
                <div>
                    <span class="result-label" style="font-weight: 700; color: #b45309;">
                        Potongan Gaji ASN (2,5%)
                        <span class="result-tag tag-asn">Dipotong dari Take Home Pay</span>
                    </span>
                    <div style="font-size: 11.5px; color: #92400e; margin-top: 2px;">
                        Setahun (12 bln): <strong id="resAsnTahun">Rp 1.098.000</strong>
                    </div>
                </div>
                <span class="result-value" id="resAsnBulan" style="font-size: 17px; color: #b45309;">Rp 91.500 / bln</span>
            </div>

            <!-- Beban Pemda 0.5% -->
            <div class="result-row" style="margin-top: 10px; background: rgba(59, 130, 246, 0.06); padding: 12px 10px; border-radius: 8px;">
                <div>
                    <span class="result-label" style="font-weight: 700; color: #1d4ed8;">
                        Beban Pemda / Pemberi Kerja (0,5%)
                        <span class="result-tag tag-pemda">Beban Anggaran APBD</span>
                    </span>
                    <div style="font-size: 11.5px; color: #1e40af; margin-top: 2px;">
                        Setahun (12 bln): <strong id="resPemdaTahun">Rp 219.600</strong>
                    </div>
                </div>
                <span class="result-value" id="resPemdaBulan" style="font-size: 17px; color: #1d4ed8;">Rp 18.300 / bln</span>
            </div>

            <!-- Total Setoran 3.0% -->
            <div class="result-row" style="margin-top: 10px; background: rgba(16, 185, 129, 0.08); padding: 12px 10px; border-radius: 8px;">
                <div>
                    <span class="result-label" style="font-weight: 800; color: #065f46;">
                        Total Disetor ke BP Tapera (3,0%)
                        <span class="result-tag tag-total">Akumulasi Tabungan</span>
                    </span>
                    <div style="font-size: 11.5px; color: #047857; margin-top: 2px;">
                        Setahun (12 bln): <strong id="resTotalTahun">Rp 1.317.600</strong>
                    </div>
                </div>
                <span class="result-value" id="resTotalBulan" style="font-size: 18px; color: #047857;">Rp 109.800 / bln</span>
            </div>
        </div>

        <div style="margin-top: 18px; padding: 12px; border-radius: 8px; background: #fffbeb; border: 1px solid #fef3c7; font-size: 12.5px; color: #92400e; line-height: 1.5;">
            <strong><i class="ph ph-info"></i> Ketentuan Pemotongan:</strong>
            Simpanan Tapera bagi ASN dialokasikan pada belanja pegawai (porsi 0,5% pemberi kerja) dan daftar potongan gaji resmi (porsi 2,5% pegawai) yang ditransfer langsung ke rekening Pengelola Tapera (Kustodian/BP Tapera).
        </div>
    </div>
</div>

<script>
    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function applyPreset() {
        const val = document.getElementById('presetGolongan').value;
        if (!val) return;
        const parts = val.split('|');
        document.getElementById('inputGapok').value = parts[0];
        document.getElementById('inputTunjJabatan').value = parts[2] || 0;
        calculateTapera();
    }

    function calculateTapera() {
        const gapok = parseFloat(document.getElementById('inputGapok').value) || 0;
        const statusPasangan = parseInt(document.getElementById('statusPasangan').value) || 0;
        const jumlahAnak = parseInt(document.getElementById('jumlahAnak').value) || 0;
        const tjJabatan = parseFloat(document.getElementById('inputTunjJabatan').value) || 0;

        // Tunjangan Suami/Istri: 10% dari gapok
        const tjPasangan = statusPasangan === 1 ? Math.round(gapok * 0.10) : 0;
        // Tunjangan Anak: 2% per anak dari gapok
        const tjAnak = Math.round(gapok * (jumlahAnak * 0.02));
        
        // Dasar Perhitungan Tapera = Gapok + Tunjangan Keluarga + Tunjangan Jabatan
        const dasarTapera = gapok + tjPasangan + tjAnak + tjJabatan;

        // Potongan ASN: 2,5%
        const asnBulan = Math.round(dasarTapera * 0.025);
        const asnTahun = asnBulan * 12;

        // Beban Pemda: 0,5%
        const pemdaBulan = Math.round(dasarTapera * 0.005);
        const pemdaTahun = pemdaBulan * 12;

        // Total 3,0%
        const totalBulan = asnBulan + pemdaBulan;
        const totalTahun = totalBulan * 12;

        // Render to UI
        document.getElementById('resGapok').innerText = formatRupiah(gapok);
        document.getElementById('resTjPasangan').innerText = formatRupiah(tjPasangan);
        document.getElementById('resTjAnak').innerText = formatRupiah(tjAnak);
        document.getElementById('resTjJabatan').innerText = formatRupiah(tjJabatan);
        document.getElementById('resDasarTapera').innerText = formatRupiah(dasarTapera);

        document.getElementById('resAsnBulan').innerText = formatRupiah(asnBulan) + ' / bln';
        document.getElementById('resAsnTahun').innerText = formatRupiah(asnTahun);

        document.getElementById('resPemdaBulan').innerText = formatRupiah(pemdaBulan) + ' / bln';
        document.getElementById('resPemdaTahun').innerText = formatRupiah(pemdaTahun);

        document.getElementById('resTotalBulan').innerText = formatRupiah(totalBulan) + ' / bln';
        document.getElementById('resTotalTahun').innerText = formatRupiah(totalTahun);
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateTapera();
    });
</script>

@elseif($tab === 'rekap')
<!-- TAB 2: REKAPITULASI PER SKPD (DATA RIIL) -->
<div class="table-container">
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th>Nama SKPD / Satuan Kerja</th>
                <th style="text-align: center;">Jml ASN</th>
                <th style="text-align: right;">Gaji Pokok</th>
                <th style="text-align: right;">Tunj. Keluarga</th>
                <th style="text-align: right;">Tunj. Jabatan</th>
                <th style="text-align: right; background: #f1f5f9; color: #0f172a;">Dasar Tapera (100%)</th>
                <th style="text-align: right; background: #eff6ff; color: #1e40af;">Beban Pemda (0,5%)</th>
                <th style="text-align: right; background: #fffbeb; color: #b45309;">Potongan ASN (2,5%)</th>
                <th style="text-align: right; background: #ecfdf5; color: #065f46;">Total Iuran (3,0%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekaps as $index => $row)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-weight: 600; color: #0f172a;">{{ $row->skpd ?: 'Satker Lainnya' }}</td>
                <td style="text-align: center; font-weight: 600;">{{ number_format($row->count_gaji) }}</td>
                <td class="col-number">{{ number_format($row->total_gapok, 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($row->total_tj_keluarga, 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($row->total_tj_jabatan, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; background: #f8fafc;">{{ number_format($row->dasar_tapera, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; color: #1d4ed8; background: #f8faff;">{{ number_format($row->tapera_pemda, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; color: #b45309; background: #fffdf5;">{{ number_format($row->tapera_asn, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 800; color: #047857; background: #f6fdfa;">{{ number_format($row->tapera_total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 30px; color: #64748b;">
                    <i class="ph ph-folder-open" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                    Tidak ada data penggajian pada periode atau filter SKPD yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($rekaps->count() > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: center;">GRAND TOTAL:</td>
                <td style="text-align: center;">{{ number_format($rekaps->sum('count_gaji')) }}</td>
                <td class="col-number">{{ number_format($rekaps->sum('total_gapok'), 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($rekaps->sum('total_tj_keluarga'), 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($rekaps->sum('total_tj_jabatan'), 0, ',', '.') }}</td>
                <td class="col-number" style="background: #e2e8f0;">{{ number_format($rekaps->sum('dasar_tapera'), 0, ',', '.') }}</td>
                <td class="col-number" style="color: #1e40af; background: #dbeafe;">{{ number_format($rekaps->sum('tapera_pemda'), 0, ',', '.') }}</td>
                <td class="col-number" style="color: #b45309; background: #fef3c7;">{{ number_format($rekaps->sum('tapera_asn'), 0, ',', '.') }}</td>
                <td class="col-number" style="color: #065f46; background: #d1fae5;">{{ number_format($rekaps->sum('tapera_total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@elseif($tab === 'rinci')
<!-- TAB 3: DAFTAR NOMINATIF PEGAWAI (RINCI) -->
<div class="table-container">
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th>NIP & Nama Pegawai</th>
                <th>SKPD & Jabatan</th>
                <th style="text-align: right;">Gaji Pokok</th>
                <th style="text-align: right;">Tunj. Keluarga</th>
                <th style="text-align: right;">Tunj. Jabatan</th>
                <th style="text-align: right; background: #f1f5f9; color: #0f172a;">Dasar Tapera</th>
                <th style="text-align: right; background: #eff6ff; color: #1e40af;">Beban Pemda (0,5%)</th>
                <th style="text-align: right; background: #fffbeb; color: #b45309;">Pot. ASN (2,5%)</th>
                <th style="text-align: right; background: #ecfdf5; color: #065f46;">Total (3,0%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($realisasis as $index => $item)
            @php
                $nip = $item->pegawai->nip ?? ($item->raw_data['nip'] ?? $item->raw_data['NIP'] ?? '-');
                $nama = $item->pegawai->nama ?? ($item->raw_data['nama'] ?? $item->raw_data['Nama'] ?? '-');
                $skpd = $item->pegawai->unitKerja->skpd ?? ($item->raw_data['SKPD'] ?? '-');
                $jabatan = $item->pegawai->jabatan->nama ?? ($item->raw_data['Jabatan'] ?? '-');
            @endphp
            <tr>
                <td style="text-align: center;">{{ $realisasis->firstItem() + $index }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $nama }}</div>
                    <div style="font-size: 11.5px; color: #64748b; font-family: monospace;">NIP: {{ $nip }}</div>
                </td>
                <td>
                    <div style="font-weight: 600; color: #334155;">{{ $skpd }}</div>
                    <div style="font-size: 11.5px; color: #64748b;">{{ $jabatan }}</div>
                </td>
                <td class="col-number">{{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($item->tunj_keluarga, 0, ',', '.') }}</td>
                <td class="col-number">{{ number_format($item->tunj_jabatan, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; background: #f8fafc;">{{ number_format($item->dasar_tapera, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; color: #1d4ed8; background: #f8faff;">{{ number_format($item->simulasi_tapera_pk, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 700; color: #b45309; background: #fffdf5;">{{ number_format($item->simulasi_tapera_asn, 0, ',', '.') }}</td>
                <td class="col-number" style="font-weight: 800; color: #047857; background: #f6fdfa;">{{ number_format($item->simulasi_tapera_total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 30px; color: #64748b;">
                    <i class="ph ph-folder-open" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                    Tidak ada data pegawai yang sesuai dengan pencarian atau filter.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 16px;">
    {{ $realisasis->links() }}
</div>
@endif

@endsection
