@extends('layouts.app')

@section('title', 'Database SIMGAJI (.DBF)')
@section('page_title', 'Database SIMGAJI')

@section('content')
<div class="app-header">
    <div>
        <h2>Database SIMGAJI (.DBF)</h2>
        <p>Manajemen dan unggah file database SIMGAJI — <strong>Master Pegawai (MST_PGW)</strong> dan <strong>Histori Gaji Pokok & SK (HIS_GPOK)</strong> untuk rekonsiliasi kepegawaian yang akurat.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('laporan.rekonsiliasi_simgaji.index') }}" class="btn btn-primary" title="Buka hasil analisis perbandingan data">
            <i class="ph-bold ph-chart-line-up"></i> Buka Laporan Rekonsiliasi
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error') || !empty($error))
    <div class="alert alert-error">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <div>{{ session('error') ?? $error }}</div>
    </div>
@endif

<!-- Top Section: Upload Form & Two Active Cards -->
<div style="display: grid; grid-template-columns: 380px 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- Upload Card -->
    <div class="card" style="padding: 22px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="ph-bold ph-cloud-arrow-up"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Unggah File Database DBF</h3>
                <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Format: .dbf / .DBF (Ekspor SIMGAJI)</p>
            </div>
        </div>

        <form action="{{ route('master.simgaji_dbf.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Pilih Berkas DBF *</label>
                <input type="file" name="file_dbf" accept=".dbf,.DBF" required class="form-control" style="padding: 10px; border: 2px dashed var(--border-color); border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Kategori Database</label>
                <select name="jenis_dbf" class="form-control" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                    <option value="auto" selected>✨ Otomatis Deteksi (Direkomendasikan)</option>
                    <option value="mst_pgw">👤 Master Pegawai (MST_PGW)</option>
                    <option value="his_gpok">📜 Histori Gaji Pokok & SK (HIS_GPOK)</option>
                    <option value="kel">👨‍👩‍👧‍👦 Riwayat Anggota Keluarga & Tanggungan (KEL)</option>
                </select>
                <small style="color: var(--text-muted); font-size: 11.5px; display: block; margin-top: 4px;">
                    Sistem membaca kolom DBF secara otomatis untuk menentukan kategori.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Catatan / Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="form-control" placeholder="Misal: Ekspor SIMGAJI Periode September 2026">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-color: #1d4ed8;">
                <i class="ph-bold ph-upload-simple"></i> Unggah & Simpan ke Server
            </button>
        </form>
    </div>

    <!-- Active Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <!-- Card 1: Active MST_PGW -->
        <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; border-top: 3px solid #2563eb;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                            <i class="ph-bold ph-users"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">1. Master Pegawai</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">Data Pokok Pegawai Aktif</span>
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 999px; font-weight: 700; font-size: 10.5px;">
                        <i class="ph-bold ph-check"></i> AKTIF
                    </span>
                </div>

                @if(!empty($activeMstPgw))
                    <div style="background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                        <div style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; word-break: break-all;">
                            <i class="ph-bold ph-file-code" style="color: #2563eb;"></i> {{ $activeMstPgw['filename'] }}
                        </div>
                        <div style="font-size: 12px; line-height: 1.6;">
                            <div><span style="color: var(--text-muted);">Total Pegawai:</span> <strong style="color: #2563eb;">{{ number_format($activeMstPgw['records'] ?? 0, 0, ',', '.') }} orang</strong></div>
                            <div><span style="color: var(--text-muted);">Ukuran:</span> {{ $activeMstPgw['size'] ?? '-' }}</div>
                            <div><span style="color: var(--text-muted);">Diunggah:</span> {{ $activeMstPgw['uploaded_at'] ?? '-' }}</div>
                            <div style="margin-top: 4px; font-style: italic; color: var(--text-muted); font-size: 11.5px;">{{ $activeMstPgw['keterangan'] ?? '-' }}</div>
                        </div>
                    </div>
                    <form action="{{ route('master.simgaji_dbf.sync') }}" method="POST" style="margin-bottom: 10px;">
                        @csrf
                        <input type="hidden" name="type" value="master">
                        <button type="submit" class="btn btn-export" style="width: 100%; justify-content: center; font-size: 11.5px; color: #2563eb; border-color: rgba(37, 99, 235, 0.3);" title="Sinkronkan NIK, No. Rekening, dan NPWP ke Master Pegawai">
                            <i class="ph-bold ph-arrows-clockwise"></i> Sinkronkan NIK & Rekening ({{ number_format($totalPegawaiWithFinancial ?? 0, 0, ',', '.') }} terisi)
                        </button>
                    </form>
                @else
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); background: var(--bg-surface-secondary, #f8fafc); border-radius: 8px; font-size: 12px;">
                        Belum ada Master Pegawai aktif.
                    </div>
                @endif
            </div>

            <div style="font-size: 11.5px; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 10px;">
                <i class="ph-bold ph-info" style="color: #2563eb;"></i> Sumber NIK, nomor rekening, NPWP, dan acuan snapshot SKPD/pangkat.
            </div>
        </div>

        <!-- Card 2: Active HIS_GPOK -->
        <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; border-top: 3px solid #9333ea;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(147, 51, 234, 0.1); color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                            <i class="ph-bold ph-clock-counter-clockwise"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">2. Histori Gaji Pokok & SK</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">Riwayat SK Pangkat & Gaji Berkala</span>
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 999px; font-weight: 700; font-size: 10.5px;">
                        <i class="ph-bold ph-check"></i> AKTIF
                    </span>
                </div>

                @if(!empty($activeHisGpok))
                    <div style="background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                        <div style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; word-break: break-all;">
                            <i class="ph-bold ph-file-code" style="color: #9333ea;"></i> {{ $activeHisGpok['filename'] }}
                        </div>
                        <div style="font-size: 12px; line-height: 1.6;">
                            <div><span style="color: var(--text-muted);">Total Riwayat:</span> <strong style="color: #9333ea;">{{ number_format($activeHisGpok['records'] ?? 0, 0, ',', '.') }} record</strong></div>
                            <div><span style="color: var(--text-muted);">Ukuran:</span> {{ $activeHisGpok['size'] ?? '-' }}</div>
                            <div><span style="color: var(--text-muted);">Diunggah:</span> {{ $activeHisGpok['uploaded_at'] ?? '-' }}</div>
                            <div style="margin-top: 4px; font-style: italic; color: var(--text-muted); font-size: 11.5px;">{{ $activeHisGpok['keterangan'] ?? '-' }}</div>
                        </div>
                    </div>
                @else
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); background: var(--bg-surface-secondary, #f8fafc); border-radius: 8px; font-size: 12px;">
                        Belum ada Histori Gaji Pokok aktif.
                    </div>
                @endif
            </div>

            <div style="font-size: 11.5px; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 10px;">
                <i class="ph-bold ph-check-circle" style="color: #10b981;"></i> Mendeteksi SK Kenaikan Pangkat yang sudah diinput di SIMGAJI namun baru berlaku bulan mendatang.
            </div>
        </div>

        <!-- Card 3: Active KEL (Keluarga & Tanggungan) -->
        <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; border-top: 3px solid #059669;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                            <i class="ph-bold ph-users-four"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">3. Riwayat Keluarga</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">Anggota Keluarga & Tanggungan</span>
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 3px 8px; border-radius: 999px; font-weight: 700; font-size: 10.5px;">
                        <i class="ph-bold ph-check"></i> AKTIF
                    </span>
                </div>

                @if(!empty($activeKel))
                    <div style="background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                        <div style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; word-break: break-all;">
                            <i class="ph-bold ph-file-code" style="color: #059669;"></i> {{ $activeKel['filename'] }}
                        </div>
                        <div style="font-size: 12px; line-height: 1.6;">
                            <div><span style="color: var(--text-muted);">Total Anggota:</span> <strong style="color: #059669;">{{ number_format($activeKel['records'] ?? 0, 0, ',', '.') }} orang</strong></div>
                            <div><span style="color: var(--text-muted);">Tersimpan DB:</span> <strong style="color: #059669;">{{ number_format($totalKeluargaDb ?? 0, 0, ',', '.') }} record</strong></div>
                            <div><span style="color: var(--text-muted);">Ukuran:</span> {{ $activeKel['size'] ?? '-' }}</div>
                            <div style="margin-top: 4px; font-style: italic; color: var(--text-muted); font-size: 11.5px;">{{ $activeKel['keterangan'] ?? '-' }}</div>
                        </div>
                    </div>
                    <form action="{{ route('master.simgaji_dbf.sync') }}" method="POST" style="margin-bottom: 10px;">
                        @csrf
                        <input type="hidden" name="type" value="keluarga">
                        <button type="submit" class="btn btn-export" style="width: 100%; justify-content: center; font-size: 11.5px; color: #059669; border-color: rgba(16, 185, 129, 0.3);" title="Muat ulang 70rb record anggota keluarga ke database">
                            <i class="ph-bold ph-arrows-clockwise"></i> Sinkronkan Data Keluarga ke Database
                        </button>
                    </form>
                @else
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); background: var(--bg-surface-secondary, #f8fafc); border-radius: 8px; font-size: 12px;">
                        Belum ada file KEL (Keluarga) aktif.
                    </div>
                @endif
            </div>

            <div style="font-size: 11.5px; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 10px;">
                <i class="ph-bold ph-users" style="color: #059669;"></i> Menampilkan daftar suami/istri dan anak pada riwayat Trace Gaji dan profil pegawai.
            </div>
        </div>
    </div>
</div>

<!-- History Table Card -->
<div class="card" style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Riwayat File Database SIMGAJI Tersimpan</h3>
            <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Daftar file DBF yang tersimpan di sistem. Anda dapat beralih file acuan untuk masing-masing kategori secara terpisah.</p>
        </div>
        <div>
            <a href="{{ route('laporan.rekonsiliasi_simgaji.refresh') }}" class="btn btn-export" title="Perbarui pembacaan file aktif dan cache analisis">
                <i class="ph-bold ph-arrows-clockwise"></i> Refresh Cache Analisis
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="width: 170px;">Kategori File</th>
                    <th>Nama File Asli</th>
                    <th>Keterangan</th>
                    <th style="width: 100px; text-align: center;">Ukuran</th>
                    <th style="width: 130px; text-align: center;">Total Record</th>
                    <th style="width: 140px; text-align: center;">Tanggal Upload</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 140px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $index => $file)
                    @php
                        $fileType = $file['type'] ?? 'mst_pgw';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            @if($fileType === 'his_gpok')
                                <span class="badge" style="background: rgba(147, 51, 234, 0.1); color: #9333ea; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                    <i class="ph-bold ph-clock-counter-clockwise"></i> Histori Gaji & SK
                                </span>
                            @elseif($fileType === 'kel')
                                <span class="badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                    <i class="ph-bold ph-users-four"></i> Riwayat Keluarga
                                </span>
                            @else
                                <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
                                    <i class="ph-bold ph-users"></i> Master Pegawai
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: var(--text-main);">{{ $file['filename'] }}</strong>
                            @if(file_exists($file['path']))
                                <small style="display: block; color: var(--text-muted); font-size: 11px;">
                                    <i class="ph-bold ph-check-circle" style="color: #10b981;"></i> Tersedia di server
                                </small>
                            @else
                                <small style="display: block; color: #ef4444; font-size: 11px;">
                                    <i class="ph-bold ph-warning-circle"></i> File fisik tidak ditemukan
                                </small>
                            @endif
                        </td>
                        <td>{{ $file['keterangan'] ?? '-' }}</td>
                        <td style="text-align: center;"><code>{{ $file['size'] }}</code></td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); padding: 4px 8px; border-radius: 6px; font-weight: 700;">
                                {{ number_format($file['records'] ?? 0, 0, ',', '.') }} {{ $fileType === 'his_gpok' ? 'record' : ($fileType === 'kel' ? 'anggota' : 'pegawai') }}
                            </span>
                        </td>
                        <td style="text-align: center;"><small>{{ $file['uploaded_at'] }}</small></td>
                        <td style="text-align: center;">
                            @if(!empty($file['is_active']))
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 11px;">
                                    <i class="ph-bold ph-check"></i> AKTIF
                                </span>
                            @else
                                <span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 10px; border-radius: 999px; font-weight: 600; font-size: 11px;">
                                    ARSIP
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                @if(empty($file['is_active']))
                                    <form action="{{ route('master.simgaji_dbf.activate', $file['id']) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-export" style="padding: 4px 8px; font-size: 11.5px; color: #059669;" title="Jadikan file ini sebagai acuan aktif untuk kategorinya">
                                            <i class="ph-bold ph-power"></i> Aktifkan
                                        </button>
                                    </form>
                                @endif

                                @if(!in_array($file['id'], ['default_mst_pgw', 'default_his_gpok', 'default_kel']))
                                    <form action="{{ route('master.simgaji_dbf.delete', $file['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file ini dari riwayat?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-export" style="padding: 4px 8px; font-size: 11.5px; color: #ef4444;" title="Hapus file ini">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            Belum ada riwayat file database SIMGAJI yang diunggah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
