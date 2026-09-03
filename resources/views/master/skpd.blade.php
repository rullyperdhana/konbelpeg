@extends('layouts.app')

@section('title', 'Antasari Administrative System - SKPD')
@section('page_title', 'Antasari Administrative System')

@section('content')
<div class="app-header">
    <div>
        <h2>Master Data Unit Kerja (SKPD)</h2>
        <p>Pengelolaan hierarki SKPD, Unit Pelaksana Teknis (UPT), dan Satuan Kerja.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <i class="ph ph-plus-circle"></i> Tambah Unit Kerja
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
    <form action="/master/skpd" method="GET" class="search-box">
        <input type="text" name="search" placeholder="Cari nama SKPD, UPT, atau Satker..." value="{{ request('search') }}">
        <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>SKPD</th>
                <th>UPT</th>
                <th>SATKER</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unitKerjas as $index => $item)
            <tr>
                <td>{{ $unitKerjas->firstItem() + $index }}</td>
                <td style="font-weight: 600;">{{ $item->skpd ?? '-' }}</td>
                <td>{{ $item->upt ?? '-' }}</td>
                <td>{{ $item->satker ?? '-' }}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-edit" onclick="openEditModal({{ $item->id }}, '{{ $item->skpd }}', '{{ $item->upt }}', '{{ $item->satker }}')">Edit</button>
                        <form action="/master/skpd/{{ $item->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Unit Kerja ini?');">
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
            Menampilkan {{ $unitKerjas->firstItem() }} - {{ $unitKerjas->lastItem() }} dari {{ $unitKerjas->total() }} data
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$unitKerjas->onFirstPage())
                <a href="{{ $unitKerjas->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($unitKerjas->hasMorePages())
                <a href="{{ $unitKerjas->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Unit Kerja</h3>
            <button class="btn-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="/master/skpd" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama SKPD (Induk) *</label>
                <input type="text" name="skpd" required placeholder="Contoh: BADAN RISET DAN INOVASI DAERAH">
            </div>
            <div class="form-group">
                <label>Nama UPT</label>
                <input type="text" name="upt" placeholder="Kosongkan atau isi '-' jika tidak ada">
            </div>
            <div class="form-group">
                <label>Nama Satker</label>
                <input type="text" name="satker" placeholder="Kosongkan atau isi '-' jika tidak ada">
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
            <h3>Edit Unit Kerja</h3>
            <button class="btn-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama SKPD (Induk) *</label>
                <input type="text" name="skpd" id="edit_skpd" required>
            </div>
            <div class="form-group">
                <label>Nama UPT</label>
                <input type="text" name="upt" id="edit_upt">
            </div>
            <div class="form-group">
                <label>Nama Satker</label>
                <input type="text" name="satker" id="edit_satker">
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

    function openEditModal(id, skpd, upt, satker) {
        document.getElementById('editForm').action = '/master/skpd/' + id;
        document.getElementById('edit_skpd').value = skpd !== '-' ? skpd : '';
        document.getElementById('edit_upt').value = upt !== '-' ? upt : '';
        document.getElementById('edit_satker').value = satker !== '-' ? satker : '';
        openModal('editModal');
    }
</script>
@endsection
