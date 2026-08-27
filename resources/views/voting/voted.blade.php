@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center items-center px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border border-slate-100 text-center">
        
        <div class="mx-auto w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Anda Sudah Memilih
        </h2>

        <p class="text-slate-600 text-sm mt-3">
            Halo <strong>{{ $student->nama }}</strong> (Kelas: {{ $student->kelas }}), Anda telah menggunakan hak pilih Anda pada:
        </p>

        <p class="text-xs font-bold text-emerald-700 mt-1">
            {{ $student->voted_at ? $student->voted_at->translatedFormat('l, d F Y - H:i') : 'Selesai' }} WIB
        </p>

        <div class="my-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium">
            Satu siswa hanya dapat melakukan pemungutan suara satu kali. Pilihan Anda sudah tersimpan dengan aman.
        </div>

        <form action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit" 
                class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-extrabold rounded-2xl shadow-lg transition">
                Keluar Dari Aplikasi
            </button>
        </form>
    </div>
</div>
@endsection
