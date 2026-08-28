@extends('layouts.app')

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8" x-data="studentLoginHandler()">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo Header -->
        <div class="flex justify-center">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-3xl p-3.5 shadow-xl shadow-emerald-700/20 flex items-center justify-center transform hover:rotate-3 transition duration-300">
                @if(isset($logoPath) && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo SMK" class="max-h-full max-w-full object-contain">
                @else
                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L15 8L21 9L17 14L18 20L12 17L6 20L7 14L3 9L9 8L12 2Z" />
                    </svg>
                @endif
            </div>
        </div>

        <h2 class="mt-5 text-center text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Pemilihan Ketua IPM
        </h2>
        <p class="mt-1.5 text-center text-sm font-semibold text-emerald-700">
            {{ $schoolName ?? 'SMK Muhammadiyah 1 Bantul' }}
        </p>
        <p class="mt-1 text-center text-xs text-slate-500">
            Silakan login untuk memberikan suara Anda secara sah dan rahasia
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-3xl sm:px-10 border border-slate-100">
            
            @if($electionStatus !== 'open')
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium text-center">
                    <svg class="w-6 h-6 text-amber-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Voting saat ini sedang {{ strtoupper($electionStatus) }}. Anda belum dapat melakukan pemilihan.
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <!-- Manual NIS + Token Form -->
            <form action="{{ route('student.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="nis" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Nomor Induk Siswa (NIS)
                    </label>
                    <div class="mt-1.5 relative rounded-2xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
                        </div>
                        <input id="nis" name="nis" type="text" required value="{{ old('nis') }}"
                            placeholder="Masukkan NIS Anda"
                            class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label for="token" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Token Pemilihan
                    </label>
                    <div class="mt-1.5 relative rounded-2xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"></path></svg>
                        </div>
                        <input id="token" name="token" type="password" required
                            placeholder="Masukkan Token (cth: ABC123XYZ)"
                            class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition">
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-2xl shadow-lg shadow-emerald-700/20 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition duration-200">
                    Login Siswa
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs font-semibold uppercase tracking-wider">
                    <span class="bg-white px-3 text-slate-400">Atau</span>
                </div>
            </div>

            <!-- Login QR Button -->
            <div class="mt-6">
                <button type="button" @click="openQrModal()"
                    class="w-full flex items-center justify-center py-3.5 px-4 border border-emerald-600/30 rounded-2xl text-sm font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition duration-200">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Scan QR Code Kartu Pemilih
                </button>
            </div>

            <!-- Admin Link -->
            <div class="mt-8 text-center border-t border-slate-100 pt-4">
                <a href="{{ route('admin.login') }}" class="text-xs font-semibold text-slate-400 hover:text-emerald-700 transition">
                    Akses Halaman Panitia / Admin &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- QR Code Scanner Modal -->
    <div x-show="qrModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="qrModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="closeQrModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="qrModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-800 to-emerald-900 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                        <h3 class="text-base font-bold">Scan QR Code Siswa</h3>
                    </div>
                    <button type="button" @click="closeQrModal()" class="text-emerald-200 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6">

                    <!-- ===== PRIMARY ACTION: Native Camera / Galeri (Always works, bypasses Permissions-Policy) ===== -->
                    <div class="mb-5">
                        <p class="text-xs font-bold text-slate-700 mb-2 text-center">Pilih cara scan QR Code kartu pemilih:</p>

                        <!-- Primary: Ambil Foto via Kamera Native -->
                        <label for="qr-file-input"
                            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold rounded-2xl cursor-pointer flex items-center justify-center transition shadow-md shadow-emerald-200 mb-2">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Ambil Foto / Upload QR Code
                        </label>
                        <!-- input capture=environment → langsung buka kamera native HP, tidak butuh getUserMedia -->
                        <input id="qr-file-input" type="file" accept="image/*" capture="environment" class="hidden" @change="handleFileUpload($event)">
                        <p class="text-[10px] text-slate-400 text-center">Ketuk tombol hijau → arahkan kamera HP ke QR Code → foto → selesai</p>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                        <div class="relative flex justify-center"><span class="bg-white px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">atau gunakan live scanner (jika tersedia)</span></div>
                    </div>

                    <!-- ===== SECONDARY: Live Camera Scanner ===== -->
                    <!-- Scanner Video Container (hidden when camera fails to prevent empty space) -->
                    <div id="reader" x-show="!cameraFailed" class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200" style="min-height: 200px;"></div>

                    <!-- Status Message & Retry Action -->
                    <div x-show="qrMessage" class="mt-3 p-3 rounded-2xl text-xs font-semibold text-center leading-relaxed" :class="qrSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200'">
                        <p x-text="qrMessage" style="white-space: pre-line;"></p>
                        <template x-if="!qrSuccess && !isHttp">
                            <button type="button" @click="initScanner()" class="mt-2 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold rounded-xl shadow-sm transition inline-flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Coba Live Scanner Lagi
                            </button>
                        </template>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex justify-end">
                    <button type="button" @click="closeQrModal()" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function studentLoginHandler() {
    return {
        qrModalOpen: false,
        qrScanner: null,
        qrMessage: '',
        qrSuccess: false,
        cameraFailed: false,
        isHttp: window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1',

        openQrModal() {
            this.qrModalOpen = true;
            this.qrMessage = '';
            this.cameraFailed = false;
            this.$nextTick(() => {
                this.initScanner();
            });
        },

        closeQrModal() {
            this.qrModalOpen = false;
            this.stopAndClear().catch(() => {});
        },

        /**
         * Stop active scanner and clear DOM. Returns a Promise.
         */
        stopAndClear() {
            const self = this;
            const scanner = self.qrScanner;
            self.qrScanner = null;

            // Clear the #reader div manually to prevent "element already in use" errors
            const readerEl = document.getElementById('reader');
            if (readerEl) readerEl.innerHTML = '';

            if (!scanner) return Promise.resolve();

            try {
                return scanner.stop()
                    .then(() => { try { scanner.clear(); } catch (e) {} })
                    .catch(() => { try { scanner.clear(); } catch (e) {} });
            } catch (e) {
                try { scanner.clear(); } catch (e) {}
                return Promise.resolve();
            }
        },

        initScanner() {
            const self = this;

            if (typeof Html5Qrcode === 'undefined') {
                self.qrMessage = 'Library QR Scanner tidak dapat dimuat. Pastikan koneksi internet stabil lalu muat ulang halaman.';
                self.qrSuccess = false;
                self.cameraFailed = true;
                return;
            }

            if (self.isHttp) {
                self.qrMessage = 'Kamera live memerlukan HTTPS. Gunakan tombol "Upload / Ambil Foto QR Code" di bawah sebagai alternatif.';
                self.qrSuccess = false;
                self.cameraFailed = true;
                return;
            }

            self.qrMessage = 'Meminta izin akses kamera...';
            self.qrSuccess = true;
            self.cameraFailed = false;

            self.stopAndClear().then(() => {
                self.startCameraStream();
            });
        },

        startCameraStream() {
            const self = this;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                self.handleCameraError({ name: 'NotSupportedError' });
                return;
            }

            // Clear DOM once more to be safe
            const readerEl = document.getElementById('reader');
            if (readerEl) readerEl.innerHTML = '';

            const html5QrCode = new Html5Qrcode("reader");
            self.qrScanner = html5QrCode;

            const qrboxFn = (w, h) => {
                const edge = Math.floor(Math.min(w, h) * 0.78);
                return { width: Math.max(edge, 180), height: Math.max(edge, 180) };
            };
            const config = { fps: 10, qrbox: qrboxFn, aspectRatio: 1.0 };

            // Strategy: Try facingMode "environment" (rear camera) directly first.
            // Direct start triggers native getUserMedia browser permission dialog without failing on getCameras() pre-check.
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => self.processQrPayload(decodedText),
                () => {}
            )
            .then(() => {
                self.qrMessage = '✅ Kamera aktif! Arahkan ke QR Code pada kartu pemilih.';
                self.qrSuccess = true;
                self.cameraFailed = false;
            })
            .catch((err1) => {
                console.warn('First attempt (facingMode environment) failed, trying user camera fallback...', err1);

                // Fallback 1: Try facingMode "user" (for laptops/desktops with front camera)
                self.stopAndClear().then(() => {
                    if (document.getElementById('reader')) document.getElementById('reader').innerHTML = '';
                    const html5QrCode2 = new Html5Qrcode("reader");
                    self.qrScanner = html5QrCode2;

                    return html5QrCode2.start(
                        { facingMode: "user" },
                        config,
                        (decodedText) => self.processQrPayload(decodedText),
                        () => {}
                    );
                })
                .then(() => {
                    self.qrMessage = '✅ Kamera aktif! Arahkan ke QR Code pada kartu pemilih.';
                    self.qrSuccess = true;
                    self.cameraFailed = false;
                })
                .catch((err2) => {
                    console.warn('Second attempt (facingMode user) failed, trying getCameras fallback...', err2);

                    const finalErr = err2 || err1;
                    const errName = (finalErr && finalErr.name) ? finalErr.name : String(finalErr);
                    if (errName === 'NotAllowedError' || errName === 'PermissionDeniedError') {
                        self.handleCameraError(finalErr);
                        return;
                    }

                    // Fallback 2: Try explicit device enumeration via getCameras()
                    self.stopAndClear().then(() => {
                        if (document.getElementById('reader')) document.getElementById('reader').innerHTML = '';
                        return Html5Qrcode.getCameras();
                    })
                    .then(devices => {
                        if (devices && devices.length > 0) {
                            const html5QrCode3 = new Html5Qrcode("reader");
                            self.qrScanner = html5QrCode3;

                            return html5QrCode3.start(
                                devices[0].id,
                                config,
                                (decodedText) => self.processQrPayload(decodedText),
                                () => {}
                            );
                        } else {
                            throw finalErr;
                        }
                    })
                    .then(() => {
                        self.qrMessage = '✅ Kamera aktif! Arahkan ke QR Code pada kartu pemilih.';
                        self.qrSuccess = true;
                        self.cameraFailed = false;
                    })
                    .catch((err3) => {
                        console.error('All camera start attempts failed:', err3);
                        self.handleCameraError(err3 || finalErr);
                    });
                });
            });
        },

        handleCameraError(err) {
            const rawErrStr = (err && err.message) ? `${err.name || 'Error'}: ${err.message}` : (err && err.name ? err.name : String(err || ''));
            const errName = (err && err.name) ? err.name : (typeof err === 'string' ? err : '');
            let msg = '';

            if (this.isHttp) {
                msg = '🔒 Kamera memerlukan HTTPS. Hubungi panitia untuk mengaktifkan HTTPS, atau gunakan tombol "Upload Foto QR" di atas.';
            } else if (errName === 'NotAllowedError' || errName === 'PermissionDeniedError') {
                msg = '🚫 Izin kamera ditolak oleh Browser / Windows OS.\n\nJika izin Chrome sudah "Izinkan", pastikan Pengaturan Windows → Privasi → Kamera → "Izinkan aplikasi desktop mengakses kamera" sudah AKTIF.';
            } else if (errName === 'NotFoundError' || errName === 'DevicesNotFoundError') {
                msg = '📵 Kamera tidak ditemukan di perangkat ini. Gunakan tombol "Upload / Ambil Foto QR Code" di atas.';
            } else if (errName === 'NotReadableError' || errName === 'TrackStartError') {
                msg = '⚠️ Kamera sedang digunakan aplikasi lain (mis. Zoom / WhatsApp / Meet) atau terkunci oleh sistem. Tutup aplikasi lain lalu tekan "Coba Lagi".';
            } else if (errName === 'NotSupportedError' || errName === 'TypeError') {
                msg = '❌ Browser ini tidak mendukung akses kamera live. Gunakan Chrome/Safari versi terbaru, atau gunakan tombol "Upload Foto QR" di atas.';
            } else if (errName === 'OverconstrainedError') {
                msg = '📷 Kamera tidak mendukung konfigurasi yang diminta. Tekan "Coba Lagi" atau gunakan tombol "Upload Foto QR" di atas.';
            } else {
                msg = '❌ Tidak dapat mengakses kamera live.';
            }

            if (rawErrStr && rawErrStr !== '[object Object]' && !msg.includes(rawErrStr)) {
                msg += '\n\n(Detail Error: ' + rawErrStr + ')';
            }

            this.qrMessage = msg;
            this.qrSuccess = false;
            this.cameraFailed = true;
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const self = this;
            self.qrMessage = '⏳ Membaca gambar QR Code...';
            self.qrSuccess = true;

            const doScanFile = () => {
                const readerEl = document.getElementById('reader');
                if (readerEl) readerEl.innerHTML = '';

                const fileScanner = new Html5Qrcode("reader");
                fileScanner.scanFile(file, /* showImage= */ true)
                    .then(decodedText => {
                        try { fileScanner.clear(); } catch(e) {}
                        self.processQrPayload(decodedText);
                    })
                    .catch(() => {
                        try { fileScanner.clear(); } catch(e) {}
                        self.qrMessage = '❌ QR Code tidak terdeteksi dari foto ini.\n\nPastikan:\n• Foto cukup terang & fokus\n• Seluruh QR Code terlihat penuh\n• Gambar tidak buram';
                        self.qrSuccess = false;
                    });
            };

            // Stop live camera first if running, then scan file
            self.stopAndClear().then(() => {
                doScanFile();
            }).catch(() => {
                doScanFile();
            });

            // Reset input so the same file can be re-selected
            event.target.value = '';
        },

        processQrPayload(rawPayload) {
            const self = this;
            self.qrMessage = '⏳ Memproses data QR Code...';
            self.qrSuccess = true;

            if (self.qrScanner) {
                try { self.qrScanner.pause(); } catch(e) {}
            }

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                self.qrMessage = '❌ Token CSRF tidak ditemukan. Muat ulang halaman lalu coba lagi.';
                self.qrSuccess = false;
                return;
            }

            fetch('{{ route("student.login.qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
                },
                body: JSON.stringify({ qr_payload: rawPayload })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.qrMessage = '✅ Login berhasil! Mengarahkan ke halaman voting...';
                    self.qrSuccess = true;
                    self.stopAndClear().catch(() => {});
                    setTimeout(() => { window.location.href = data.redirect; }, 600);
                } else {
                    self.qrMessage = '❌ ' + (data.message || 'QR Code tidak valid.');
                    self.qrSuccess = false;
                    if (self.qrScanner) try { self.qrScanner.resume(); } catch(e) {}
                }
            })
            .catch(() => {
                self.qrMessage = '❌ Gagal terhubung ke server. Periksa koneksi internet lalu coba lagi.';
                self.qrSuccess = false;
                if (self.qrScanner) try { self.qrScanner.resume(); } catch(e) {}
            });
        }
    };
}
</script>
@endpush


