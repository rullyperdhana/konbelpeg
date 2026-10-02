@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page_title', 'Daftar Pegawai')

@section('content')
<div class="app-header">
    <div>
        <h2>Data Pegawai</h2>
        <p>Pengelolaan master pegawai, status kepegawaian, jabatan, dan unit kerja / UPTD.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <i class="ph ph-plus-circle"></i> Tambah Pegawai
    </button>
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
</script>
@endsection
