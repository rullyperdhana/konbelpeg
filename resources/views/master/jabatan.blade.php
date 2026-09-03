@extends('layouts.app')

@section('title', 'Master Data Jabatan')
@section('page_title', 'Master Data Jabatan')

@section('content')
<div class="app-header">
    <div>
        <h2>Master Data Jabatan</h2>
        <p>Pengelolaan daftar Jabatan Struktural dan Fungsional.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <i class="ph ph-plus-circle"></i> Tambah Jabatan
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">Terjadi kesalahan pada input form.</div>
@endif

<div class="toolbar">
    <form action="/master/jabatan" method="GET" class="search-box">
        <input type="text" name="search" placeholder="Cari nama jabatan, eselon..." value="{{ request('search') }}">
        <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Jabatan</th>
                <th>Jenis</th>
                <th>Eselon</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jabatans as $index => $item)
            <tr>
                <td>{{ $jabatans->firstItem() + $index }}</td>
                <td style="font-weight: 500; color: #3b82f6;">{{ $item->nama ?? '-' }}</td>
                <td><span style="background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{ $item->jenis ?? 'UMUM' }}</span></td>
                <td>{{ $item->eselon ?? '-' }}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ $item->nama }}', '{{ $item->jenis }}', '{{ $item->eselon }}')">Edit</button>
                        <form action="/master/jabatan/{{ $item->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Jabatan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div style="font-size: 13px; color: #64748b;">
            Menampilkan {{ $jabatans->firstItem() ?? 0 }} - {{ $jabatans->lastItem() ?? 0 }} dari {{ $jabatans->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$jabatans->onFirstPage())
                <a href="{{ $jabatans->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($jabatans->hasMorePages())
                <a href="{{ $jabatans->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Jabatan</h3>
            <button class="btn-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="/master/jabatan" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Jabatan *</label>
                <input type="text" name="nama" required placeholder="Contoh: ANALIS KEBIJAKAN AHLI MADYA">
            </div>
            <div class="form-group">
                <label>Jenis Jabatan</label>
                <select name="jenis">
                    <option value="FUNGSIONAL">FUNGSIONAL</option>
                    <option value="STRUKTURAL">STRUKTURAL</option>
                    <option value="UMUM">UMUM</option>
                </select>
            </div>
            <div class="form-group">
                <label>Eselon</label>
                <input type="text" name="eselon" placeholder="Kosongkan atau isi '-' jika tidak ada">
            </div>
            <div style="text-align: right; margin-top: 24px;">
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
            <h3>Edit Jabatan</h3>
            <button class="btn-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Jabatan *</label>
                <input type="text" name="nama" id="edit_nama" required>
            </div>
            <div class="form-group">
                <label>Jenis Jabatan</label>
                <select name="jenis" id="edit_jenis">
                    <option value="FUNGSIONAL">FUNGSIONAL</option>
                    <option value="STRUKTURAL">STRUKTURAL</option>
                    <option value="UMUM">UMUM</option>
                </select>
            </div>
            <div class="form-group">
                <label>Eselon</label>
                <input type="text" name="eselon" id="edit_eselon">
            </div>
            <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn-edit" onclick="closeModal('editModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn-primary">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openEditModal(id, nama, jenis, eselon) {
        document.getElementById('editForm').action = '/master/jabatan/' + id;
        document.getElementById('edit_nama').value = nama;
        
        let jenisSelect = document.getElementById('edit_jenis');
        jenisSelect.value = jenis && jenis !== '-' ? jenis : 'UMUM';
        
        document.getElementById('edit_eselon').value = eselon !== '-' ? eselon : '';
        openModal('editModal');
    }
</script>
@endsection
