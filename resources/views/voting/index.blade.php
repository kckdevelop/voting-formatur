@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28" x-data="votingComponent({{ $maxChoices }}, {{ json_encode($candidates) }}, {{ $remainingSeconds }})">
    
    <!-- Compact Top Announcement Header -->
    <div class="mb-6 pb-4 border-b border-slate-200">
        <div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 mb-1">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tahap 1: Pilih Calon Formatur
            </span>
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                Silakan Pilih Tepat {{ $maxChoices }} Calon Formatur
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Klik pada kartu calon untuk memilih atau membatalkan pilihan.
            </p>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center">
            <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Candidates Grid (5 columns per row on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3.5 sm:gap-4">
        @foreach($candidates as $candidate)
            <div 
                @click="toggleSelect({{ $candidate->id }})"
                :class="{
                    'ring-4 ring-emerald-600 bg-emerald-50/40 shadow-xl border-emerald-500 scale-[1.02]': isSelected({{ $candidate->id }}),
                    'bg-white border-slate-200 shadow-md hover:border-emerald-300 hover:shadow-lg': !isSelected({{ $candidate->id }}) && !isMaxReached(),
                    'bg-slate-50 opacity-50 border-slate-200 cursor-not-allowed': !isSelected({{ $candidate->id }}) && isMaxReached()
                }"
                class="relative rounded-2xl border p-3.5 transition duration-300 flex flex-col justify-between cursor-pointer group select-none">
                
                <!-- Candidate Photo with Integrated Top-Left Number & Top-Right Checkmark -->
                <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-100 mb-2.5 border border-slate-200/60 shadow-inner">
                    <!-- Integrated Order Number Badge (Pojok Kiri Atas Foto) -->
                    <div class="absolute top-2 left-2 z-10 w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-900/90 text-white font-black text-xs sm:text-sm flex items-center justify-center shadow-lg border border-white/20 backdrop-blur-md">
                        {{ sprintf('%02d', $candidate->nomor_urut) }}
                    </div>

                    <!-- Integrated Checkmark Badge (Pojok Kanan Atas Foto) -->
                    <div x-show="isSelected({{ $candidate->id }})" x-cloak
                        class="absolute top-2 right-2 z-10 w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-lg animate-bounce border border-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    @if($candidate->foto)
                        <img src="{{ asset('storage/' . $candidate->foto) }}" alt="{{ $candidate->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-gradient-to-b from-slate-100 to-slate-200">
                            <svg class="w-14 h-14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                    @endif
                </div>

                <!-- Candidate Name & Class -->
                <div class="text-center mb-2.5">
                    <h3 class="font-extrabold text-slate-900 text-xs sm:text-sm leading-tight group-hover:text-emerald-700 transition line-clamp-1" title="{{ $candidate->nama }}">
                        {{ $candidate->nama }}
                    </h3>
                    <p class="text-[11px] font-semibold text-emerald-600 mt-0.5">
                        Kelas: {{ $candidate->kelas }}
                    </p>
                </div>

                <!-- Vision & Mission Preview Button (Diatur dari Pengaturan Admin) -->
                @if($showVisiMisi && ($candidate->visi || $candidate->misi))
                    <button type="button" 
                        @click.stop="openModal({{ json_encode($candidate) }})"
                        class="w-full py-1.5 px-2 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px] sm:text-[11px] font-bold rounded-lg transition flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="truncate">Visi & Misi</span>
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Compact Sticky Bottom Floating Bar -->
    <div class="fixed bottom-4 inset-x-4 max-w-4xl mx-auto z-40 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl p-3 sm:px-5 sm:py-3 shadow-2xl border border-slate-800/80 flex items-center justify-between gap-3">
        <!-- Counter Info -->
        <div class="flex items-center space-x-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-black text-xs sm:text-sm flex-shrink-0">
                <span x-text="selected.length">0</span>/{{ $maxChoices }}
            </div>
            <div class="min-w-0 hidden sm:block">
                <div class="text-[10px] uppercase font-bold text-slate-400 leading-none">Pilihan Anda</div>
                <div class="text-xs font-extrabold mt-1 text-white leading-tight">
                    <span x-text="selected.length === maxChoices ? 'Kuota Pilihan Terpenuhi!' : 'Pilih ' + (maxChoices - selected.length) + ' calon lagi'"></span>
                </div>
            </div>
        </div>

        <!-- Center: Countdown Timer Badge -->
        <div class="flex items-center space-x-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl border transition-all duration-300 shadow-sm flex-shrink-0"
             :class="timerSeconds < 60 ? 'bg-rose-600/90 text-white border-rose-500 animate-pulse' : 'bg-slate-800/80 text-white border-slate-700/80'">
            <svg class="w-4 h-4 text-amber-400 flex-shrink-0 animate-spin" style="animation-duration: 6s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-right leading-none">
                <div class="text-[8px] sm:text-[9px] uppercase font-extrabold tracking-wider" :class="timerSeconds < 60 ? 'text-rose-100' : 'text-slate-400'">Sisa Waktu</div>
                <div class="text-xs sm:text-sm font-black font-mono tracking-wider mt-0.5" x-text="formatTimer()">00:00</div>
            </div>
        </div>

        <!-- Submit Button: Triggers Confirm Modal -->
        <div class="flex-shrink-0">
            <button type="button" 
                @click="openConfirmModal()"
                :disabled="selected.length !== maxChoices"
                :class="selected.length === maxChoices 
                    ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white shadow-lg shadow-emerald-700/40 cursor-pointer transform hover:-translate-y-0.5 ring-2 ring-emerald-400/30' 
                    : 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700 opacity-75'"
                class="px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl font-extrabold text-xs flex items-center transition duration-200">
                <span class="hidden sm:inline">Lanjutkan Konfirmasi</span>
                <span class="sm:hidden">Konfirmasi</span>
                <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>
    </div>

    <!-- Vision & Mission Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="bg-gradient-to-r from-emerald-800 to-emerald-900 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-xl bg-white text-emerald-800 font-bold text-sm flex items-center justify-center" x-text="activeCandidate ? activeCandidate.nomor_urut : ''"></span>
                        <div>
                            <h3 class="text-base font-bold" x-text="activeCandidate ? activeCandidate.nama : ''"></h3>
                            <p class="text-xs text-emerald-200" x-text="activeCandidate ? 'Kelas: ' + activeCandidate.kelas : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-emerald-200 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 mb-1">Visi</h4>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-sm text-slate-700 whitespace-pre-line" x-text="activeCandidate && activeCandidate.visi ? activeCandidate.visi : 'Tidak ada visi.'"></div>
                    </div>

                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 mb-1">Misi</h4>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-sm text-slate-700 whitespace-pre-line" x-text="activeCandidate && activeCandidate.misi ? activeCandidate.misi : 'Tidak ada misi.'"></div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex justify-end">
                    <button type="button" @click="modalOpen = false" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Overlay -->
    <div x-show="confirmModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="confirm-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="confirmModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="confirmModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="confirmModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-emerald-900 px-6 py-6 text-white">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 mb-2">
                        Tahap 2: Konfirmasi Pemilihan
                    </span>
                    <h3 class="text-xl font-extrabold tracking-tight" id="confirm-modal-title">
                        Konfirmasi Pilihan Suara Anda
                    </h3>
                    <p class="text-xs text-emerald-200 mt-1">
                        Anda telah memilih tepat <strong class="text-white font-bold" x-text="selected.length"></strong> calon formatur. Periksa kembali sebelum mengirim.
                    </p>
                </div>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Warning Box -->
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start space-x-3">
                        <div class="p-1.5 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-amber-900">Peringatan Penting</h4>
                            <p class="text-[11px] text-amber-800 mt-0.5">
                                Suara Anda akan langsung dicatat secara permanen di database. Pilihan <strong>tidak dapat diubah</strong> setelah ini.
                            </p>
                        </div>
                    </div>

                    <!-- Selected Candidates List Grid -->
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Daftar Calon Formatur Yang Anda Pilih:
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template x-for="candidate in getSelectedCandidates()" :key="candidate.id">
                                <div class="flex items-center space-x-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center flex-shrink-0" x-text="String(candidate.nomor_urut).padStart(2, '0')"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-extrabold text-slate-900 truncate" x-text="candidate.nama"></p>
                                        <p class="text-[10px] text-emerald-700 font-semibold" x-text="'Kelas: ' + candidate.kelas"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Form & Submit Actions -->
                <form action="{{ route('student.voting.submit') }}" method="POST" class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    @csrf
                    
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="candidates[]" :value="id">
                    </template>

                    <button type="button" @click="confirmModalOpen = false" class="w-full sm:w-auto px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
                        &larr; Batal & Ubah Pilihan
                    </button>

                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-emerald-700/30 transition duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Kirim Suara Sekarang
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function votingComponent(maxChoices, candidatesList, remainingSeconds) {
    return {
        maxChoices: maxChoices,
        candidatesList: candidatesList || [],
        selected: [],
        modalOpen: false,
        confirmModalOpen: false,
        activeCandidate: null,
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
        },

        isSelected(id) {
            return this.selected.includes(id);
        },

        isMaxReached() {
            return this.selected.length >= this.maxChoices;
        },

        toggleSelect(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                if (this.selected.length < this.maxChoices) {
                    this.selected.push(id);
                }
            }
        },

        getSelectedCandidates() {
            return this.candidatesList.filter(c => this.selected.includes(c.id)).sort((a, b) => a.nomor_urut - b.nomor_urut);
        },

        openModal(candidate) {
            this.activeCandidate = candidate;
            this.modalOpen = true;
        },

        openConfirmModal() {
            if (this.selected.length !== this.maxChoices) {
                alert(`Anda harus memilih tepat ${this.maxChoices} calon formatur sebelum melanjutkan.`);
                return;
            }
            this.confirmModalOpen = true;
        }
    };
}
</script>
@endpush
