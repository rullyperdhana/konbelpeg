{{-- Reusable DBF Upload & Sync Progress Modal Scripts --}}
<style>
    @keyframes dbfPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.85; transform: scale(1.02); }
    }
    @keyframes dbfShimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .dbf-progress-active {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #60a5fa 100%) !important;
        background-size: 200% 100% !important;
        animation: dbfShimmer 1.5s infinite linear !important;
    }
</style>

<script>
(function() {
    // Tampilkan hasil sukses upload jika ada flash session
    @if(session('upload_result'))
        document.addEventListener('DOMContentLoaded', function() {
            window.showDbfUploadSuccessResult(@json(session('upload_result')));
        });
    @endif

    /**
     * Tampilkan modal sukses lengkap setelah proses upload/sinkronisasi
     */
    window.showDbfUploadSuccessResult = function(data) {
        if (!window.Swal) {
            alert(data.message || 'File DBF berhasil diproses.');
            return;
        }

        const formattedRecords = Number(data.records || 0).toLocaleString('id-ID');
        const formattedTotalDb = data.total_keluarga_db ? Number(data.total_keluarga_db).toLocaleString('id-ID') : null;

        const html = `
            <div style="text-align: left; font-size: 13px; line-height: 1.6; color: #1e293b;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; display: flex; align-items: flex-start; gap: 10px;">
                    <div style="font-size: 20px; color: #16a34a; line-height: 1;">✅</div>
                    <div style="font-size: 12.5px; color: #166534; font-weight: 500;">
                        ${data.message || 'Berkas DBF berhasil diunggah dan diproses ke sistem.'}
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 0; color: #64748b; width: 145px;">Nama Berkas:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: #1e293b; word-break: break-all;">
                                <i class="ph-bold ph-file-code" style="color: #2563eb;"></i> ${data.filename || '-'}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 0; color: #64748b;">Kategori DBF:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #2563eb;">
                                ${data.type_label || data.type || '-'}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 0; color: #64748b;">Total Record Berkas:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: #059669;">
                                ${formattedRecords} baris data
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 0; color: #64748b;">Sinkronisasi Database:</td>
                            <td style="padding: 6px 0; font-weight: 600;">
                                ${data.auto_synced 
                                    ? '<span style="color: #16a34a; display: inline-flex; align-items: center; gap: 4px;">✔️ Otomatis Tersinkron</span>' 
                                    : '<span style="color: #d97706;">⚠️ Manual (Belum disinkronkan)</span>'}
                            </td>
                        </tr>
                        ${formattedTotalDb ? `
                        <tr>
                            <td style="padding: 6px 0; color: #64748b;">Total di Basis Data:</td>
                            <td style="padding: 6px 0; font-weight: 700; color: #1e293b;">
                                ${formattedTotalDb} anggota keluarga siap digunakan
                            </td>
                        </tr>` : ''}
                    </table>
                </div>

                <div style="margin-top: 12px; font-size: 11.5px; color: #64748b; text-align: center;">
                    Halaman akan disegarkan otomatis agar data rekonsiliasi &amp; audit menggunakan berkas terbaru ini.
                </div>
            </div>
        `;

        Swal.fire({
            title: '<div style="font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; justify-content: center; gap: 8px;"><span>🎉</span> Berkas DBF Berhasil Diproses!</div>',
            html: html,
            icon: null,
            confirmButtonText: '<i class="ph-bold ph-arrows-clockwise"></i> Segarkan & Tampilkan Data',
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            customClass: {
                popup: 'dbf-swal-popup'
            }
        }).then(() => {
            window.location.reload();
        });
    };

    /**
     * Handler submit form upload DBF dengan indikator status langkah demi langkah & progress bar
     */
    window.attachDbfUploadHandler = function(formSelector, fileInputSelector) {
        const form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = fileInputSelector 
                ? form.querySelector(fileInputSelector) 
                : (form.querySelector('input[type="file"]') || document.getElementById('inputDbfFile'));

            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Berkas DBF',
                    text: 'Silakan pilih berkas database (.dbf atau .DBF) terlebih dahulu sebelum mengunggah.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            const file = fileInput.files[0];
            const fileName = file.name;
            const fileSizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const ext = fileName.split('.').pop().toLowerCase();

            if (ext !== 'dbf') {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Berkas Tidak Sesuai',
                    text: 'Berkas yang dipilih harus berekstensi .dbf atau .DBF dari ekspor SIMGAJI Taspen.',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            const autoSyncInput = form.querySelector('input[name="auto_sync"]');
            const willAutoSync = autoSyncInput ? autoSyncInput.checked : true;

            // Tutup modal popup jika formulir berada di dalam modal-overlay
            const parentModal = form.closest('.modal-overlay');
            if (parentModal) {
                parentModal.classList.remove('active');
            }

            // Template progress dialog
            const progressHtml = `
                <div style="text-align: left; font-size: 12.5px; line-height: 1.5; color: #1e293b;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                        <div style="font-size: 26px; color: #2563eb; line-height: 1;">📂</div>
                        <div style="overflow: hidden; flex: 1;">
                            <div style="font-weight: 700; color: #1e293b; font-size: 13px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;" title="${fileName}">
                                ${fileName}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b;">
                                Ukuran: <strong>${fileSizeMb} MB</strong> (${file.size.toLocaleString('id-ID')} bytes)
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 6px;">
                            <span style="font-weight: 600; color: #475569;" id="dbfStageLabel">Mengunggah berkas ke server...</span>
                            <span style="font-weight: 700; color: #2563eb;" id="dbfProgressPct">15%</span>
                        </div>
                        <div style="width: 100%; height: 9px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                            <div id="dbfProgressBar" class="dbf-progress-active" style="width: 15%; height: 100%; border-radius: 999px; transition: width 0.4s ease;"></div>
                        </div>
                    </div>

                    <!-- Checklist Tahapan Proses -->
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                        <div id="dbfStep1" style="display: flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 600;">
                            <span class="step-icon" style="display: inline-block; width: 18px; text-align: center;">⏳</span>
                            <span>1. Mengunggah berkas ke server...</span>
                        </div>
                        <div id="dbfStep2" style="display: flex; align-items: center; gap: 8px; color: #94a3b8;">
                            <span class="step-icon" style="display: inline-block; width: 18px; text-align: center;">⚪</span>
                            <span>2. Membaca &amp; memvalidasi record berkas DBF...</span>
                        </div>
                        <div id="dbfStep3" style="display: flex; align-items: center; gap: 8px; color: #94a3b8;">
                            <span class="step-icon" style="display: inline-block; width: 18px; text-align: center;">⚪</span>
                            <span>3. ${willAutoSync ? 'Menyinkronkan data ke tabel basis data...' : 'Menyimpan berkas sebagai acuan aktif...'}</span>
                        </div>
                        <div id="dbfStep4" style="display: flex; align-items: center; gap: 8px; color: #94a3b8;">
                            <span class="step-icon" style="display: inline-block; width: 18px; text-align: center;">⚪</span>
                            <span>4. Memperbarui cache rekonsiliasi &amp; audit...</span>
                        </div>
                    </div>

                    <div style="margin-top: 14px; text-align: center; font-size: 11px; color: #dc2626; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <span style="font-size: 13px;">⚠️</span>
                        <span>Mohon jangan menutup atau memuat ulang halaman saat proses berlangsung.</span>
                    </div>
                </div>
            `;

            Swal.fire({
                title: '<div style="font-size: 17px; font-weight: 700; color: #0f172a;">Memproses Berkas DBF SIMGAJI</div>',
                html: progressHtml,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Update timer visual progres secara dinamis
                    let currentPct = 15;
                    const stageLabel = document.getElementById('dbfStageLabel');
                    const progressPct = document.getElementById('dbfProgressPct');
                    const progressBar = document.getElementById('dbfProgressBar');
                    const step1 = document.getElementById('dbfStep1');
                    const step2 = document.getElementById('dbfStep2');
                    const step3 = document.getElementById('dbfStep3');
                    const step4 = document.getElementById('dbfStep4');

                    const updateStep = (stepEl, icon, color, isBold) => {
                        if (!stepEl) return;
                        stepEl.querySelector('.step-icon').innerHTML = icon;
                        stepEl.style.color = color;
                        stepEl.style.fontWeight = isBold ? '600' : 'normal';
                    };

                    const timer = setInterval(() => {
                        if (currentPct < 40) {
                            currentPct += 5;
                        } else if (currentPct < 70) {
                            // Masuk tahap 2
                            updateStep(step1, '✅', '#16a34a', false);
                            updateStep(step2, '⏳', '#2563eb', true);
                            if (stageLabel) stageLabel.textContent = 'Membaca struktur tabel & record DBF...';
                            currentPct += 3;
                        } else if (currentPct < 90) {
                            // Masuk tahap 3
                            updateStep(step2, '✅', '#16a34a', false);
                            updateStep(step3, '⏳', '#2563eb', true);
                            if (stageLabel) stageLabel.textContent = willAutoSync ? 'Menyinkronkan data (~70.000 record) ke database...' : 'Menyimpan konfigurasi berkas...';
                            currentPct += 1.5;
                        } else if (currentPct < 96) {
                            updateStep(step3, '⏳', '#2563eb', true);
                            currentPct += 0.5;
                        }

                        if (progressBar) progressBar.style.width = Math.min(currentPct, 96) + '%';
                        if (progressPct) progressPct.textContent = Math.round(Math.min(currentPct, 96)) + '%';
                    }, 500);

                    // Simpan interval pada instance Swal agar dapat dibersihkan
                    Swal._dbfTimer = timer;
                }
            });

            // Eksekusi AJAX Request
            const formData = new FormData(form);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(token ? { 'X-CSRF-TOKEN': token } : {})
                }
            })
            .then(async (response) => {
                if (Swal._dbfTimer) clearInterval(Swal._dbfTimer);

                const data = await response.json().catch(() => null);

                if (!response.ok) {
                    const errorMsg = (data && data.message) ? data.message : 'Terjadi kesalahan pada server saat memproses file DBF (Kode status: ' + response.status + ').';
                    throw new Error(errorMsg);
                }

                if (!data || !data.success) {
                    throw new Error((data && data.message) ? data.message : 'Gagal memproses file database SIMGAJI.');
                }

                // Tuntaskan visual ke 100%
                const stageLabel = document.getElementById('dbfStageLabel');
                const progressPct = document.getElementById('dbfProgressPct');
                const progressBar = document.getElementById('dbfProgressBar');
                const step3 = document.getElementById('dbfStep3');
                const step4 = document.getElementById('dbfStep4');

                if (step3) {
                    step3.querySelector('.step-icon').innerHTML = '✅';
                    step3.style.color = '#16a34a';
                    step3.style.fontWeight = 'normal';
                }
                if (step4) {
                    step4.querySelector('.step-icon').innerHTML = '✅';
                    step4.style.color = '#16a34a';
                    step4.style.fontWeight = '600';
                }
                if (stageLabel) stageLabel.textContent = 'Pemrosesan berkas selesai!';
                if (progressPct) progressPct.textContent = '100%';
                if (progressBar) progressBar.style.width = '100%';

                setTimeout(() => {
                    window.showDbfUploadSuccessResult(data);
                }, 600);
            })
            .catch((error) => {
                if (Swal._dbfTimer) clearInterval(Swal._dbfTimer);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memproses Berkas DBF',
                    text: error.message || 'Terjadi kesalahan sistem atau kendala jaringan saat mengunggah.',
                    confirmButtonText: 'Tutup & Coba Lagi',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    };

    /**
     * Handler untuk form Sinkronisasi DBF manual
     */
    window.attachDbfSyncHandler = function(formSelector) {
        const forms = typeof formSelector === 'string' ? document.querySelectorAll(formSelector) : [formSelector];
        forms.forEach(form => {
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const label = form.getAttribute('data-label') || 'Data SIMGAJI';

                Swal.fire({
                    title: 'Mulai Sinkronisasi Database?',
                    html: `Sistem akan membaca record dari berkas DBF aktif dan memperbarui <strong>${label}</strong> ke dalam basis data sistem.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ph-bold ph-arrows-clockwise"></i> Ya, Sinkronkan Sekarang',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Sedang Menyinkronkan Data...',
                        html: `
                            <div style="font-size: 13px; line-height: 1.5; color: #475569; padding: 4px;">
                                <p style="margin-bottom: 10px;">Sedang memproses pembaruan <strong>${label}</strong> dari berkas DBF ke database MySQL...</p>
                                <div style="display: flex; justify-content: center; margin: 12px 0;">
                                    <div class="dbf-progress-active" style="width: 100%; height: 6px; border-radius: 999px;"></div>
                                </div>
                                <small style="color: #64748b;">Harap tunggu beberapa saat hingga proses selesai.</small>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData(form);
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            ...(token ? { 'X-CSRF-TOKEN': token } : {})
                        }
                    })
                    .then(async (response) => {
                        const data = await response.json().catch(() => null);
                        if (!response.ok || !data || !data.success) {
                            throw new Error((data && data.message) ? data.message : 'Gagal melakukan sinkronisasi data.');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Selesai!',
                            html: `<div style="font-size: 13px; color: #1e293b;">${data.message}</div>`,
                            confirmButtonText: 'Segarkan Halaman',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Sinkronisasi Gagal',
                            text: err.message || 'Terjadi kesalahan sistem saat menyinkronkan data.',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                });
            });
        });
    };
})();
</script>
