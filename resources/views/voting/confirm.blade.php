@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="confirmComponent({{ $remainingSeconds }})">
    
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-emerald-900 px-6 sm:px-8 py-8 text-white relative">
            <div class="text-center max-w-lg mx-auto">
                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-extrabold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 mb-3">
                    Tahap 2: Konfirmasi Pemilihan
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Konfirmasi Pilihan Suara Anda
                </h2>
                <p class="text-xs sm:text-sm text-emerald-200 mt-1">
                    Anda telah memilih tepat {{ count($selectedCandidates) }} calon formatur. Silakan periksa kembali daftar pilihan Anda sebelum mengirimkan suara.
                </p>
            </div>
        </div>

        <!-- Floating Session Countdown Badge (Stays Fixed on Bottom-Right Corner) -->
        <div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 flex items-center space-x-2.5 px-4 py-2.5 rounded-2xl border transition-all duration-300 shadow-2xl backdrop-blur-md"
             :class="timerSeconds < 60 ? 'bg-rose-600/95 text-white border-rose-500 animate-pulse shadow-rose-600/40' : 'bg-slate-900/95 text-white border-slate-700/80 shadow-slate-950/40'">
            <svg class="w-5 h-5 text-amber-400 flex-shrink-0 animate-spin" style="animation-duration: 6s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-right">
                <div class="text-[9px] uppercase font-extrabold tracking-wider" :class="timerSeconds < 60 ? 'text-rose-100' : 'text-slate-400'">Sisa Waktu Voting</div>
                <div class="text-sm font-black font-mono leading-none tracking-wider mt-0.5" x-text="formatTimer()">00:00</div>
            </div>
        </div>

        <div class="p-6 sm:p-10 space-y-8">
            
            <!-- Warning Alert -->
            <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-5 flex items-start space-x-4">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Peringatan Penting</h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Setelah Anda menekan tombol <strong>"Kirim Suara"</strong>, suara Anda akan langsung dicatat secara permanen di database dan pilihan tidak dapat diubah atau dikembalikan lagi.
                    </p>
                </div>
            </div>

            <!-- Selected Candidates List -->
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Daftar {{ count($selectedCandidates) }} Calon Formatur Yang Anda Pilih:
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($selectedCandidates as $index => $candidate)
                        <div class="flex items-center space-x-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-200 shadow-sm hover:border-emerald-300 transition">
                            <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                {{ sprintf('%02d', $candidate->nomor_urut) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-extrabold text-slate-900 truncate">
                                    {{ $candidate->nama }}
                                </p>
                                <p class="text-xs text-emerald-700 font-semibold">
                                    Kelas: {{ $candidate->kelas }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('student.voting') }}"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3.5 border border-slate-300 rounded-2xl text-xs font-extrabold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali & Ubah Pilihan
                </a>

                <button type="button" @click="submitModalOpen = true"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 border border-transparent rounded-2xl text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-emerald-800 hover:from-emerald-700 hover:to-emerald-900 shadow-xl shadow-emerald-700/30 transition duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Kirim Suara Sekarang
                </button>
            </div>

        </div>
    </div>

    <!-- Final Submission Confirmation Modal -->
    <div x-show="submitModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="submitModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="submitModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="submitModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 p-6 sm:p-8">
                
                <div class="w-16 h-16 bg-emerald-100 text-emerald-700 rounded-3xl mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <h3 class="text-xl font-extrabold text-slate-900 text-center tracking-tight">Kirim Suara Pemilihan?</h3>
                <p class="text-xs text-slate-500 text-center mt-2 leading-relaxed">
                    Apakah Anda yakin ingin mengirim suara ini? Pilihan <strong class="text-slate-800">tidak dapat diubah atau dibatalkan</strong> setelah ini.
                </p>

                <form action="{{ route('student.voting.submit') }}" method="POST" class="mt-6 flex items-center justify-between gap-3">
                    @csrf
                    @foreach($selectedIds as $id)
                        <input type="hidden" name="candidates[]" value="{{ $id }}">
                    @endforeach

                    <button type="button" @click="submitModalOpen = false" class="w-1/2 py-3 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-2xl transition text-center">
                        Batal
                    </button>

                    <button type="submit" class="w-1/2 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-2xl shadow-lg shadow-emerald-600/30 transition text-center">
                        Ya, Kirim Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmComponent(remainingSeconds) {
    return {
        submitModalOpen: false,
        timerSeconds: remainingSeconds || 300,
        timerInterval: null,

        init() {
            this.startTimer();
        },

        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.timerSeconds > 0) {
                    this.timerSeconds--;
                } else {
                    clearInterval(this.timerInterval);
                    alert("Waktu sesi voting Anda telah habis. Anda akan dikembalikan ke halaman login.");
                    window.location.href = "{{ route('student.login') }}";
                }
            }, 1000);
        },

        formatTimer() {
            const m = Math.floor(this.timerSeconds / 60);
            const s = this.timerSeconds % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
    };
}
</script>
@endpush
