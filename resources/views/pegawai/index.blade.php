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
        <input type="text" name="search" placeholder="Cari NIP atau Nama..." value="{{ request('search') }}">
        <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Status / Gol</th>
                <th>Jabatan</th>
                <th>Unit Kerja / UPTD</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawais as $item)
            <tr>
                <td style="font-weight: 600; color: #475569;">{{ $item->nip }}</td>
                <td>
                    <div style="font-weight: 600; font-size: 14px; color: #0f172a;">{{ $item->nama }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ $item->tempat_lahir ?? '-' }}, {{ $item->tgl_lahir ? date('d-m-Y', strtotime($item->tgl_lahir)) : '-' }}</div>
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
                        <button class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ $item->nip }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->tempat_lahir) }}', '{{ $item->tgl_lahir }}', '{{ $item->jk }}', '{{ $item->agama }}', '{{ $item->status_pegawai }}', '{{ $item->golru }}', '{{ $item->jabatan_id }}', '{{ $item->unit_kerja_id }}')">Edit</button>
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

    function openEditModal(id, nip, nama, tempat_lahir, tgl_lahir, jk, agama, status, golru, jabatan_id, unit_kerja_id) {
        document.getElementById('editForm').action = '/pegawai/' + id;
        document.getElementById('edit_nip').value = nip;
        document.getElementById('edit_nama').value = nama;
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
