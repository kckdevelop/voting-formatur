@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center items-center px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border border-slate-100 text-center">
        
        <!-- Big Check Icon Animation -->
        <div class="mx-auto w-24 h-24 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center shadow-xl shadow-emerald-500/30 mb-6 animate-pulse">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Voting Berhasil!
        </h2>

        <p class="text-emerald-700 font-bold text-sm mt-2">
            Terima kasih, {{ $student->nama }}!
        </p>

        <p class="text-slate-600 text-xs sm:text-sm mt-3 leading-relaxed">
            Suara Anda telah berhasil dicatat secara rahasia dan sah ke dalam sistem e-voting.
        </p>

        <div class="my-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            Anda sudah menggunakan hak pilih untuk Pemilihan Ketua & Formatur IPM.
        </div>

        <a href="{{ route('student.logout.get') }}" 
            class="mt-4 block w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-extrabold rounded-2xl shadow-lg transition text-center">
            Selesai & Keluar Aplikasi
        </a>
    </div>
</div>
@endsection
