@extends('layouts.admin', ['headerTitle' => '⚙️ Atur Hasil Perolehan Suara'])

@section('content')
<div class="space-y-6" x-data="{ batchMode: false, confirmReset: null }">

    {{-- WARNING BANNER --}}
    <div class="bg-rose-900 border border-rose-700 rounded-3xl p-5 flex items-start gap-4 shadow-lg">
        <div class="w-10 h-10 bg-rose-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-base font-black text-white tracking-tight">HALAMAN TERLARANG — AKSES RAHASIA</h2>
            <p class="text-sm text-rose-200 mt-1 leading-relaxed">
                Halaman ini digunakan untuk mengatur perolehan suara secara manual. Setiap perubahan <strong>dicatat di Audit Log</strong>.
                Gunakan hanya jika benar-benar diperlukan sebagai antisipasi hasil yang tidak sesuai.
            </p>
        </div>
    </div>

    {{-- STATS ROW --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Total Calon</p>
            <p class="text-3xl font-black text-slate-900">{{ count($candidates) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Total Voter Aktif</p>
            <p class="text-3xl font-black text-blue-600">{{ $totalStudents }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Sudah Memilih</p>
            <p class="text-3xl font-black text-emerald-600">{{ $totalVoters }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Total Suara Masuk</p>
            <p class="text-3xl font-black text-amber-600">{{ $totalVotes }}</p>
        </div>
    </div>

    {{-- MODE TOGGLE + BATCH FORM --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Kontrol Perolehan Suara</h3>
                <p class="text-xs text-slate-500 mt-0.5">Atur suara per calon satu persatu, atau gunakan mode <strong>Batch</strong> untuk mengubah semua sekaligus.</p>
            </div>
            <button type="button" @click="batchMode = !batchMode"
                :class="batchMode ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span x-text="batchMode ? 'Mode Batch: ON' : 'Mode Batch: OFF'"></span>
            </button>
        </div>

        {{-- BATCH FORM (semua calon sekaligus) --}}
        <div x-show="batchMode" x-cloak>
            <form action="{{ route('admin.atur-hasil.update-all') }}" method="POST" class="space-y-4"
                onsubmit="return confirm('⚠️ Yakin ingin mengubah suara semua calon sekaligus? Tindakan ini dicatat di Audit Log.')">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($candidates as $candidate)
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shadow flex-shrink-0">
                                {{ sprintf('%02d', $candidate->nomor_urut) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $candidate->nama }}</p>
                                <p class="text-xs text-slate-500">{{ $candidate->kelas }}</p>
                            </div>
                        </div>

                        {{-- Mini bar --}}
                        @php $pct = $candidate->percentage; @endphp
                        <div class="w-full bg-slate-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-emerald-500 transition-all"
                                style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 text-center">Saat ini: <strong class="text-slate-800">{{ $candidate->vote_details_count }}</strong> suara ({{ $pct }}%)</p>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Target Suara</label>
                            <input type="number" name="suara[{{ $candidate->id }}]"
                                value="{{ $candidate->vote_details_count }}"
                                min="0" max="9999" required
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm rounded-2xl shadow-md transition">
                        💾 Simpan Semua Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- INDIVIDUAL CARDS --}}
        <div x-show="!batchMode">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($candidates as $candidate)
                @php
                    $pct = $candidate->percentage;
                    $barColor = match(true) {
                        $pct >= 50 => 'bg-emerald-500',
                        $pct >= 25 => 'bg-amber-500',
                        default    => 'bg-rose-400',
                    };
                @endphp
                <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition">
                    {{-- Foto / Header --}}
                    <div class="relative">
                        <div class="w-full h-36 bg-slate-100 overflow-hidden">
                            @if($candidate->foto)
                                <img src="{{ asset('storage/' . $candidate->foto) }}"
                                    alt="{{ $candidate->nama }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-14 h-14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <span class="absolute top-3 left-3 w-8 h-8 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shadow">
                            {{ sprintf('%02d', $candidate->nomor_urut) }}
                        </span>
                    </div>

                    <div class="p-4 space-y-3">
                        {{-- Info --}}
                        <div class="text-center">
                            <h4 class="font-extrabold text-slate-900 text-sm leading-tight">{{ $candidate->nama }}</h4>
                            <p class="text-xs text-emerald-600 font-semibold">{{ $candidate->kelas }}</p>
                        </div>

                        {{-- Progress Bar --}}
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-slate-500 font-semibold">Perolehan</span>
                                <span class="text-xs font-black text-slate-700">{{ $candidate->vote_details_count }} suara ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $barColor }} transition-all duration-500"
                                    style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                        </div>

                        {{-- Input Form --}}
                        <form action="{{ route('admin.atur-hasil.update', $candidate->id) }}" method="POST"
                            onsubmit="return confirm('Ubah suara {{ $candidate->nama }} ke nilai yang dimasukkan?')">
                            @csrf
                            @method('PATCH')
                            <div class="flex gap-2 items-end">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Set Suara</label>
                                    <input type="number" name="jumlah_suara"
                                        value="{{ $candidate->vote_details_count }}"
                                        min="0" max="9999" required
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-rose-400">
                                </div>
                                <button type="submit"
                                    class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition shadow text-xs font-black flex-shrink-0"
                                    title="Simpan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </form>

                        {{-- Reset --}}
                        <form action="{{ route('admin.atur-hasil.reset', $candidate->id) }}" method="POST"
                            onsubmit="return confirm('⚠️ Reset semua suara {{ $candidate->nama }} ke 0?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full py-1.5 text-xs font-bold text-rose-500 hover:text-white hover:bg-rose-500 border border-rose-200 hover:border-rose-500 rounded-xl transition">
                                Reset ke 0
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- BACK LINK --}}
    <div class="flex justify-start">
        <a href="{{ route('admin.dashboard') }}"
            class="text-xs font-bold text-slate-400 hover:text-slate-600 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection
