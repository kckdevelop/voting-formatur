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
                    <p class="text-xs text-slate-500 mb-3 text-center">
                        Arahkan kamera ke QR Code yang tertera pada kartu pemilih Anda.
                    </p>

                    <!-- HTTPS Warning Banner -->
                    <template x-if="isHttp">
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-[11px] font-semibold flex items-start space-x-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span>
                                <strong>Info Akses Kamera HP:</strong> Browser HP membutuhkan koneksi aman (<strong>HTTPS://</strong>) untuk membuka kamera live. Jika menggunakan HTTP, silakan gunakan tombol <strong>"Upload / Ambil Foto QR Code"</strong> di bawah.
                            </span>
                        </div>
                    </template>

                    <!-- Scanner Video Container -->
                    <div id="reader" class="w-full bg-slate-100 rounded-2xl overflow-hidden min-h-[240px] border border-slate-200"></div>

                    <!-- Status Message & Retry Action -->
                    <div x-show="qrMessage" class="mt-4 p-3.5 rounded-2xl text-xs font-semibold text-center leading-relaxed" :class="qrSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                        <p x-text="qrMessage"></p>
                        <template x-if="!qrSuccess && !isHttp">
                            <button type="button" @click="initScanner()" class="mt-2.5 px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-sm transition inline-flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Coba Minta Izin Kamera Lagi
                            </button>
                        </template>
                    </div>

                    <!-- Alternative Fallback: File / Photo Upload -->
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col items-center">
                        <label for="qr-file-input" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-700 text-xs font-bold rounded-xl cursor-pointer flex items-center justify-center transition shadow-sm border border-slate-200">
                            <svg class="w-4 h-4 mr-2 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Upload / Ambil Foto QR Code
                        </label>
                        <input id="qr-file-input" type="file" accept="image/*" capture="environment" class="hidden" @change="handleFileUpload($event)">
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan ini jika kamera live tidak muncul atau terblokir di HP</p>
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
        isHttp: window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1',

        openQrModal() {
            this.qrModalOpen = true;
            this.qrMessage = '';
            this.$nextTick(() => {
                this.initScanner();
            });
        },

        closeQrModal() {
            this.qrModalOpen = false;
            if (this.qrScanner) {
                try {
                    this.qrScanner.stop().then(() => {
                        this.qrScanner.clear();
                    }).catch(err => console.error(err));
                } catch(e) {}
            }
        },

        initScanner() {
            const self = this;
            if (typeof Html5Qrcode === 'undefined') {
                self.qrMessage = 'Library QR Scanner tidak dapat dimuat. Pastikan koneksi internet terhubung.';
                self.qrSuccess = false;
                return;
            }

            self.qrMessage = 'Meminta izin akses kamera browser...';
            self.qrSuccess = true;

            if (self.qrScanner) {
                try {
                    self.qrScanner.stop().catch(() => {}).finally(() => {
                        self.startCameraStream();
                    });
                    return;
                } catch(e) {}
            }

            self.startCameraStream();
        },

        startCameraStream() {
            const self = this;
            const html5QrCode = new Html5Qrcode("reader");
            self.qrScanner = html5QrCode;
            const config = { fps: 10, qrbox: { width: 220, height: 220 } };

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                self.handleCameraError("Browser HP Anda tidak mendukung streaming kamera live (API MediaDevices tidak tersedia atau situs diakses via HTTP).");
                return;
            }

            // Step 1: Explicitly request native browser camera permission
            navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: "environment" } } })
                .catch(() => navigator.mediaDevices.getUserMedia({ video: true }))
                .then((stream) => {
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }

                    // Step 2: Start Html5Qrcode scanner with environment camera
                    return html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => self.processQrPayload(decodedText),
                        () => {}
                    );
                })
                .then(() => {
                    self.qrMessage = 'Kamera aktif! Arahkan kamera ke QR Code.';
                    self.qrSuccess = true;
                })
                .catch((err) => {
                    console.warn("Direct facingMode failed, trying getCameras fallback...", err);
                    
                    return Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length > 0) {
                            const backCamera = devices.find(d => 
                                d.label.toLowerCase().includes('back') || 
                                d.label.toLowerCase().includes('rear') || 
                                d.label.toLowerCase().includes('lingkungan') ||
                                d.label.toLowerCase().includes('0')
                            ) || devices[devices.length - 1];

                            return html5QrCode.start(
                                backCamera.id,
                                config,
                                (decodedText) => self.processQrPayload(decodedText),
                                () => {}
                            ).then(() => {
                                self.qrMessage = 'Kamera aktif! Arahkan kamera ke QR Code.';
                                self.qrSuccess = true;
                            });
                        } else {
                            throw err;
                        }
                    });
                })
                .catch((err) => {
                    self.handleCameraError(err);
                });
        },

        handleCameraError(err) {
            let errorMsg = 'Tidak dapat mengakses kamera live. ';
            if (this.isHttp) {
                errorMsg += 'Situs ini diakses melalui HTTP (bukan HTTPS). Gunakan URL https:// atau pakai tombol "Upload / Ambil Foto QR Code" di bawah.';
            } else {
                errorMsg += 'Izinkan akses kamera pada popup browser HP Anda atau gunakan tombol "Upload / Ambil Foto QR Code" di bawah.';
            }
            this.qrMessage = errorMsg;
            this.qrSuccess = false;
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const self = this;
            self.qrMessage = 'Membaca gambar QR Code...';
            self.qrSuccess = true;

            if (!self.qrScanner) {
                self.qrScanner = new Html5Qrcode("reader");
            }

            self.qrScanner.scanFile(file, true)
                .then(decodedText => {
                    self.processQrPayload(decodedText);
                })
                .catch(err => {
                    self.qrMessage = 'QR Code tidak terdeteksi dari foto. Pastikan foto QR Code jelas, fokus, dan tidak buram.';
                    self.qrSuccess = false;
                });
        },

        processQrPayload(rawPayload) {
            const self = this;
            self.qrMessage = 'Memproses data QR Code...';
            self.qrSuccess = true;

            if (self.qrScanner) {
                try { self.qrScanner.pause(); } catch(e) {}
            }

            fetch('{{ route("student.login.qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ qr_payload: rawPayload })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.qrMessage = 'Login berhasil! Mengarahkan ke halaman voting...';
                    self.qrSuccess = true;
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    self.qrMessage = data.message || 'Login QR Code gagal.';
                    self.qrSuccess = false;
                    if (self.qrScanner) try { self.qrScanner.resume(); } catch(e) {}
                }
            })
            .catch(err => {
                self.qrMessage = 'Terjadi kesalahan sistem saat memproses QR Code.';
                self.qrSuccess = false;
                if (self.qrScanner) try { self.qrScanner.resume(); } catch(e) {}
            });
        }
    };
}
</script>
@endpush
