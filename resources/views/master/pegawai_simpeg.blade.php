@extends('layouts.app')

@section('title', 'Upload Master Pegawai SIMPEG')
@section('page_title', 'Master Pegawai SIMPEG')

@section('content')
<div class="app-header">
    <div>
        <h2>Data Master Pegawai SIMPEG (.xlsx / .csv)</h2>
        <p>Pusat unggah dan sinkronisasi berkas master kepegawaian BKD (SIMPEG) — mencakup NIP, Nama, SKPD, UPTD/Satker, Jabatan, dan Golongan Ruang.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('master.pegawai_simpeg.template') }}" class="btn" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph-bold ph-download-simple"></i> Download Template Excel
        </a>
        <a href="/pegawai" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph-bold ph-users"></i> Buka Daftar Pegawai
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <i class="ph-bold ph-check-circle" style="font-size: 20px; color: #059669;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <i class="ph-bold ph-warning-circle" style="font-size: 20px; color: #dc2626;"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

<!-- Statistik Singkat Master Pegawai -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Card 1: Total Pegawai -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #2563eb;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Pegawai</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="ph-bold ph-users"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-main); line-height: 1.2;">
            {{ number_format($totalPegawai, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px; display: flex; gap: 8px; flex-wrap: wrap;">
            <span><strong style="color: #2563eb;">PNS:</strong> {{ number_format($totalPns, 0, ',', '.') }}</span>
            <span>&bull;</span>
            <span><strong style="color: #059669;">PPPK:</strong> {{ number_format($totalPppk, 0, ',', '.') }}</span>
            @if($totalParuhWaktu > 0)
                <span>&bull;</span>
                <span><strong style="color: #d97706;">PW:</strong> {{ number_format($totalParuhWaktu, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>

    <!-- Card 2: Total SKPD & UPTD -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #059669;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Unit Kerja / SKPD</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="ph-bold ph-buildings"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-main); line-height: 1.2;">
            {{ number_format($totalSkpd, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">
            SKPD Induk Terpetakan di Database
        </div>
    </div>

    <!-- Card 3: Total Jabatan -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Master Jabatan</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="ph-bold ph-identification-badge"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-main); line-height: 1.2;">
            {{ number_format($totalJabatan, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">
            Nomenklatur Jabatan Pegawai
        </div>
    </div>

    <!-- Card 4: Berkas Terakhir -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #f59e0b;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Berkas Terakhir</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="ph-bold ph-clock-counter-clockwise"></i>
            </div>
        </div>
        <div style="font-size: 14px; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $activeFile['original_name'] ?? 'Belum ada berkas' }}">
            {{ $activeFile['original_name'] ?? 'Belum ada' }}
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">
            @if(!empty($activeFile['uploaded_at']))
                {{ date('d M Y H:i', strtotime($activeFile['uploaded_at'])) }} ({{ number_format($activeFile['total_rows'] ?? 0, 0, ',', '.') }} baris)
            @else
                Siap menerima berkas baru
            @endif
        </div>
    </div>
</div>

<!-- Main Section: Form Upload & Panduan Kolom -->
<div style="display: grid; grid-template-columns: 420px 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- Form Upload Card -->
    <div class="card" style="padding: 24px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="ph-bold ph-cloud-arrow-up"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Unggah File Excel SIMPEG</h3>
                <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Mendukung format: .xlsx, .xls, .csv</p>
            </div>
        </div>

        <!-- Notice Box Alur Sinkronisasi Cerdas (Dual-Sync) -->
        <div style="margin-bottom: 18px; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 13px 15px;">
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <i class="ph-bold ph-info" style="font-size: 20px; color: #2563eb; margin-top: 1px; flex-shrink: 0;"></i>
                <div style="font-size: 12px; color: var(--text-main); line-height: 1.5;">
                    <strong style="color: #1d4ed8; font-size: 12.5px;">Informasi Alur Sinkronisasi Master Pegawai:</strong><br>
                    Data SIMPEG baru akan dipadukan secara otomatis ke database:
                    <ul style="margin: 5px 0 0 0; padding-left: 16px; color: var(--text-muted); font-size: 11.5px;">
                        <li><strong>Pegawai Baru:</strong> Otomatis ditambahkan ke database beserta relasi SKPD, UPTD, dan Jabatannya.</li>
                        <li><strong>Pegawai Lama:</strong> Data SKPD, unit kerja/UPTD, jabatan, dan pangkat langsung diperbarui mengikuti mutasi.</li>
                        <li><strong>Proteksi Data SIMGAJI:</strong> NIK, No. Rekening, Bank Penyalur, dan Tanggungan Keluarga <strong>tidak akan terhapus / tertimpa</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>

        <form id="uploadSimpegForm" action="{{ route('master.pegawai_simpeg.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- File Input -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Pilih Berkas Excel SIMPEG *</label>
                <div style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 16px; text-align: center; background: var(--bg-surface-secondary, #f8fafc); transition: all 0.2s;" id="dropZone">
                    <i class="ph-bold ph-file-xls" style="font-size: 32px; color: #059669; display: block; margin-bottom: 8px;"></i>
                    <input type="file" name="file" id="fileSimpeg" accept=".xlsx,.xls,.csv" required style="display: none;">
                    <button type="button" class="btn" style="background: white; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 600; padding: 6px 14px;" onclick="document.getElementById('fileSimpeg').click()">
                        Pilih Berkas Dari Komputer
                    </button>
                    <div id="fileNameDisplay" style="font-size: 12px; color: #475569; margin-top: 8px; font-weight: 500;">
                        Belum ada berkas dipilih
                    </div>
                </div>
            </div>

            <!-- Mode Impor -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Metode Penanganan Data</label>
                <select name="mode" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                    <option value="upsert" selected>🔄 Perbarui Data Lama & Tambah Pegawai Baru (Rekomendasi)</option>
                    <option value="insert_only">➕ Hanya Tambah Pegawai Baru (Abaikan yang sudah ada)</option>
                </select>
                <small style="color: var(--text-muted); font-size: 11.5px; display: block; margin-top: 5px; line-height: 1.4;">
                    <strong>Mode Rekomendasi:</strong> Memperbarui mutasi SKPD, jabatan, dan kenaikan pangkat untuk pegawai lama tanpa menghapus data rekening/NIK SIMGAJI yang sudah tersimpan.
                </small>
            </div>

            <!-- Catatan / Keterangan -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Catatan / Keterangan Berkas (Opsional)</label>
                <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Pembaruan SIMPEG TMT 1 Oktober 2026" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
            </div>

            <button type="submit" id="btnSubmitUpload" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px; font-weight: 700; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-color: #1d4ed8; font-size: 13.5px;">
                <i class="ph-bold ph-upload-simple"></i> Mulai Unggah & Impor Data
            </button>
        </form>
    </div>

    <!-- Panduan Format Kolom -->
    <div class="card" style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ph-bold ph-table" style="font-size: 20px; color: #2563eb;"></i>
                <h3 style="margin: 0; font-size: 15.5px; font-weight: 700;">Format Kolom Spreadsheet SIMPEG</h3>
            </div>
            <a href="{{ route('master.pegawai_simpeg.template') }}" class="btn" style="font-size: 11.5px; padding: 5px 10px; background: rgba(37, 99, 235, 0.08); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2);">
                <i class="ph-bold ph-file-arrow-down"></i> Unduh File Contoh (.xlsx)
            </a>
        </div>

        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 14px; line-height: 1.5;">
            Header kolom pada baris pertama Excel Anda harus sesuai dengan nama-nama berikut (huruf besar/kecil tidak sensitif):
        </p>

        <div style="max-height: 380px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px;">
            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 8px 12px; text-align: left; width: 120px;">Nama Kolom</th>
                        <th style="padding: 8px 12px; text-align: center; width: 80px;">Wajib?</th>
                        <th style="padding: 8px 12px; text-align: left;">Keterangan & Contoh Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; color: #2563eb; font-family: monospace;">NIP</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #fee2e2; color: #dc2626; font-size: 10px; padding: 2px 6px;">Wajib</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">18 digit NIP pegawai (Contoh: <code>198501152010011005</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; color: #2563eb; font-family: monospace;">NAMA</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #fee2e2; color: #dc2626; font-size: 10px; padding: 2px 6px;">Wajib</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Nama lengkap beserta gelar (Contoh: <code>Drs. AHMAD FAUZI, M.Si</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">STATUS</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Status kepegawaian: <code>PNS</code>, <code>PPPK</code>, atau <code>PPPK PARUH WAKTU</code></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">GOLRU</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Golongan ruang (Contoh: <code>IV/a</code>, <code>III/d</code>, <code>IX</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">SKPD</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Nama SKPD induk pemda (Contoh: <code>DINAS PENDIDIKAN</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">UPT</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Unit pelaksana teknis / sekolah (Contoh: <code>SMAN 1 BANJARMASIN</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">SATKER</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Satuan kerja detail penempatan pegawai</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">JABATAN</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Nomenklatur jabatan (Contoh: <code>Kepala Bidang Anggaran</code>)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">TGL_LAHIR</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);">Format: <code>DD-MM-YYYY</code> atau <code>YYYY-MM-DD</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 7px 12px; font-weight: 700; font-family: monospace;">KOLOM LAIN</td>
                        <td style="padding: 7px 12px; text-align: center;"><span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px;">Opsional</span></td>
                        <td style="padding: 7px 12px; color: var(--text-main);"><code>ESELON</code>, <code>JENIS_JABATAN</code>, <code>TEMPAT_LAHIR</code>, <code>JK</code>, <code>AGAMA</code>, <code>TMT_GOLRU</code>, <code>MK_THN</code>, <code>MK_BLN</code>, <code>TK_IJAZAH</code>, <code>NM_PENDIDIKAN</code>, <code>TH_LULUS</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Riwayat Berkas SIMPEG yang Pernah Diunggah -->
<div class="card" style="padding: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-folder" style="font-size: 20px; color: #d97706;"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Riwayat Berkas Excel SIMPEG di Server</h3>
        </div>
        <span style="font-size: 12px; color: var(--text-muted);">Total Berkas: {{ count($files) }}</span>
    </div>

    @if(empty($files))
        <div style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
            <i class="ph-bold ph-tray" style="font-size: 44px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
            <p style="margin: 0; font-size: 14px;">Belum ada berkas SIMPEG yang diunggah. Silakan gunakan formulir di atas untuk mengunggah berkas pertama Anda.</p>
        </div>
    @else
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>Nama Berkas & Keterangan</th>
                        <th>Tanggal Unggah</th>
                        <th>Ukuran</th>
                        <th>Total Data</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $idx => $f)
                    <tr style="{{ !empty($f['is_active']) ? 'background: rgba(37, 99, 235, 0.03);' : '' }}">
                        <td style="text-align: center; font-weight: 600; color: #64748b;">{{ $idx + 1 }}</td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                                <i class="ph-bold ph-file-xls" style="color: #059669; font-size: 16px;"></i>
                                {{ $f['original_name'] ?? $f['filename'] }}
                            </div>
                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                {{ $f['keterangan'] ?? 'Tanpa catatan' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12.5px; color: var(--text-main);">
                                {{ !empty($f['uploaded_at']) ? date('d-m-Y H:i', strtotime($f['uploaded_at'])) : '-' }}
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: #64748b;">
                                {{ !empty($f['file_size']) ? round($f['file_size'] / (1024 * 1024), 2) . ' MB' : '-' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #2563eb; font-size: 13px;">
                                {{ number_format($f['total_rows'] ?? 0, 0, ',', '.') }}
                            </span>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                Baru: {{ number_format($f['inserted_count'] ?? 0, 0, ',', '.') }} | Update: {{ number_format($f['updated_count'] ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            @if(!empty($f['is_active']))
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; font-size: 11px;">
                                    <i class="ph-bold ph-check"></i> AKTIF
                                </span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px;">
                                    Arsip
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <button type="button" class="btn" style="padding: 5px 10px; font-size: 11.5px; background: rgba(37, 99, 235, 0.08); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2);" onclick="reSyncFile('{{ $f['id'] }}', '{{ addslashes($f['original_name'] ?? $f['filename']) }}')" title="Impor ulang dari file ini">
                                    <i class="ph-bold ph-arrows-clockwise"></i> Impor Ulang
                                </button>
                                @if($f['id'] !== 'default_initial_simpeg')
                                    <form action="{{ route('master.pegawai_simpeg.destroy', $f['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip berkas ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="padding: 5px 8px; font-size: 11.5px; background: rgba(239, 68, 68, 0.08); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2);" title="Hapus arsip">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- SweetAlert2 Script & Progress Handling -->
<script>
    // File input change visual update
    document.getElementById('fileSimpeg').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const display = document.getElementById('fileNameDisplay');
        if (file) {
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            display.innerHTML = '<strong style="color: #059669;"><i class="ph-bold ph-file-check"></i> ' + file.name + '</strong> (' + sizeMb + ' MB)';
        } else {
            display.innerText = 'Belum ada berkas dipilih';
        }
    });

    // Upload Form Submit dengan SweetAlert2 & Real-time Progress Bar
    document.getElementById('uploadSimpegForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const fileInput = document.getElementById('fileSimpeg');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire('Peringatan', 'Silakan pilih berkas Excel terlebih dahulu.', 'warning');
            return;
        }

        const formData = new FormData(form);
        const uploadId = Date.now().toString() + Math.random().toString(36).substring(2, 7);
        formData.append('upload_id', uploadId);

        const btn = document.getElementById('btnSubmitUpload');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Mengunggah Berkas...';

        Swal.fire({
            title: 'Mengimpor Data Pegawai SIMPEG',
            html: `
                <div style="margin-top: 15px; margin-bottom: 10px; text-align: left; font-size: 13px; color: #64748b;" id="progress-text">Menyiapkan dan membaca berkas spreadsheet...</div>
                <div style="width: 100%; background-color: #e2e8f0; border-radius: 999px; height: 14px; overflow: hidden;">
                    <div id="progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width 0.3s ease;"></div>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Polling Progress
        const pollInterval = setInterval(() => {
            fetch('/upload/progress?id=' + uploadId)
                .then(res => res.json())
                .then(data => {
                    if (data && data.progress > 0) {
                        let percent = data.total > 0 ? Math.round((data.progress / data.total) * 100) : 0;
                        if (percent > 100) percent = 100;

                        const text = data.total > 0 
                            ? `Memproses data ke-${data.progress.toLocaleString()} dari ${data.total.toLocaleString()} (${percent}%)`
                            : `Memproses data ke-${data.progress.toLocaleString()}...`;

                        const progText = document.getElementById('progress-text');
                        const progBar = document.getElementById('progress-bar');
                        if (progText) progText.innerText = text;
                        if (progBar && data.total > 0) progBar.style.width = percent + '%';
                    }
                }).catch(err => console.error(err));
        }, 1000);

        // Kirim request AJAX
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
                const res = data.result || {};
                Swal.fire({
                    icon: 'success',
                    title: 'Impor Pegawai Berhasil!',
                    html: `
                        <p style="font-size: 13.5px; color: #475569; margin: 6px 0 14px 0;">Data kepegawaian SIMPEG berhasil diproses ke database.</p>
                        <div style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 8px; font-size: 13px;">Ringkasan Data:</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px;">
                                <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <div style="color: #64748b; font-size: 11px;">Total Baris File</div>
                                    <div style="font-weight: 800; font-size: 16px; color: #2563eb;">${(res.total_rows || 0).toLocaleString()}</div>
                                </div>
                                <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <div style="color: #64748b; font-size: 11px;">Pegawai Baru</div>
                                    <div style="font-weight: 800; font-size: 16px; color: #059669;">+${(res.inserted_count || 0).toLocaleString()}</div>
                                </div>
                                <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <div style="color: #64748b; font-size: 11px;">Pegawai Diperbarui</div>
                                    <div style="font-weight: 800; font-size: 16px; color: #d97706;">${(res.updated_count || 0).toLocaleString()}</div>
                                </div>
                                <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <div style="color: #64748b; font-size: 11px;">Baris Dilewati</div>
                                    <div style="font-weight: 800; font-size: 16px; color: #64748b;">${(res.skipped_count || 0).toLocaleString()}</div>
                                </div>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Selesai & Muat Ulang'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph-bold ph-upload-simple"></i> Mulai Unggah & Impor Data';
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat mengimpor berkas.', 'error');
            }
        })
        .catch(err => {
            clearInterval(pollInterval);
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-bold ph-upload-simple"></i> Mulai Unggah & Impor Data';
            Swal.fire('Error!', 'Gagal menghubungi server atau berkas melebihi batas upload.', 'error');
        });
    });

    // Re-sync file function
    function reSyncFile(fileId, fileName) {
        Swal.fire({
            title: 'Impor Ulang Berkas?',
            text: 'Aplikasi akan menyinkronkan kembali master pegawai dari berkas "' + fileName + '".',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Jalankan Impor Ulang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const uploadId = Date.now().toString() + Math.random().toString(36).substring(2, 7);

                Swal.fire({
                    title: 'Menyinkronkan Ulang Data',
                    html: `
                        <div style="margin-top: 15px; margin-bottom: 10px; text-align: left; font-size: 13px; color: #64748b;" id="progress-text">Memproses ulang data pegawai...</div>
                        <div style="width: 100%; background-color: #e2e8f0; border-radius: 999px; height: 14px; overflow: hidden;">
                            <div id="progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width 0.3s ease;"></div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const pollInterval = setInterval(() => {
                    fetch('/upload/progress?id=' + uploadId)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.progress > 0) {
                                let percent = data.total > 0 ? Math.round((data.progress / data.total) * 100) : 0;
                                if (percent > 100) percent = 100;

                                const text = data.total > 0 
                                    ? `Memproses data ke-${data.progress.toLocaleString()} dari ${data.total.toLocaleString()} (${percent}%)`
                                    : `Memproses data ke-${data.progress.toLocaleString()}...`;

                                const progText = document.getElementById('progress-text');
                                const progBar = document.getElementById('progress-bar');
                                if (progText) progText.innerText = text;
                                if (progBar && data.total > 0) progBar.style.width = percent + '%';
                            }
                        }).catch(err => console.error(err));
                }, 1000);

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('upload_id', uploadId);

                fetch('/master/pegawai-simpeg/' + fileId + '/sync', {
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
                        const res = data.result || {};
                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Selesai!',
                            html: `
                                <p style="font-size: 13.5px; color: #475569; margin: 6px 0 14px 0;">Sinkronisasi ulang berkas SIMPEG berhasil dituntaskan.</p>
                                <div style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 8px; font-size: 13px;">Ringkasan Pembaruan:</div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px;">
                                        <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="color: #64748b; font-size: 11px;">Total Baris File</div>
                                            <div style="font-weight: 800; font-size: 16px; color: #2563eb;">${(res.total_rows || 0).toLocaleString()}</div>
                                        </div>
                                        <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="color: #64748b; font-size: 11px;">Pegawai Baru</div>
                                            <div style="font-weight: 800; font-size: 16px; color: #059669;">+${(res.inserted_count || 0).toLocaleString()}</div>
                                        </div>
                                        <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="color: #64748b; font-size: 11px;">Pegawai Diperbarui</div>
                                            <div style="font-weight: 800; font-size: 16px; color: #d97706;">${(res.updated_count || 0).toLocaleString()}</div>
                                        </div>
                                        <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="color: #64748b; font-size: 11px;">Baris Dilewati</div>
                                            <div style="font-weight: 800; font-size: 16px; color: #64748b;">${(res.skipped_count || 0).toLocaleString()}</div>
                                        </div>
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'Selesai & Muat Ulang'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(err => {
                    clearInterval(pollInterval);
                    Swal.fire('Error!', 'Terjadi kesalahan saat sinkronisasi.', 'error');
                });
            }
        });
    }
</script>
@endsection
