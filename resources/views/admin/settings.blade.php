@extends('layouts.admin', ['headerTitle' => 'Pengaturan Aplikasi Pemilihan'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <h2 class="text-base font-extrabold text-slate-900 mb-1">Pengaturan Umum & Branding</h2>
        <p class="text-xs text-slate-500 mb-6">Konfigurasi nama sekolah, judul kegiatan pemilihan, dan batasan suara formatur.</p>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Sekolah</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $schoolName) }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-emerald-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Kegiatan Pemilihan</label>
                    <input type="text" name="election_name" value="{{ old('election_name', $electionName) }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-emerald-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tahun / Periode Pemilihan</label>
                    <input type="text" name="election_year" value="{{ old('election_year', $electionYear) }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-emerald-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Jumlah Maksimal Pilihan Formatur</label>
                    <input type="number" name="max_choices" value="{{ old('max_choices', $maxChoices) }}" required min="1" max="50"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-600">
                    <p class="text-[10px] text-slate-400 mt-1">Default = 9. Setiap siswa wajib memilih tepat jumlah calon ini.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Batasan Waktu Sesi Voting (Menit)</label>
                    <input type="number" name="voting_timeout_minutes" value="{{ old('voting_timeout_minutes', $votingTimeout ?? 5) }}" required min="1" max="60"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-amber-700 focus:ring-2 focus:ring-amber-600">
                    <p class="text-[10px] text-slate-400 mt-1">Default = 5 menit. Jika waktu habis, siswa otomatis di-logout ke halaman login.</p>
                </div>
            </div>

            <!-- Logo Upload -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Logo Sekolah / Kegiatan</label>
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl p-2 border border-slate-200 flex items-center justify-center flex-shrink-0">
                        @if($logoPath)
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                        @else
                            <svg class="w-8 h-8 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15 8L21 9L17 14L18 20L12 17L6 20L7 14L3 9L9 8L12 2Z"/></svg>
                        @endif
                    </div>
                    <div class="flex-grow">
                        <input type="file" name="logo" accept="image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl text-xs">
                        <p class="text-[10px] text-slate-400 mt-1">Format JPG, PNG, WEBP, atau SVG. Ukuran maks 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Publikasikan Hasil Voting</h4>
                        <p class="text-[11px] text-slate-500">Jika diaktifkan, hasil voting dapat diakses umum jika fitur dipublikasikan.</p>
                    </div>
                    <input type="checkbox" name="public_results" value="1" {{ $publicResults ? 'checked' : '' }} class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded cursor-pointer">
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Tampilkan Tombol Visi & Misi</h4>
                        <p class="text-[11px] text-slate-500">Jika diaktifkan, tombol "Visi & Misi" akan ditampilkan pada kartu calon di halaman voting siswa.</p>
                    </div>
                    <input type="checkbox" name="show_visi_misi" value="1" {{ $showVisiMisi ? 'checked' : '' }} class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded cursor-pointer">
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-2xl shadow-md transition">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
