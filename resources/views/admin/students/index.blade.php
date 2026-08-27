@extends('layouts.admin', ['headerTitle' => 'Manajemen Data Siswa / Voter'])

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, importModalOpen: false, activeStudent: null }">
    
    <!-- Top Action Bar & Filters -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto flex-grow">
            <!-- Search Input -->
            <div class="relative min-w-[200px] flex-grow sm:flex-grow-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS / Nama..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Kelas Filter -->
            <select name="kelas" onchange="this.form.submit()" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-emerald-600">
                <option value="">-- Semua Kelas --</option>
                @foreach($kelases as $k)
                    <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>

            <!-- Voted Filter -->
            <select name="voted" onchange="this.form.submit()" class="py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-emerald-600">
                <option value="">-- Status Voting --</option>
                <option value="1" {{ request('voted') === '1' ? 'selected' : '' }}>Sudah Voting</option>
                <option value="0" {{ request('voted') === '0' ? 'selected' : '' }}>Belum Voting</option>
            </select>

            @if(request()->anyFilled(['search', 'kelas', 'voted', 'status']))
                <a href="{{ route('admin.students.index') }}" class="py-2.5 px-3 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-2xl text-xs font-bold transition">
                    Reset Filter
                </a>
            @endif
        </form>

        <!-- Right Buttons -->
        <div class="flex items-center space-x-2 w-full md:w-auto justify-end">
            <a href="{{ route('admin.students.qr-cards', ['kelas' => request('kelas')]) }}" class="px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 text-xs font-bold rounded-2xl transition flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Cetak Kartu QR
            </a>

            <a href="{{ route('admin.students.export') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </a>

            <button type="button" @click="importModalOpen = true" class="px-4 py-2.5 bg-green-50 hover:bg-green-100 text-green-800 border border-green-200 text-xs font-bold rounded-2xl transition flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4-4m-4 4V4"></path></svg>
                Import Excel
            </button>

            <form action="{{ route('admin.students.bulk-regenerate-tokens') }}" method="POST" onsubmit="return confirm('Regenerate token secara massal untuk seluruh siswa?')">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold rounded-2xl transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Regenerate Token Massal
                </button>
            </form>

            <button type="button" @click="addModalOpen = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-md transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Siswa
            </button>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-extrabold uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-4">#</th>
                        <th class="py-4 px-4">NIS</th>
                        <th class="py-4 px-4">Nama Siswa</th>
                        <th class="py-4 px-4">Kelas</th>
                        <th class="py-4 px-4">Token (Plain)</th>
                        <th class="py-4 px-4">Status Akun</th>
                        <th class="py-4 px-4">Status Voting</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($students as $index => $student)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $students->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $student->nis }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $student->nama }}</td>
                            <td class="py-3 px-4 font-semibold text-emerald-700">{{ $student->kelas }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-600 bg-slate-50 rounded-lg">
                                {{ $student->plain_token ?? '••••••••' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $student->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($student->has_voted)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 inline-flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Sudah Voting
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500">
                                        Belum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <!-- Regenerate Single Token -->
                                <form action="{{ route('admin.students.regenerate-token', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Regenerate token siswa ini?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition" title="Regenerate Token">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </form>

                                <!-- Reset Vote Status -->
                                @if($student->has_voted)
                                    <form action="{{ route('admin.students.reset-vote', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Reset status voting siswa ini menjadi Belum Voting?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition" title="Reset Status Voting">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                @endif

                                <!-- Edit -->
                                <button type="button" @click="activeStudent = {{ json_encode($student) }}; editModalOpen = true" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Edit Siswa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                <!-- Delete -->
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus siswa ini dari sistem?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-slate-400">Tidak ada data siswa yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
    </div>

    <!-- Add Student Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="addModalOpen = false"></div>
            
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl z-10 space-y-4">
                <h3 class="text-lg font-extrabold text-slate-900">Tambah Siswa Baru</h3>
                
                <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">NIS</label>
                        <input type="text" name="nis" required placeholder="Cth: 123456" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required placeholder="Nama Siswa" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kelas</label>
                        <input type="text" name="kelas" required placeholder="Cth: XI TKJ 1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-extrabold rounded-xl hover:bg-emerald-700">Simpan Siswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
            
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl z-10 space-y-4" x-show="activeStudent">
                <h3 class="text-lg font-extrabold text-slate-900">Edit Data Siswa</h3>
                
                <form :action="'/admin/students/' + (activeStudent ? activeStudent.id : '')" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">NIS</label>
                        <input type="text" name="nis" :value="activeStudent ? activeStudent.nis : ''" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" :value="activeStudent ? activeStudent.nama : ''" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kelas</label>
                        <input type="text" name="kelas" :value="activeStudent ? activeStudent.kelas : ''" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Status Akun</label>
                        <select name="status" :value="activeStudent ? activeStudent.status : 'active'" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-extrabold rounded-xl hover:bg-emerald-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Import Excel Modal -->
    <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="importModalOpen = false"></div>

            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10 space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Import Data Siswa</h3>
                        <p class="text-xs text-slate-500">Upload file Excel (.xlsx / .xls)</p>
                    </div>
                </div>

                {{-- Format Info --}}
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2">
                    <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Format Kolom Excel (Baris 1 = Header)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">Kolom A</p>
                            <p class="text-xs font-black text-slate-800">NIS</p>
                            <p class="text-[10px] text-slate-400">Wajib</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">Kolom B</p>
                            <p class="text-xs font-black text-slate-800">Nama</p>
                            <p class="text-[10px] text-slate-400">Wajib</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">Kolom C</p>
                            <p class="text-xs font-black text-slate-800">Kelas</p>
                            <p class="text-[10px] text-slate-400">Wajib</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Token akan digenerate otomatis. Lewati baris 1 (header).</p>

                    {{-- Download Template Button --}}
                    <a href="{{ route('admin.students.import.template') }}"
                        class="mt-2 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Template Excel (.xlsx)
                    </a>
                </div>

                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">File Excel</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-green-100 file:text-green-700 hover:file:bg-green-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Mode Import</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-start gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition">
                                <input type="radio" name="mode" value="append" checked class="mt-0.5 accent-green-600">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Tambahkan</p>
                                    <p class="text-[10px] text-slate-500">Siswa baru ditambahkan, NIS duplikat dilewati</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition">
                                <input type="radio" name="mode" value="update" class="mt-0.5 accent-amber-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Update</p>
                                    <p class="text-[10px] text-slate-500">NIS duplikat akan diupdate nama & kelasnya</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="importModalOpen = false"
                            class="px-4 py-2 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white text-xs font-extrabold rounded-xl hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4-4m-4 4V4"/>
                            </svg>
                            Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
