<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanGabunganController;
use App\Http\Controllers\LaporanIwpJamkesController;
use App\Http\Controllers\LaporanPegawaiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenyelarasanUnitKerjaController;
use App\Http\Controllers\PppkGuruController;
use App\Http\Controllers\RealisasiGajiController;
use App\Http\Controllers\RealisasiTppController;
use App\Http\Controllers\RekonsiliasiSimgajiController;
use App\Http\Controllers\SettingDataController;
use App\Http\Controllers\SikdCoreController;
use App\Http\Controllers\TraceGajiPegawaiController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\UnmatchedNipController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Root redirect based on auth status
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master SKPD & Jabatan
    Route::get('/master/skpd/export/pdf', [UnitKerjaController::class, 'exportPdf'])->name('master.skpd.export_pdf');
    Route::get('/master/skpd/export/excel', [UnitKerjaController::class, 'exportExcel'])->name('master.skpd.export_excel');
    Route::get('/master/skpd', [UnitKerjaController::class, 'index'])->name('master.skpd.index');
    Route::post('/master/skpd', [UnitKerjaController::class, 'store'])->name('master.skpd.store');
    Route::put('/master/skpd/{id}', [UnitKerjaController::class, 'update'])->name('master.skpd.update');
    Route::delete('/master/skpd/{id}', [UnitKerjaController::class, 'destroy'])->name('master.skpd.destroy');
    Route::get('/master/jabatan', [JabatanController::class, 'index']);
    Route::post('/master/jabatan', [JabatanController::class, 'store']);
    Route::put('/master/jabatan/{id}', [JabatanController::class, 'update']);
    Route::delete('/master/jabatan/{id}', [JabatanController::class, 'destroy']);

    // Pegawai
    Route::get('/pegawai', [PegawaiController::class, 'index']);
    Route::post('/pegawai', [PegawaiController::class, 'store']);
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update']);
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);

    // Laporan Umum & Pegawai per SKPD/UPT/Satker
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan/pegawai/export/pdf', [LaporanPegawaiController::class, 'exportPdf'])->name('laporan.pegawai.export_pdf');
    Route::get('/laporan/pegawai/export/excel', [LaporanPegawaiController::class, 'exportExcel'])->name('laporan.pegawai.export_excel');
    Route::get('/laporan/pegawai/filter-options', [LaporanPegawaiController::class, 'filterOptions'])->name('laporan.pegawai.filter_options');
    Route::get('/laporan/pegawai', [LaporanPegawaiController::class, 'index'])->name('laporan.pegawai.index');

    // Realisasi TPP
    Route::get('/realisasi/tpp', [RealisasiTppController::class, 'index']);
    Route::post('/realisasi/tpp/import', [RealisasiTppController::class, 'import']);
    Route::get('/realisasi/tpp/export/pdf', [RealisasiTppController::class, 'exportPdf']);
    Route::get('/realisasi/tpp/export/excel', [RealisasiTppController::class, 'exportExcel']);

    // Realisasi Gaji
    Route::get('/realisasi/gaji', [RealisasiGajiController::class, 'index']);
    Route::post('/realisasi/gaji/import', [RealisasiGajiController::class, 'import']);
    Route::get('/realisasi/gaji/export/pdf', [RealisasiGajiController::class, 'exportPdf']);
    Route::get('/realisasi/gaji/export/excel', [RealisasiGajiController::class, 'exportExcel']);

    // Trace Riwayat & Daftar Penggajian Pegawai Per Orang
    Route::get('/laporan/trace-gaji', [TraceGajiPegawaiController::class, 'index'])->name('laporan.trace_gaji.index');
    Route::get('/realisasi/trace-gaji', [TraceGajiPegawaiController::class, 'index'])->name('realisasi.trace_gaji.index');
    Route::get('/laporan/trace-gaji/{pegawai}', [TraceGajiPegawaiController::class, 'show'])->name('laporan.trace_gaji.show');
    Route::get('/realisasi/trace-gaji/{pegawai}', [TraceGajiPegawaiController::class, 'show']);
    Route::get('/laporan/trace-gaji/{pegawai}/export-pdf', [TraceGajiPegawaiController::class, 'exportPdf'])->name('laporan.trace_gaji.export_pdf');
    Route::get('/laporan/trace-gaji/{pegawai}/slip-pdf/{gaji}', [TraceGajiPegawaiController::class, 'slipPdf'])->name('laporan.trace_gaji.slip_pdf');

    // Upload Progress
    Route::get('/upload/progress', function (Request $request) {
        $id = $request->get('id');
        if (! $id) {
            return response()->json(['progress' => 0, 'total' => 0, 'percent' => 0]);
        }
        $data = Cache::store('file')->get('upload_progress_'.$id);
        if (! $data) {
            $data = Cache::get('upload_progress_'.$id, ['progress' => 0, 'total' => 0, 'percent' => 0]);
        }

        return response()->json($data);
    });

    // Laporan Gabungan
    Route::get('/laporan/gabungan', [LaporanGabunganController::class, 'index']);
    Route::get('/laporan/gabungan/export/pdf', [LaporanGabunganController::class, 'exportPdf']);
    Route::get('/laporan/gabungan/export/excel', [LaporanGabunganController::class, 'exportExcel']);

    // Laporan SIKD Core
    Route::get('/laporan/sikd-core', [SikdCoreController::class, 'index']);
    Route::get('/laporan/sikd-core/export/pdf', [SikdCoreController::class, 'exportPdf']);
    Route::get('/laporan/sikd-core/export/excel', [SikdCoreController::class, 'exportExcel']);
    Route::get('/laporan/sikd-core/rinci', [SikdCoreController::class, 'rinci']);
    Route::get('/laporan/sikd-core/rinci/export/excel', [SikdCoreController::class, 'exportExcelRinci']);

    // Laporan PPPK Guru
    Route::get('/laporan/pppk-guru', [PppkGuruController::class, 'index']);
    Route::get('/laporan/pppk-guru/export/pdf', [PppkGuruController::class, 'exportPdf']);
    Route::get('/laporan/pppk-guru/export/excel', [PppkGuruController::class, 'exportExcel']);
    Route::get('/laporan/pppk-guru/rinci', [PppkGuruController::class, 'rinci']);
    Route::get('/laporan/pppk-guru/rinci/export/excel', [PppkGuruController::class, 'exportExcelRinci']);
    Route::get('/laporan/pppk-guru/rinci/export/pdf', [PppkGuruController::class, 'exportPdfRinci']);

    // Laporan Unmatched NIP
    Route::get('/laporan/unmatched-nip', [UnmatchedNipController::class, 'index']);
    Route::delete('/laporan/unmatched-nip/clear', [UnmatchedNipController::class, 'destroyAll']);

    // Master Database SIMGAJI (.DBF) Management
    Route::get('/master/simgaji-dbf', [RekonsiliasiSimgajiController::class, 'uploadPage'])->name('master.simgaji_dbf.index');
    Route::post('/master/simgaji-dbf/upload', [RekonsiliasiSimgajiController::class, 'uploadDbf'])->name('master.simgaji_dbf.upload');
    Route::post('/master/simgaji-dbf/sync', [RekonsiliasiSimgajiController::class, 'syncSimgaji'])->name('master.simgaji_dbf.sync');
    Route::post('/master/simgaji-dbf/{id}/activate', [RekonsiliasiSimgajiController::class, 'setActiveDbf'])->name('master.simgaji_dbf.activate');
    Route::delete('/master/simgaji-dbf/{id}', [RekonsiliasiSimgajiController::class, 'deleteDbf'])->name('master.simgaji_dbf.delete');

    // Rekonsiliasi SIMGAJI
    Route::get('/laporan/rekonsiliasi-simgaji', [RekonsiliasiSimgajiController::class, 'index'])->name('laporan.rekonsiliasi_simgaji.index');
    Route::post('/laporan/rekonsiliasi-simgaji/sync-pegawai', [RekonsiliasiSimgajiController::class, 'syncPegawai'])->name('laporan.rekonsiliasi_simgaji.sync_pegawai');
    Route::post('/laporan/rekonsiliasi-simgaji/sync-pangkat', [RekonsiliasiSimgajiController::class, 'syncPangkat'])->name('laporan.rekonsiliasi_simgaji.sync_pangkat');
    Route::post('/laporan/rekonsiliasi-simgaji/sync-skpd', [RekonsiliasiSimgajiController::class, 'syncSkpd'])->name('laporan.rekonsiliasi_simgaji.sync_skpd');
    Route::post('/laporan/rekonsiliasi-simgaji/sync-jabatan', [RekonsiliasiSimgajiController::class, 'syncJabatan'])->name('laporan.rekonsiliasi_simgaji.sync_jabatan');
    Route::post('/laporan/rekonsiliasi-simgaji/upload', [RekonsiliasiSimgajiController::class, 'uploadDbf'])->name('laporan.rekonsiliasi_simgaji.upload');
    Route::get('/laporan/rekonsiliasi-simgaji/refresh', [RekonsiliasiSimgajiController::class, 'refreshCache'])->name('laporan.rekonsiliasi_simgaji.refresh');
    Route::get('/laporan/rekonsiliasi-simgaji/export/excel', [RekonsiliasiSimgajiController::class, 'exportExcel'])->name('laporan.rekonsiliasi_simgaji.export_excel');
    Route::get('/laporan/rekonsiliasi-simgaji/export/pdf', [RekonsiliasiSimgajiController::class, 'exportPdf'])->name('laporan.rekonsiliasi_simgaji.export_pdf');

    // Penyelarasan SKPD & UPTD (SIMGAJI vs SIMPEG)
    Route::get('/laporan/penyelarasan-unit-kerja', [PenyelarasanUnitKerjaController::class, 'index'])->name('laporan.penyelarasan_unit.index');
    Route::get('/laporan/penyelarasan-unit-kerja/refresh', [PenyelarasanUnitKerjaController::class, 'refreshCache'])->name('laporan.penyelarasan_unit.refresh');
    Route::get('/laporan/penyelarasan-unit-kerja/export/excel', [PenyelarasanUnitKerjaController::class, 'exportExcel'])->name('laporan.penyelarasan_unit.export_excel');
    Route::get('/laporan/penyelarasan-unit-kerja/export/pdf', [PenyelarasanUnitKerjaController::class, 'exportPdf'])->name('laporan.penyelarasan_unit.export_pdf');

    // Laporan Rekonsiliasi IWP & BPJS Kesehatan (Jamkes)
    Route::get('/laporan/iwp-jamkes', [LaporanIwpJamkesController::class, 'index'])->name('laporan.iwp_jamkes.index');
    Route::get('/laporan/iwp-jamkes/export/excel', [LaporanIwpJamkesController::class, 'exportExcel'])->name('laporan.iwp_jamkes.export_excel');
    Route::get('/laporan/iwp-jamkes/export/pdf', [LaporanIwpJamkesController::class, 'exportPdf'])->name('laporan.iwp_jamkes.export_pdf');

    // Pengaturan
    Route::prefix('setting/users')->name('setting.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('setting/data')->name('setting.data.')->group(function () {
        Route::get('/', [SettingDataController::class, 'index'])->name('index');
        Route::delete('/hapus', [SettingDataController::class, 'destroy'])->name('destroy');
    });
});
