@extends('layouts.admin', ['headerTitle' => 'Dashboard Panitia Pemilihan'])

@section('content')
<div class="space-y-8" x-data="{ resetModalOpen: false, confirmInput: '' }">
    
    <!-- Election Status Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Status Pemilihan Saat Ini:</span>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider 
                    {{ $electionStatus === 'open' ? 'bg-emerald-500 text-white animate-pulse' : ($electionStatus === 'paused' ? 'bg-amber-500 text-white' : ($electionStatus === 'closed' ? 'bg-rose-600 text-white' : 'bg-slate-700 text-slate-200')) }}">
                    {{ strtoupper($electionStatus) }}
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-1">
                Pemilihan Ketua & Formatur IPM
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
                Batas Pilihan Maksimal: <strong class="text-emerald-400">{{ $maxChoices }} Calon Formatur</strong> per siswa.
            </p>
        </div>

        <!-- Status Change Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.status.update') }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="open">
                <button type="submit" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center {{ $electionStatus === 'open' ? 'bg-emerald-600 text-white ring-2 ring-emerald-400' : 'bg-slate-800 text-emerald-400 hover:bg-slate-700' }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>
                    Buka Voting
                </button>
            </form>

            <form action="{{ route('admin.status.update') }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="paused">
                <button type="submit" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center {{ $electionStatus === 'paused' ? 'bg-amber-600 text-white ring-2 ring-amber-400' : 'bg-slate-800 text-amber-400 hover:bg-slate-700' }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pause Voting
                </button>
            </form>

            <form action="{{ route('admin.status.update') }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="closed">
                <button type="submit" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center {{ $electionStatus === 'closed' ? 'bg-rose-600 text-white ring-2 ring-rose-400' : 'bg-slate-800 text-rose-400 hover:bg-slate-700' }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    Tutup Voting
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Students -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Total Siswa</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-slate-900">{{ number_format($totalStudents) }}</div>
                <p class="text-xs text-slate-500 mt-1 font-semibold">{{ $activeStudents }} siswa aktif terdaftar</p>
            </div>
        </div>

        <!-- Sudah Voting -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Sudah Voting</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-emerald-600">{{ number_format($votedStudents) }}</div>
                <p class="text-xs text-slate-500 mt-1 font-semibold">Tercatat masuk di database</p>
            </div>
        </div>

        <!-- Belum Voting -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Belum Voting</span>
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-rose-600">{{ number_format($unvotedStudents) }}</div>
                <p class="text-xs text-slate-500 mt-1 font-semibold">Siswa aktif belum menggunakan hak pilih</p>
            </div>
        </div>

        <!-- Participation Rate -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Partisipasi</span>
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-purple-700">{{ $participationRate }}%</div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-2">
                    <div class="bg-purple-600 h-full rounded-full" style="width: {{ min(100, $participationRate) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links & Reset Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <a href="{{ route('admin.results') }}" class="p-6 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-3xl shadow-lg hover:shadow-xl transition flex items-center justify-between group">
            <div>
                <h3 class="text-lg font-extrabold">Lihat Diagram Live</h3>
                <p class="text-xs text-amber-100 mt-0.5">Hasil voting realtime & perolehan suara</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </a>

        <a href="{{ route('admin.students.qr-cards') }}" class="p-6 bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-3xl shadow-lg hover:shadow-xl transition flex items-center justify-between group">
            <div>
                <h3 class="text-lg font-extrabold">Cetak Kartu QR Siswa</h3>
                <p class="text-xs text-emerald-100 mt-0.5">Kartu pemilih berisi NIS & QR Code</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
            </div>
        </a>

        <button type="button" @click="resetModalOpen = true" class="p-6 bg-white border-2 border-rose-200 text-rose-700 hover:bg-rose-50 rounded-3xl shadow-sm hover:shadow-md transition text-left flex items-center justify-between group">
            <div>
                <h3 class="text-lg font-extrabold">Reset Data Voting</h3>
                <p class="text-xs text-rose-500 mt-0.5">Hapus seluruh suara & reset status siswa</p>
            </div>
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center group-hover:scale-110 transition text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
        </button>
    </div>

    <!-- Recent Audit Logs -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Aktivitas Terbaru (Audit Log)
            </h3>
            <a href="{{ route('admin.audit-logs') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-2">Waktu</th>
                        <th class="py-3 px-2">User</th>
                        <th class="py-3 px-2">Aksi</th>
                        <th class="py-3 px-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="py-3 px-2 font-mono text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-2 font-bold text-slate-900 whitespace-nowrap">{{ $log->user }}</td>
                            <td class="py-3 px-2">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] bg-slate-100 text-slate-800">{{ $log->action }}</span>
                            </td>
                            <td class="py-3 px-2 text-slate-600">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada catatan aktivitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reset Votes Modal -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="resetModalOpen = false"></div>
            
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10 space-y-4 border border-rose-100">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>

                <h3 class="text-lg font-extrabold text-slate-900">Konfirmasi Reset Seluruh Suara</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tindakan ini akan <strong>MENGHAPUS SEMUA RECORD SUARA MASUK</strong> dan mengembalikan status seluruh siswa menjadi <strong>Belum Voting</strong>. Tindakan ini tidak dapat dibatalkan!
                </p>

                <form action="{{ route('admin.reset-votes') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-rose-700 mb-1">
                            Ketik: RESET-PEMILIHAN
                        </label>
                        <input type="text" name="confirm_text" x-model="confirmInput" required placeholder="RESET-PEMILIHAN"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-rose-500">
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="resetModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">
                            Batal
                        </button>
                        <button type="submit" :disabled="confirmInput !== 'RESET-PEMILIHAN'"
                            :class="confirmInput === 'RESET-PEMILIHAN' ? 'bg-rose-600 text-white' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                            class="px-5 py-2 text-xs font-extrabold rounded-xl transition">
                            Hapus & Reset Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
