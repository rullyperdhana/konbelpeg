@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page_title', 'Daftar Pegawai')

@section('content')
<div class="app-header">
    <div>
        <h2>Data Pegawai</h2>
        <p>Pengelolaan master pegawai, status kepegawaian, jabatan, dan unit kerja / UPTD.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn" onclick="openModal('uploadSimpegModal')" style="background: rgba(37, 99, 235, 0.08); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.25); display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class="ph-bold ph-file-arrow-up" style="font-size: 16px;"></i> Upload Excel SIMPEG
        </button>
        <button class="btn btn-primary" onclick="openModal('addModal')">
            <i class="ph ph-plus-circle"></i> Tambah Pegawai
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">Terjadi kesalahan pada input form. Mohon periksa kembali (NIP mungkin duplikat).</div>
@endif

<div class="toolbar">
    <form action="/pegawai" method="GET" class="search-box" style="width: 700px; overflow: visible;">
        <div style="width: 350px; border-right: 1px solid #e2e8f0;">
            <select name="skpd_filter" id="skpd-filter" placeholder="Ketik nama SKPD..." autocomplete="off">
                <option value="">Semua SKPD</option>
                @foreach($filterUnitKerjas as $uk)
                    <option value="{{ $uk->skpd }}" {{ request('skpd_filter') == $uk->skpd ? 'selected' : '' }}>
                        {{ $uk->skpd }}
                    </option>
                @endforeach
            </select>
        </div>
        <input type="text" name="search" placeholder="Cari NIP, Nama, atau NIK..." value="{{ request('search') }}">
        <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>NIP & NIK</th>
                <th>Nama Pegawai & Rekening</th>
                <th>Status / Gol</th>
                <th>Jabatan</th>
                <th>Unit Kerja / UPTD</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawais as $item)
            <tr>
                <td>
                    <div style="font-weight: 700; color: #334155; font-family: monospace; font-size: 13.5px;">{{ $item->nip }}</div>
                    @if($item->nik)
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                            <span style="color: #94a3b8; font-weight: 600;">NIK:</span> <span style="font-family: monospace;">{{ $item->nik }}</span>
                        </div>
                    @endif
                </td>
                <td>
                    <div style="font-weight: 600; font-size: 14px; color: #0f172a;">{{ $item->nama }}</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                        <span>{{ $item->tempat_lahir ?? '-' }}, {{ $item->tgl_lahir ? date('d-m-Y', strtotime($item->tgl_lahir)) : '-' }}</span>
                        @if($item->no_rekening)
                            <span style="margin-left: 6px; color: #0284c7; font-weight: 600;">
                                <i class="ph ph-credit-card"></i> {{ $item->no_rekening }}
                                @if($item->nama_bank)
                                    <small style="color: #64748b; font-weight: normal;">({{ $item->nama_bank }})</small>
                                @endif
                            </span>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="badge {{ str_contains(strtoupper($item->status_pegawai ?? ''), 'PARUH') ? 'badge-paruh' : (strtolower($item->status_pegawai ?? '') == 'pns' ? 'badge-pns' : 'badge-pppk') }}">{{ $item->status_pegawai ?? '-' }}</span>
                    <div style="font-size: 12px; font-weight: 600; color: #334155; margin-top: 4px;">{{ $item->golru ?? '-' }}</div>
                </td>
                <td>{{ $item->jabatan->nama ?? '-' }}</td>
                <td>
                    <div style="font-weight: 500;">{{ $item->unitKerja->skpd ?? '-' }}</div>
                    @if(!empty($item->unitKerja->upt))
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;"><i class="ph ph-buildings"></i> UPTD: {{ $item->unitKerja->upt }}</div>
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        <button type="button" class="btn-edit" style="color: #059669; border-color: rgba(5, 150, 105, 0.3);" onclick="openDetailModal({{ json_encode($item) }})">
                            <i class="ph ph-eye"></i> Detail
                        </button>
                        <button class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ $item->nip }}', '{{ addslashes($item->nama) }}', '{{ $item->nik }}', '{{ $item->no_rekening }}', '{{ $item->nama_bank }}', '{{ $item->npwp }}', '{{ addslashes($item->tempat_lahir) }}', '{{ $item->tgl_lahir }}', '{{ $item->jk }}', '{{ $item->agama }}', '{{ $item->status_pegawai }}', '{{ $item->golru }}', '{{ $item->jabatan_id }}', '{{ $item->unit_kerja_id }}')">Edit</button>
                        <form action="/pegawai/{{ $item->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Pegawai ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">Data pegawai tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div style="font-size: 13px; color: #64748b;">
            Menampilkan {{ $pegawais->firstItem() ?? 0 }} - {{ $pegawais->lastItem() ?? 0 }} dari {{ $pegawais->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$pegawais->onFirstPage())
                <a href="{{ $pegawais->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($pegawais->hasMorePages())
                <a href="{{ $pegawais->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Pegawai</h3>
            <button class="btn-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="/pegawai" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>NIP *</label>
                    <input type="text" name="nip" required placeholder="NIP 18 Digit">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" required>
                </div>
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir">
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jk">
                        <option value="">- Pilih -</option>
                        <option value="LAKI-LAKI">LAKI-LAKI</option>
                        <option value="PEREMPUAN">PEREMPUAN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Agama</label>
                    <input type="text" name="agama">
                </div>
                <div class="form-group">
                    <label>Status Pegawai</label>
                    <select name="status_pegawai">
                        <option value="">- Pilih -</option>
                        <option value="PNS">PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="CPNS">CPNS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Golongan Ruang</label>
                    <input type="text" name="golru" placeholder="Misal: IV/e">
                </div>
                
                <div class="form-group">
                    <label>NIK (No. KTP)</label>
                    <input type="text" name="nik" placeholder="16 digit NIK">
                </div>
                <div class="form-group">
                    <label>No. Rekening</label>
                    <input type="text" name="no_rekening" placeholder="Nomor Rekening">
                </div>
                <div class="form-group">
                    <label>Bank Penyalur</label>
                    <input type="text" name="nama_bank" placeholder="Misal: Bank Kalsel">
                </div>
                <div class="form-group">
                    <label>NPWP</label>
                    <input type="text" name="npwp" placeholder="Nomor NPWP">
                </div>
                
                <div class="form-group full">
                    <label>Unit Kerja (SKPD)</label>
                    <select name="unit_kerja_id">
                        <option value="">- Pilih Unit Kerja -</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id }}">{{ $uk->skpd }} {{ $uk->upt ? ' - '.$uk->upt : '' }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group full">
                    <label>Jabatan</label>
                    <select name="jabatan_id">
                        <option value="">- Pilih Jabatan -</option>
                        @foreach($jabatans as $jab)
                            <option value="{{ $jab->id }}">{{ $jab->nama }} ({{ $jab->jenis }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" class="btn-edit" onclick="closeModal('addModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Pegawai</h3>
            <button class="btn-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label>NIP *</label>
                    <input type="text" name="nip" id="edit_nip" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="edit_tempat_lahir">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" id="edit_tgl_lahir">
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jk" id="edit_jk">
                        <option value="">- Pilih -</option>
                        <option value="LAKI-LAKI">LAKI-LAKI</option>
                        <option value="PEREMPUAN">PEREMPUAN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Agama</label>
                    <input type="text" name="agama" id="edit_agama">
                </div>
                <div class="form-group">
                    <label>Status Pegawai</label>
                    <select name="status_pegawai" id="edit_status_pegawai">
                        <option value="">- Pilih -</option>
                        <option value="PNS">PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="CPNS">CPNS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Golongan Ruang</label>
                    <input type="text" name="golru" id="edit_golru">
                </div>
                
                <div class="form-group">
                    <label>NIK (No. KTP)</label>
                    <input type="text" name="nik" id="edit_nik" placeholder="16 digit NIK">
                </div>
                <div class="form-group">
                    <label>No. Rekening</label>
                    <input type="text" name="no_rekening" id="edit_no_rekening" placeholder="Nomor Rekening">
                </div>
                <div class="form-group">
                    <label>Bank Penyalur</label>
                    <input type="text" name="nama_bank" id="edit_nama_bank" placeholder="Misal: Bank Kalsel">
                </div>
                <div class="form-group">
                    <label>NPWP</label>
                    <input type="text" name="npwp" id="edit_npwp" placeholder="Nomor NPWP">
                </div>
                
                <div class="form-group full">
                    <label>Unit Kerja (SKPD)</label>
                    <select name="unit_kerja_id" id="edit_unit_kerja_id">
                        <option value="">- Pilih Unit Kerja -</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id }}">{{ $uk->skpd }} {{ $uk->upt ? ' - '.$uk->upt : '' }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group full">
                    <label>Jabatan</label>
                    <select name="jabatan_id" id="edit_jabatan_id">
                        <option value="">- Pilih Jabatan -</option>
                        @foreach($jabatans as $jab)
                            <option value="{{ $jab->id }}">{{ $jab->nama }} ({{ $jab->jenis }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" class="btn-edit" onclick="closeModal('editModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn-primary">Update Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Pegawai & SIMGAJI -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-content" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 id="dt_nama" style="margin: 0; font-size: 17px; font-weight: 700; color: var(--text-main);">Detail Pegawai</h3>
                <span id="dt_nip" style="font-family: monospace; font-size: 13px; color: var(--text-muted); font-weight: 600;"></span>
            </div>
            <button class="btn-close" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <!-- Kotak Info BKD -->
                <div style="background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px;">
                    <div style="font-weight: 700; font-size: 13px; color: #2563eb; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-identification-badge"></i> Data Kepegawaian (BKD)
                    </div>
                    <div style="font-size: 12.5px; line-height: 1.8;">
                        <div><span style="color: var(--text-muted);">Status / Gol:</span> <strong id="dt_status_gol">-</strong></div>
                        <div><span style="color: var(--text-muted);">Jabatan:</span> <strong id="dt_jabatan">-</strong></div>
                        <div><span style="color: var(--text-muted);">Unit Kerja:</span> <strong id="dt_skpd">-</strong></div>
                        <div><span style="color: var(--text-muted);">TTL:</span> <span id="dt_ttl">-</span></div>
                        <div><span style="color: var(--text-muted);">Jenis Kelamin / Agama:</span> <span id="dt_jk_agama">-</span></div>
                    </div>
                </div>

                <!-- Kotak Info SIMGAJI Taspen -->
                <div style="background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px;">
                    <div style="font-weight: 700; font-size: 13px; color: #059669; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-bank"></i> Identitas Finansial & SIMGAJI Taspen
                    </div>
                    <div style="font-size: 12.5px; line-height: 1.8;">
                        <div><span style="color: var(--text-muted);">NIK (No. KTP):</span> <strong id="dt_nik" style="font-family: monospace;">-</strong></div>
                        <div><span style="color: var(--text-muted);">No. Rekening:</span> <strong id="dt_norek" style="font-family: monospace; color: #0284c7;">-</strong></div>
                        <div><span style="color: var(--text-muted);">Bank Penyalur:</span> <span id="dt_bank">-</span></div>
                        <div><span style="color: var(--text-muted);">NPWP:</span> <span id="dt_npwp" style="font-family: monospace;">-</span></div>
                        <div><span style="color: var(--text-muted);">No. Karpeg:</span> <span id="dt_karpeg">-</span></div>
                    </div>
                </div>
            </div>

            <!-- Tabel Anggota Keluarga SIMGAJI -->
            <div style="border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 16px;">
                <div style="background: rgba(5, 150, 105, 0.08); padding: 10px 14px; font-weight: 700; font-size: 13px; color: #059669; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="ph ph-users-four"></i> Anggota Keluarga & Tanggungan (SIMGAJI Taspen)</span>
                    <span id="dt_keluarga_count" class="badge" style="background: #059669; color: #fff; font-size: 11px;">0 Anggota</span>
                </div>
                <div style="max-height: 220px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 8px 10px; text-align: left;">Nama Anggota</th>
                                <th style="padding: 8px 10px; text-align: left;">Hubungan</th>
                                <th style="padding: 8px 10px; text-align: left;">JK</th>
                                <th style="padding: 8px 10px; text-align: left;">Tgl Lahir / Usia</th>
                                <th style="padding: 8px 10px; text-align: center;">Tunjangan</th>
                            </tr>
                        </thead>
                        <tbody id="dt_keluarga_tbody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 16px; color: var(--text-muted);">Tidak ada data keluarga.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <a id="dt_trace_link" href="#" class="btn btn-primary" style="font-size: 12.5px; padding: 7px 14px;">
                    <i class="ph ph-chart-line-up"></i> Buka Lembar Trace Gaji Pegawai Ini
                </a>
                <button type="button" class="btn-edit" onclick="closeModal('detailModal')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Data Pegawai SIMPEG -->
<div class="modal-overlay" id="uploadSimpegModal">
    <div class="modal-content" style="max-width: 640px; width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="ph-bold ph-cloud-arrow-up"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Upload Master Pegawai SIMPEG</h3>
                    <p style="margin: 0; font-size: 11.5px; color: var(--text-muted);">Pembaruan berkas spreadsheet (.xlsx, .xls, .csv) BKD</p>
                </div>
            </div>
            <button class="btn-close" onclick="closeModal('uploadSimpegModal')">&times;</button>
        </div>
        <div style="padding: 22px;">
            <!-- Notice Box Informasi Alur Sinkronisasi Cerdas -->
            <div style="margin-bottom: 18px; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 13px 15px;">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <i class="ph-bold ph-info" style="font-size: 20px; color: #2563eb; margin-top: 1px; flex-shrink: 0;"></i>
                    <div style="font-size: 12px; color: var(--text-main); line-height: 1.5;">
                        <strong style="color: #1d4ed8; font-size: 12.5px;">Informasi Alur Sinkronisasi Master Pegawai:</strong><br>
                        Data SIMPEG baru akan dipadukan secara otomatis ke database:
                        <ul style="margin: 5px 0 0 0; padding-left: 16px; color: var(--text-muted); font-size: 11.5px;">
                            <li><strong>Pegawai Baru:</strong> Otomatis didaftarkan ke sistem beserta relasi SKPD, UPTD, dan Jabatannya.</li>
                            <li><strong>Pegawai Lama:</strong> Data SKPD, unit kerja/UPTD, jabatan, dan golongan langsung diperbarui mengikuti mutasi.</li>
                            <li><strong>Proteksi Data SIMGAJI:</strong> NIK, No. Rekening, Bank Penyalur, dan Tanggungan Keluarga <strong>tidak akan terhapus / tertimpa</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form id="modalUploadSimpegForm" action="{{ route('master.pegawai_simpeg.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- File Input -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Pilih Berkas Excel SIMPEG (.xlsx, .xls, .csv) *</label>
                    <input type="file" name="file" id="modalFileSimpeg" accept=".xlsx,.xls,.csv" required class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <small style="color: var(--text-muted); font-size: 11.5px; display: block; margin-top: 4px;">Header kolom wajib: <code>NIP</code> dan <code>NAMA</code>. Kolom pelengkap: <code>STATUS</code>, <code>GOLRU</code>, <code>SKPD</code>, <code>UPT</code>, <code>JABATAN</code>, dll.</small>
                </div>

                <!-- Mode Penanganan -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Metode Penanganan Data</label>
                    <select name="mode" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                        <option value="upsert" selected>🔄 Perbarui Data Lama & Tambah Pegawai Baru (Rekomendasi)</option>
                        <option value="insert_only">➕ Hanya Tambah Pegawai Baru (Abaikan yang sudah ada)</option>
                    </select>
                </div>

                <!-- Catatan / Keterangan -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; display: block;">Catatan / Keterangan Berkas (Opsional)</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Pembaruan SIMPEG TMT 1 Oktober 2026" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="{{ route('master.pegawai_simpeg.template') }}" class="btn" style="font-size: 11.5px; color: #2563eb; background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); padding: 6px 12px;">
                            <i class="ph-bold ph-file-arrow-down"></i> Unduh Template
                        </a>
                        <a href="{{ route('master.pegawai_simpeg.index') }}" class="btn" style="font-size: 11.5px; color: #64748b; background: var(--bg-surface-secondary, #f8fafc); border: 1px solid var(--border-color); padding: 6px 12px;" title="Lihat riwayat dan panduan kolom lengkap">
                            <i class="ph-bold ph-list-dashes"></i> Riwayat & Arsip
                        </a>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn" onclick="closeModal('uploadSimpegModal')" style="padding: 7px 14px; background: var(--bg-surface-secondary, #f1f5f9); border: 1px solid var(--border-color);">Batal</button>
                        <button type="submit" id="btnModalUploadSimpeg" class="btn btn-primary" style="padding: 7px 16px; font-weight: 600;">
                            <i class="ph-bold ph-upload-simple"></i> Mulai Unggah & Impor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Initialize TomSelect for Search Filter
    document.addEventListener("DOMContentLoaded", function() {
        new TomSelect("#skpd-filter",{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openDetailModal(item) {
        document.getElementById('dt_nama').textContent = item.nama || '-';
        document.getElementById('dt_nip').textContent = 'NIP: ' + (item.nip || '-');
        document.getElementById('dt_status_gol').textContent = (item.status_pegawai || '-') + ' / Gol: ' + (item.golru || '-');
        document.getElementById('dt_jabatan').textContent = (item.jabatan ? item.jabatan.nama : '-');
        document.getElementById('dt_skpd').textContent = (item.unit_kerja ? item.unit_kerja.skpd : '-');
        document.getElementById('dt_ttl').textContent = (item.tempat_lahir || '-') + ', ' + (item.tgl_lahir || '-');
        document.getElementById('dt_jk_agama').textContent = (item.jk || '-') + ' / ' + (item.agama || '-');

        document.getElementById('dt_nik').textContent = item.nik || 'Belum tersinkron';
        document.getElementById('dt_norek').textContent = item.no_rekening || 'Belum tersinkron';
        document.getElementById('dt_bank').textContent = item.nama_bank || '-';
        document.getElementById('dt_npwp').textContent = item.npwp || '-';
        document.getElementById('dt_karpeg').textContent = item.no_karpeg || '-';

        document.getElementById('dt_trace_link').href = '/laporan/trace-gaji?pegawai_id=' + item.id;

        let tbody = document.getElementById('dt_keluarga_tbody');
        tbody.innerHTML = '';
        let families = item.simgaji_keluargas || [];
        document.getElementById('dt_keluarga_count').textContent = families.length + ' Anggota Terdata';

        if (families.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 16px; color: var(--text-muted);">Tidak ada catatan anggota keluarga pada database SIMGAJI.</td></tr>';
        } else {
            families.forEach(function(f) {
                let isTertunjang = (f.kdtunjang === '2');
                let badge = isTertunjang 
                    ? '<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 2px 8px; border-radius: 999px; font-weight: 700; font-size: 10.5px;">Tertunjang</span>'
                    : '<span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 2px 8px; border-radius: 999px; font-size: 10.5px;">Tidak</span>';

                let row = document.createElement('tr');
                row.style.borderBottom = '1px solid var(--border-color)';
                row.innerHTML = 
                    '<td style="padding: 8px 10px; font-weight: 600;">' + (f.nmkel || '-') + '</td>' +
                    '<td style="padding: 8px 10px; color: var(--text-muted);">' + (f.hubungan || '-') + '</td>' +
                    '<td style="padding: 8px 10px;">' + (f.jenis_kelamin || '-') + '</td>' +
                    '<td style="padding: 8px 10px;">' + (f.tgllhr ? f.tgllhr.substring(0, 10) : '-') + '</td>' +
                    '<td style="padding: 8px 10px; text-align: center;">' + badge + '</td>';
                tbody.appendChild(row);
            });
        }

        openModal('detailModal');
    }

    function openEditModal(id, nip, nama, nik, no_rekening, nama_bank, npwp, tempat_lahir, tgl_lahir, jk, agama, status, golru, jabatan_id, unit_kerja_id) {
        document.getElementById('editForm').action = '/pegawai/' + id;
        document.getElementById('edit_nip').value = nip;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_nik').value = (nik && nik !== 'null') ? nik : '';
        document.getElementById('edit_no_rekening').value = (no_rekening && no_rekening !== 'null') ? no_rekening : '';
        document.getElementById('edit_nama_bank').value = (nama_bank && nama_bank !== 'null') ? nama_bank : '';
        document.getElementById('edit_npwp').value = (npwp && npwp !== 'null') ? npwp : '';
        document.getElementById('edit_tempat_lahir').value = tempat_lahir !== '-' ? tempat_lahir : '';
        document.getElementById('edit_tgl_lahir').value = tgl_lahir !== '-' ? tgl_lahir : '';
        document.getElementById('edit_jk').value = jk;
        document.getElementById('edit_agama').value = agama !== '-' ? agama : '';
        document.getElementById('edit_status_pegawai').value = status;
        document.getElementById('edit_golru').value = golru !== '-' ? golru : '';
        document.getElementById('edit_jabatan_id').value = jabatan_id;
        document.getElementById('edit_unit_kerja_id').value = unit_kerja_id;
        
        openModal('editModal');
    }

    // Modal Upload SIMPEG Form AJAX & Two-Step Upload/Sync
    document.getElementById('modalUploadSimpegForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const fileInput = document.getElementById('modalFileSimpeg');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire('Peringatan', 'Silakan pilih berkas Excel terlebih dahulu.', 'warning');
            return;
        }

        const formData = new FormData(form);
        const btn = document.getElementById('btnModalUploadSimpeg');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Menyimpan Berkas ke Server...';

        Swal.fire({
            title: 'Mengunggah Berkas ke Server',
            html: '<p style="color: #64748b; font-size: 13px;">Sedang memindahkan dan menyimpan berkas Excel ke server (1-2 detik)...</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-bold ph-upload-simple"></i> Unggah & Simpan ke Server';

            if (data.success) {
                closeModal('uploadSimpegModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Berkas Berhasil Diterima & Disimpan!',
                    html: `
                        <p style="font-size: 13.5px; color: #475569; margin: 6px 0 12px 0;">
                            Berkas <strong>${data.filename}</strong> telah tersimpan di server.
                        </p>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; text-align: left; font-size: 12.5px; color: #334155; margin-bottom: 12px;">
                            <strong>Langkah Selanjutnya:</strong><br>
                            Anda dapat langsung memproses data master pegawai ke database sekarang.
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '⚡ Ya, Proses & Sinkronkan Sekarang',
                    cancelButtonText: 'Tutup / Nanti Saja',
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        runSyncSimpegFromModal(data.file_id, data.filename);
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat mengunggah berkas.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-bold ph-upload-simple"></i> Unggah & Simpan ke Server';
            Swal.fire('Error!', 'Gagal menghubungi server atau berkas melebihi batas upload.', 'error');
        });
    });

    function runSyncSimpegFromModal(fileId, fileName) {
        const uploadId = Date.now().toString() + Math.random().toString(36).substring(2, 7);

        Swal.fire({
            title: 'Memproses Data Pegawai SIMPEG',
            html: `
                <div style="margin-top: 15px; margin-bottom: 10px; text-align: left; font-size: 13px; color: #64748b;" id="modal-progress-text">Menyiapkan in-memory lookup dan membaca berkas spreadsheet...</div>
                <div style="width: 100%; background-color: #e2e8f0; border-radius: 999px; height: 14px; overflow: hidden;">
                    <div id="modal-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981, #059669); transition: width 0.3s ease;"></div>
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
                            : `Memproses baris ke-${data.progress.toLocaleString()} data pegawai...`;

                        const progText = document.getElementById('modal-progress-text');
                        const progBar = document.getElementById('modal-progress-bar');
                        if (progText) progText.innerText = text;
                        if (progBar) {
                            if (data.total > 0) {
                                progBar.style.width = percent + '%';
                            } else {
                                progBar.style.width = '100%';
                            }
                        }
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
                        <p style="font-size: 13.5px; color: #475569; margin: 6px 0 14px 0;">Data kepegawaian SIMPEG berhasil disinkronkan ke database.</p>
                        <div style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 8px; font-size: 13px;">Ringkasan Data:</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px;">
                                <div style="background: white; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <div style="color: #64748b; font-size: 11px;">Total Baris Diproses</div>
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
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat memproses berkas.', 'error');
            }
        })
        .catch(err => {
            clearInterval(pollInterval);
            Swal.fire('Error!', 'Gagal menghubungi server atau proses sinkronisasi terputus.', 'error');
        });
    }
</script>
@endsection
