@extends('layouts.app')

@section('title', 'Kelola Pengguna Sistem')
@section('page_title', 'Kelola Pengguna')

@section('content')
<style>
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--luno-primary) 0%, #3b82f6 100%);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(76, 53, 222, 0.25);
    }

    .badge-self {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10.5px;
        font-weight: 700;
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .badge-active {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(6, 182, 212, 0.08);
        color: #0891b2;
        border: 1px solid rgba(6, 182, 212, 0.2);
    }

    .pwd-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .pwd-input-group input {
        width: 100%;
        padding-right: 40px !important;
    }

    .pwd-toggle-btn {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 17px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
    }

    .pwd-toggle-btn:hover {
        color: var(--text-main);
    }
</style>

<div class="app-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2>Kelola Pengguna Sistem</h2>
        <p>Manajemen akun administrator dan hak akses pengguna sistem KONBELPEG.</p>
        <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(76, 53, 222, 0.08); color: var(--luno-primary); padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(76, 53, 222, 0.2);">
                <i class="ph ph-users"></i> {{ number_format($totalUsers) }} Pengguna Terdaftar
            </span>
            <span style="font-size: 11.5px; font-weight: 600; background: rgba(16, 185, 129, 0.08); color: #059669; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="ph ph-shield-check"></i> Autentikasi Sistem Aktif
            </span>
        </div>
    </div>

    <!-- Action Button -->
    <div>
        <button class="btn btn-primary" onclick="openModal('addUserModal')" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-user-plus" style="font-size: 16px;"></i>
            <span>Tambah Pengguna</span>
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="ph ph-check-circle" style="font-size: 18px; margin-right: 6px;"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="ph ph-warning-circle" style="font-size: 18px; margin-right: 6px;"></i>
        {{ session('error') }}
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-error">
        <strong><i class="ph ph-warning"></i> Terjadi kesalahan input:</strong>
        <ul style="margin: 6px 0 0 18px; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Toolbar & Search -->
<div class="toolbar" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <form action="{{ route('setting.users.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; width: 100%; max-width: 600px;">
        <div class="search-box" style="margin: 0; flex: 1; min-width: 250px;">
            <input type="text" name="search" placeholder="Cari nama pengguna atau alamat email..." value="{{ request('search') }}">
            <button type="submit"><i class="ph ph-magnifying-glass"></i> Cari</button>
        </div>

        @if(request('search'))
            <a href="{{ route('setting.users.index') }}" class="btn" style="padding: 7px 12px; border-radius: 8px; font-size: 12px; background: var(--bg-surface-hover); color: var(--text-main); text-decoration: none; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 5px;">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </a>
        @endif
    </form>
</div>

<!-- Table Card -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 45px; text-align: center;">No</th>
                <th>Nama Pengguna</th>
                <th>Alamat Email</th>
                <th style="width: 190px;">Terdaftar Sejak</th>
                <th style="text-align: center; width: 100px;">Status</th>
                <th style="text-align: right; width: 140px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $u)
                @php
                    $isSelf = (Auth::id() === $u->id);
                    $initials = '';
                    $parts = explode(' ', trim($u->name));
                    foreach(array_slice($parts, 0, 2) as $p) {
                        $initials .= strtoupper(substr($p, 0, 1));
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $users->firstItem() + $index }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="user-avatar">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                                    {{ $u->name }}
                                    @if($isSelf)
                                        <span class="badge-self" title="Akun yang sedang Anda gunakan saat ini">
                                            <i class="ph ph-check"></i> Anda
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px;">
                                    ID Pengguna: #{{ $u->id }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 500; color: var(--text-main); font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            <i class="ph ph-envelope-simple" style="color: var(--text-muted); font-size: 14px;"></i>
                            <span>{{ $u->email }}</span>
                        </div>
                    </td>
                    <td style="font-size: 12.5px; color: var(--text-muted);">
                        {{ $u->created_at ? $u->created_at->translatedFormat('d F Y, H:i') : '-' }} WITA
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-active">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0891b2;"></span>
                            Aktif
                        </span>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <button type="button" class="btn-edit" onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')">
                                Edit
                            </button>

                            @if(!$isSelf)
                                <form action="{{ route('setting.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ addslashes($u->name) }}? Akses pengguna ini ke sistem akan dicabut secara permanen.');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                            @else
                                <button type="button" class="btn-delete" style="opacity: 0.35; cursor: not-allowed;" title="Anda tidak dapat menghapus akun Anda sendiri" disabled>
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                        <i class="ph ph-users" style="font-size: 36px; opacity: 0.4; display: block; margin-bottom: 8px;"></i>
                        Data pengguna tidak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <div style="font-size: 13px; color: var(--text-muted);">
            Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
        </div>
        <div style="display: flex; gap: 8px;">
            @if(!$users->onFirstPage())
                <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">&laquo; Prev</a>
            @endif
            @if($users->hasMorePages())
                <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 13px; font-weight: 500;">Next &raquo;</a>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Pengguna Baru</h3>
            <button class="btn-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <form action="{{ route('setting.users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Prasetyo, S.Kom" value="{{ old('name') }}">
            </div>
            
            <div class="form-group">
                <label>Alamat Email (Digunakan untuk Login) *</label>
                <input type="email" name="email" required placeholder="Contoh: budi@pemda.go.id" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label>Kata Sandi *</label>
                <div class="pwd-input-group">
                    <input type="password" name="password" id="add_password" required placeholder="Minimal 6 karakter">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePasswordVisibility('add_password', 'add_pwd_icon')">
                        <i class="ph ph-eye" id="add_pwd_icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Kata Sandi *</label>
                <div class="pwd-input-group">
                    <input type="password" name="password_confirmation" id="add_password_conf" required placeholder="Ketik ulang kata sandi">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePasswordVisibility('add_password_conf', 'add_pwd_conf_icon')">
                        <i class="ph ph-eye" id="add_pwd_conf_icon"></i>
                    </button>
                </div>
            </div>

            <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn-edit" onclick="closeModal('addUserModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Data Pengguna</h3>
            <button class="btn-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>

            <div class="form-group">
                <label>Alamat Email *</label>
                <input type="email" name="email" id="edit_email" required>
            </div>

            <div style="background: rgba(76, 53, 222, 0.05); border: 1px dashed rgba(76, 53, 222, 0.25); border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                <div style="font-size: 12px; color: var(--text-muted);">
                    <i class="ph ph-info"></i> <strong>Ubah Kata Sandi (Opsional):</strong><br>
                    Kosongkan kolom kata sandi di bawah jika tidak ingin mengganti kata sandi pengguna ini.
                </div>
            </div>

            <div class="form-group">
                <label>Kata Sandi Baru</label>
                <div class="pwd-input-group">
                    <input type="password" name="password" id="edit_password" placeholder="Minimal 6 karakter (kosongkan jika tidak diubah)">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePasswordVisibility('edit_password', 'edit_pwd_icon')">
                        <i class="ph ph-eye" id="edit_pwd_icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Kata Sandi Baru</label>
                <div class="pwd-input-group">
                    <input type="password" name="password_confirmation" id="edit_password_conf" placeholder="Ketik ulang kata sandi baru">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePasswordVisibility('edit_password_conf', 'edit_pwd_conf_icon')">
                        <i class="ph ph-eye" id="edit_pwd_conf_icon"></i>
                    </button>
                </div>
            </div>

            <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn-edit" onclick="closeModal('editUserModal')" style="margin-right: 8px;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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

    function openEditUserModal(id, name, email) {
        document.getElementById('editUserForm').action = '/setting/users/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_conf').value = '';
        openModal('editUserModal');
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ph-eye');
            icon.classList.add('ph-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('ph-eye-slash');
            icon.classList.add('ph-eye');
        }
    }
</script>
@endsection
