@extends('layouts.app')

@section('title', 'Reset Data Realisasi')
@section('page_title', 'Reset Data Realisasi')

@section('content')
<div class="app-header" style="border-left: 4px solid var(--danger) !important;">
    <div>
        <h2 style="color: var(--danger) !important; display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-octagon"></i> Hapus & Reset Data Realisasi
        </h2>
        <p>Penghapusan data di halaman ini bersifat permanen dan tidak dapat dibatalkan. Pastikan Anda telah memiliki cadangan data.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="ph-bold ph-x-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="card">
    <div style="margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
        <i class="ph-bold ph-trash" style="color: var(--danger); font-size: 20px;"></i>
        <div>
            <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0;">Form Penghapusan Data Realisasi</h3>
            <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">Tentukan jenis realisasi, periode, dan kelompok status pegawai yang ingin direset.</p>
        </div>
    </div>
    
    <form id="formHapusData" action="{{ route('setting.data.destroy') }}" method="POST">
        @csrf
        @method('DELETE')
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
            <!-- Jenis Data -->
            <div class="form-group" style="margin: 0;">
                <label>Jenis Realisasi Data *</label>
                <select name="jenis" id="selectJenis" onchange="toggleKriteriaGaji()" required>
                    <option value="">-- Pilih Jenis Data --</option>
                    <option value="GAJI">Realisasi Gaji (DBF / Excel)</option>
                    <option value="TPP">Realisasi TPP</option>
                </select>
            </div>

            <!-- Kriteria Gaji (Khusus GAJI) -->
            <div class="form-group" id="groupKriteriaGaji" style="margin: 0; display: none;">
                <label>Kriteria Gaji</label>
                <select name="jenis_gaji">
                    <option value="SEMUA">Semua Kriteria Gaji</option>
                    @foreach($daftarJenisGaji as $jg)
                        <option value="{{ $jg }}">{{ $jg }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Periode -->
            <div class="form-group" style="margin: 0;">
                <label>Periode Laporan *</label>
                <select name="periode" required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach($allPeriodes as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Status Pegawai -->
            <div class="form-group" style="margin: 0;">
                <label>Status Pegawai *</label>
                <select name="status_pegawai" required>
                    <option value="SEMUA">Semua Status (PNS, PPPK & Paruh Waktu)</option>
                    <option value="PNS">PNS Saja</option>
                    <option value="PPPK">PPPK Saja</option>
                    <option value="PPPK PARUH WAKTU">PPPK Paruh Waktu Saja</option>
                </select>
            </div>
        </div>
        
        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                <i class="ph ph-info"></i> Data master pegawai tidak akan terhapus.
            </div>
            <button type="button" onclick="confirmDelete()" class="btn btn-danger">
                <i class="ph-bold ph-trash"></i> Hapus Data Sekarang
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleKriteriaGaji() {
        const jenis = document.getElementById('selectJenis').value;
        const group = document.getElementById('groupKriteriaGaji');
        if (jenis === 'GAJI') {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    }

    function confirmDelete() {
        const jenis = document.querySelector('select[name="jenis"]').value;
        const periode = document.querySelector('select[name="periode"]').value;
        const status = document.querySelector('select[name="status_pegawai"]').value;
        const jenisGaji = document.querySelector('select[name="jenis_gaji"]').value;
        
        if (!jenis || !periode) {
            Swal.fire({
                icon: 'warning',
                title: 'Parameter Belum Lengkap',
                text: 'Silakan pilih Jenis Data dan Periode laporan terlebih dahulu.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        const kriteriaText = (jenis === 'GAJI' && jenisGaji !== 'SEMUA') ? ` (Kriteria: <b>${jenisGaji}</b>)` : '';

        Swal.fire({
            title: 'Konfirmasi Penghapusan',
            html: `Anda akan menghapus data <b>Realisasi ${jenis}</b>${kriteriaText} untuk periode <b>${periode}</b> (${status}).<br><br><span style="color:#dc2626; font-weight:600;">Tindakan ini tidak dapat dibatalkan!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formHapusData').submit();
            }
        });
    }
</script>
@endsection
