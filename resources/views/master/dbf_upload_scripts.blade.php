{{-- Reusable DBF Upload & Sync Progress Modal Scripts with Real-Time Data Counters --}}
<style>
    @keyframes dbfPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.85; transform: scale(1.02); }
    }
    @keyframes dbfShimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes dbfDotBlink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
    .dbf-progress-active {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 50%, #60a5fa 100%) !important;
        background-size: 200% 100% !important;
        animation: dbfShimmer 1.5s infinite linear !important;
    }
    .dbf-dot-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        animation: dbfDotBlink 1.2s infinite ease-in-out;
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
                                    ? '<span style="color: #16a34a; display: inline-flex; align-items: center; gap: 4px;">✔️ Otomatis Tersinkron (' + formattedRecords + ' data)</span>' 
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
     * Handler submit form upload DBF dengan indikator status langkah demi langkah, progress bar,
     * serta penghitung REAL-TIME data masuk & data tersisa.
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

            // Template progress dialog dengan REAL-TIME DATA COUNTER
            const progressHtml = `
                <div style="text-align: left; font-size: 12.5px; line-height: 1.5; color: #1e293b;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
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

                    <!-- Progress Bar Utama -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 5px;">
                            <span style="font-weight: 600; color: #475569;" id="dbfStageLabel">Mengunggah berkas ke server...</span>
                            <span style="font-weight: 700; color: #2563eb;" id="dbfProgressPct">10%</span>
                        </div>
                        <div style="width: 100%; height: 9px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                            <div id="dbfProgressBar" class="dbf-progress-active" style="width: 10%; height: 100%; border-radius: 999px; transition: width 0.35s ease;"></div>
                        </div>
                    </div>

                    <!-- KOTAK INFORMASI REAL-TIME: DATA MASUK & SISA DATA -->
                    ${willAutoSync ? `
                    <div id="dbfSyncDetailBox" style="background: rgba(37, 99, 235, 0.04); border: 1px solid rgba(37, 99, 235, 0.22); border-radius: 9px; padding: 10px 12px; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 11.5px; color: #1e293b; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                <span class="dbf-dot-indicator"></span>
                                Progres Input Basis Data (Live)
                            </span>
                            <span id="dbfSyncStatusText" style="font-size: 11px; color: #2563eb; font-weight: 600;">Menyiapkan sinkronisasi...</span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div style="background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="color: #166534; font-size: 10.5px; font-weight: 700;">📥 DATA MASUK (TERSINKRON)</div>
                                <div id="dbfInsertedCount" style="font-weight: 800; color: #15803d; font-size: 15px; margin-top: 2px;">0 data</div>
                            </div>
                            <div style="background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="color: #9a3412; font-size: 10.5px; font-weight: 700;">⏳ SISA DATA BERKAS</div>
                                <div id="dbfRemainingCount" style="font-weight: 800; color: #c2410c; font-size: 15px; margin-top: 2px;">0 data</div>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 7px; font-size: 11px; color: #64748b;">
                            <span>Total Baris Berkas: <strong id="dbfTotalTarget" style="color: #1e293b;">Sedang dihitung...</strong></span>
                            <span>Progres Database: <strong id="dbfDbPct" style="color: #2563eb; font-weight: 700;">0%</strong></span>
                        </div>
                    </div>` : ''}

                    <!-- Checklist 4 Tahapan Proses -->
                    <div style="display: flex; flex-direction: column; gap: 7px; font-size: 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
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

                    <div style="margin-top: 12px; text-align: center; font-size: 11px; color: #dc2626; display: flex; align-items: center; justify-content: center; gap: 6px;">
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
                    const stageLabel = document.getElementById('dbfStageLabel');
                    const progressPct = document.getElementById('dbfProgressPct');
                    const progressBar = document.getElementById('dbfProgressBar');
                    const step1 = document.getElementById('dbfStep1');
                    const step2 = document.getElementById('dbfStep2');
                    const step3 = document.getElementById('dbfStep3');
                    const step4 = document.getElementById('dbfStep4');
                    const insertedEl = document.getElementById('dbfInsertedCount');
                    const remainingEl = document.getElementById('dbfRemainingCount');
                    const targetEl = document.getElementById('dbfTotalTarget');
                    const dbPctEl = document.getElementById('dbfDbPct');
                    const syncStatusText = document.getElementById('dbfSyncStatusText');

                    const updateStep = (stepEl, icon, color, isBold) => {
                        if (!stepEl) return;
                        stepEl.querySelector('.step-icon').innerHTML = icon;
                        stepEl.style.color = color;
                        stepEl.style.fontWeight = isBold ? '700' : 'normal';
                    };

                    // Initial simulated stages for upload & parse (0-20%)
                    let initPct = 12;
                    const stageTimer = setInterval(() => {
                        if (initPct < 22) {
                            initPct += 3;
                            if (progressBar) progressBar.style.width = initPct + '%';
                            if (progressPct) progressPct.textContent = initPct + '%';
                        }
                    }, 400);

                    // POLLING PROGRES REAL-TIME KE SERVER
                    const progressUrl = '{{ route("master.simgaji_dbf.progress") }}';
                    const pollProgress = () => {
                        fetch(progressUrl, { cache: 'no-store' })
                            .then(res => res.json())
                            .then(pData => {
                                if (!pData) return;

                                if (pData.status === 'syncing' || pData.status === 'done') {
                                    clearInterval(stageTimer);
                                    updateStep(step1, '✅', '#16a34a', false);
                                    updateStep(step2, '✅', '#16a34a', false);

                                    const isDone = pData.status === 'done';
                                    updateStep(step3, isDone ? '✅' : '⏳', isDone ? '#16a34a' : '#2563eb', !isDone);

                                    const current = Number(pData.current || 0);
                                    const total = Number(pData.total || 0);
                                    const remaining = Number(pData.remaining !== undefined ? pData.remaining : Math.max(0, total - current));
                                    const rawPercent = total > 0 ? ((current / total) * 100) : (pData.percent || 0);
                                    const syncPercent = Math.min(100, Math.round(rawPercent));

                                    if (insertedEl) insertedEl.textContent = current.toLocaleString('id-ID') + ' data';
                                    if (remainingEl) remainingEl.textContent = remaining.toLocaleString('id-ID') + ' data';
                                    if (targetEl && total > 0) targetEl.textContent = total.toLocaleString('id-ID') + ' data';
                                    if (dbPctEl) dbPctEl.textContent = syncPercent + '%';

                                    if (syncStatusText) {
                                        syncStatusText.textContent = isDone ? 'Selesai disinkronkan' : `Menyimpan batch (${syncPercent}%)...`;
                                    }

                                    // Hitung overall progress bar (20% s/d 97%)
                                    const overallPct = total > 0 ? Math.min(97, Math.max(22, Math.round(20 + (syncPercent * 0.77)))) : 30;
                                    if (progressBar) progressBar.style.width = (isDone ? 98 : overallPct) + '%';
                                    if (progressPct) progressPct.textContent = (isDone ? 98 : overallPct) + '%';

                                    if (stageLabel) {
                                        if (isDone) {
                                            stageLabel.textContent = `Sinkronisasi selesai (${current.toLocaleString('id-ID')} data tersimpan)...`;
                                        } else if (total > 0) {
                                            stageLabel.textContent = `Menyinkronkan data: ${current.toLocaleString('id-ID')} / ${total.toLocaleString('id-ID')} (Sisa ${remaining.toLocaleString('id-ID')})...`;
                                        } else {
                                            stageLabel.textContent = pData.message || 'Menyinkronkan data ke tabel basis data...';
                                        }
                                    }
                                }
                            })
                            .catch(() => {});
                    };

                    const pollInterval = setInterval(pollProgress, 750);
                    Swal._dbfPollTimer = pollInterval;
                    Swal._dbfStageTimer = stageTimer;
                }
            });

            // Eksekusi AJAX Request Upload & Sync
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
                if (Swal._dbfPollTimer) clearInterval(Swal._dbfPollTimer);
                if (Swal._dbfStageTimer) clearInterval(Swal._dbfStageTimer);

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
                const insertedEl = document.getElementById('dbfInsertedCount');
                const remainingEl = document.getElementById('dbfRemainingCount');
                const dbPctEl = document.getElementById('dbfDbPct');
                const syncStatusText = document.getElementById('dbfSyncStatusText');

                if (step3) {
                    step3.querySelector('.step-icon').innerHTML = '✅';
                    step3.style.color = '#16a34a';
                    step3.style.fontWeight = 'normal';
                }
                if (step4) {
                    step4.querySelector('.step-icon').innerHTML = '✅';
                    step4.style.color = '#16a34a';
                    step4.style.fontWeight = '700';
                }

                const finalRecords = Number(data.records || 0);
                if (insertedEl && finalRecords > 0) insertedEl.textContent = finalRecords.toLocaleString('id-ID') + ' data';
                if (remainingEl) remainingEl.textContent = '0 data';
                if (dbPctEl) dbPctEl.textContent = '100%';
                if (syncStatusText) syncStatusText.textContent = 'Selesai 100%';

                if (stageLabel) stageLabel.textContent = 'Pemrosesan berkas selesai sepenuhnya!';
                if (progressPct) progressPct.textContent = '100%';
                if (progressBar) progressBar.style.width = '100%';

                setTimeout(() => {
                    window.showDbfUploadSuccessResult(data);
                }, 600);
            })
            .catch((error) => {
                if (Swal._dbfPollTimer) clearInterval(Swal._dbfPollTimer);
                if (Swal._dbfStageTimer) clearInterval(Swal._dbfStageTimer);

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
     * Handler untuk form Sinkronisasi DBF manual dengan penghitung REAL-TIME data masuk & sisa
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

                    const syncModalHtml = `
                        <div style="font-size: 13px; line-height: 1.5; color: #1e293b; text-align: left; padding: 4px;">
                            <p style="margin-bottom: 10px; color: #475569;">
                                Sedang memproses pembaruan <strong>${label}</strong> dari berkas DBF ke database MySQL...
                            </p>

                            <!-- Progress Bar -->
                            <div style="margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 5px;">
                                    <span style="font-weight: 600; color: #475569;" id="dbfManualStageLabel">Menyiapkan pembacaan DBF...</span>
                                    <span style="font-weight: 700; color: #2563eb;" id="dbfManualProgressPct">0%</span>
                                </div>
                                <div style="width: 100%; height: 9px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                    <div id="dbfManualProgressBar" class="dbf-progress-active" style="width: 5%; height: 100%; border-radius: 999px; transition: width 0.35s ease;"></div>
                                </div>
                            </div>

                            <!-- Live Counter Box -->
                            <div style="background: rgba(37, 99, 235, 0.04); border: 1px solid rgba(37, 99, 235, 0.22); border-radius: 9px; padding: 10px 12px; margin-bottom: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <span style="font-size: 11.5px; color: #1e293b; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                        <span class="dbf-dot-indicator"></span>
                                        Progres Input Basis Data (Live)
                                    </span>
                                    <span id="dbfManualStatusText" style="font-size: 11px; color: #2563eb; font-weight: 600;">Memproses...</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <div style="background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <div style="color: #166534; font-size: 10.5px; font-weight: 700;">📥 DATA MASUK</div>
                                        <div id="dbfManualInserted" style="font-weight: 800; color: #15803d; font-size: 15px; margin-top: 2px;">0 data</div>
                                    </div>
                                    <div style="background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <div style="color: #9a3412; font-size: 10.5px; font-weight: 700;">⏳ SISA DATA</div>
                                        <div id="dbfManualRemaining" style="font-weight: 800; color: #c2410c; font-size: 15px; margin-top: 2px;">0 data</div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 7px; font-size: 11px; color: #64748b;">
                                    <span>Total Target: <strong id="dbfManualTarget" style="color: #1e293b;">Sedang dihitung...</strong></span>
                                    <span>Persentase: <strong id="dbfManualDbPct" style="color: #2563eb; font-weight: 700;">0%</strong></span>
                                </div>
                            </div>

                            <small style="display: block; text-align: center; color: #94a3b8; font-size: 11px;">
                                Harap tunggu, proses batch berlangsung aman di latar belakang.
                            </small>
                        </div>
                    `;

                    Swal.fire({
                        title: '<div style="font-size: 17px; font-weight: 700; color: #0f172a;">Menyinkronkan Basis Data</div>',
                        html: syncModalHtml,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            const stageLabel = document.getElementById('dbfManualStageLabel');
                            const progressPct = document.getElementById('dbfManualProgressPct');
                            const progressBar = document.getElementById('dbfManualProgressBar');
                            const insertedEl = document.getElementById('dbfManualInserted');
                            const remainingEl = document.getElementById('dbfManualRemaining');
                            const targetEl = document.getElementById('dbfManualTarget');
                            const dbPctEl = document.getElementById('dbfManualDbPct');
                            const statusText = document.getElementById('dbfManualStatusText');

                            const progressUrl = '{{ route("master.simgaji_dbf.progress") }}';
                            const pollProgress = () => {
                                fetch(progressUrl, { cache: 'no-store' })
                                    .then(res => res.json())
                                    .then(pData => {
                                        if (!pData) return;

                                        if (pData.status === 'syncing' || pData.status === 'done') {
                                            const current = Number(pData.current || 0);
                                            const total = Number(pData.total || 0);
                                            const remaining = Number(pData.remaining !== undefined ? pData.remaining : Math.max(0, total - current));
                                            const rawPercent = total > 0 ? ((current / total) * 100) : (pData.percent || 0);
                                            const syncPercent = Math.min(100, Math.round(rawPercent));

                                            if (insertedEl) insertedEl.textContent = current.toLocaleString('id-ID') + ' data';
                                            if (remainingEl) remainingEl.textContent = remaining.toLocaleString('id-ID') + ' data';
                                            if (targetEl && total > 0) targetEl.textContent = total.toLocaleString('id-ID') + ' data';
                                            if (dbPctEl) dbPctEl.textContent = syncPercent + '%';
                                            if (progressBar) progressBar.style.width = Math.min(98, syncPercent) + '%';
                                            if (progressPct) progressPct.textContent = syncPercent + '%';

                                            if (statusText) {
                                                statusText.textContent = pData.status === 'done' ? 'Selesai disinkronkan' : `Menyimpan batch (${syncPercent}%)...`;
                                            }
                                            if (stageLabel) {
                                                if (pData.status === 'done') {
                                                    stageLabel.textContent = `Sinkronisasi selesai (${current.toLocaleString('id-ID')} data tersimpan)...`;
                                                } else if (total > 0) {
                                                    stageLabel.textContent = `Menyinkronkan data: ${current.toLocaleString('id-ID')} / ${total.toLocaleString('id-ID')} (Sisa ${remaining.toLocaleString('id-ID')})...`;
                                                }
                                            }
                                        }
                                    })
                                    .catch(() => {});
                            };

                            const pollInterval = setInterval(pollProgress, 750);
                            Swal._dbfManualPollTimer = pollInterval;
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
                        if (Swal._dbfManualPollTimer) clearInterval(Swal._dbfManualPollTimer);

                        const data = await response.json().catch(() => null);
                        if (!response.ok || !data || !data.success) {
                            throw new Error((data && data.message) ? data.message : 'Gagal melakukan sinkronisasi data.');
                        }

                        // Set counter ke 100%
                        const progressBar = document.getElementById('dbfManualProgressBar');
                        const progressPct = document.getElementById('dbfManualProgressPct');
                        if (progressBar) progressBar.style.width = '100%';
                        if (progressPct) progressPct.textContent = '100%';

                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sinkronisasi Selesai!',
                                html: `<div style="font-size: 13px; color: #1e293b;">${data.message}</div>`,
                                confirmButtonText: 'Segarkan Halaman',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                window.location.reload();
                            });
                        }, 400);
                    })
                    .catch((err) => {
                        if (Swal._dbfManualPollTimer) clearInterval(Swal._dbfManualPollTimer);

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
