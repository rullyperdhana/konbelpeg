<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanGabunganController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PppkGuruController;
use App\Http\Controllers\RealisasiGajiController;
use App\Http\Controllers\RealisasiTppController;
use App\Http\Controllers\RekonsiliasiSimgajiController;
use App\Http\Controllers\SettingDataController;
use App\Http\Controllers\SikdCoreController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\UnmatchedNipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/master/skpd', [UnitKerjaController::class, 'index']);
Route::post('/master/skpd', [UnitKerjaController::class, 'store']);
Route::put('/master/skpd/{id}', [UnitKerjaController::class, 'update']);
Route::delete('/master/skpd/{id}', [UnitKerjaController::class, 'destroy']);
Route::get('/master/jabatan', [JabatanController::class, 'index']);
Route::post('/master/jabatan', [JabatanController::class, 'store']);
Route::put('/master/jabatan/{id}', [JabatanController::class, 'update']);
Route::delete('/master/jabatan/{id}', [JabatanController::class, 'destroy']);
Route::get('/pegawai', [PegawaiController::class, 'index']);
Route::post('/pegawai', [PegawaiController::class, 'store']);
Route::put('/pegawai/{id}', [PegawaiController::class, 'update']);
Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);

Route::get('/laporan', [LaporanController::class, 'index']);
Route::get('/laporan/pegawai', [LaporanController::class, 'pegawai']);

Route::get('/realisasi/tpp', [RealisasiTppController::class, 'index']);
Route::post('/realisasi/tpp/import', [RealisasiTppController::class, 'import']);
Route::get('/realisasi/tpp/export/pdf', [RealisasiTppController::class, 'exportPdf']);
Route::get('/realisasi/tpp/export/excel', [RealisasiTppController::class, 'exportExcel']);

Route::get('/realisasi/gaji', [RealisasiGajiController::class, 'index']);
Route::post('/realisasi/gaji/import', [RealisasiGajiController::class, 'import']);
Route::get('/realisasi/gaji/export/pdf', [RealisasiGajiController::class, 'exportPdf']);
Route::get('/realisasi/gaji/export/excel', [RealisasiGajiController::class, 'exportExcel']);

Route::get('/upload/progress', function (Request $request) {
    $id = $request->get('id');
    if (! $id) {
        return response()->json(['progress' => 0, 'total' => 0]);
    }
    $data = Cache::get('upload_progress_'.$id, ['progress' => 0, 'total' => 0]);

    return response()->json($data);
});

Route::get('/laporan/gabungan', [LaporanGabunganController::class, 'index']);
Route::get('/laporan/gabungan/export/pdf', [LaporanGabunganController::class, 'exportPdf']);
Route::get('/laporan/gabungan/export/excel', [LaporanGabunganController::class, 'exportExcel']);

Route::get('/laporan/sikd-core', [SikdCoreController::class, 'index']);
Route::get('/laporan/sikd-core/export/pdf', [SikdCoreController::class, 'exportPdf']);
Route::get('/laporan/sikd-core/export/excel', [SikdCoreController::class, 'exportExcel']);

Route::get('/laporan/sikd-core/rinci', [SikdCoreController::class, 'rinci']);
Route::get('/laporan/sikd-core/rinci/export/excel', [SikdCoreController::class, 'exportExcelRinci']);

Route::get('/laporan/pppk-guru', [PppkGuruController::class, 'index']);
Route::get('/laporan/pppk-guru/export/pdf', [PppkGuruController::class, 'exportPdf']);
Route::get('/laporan/pppk-guru/export/excel', [PppkGuruController::class, 'exportExcel']);

Route::get('/laporan/pppk-guru/rinci', [PppkGuruController::class, 'rinci']);
Route::get('/laporan/pppk-guru/rinci/export/excel', [PppkGuruController::class, 'exportExcelRinci']);
Route::get('/laporan/pppk-guru/rinci/export/pdf', [PppkGuruController::class, 'exportPdfRinci']);

Route::get('/laporan/unmatched-nip', [UnmatchedNipController::class, 'index']);
Route::delete('/laporan/unmatched-nip/clear', [UnmatchedNipController::class, 'destroyAll']);

// Master Database SIMGAJI (.DBF) Management Routes
Route::get('/master/simgaji-dbf', [RekonsiliasiSimgajiController::class, 'uploadPage'])->name('master.simgaji_dbf.index');
Route::post('/master/simgaji-dbf/upload', [RekonsiliasiSimgajiController::class, 'uploadDbf'])->name('master.simgaji_dbf.upload');
Route::post('/master/simgaji-dbf/{id}/activate', [RekonsiliasiSimgajiController::class, 'setActiveDbf'])->name('master.simgaji_dbf.activate');
Route::delete('/master/simgaji-dbf/{id}', [RekonsiliasiSimgajiController::class, 'deleteDbf'])->name('master.simgaji_dbf.delete');

// Rekonsiliasi SIMGAJI Routes
Route::get('/laporan/rekonsiliasi-simgaji', [RekonsiliasiSimgajiController::class, 'index'])->name('laporan.rekonsiliasi_simgaji.index');
Route::post('/laporan/rekonsiliasi-simgaji/sync-pegawai', [RekonsiliasiSimgajiController::class, 'syncPegawai'])->name('laporan.rekonsiliasi_simgaji.sync_pegawai');
Route::post('/laporan/rekonsiliasi-simgaji/sync-pangkat', [RekonsiliasiSimgajiController::class, 'syncPangkat'])->name('laporan.rekonsiliasi_simgaji.sync_pangkat');
Route::post('/laporan/rekonsiliasi-simgaji/sync-skpd', [RekonsiliasiSimgajiController::class, 'syncSkpd'])->name('laporan.rekonsiliasi_simgaji.sync_skpd');
Route::post('/laporan/rekonsiliasi-simgaji/sync-jabatan', [RekonsiliasiSimgajiController::class, 'syncJabatan'])->name('laporan.rekonsiliasi_simgaji.sync_jabatan');
Route::post('/laporan/rekonsiliasi-simgaji/upload', [RekonsiliasiSimgajiController::class, 'uploadDbf'])->name('laporan.rekonsiliasi_simgaji.upload');
Route::get('/laporan/rekonsiliasi-simgaji/refresh', [RekonsiliasiSimgajiController::class, 'refreshCache'])->name('laporan.rekonsiliasi_simgaji.refresh');
Route::get('/laporan/rekonsiliasi-simgaji/export/excel', [RekonsiliasiSimgajiController::class, 'exportExcel'])->name('laporan.rekonsiliasi_simgaji.export_excel');
Route::get('/laporan/rekonsiliasi-simgaji/export/pdf', [RekonsiliasiSimgajiController::class, 'exportPdf'])->name('laporan.rekonsiliasi_simgaji.export_pdf');

// Pengaturan Routes
Route::prefix('setting/data')->name('setting.data.')->group(function () {
    Route::get('/', [SettingDataController::class, 'index'])->name('index');
    Route::delete('/hapus', [SettingDataController::class, 'destroy'])->name('destroy');
});
