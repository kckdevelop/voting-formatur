@extends('layouts.admin', ['headerTitle' => 'Manajemen Data Calon Formatur'])

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    importModalOpen: false,
    activeCandidate: null,
    selectedIds: [],
    toggleOne(id) {
        const idx = this.selectedIds.indexOf(id);
        if (idx === -1) {
            this.selectedIds.push(id);
        } else {
            this.selectedIds.splice(idx, 1);
        }
    },
    confirmBulkDelete() {
        if (this.selectedIds.length === 0) {
            alert('Pilih minimal satu calon formatur terlebih dahulu.');
            return;
        }
        if (confirm('Hapus ' + this.selectedIds.length + ' calon formatur yang dipilih?\nFoto akan ikut dihapus dari sistem. Tindakan ini tidak bisa dibatalkan!')) {
            document.getElementById('bulk-delete-form-candidates').submit();
        }
    }
}">
    
    <!-- Top Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Daftar Calon Formatur IPM</h2>
            <p class="text-xs text-slate-500">Total calon terdaftar: {{ count($candidates) }} siswa | Total suara masuk: {{ $totalVotes }} suara</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button type="button" @click="importModalOpen = true" class="px-4 py-2.5 bg-green-50 hover:bg-green-100 text-green-800 border border-green-200 text-xs font-bold rounded-2xl transition flex items-center justify-center">
                <svg class="w-4 h-4 mr-1.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4-4m-4 4V4"></path></svg>
                Import Excel
            </button>

            <button type="button" @click="addModalOpen = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-2xl shadow-md transition flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Calon Formatur
            </button>
        </div>
    </div>

    <!-- Bulk Delete Toolbar (muncul ketika ada yang dipilih) -->
    <div x-show="selectedIds.length > 0" x-cloak
        class="bg-rose-50 border border-rose-200 rounded-2xl px-5 py-3 flex items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <span class="text-sm font-extrabold text-rose-800">
                <span x-text="selectedIds.length"></span> calon dipilih
            </span>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="selectedIds = []"
                class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Batalkan Pilihan
            </button>
            <!-- Hidden Bulk Delete Form -->
            <form id="bulk-delete-form-candidates" action="{{ route('admin.candidates.bulk-delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
            <button type="button" @click="confirmBulkDelete()"
                class="px-4 py-1.5 text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition flex items-center gap-1.5 shadow">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus yang Dipilih
            </button>
        </div>
    </div>

    <!-- Candidate Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($candidates as $candidate)
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between relative"
                :class="selectedIds.includes({{ $candidate->id }}) ? 'ring-2 ring-rose-400 border-rose-300' : ''">
                
                <!-- Checkbox Overlay -->
                <div class="absolute top-3 left-3 z-10">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded accent-rose-600 cursor-pointer shadow"
                            :checked="selectedIds.includes({{ $candidate->id }})"
                            @change="toggleOne({{ $candidate->id }})">
                    </label>
                </div>

                <div>
                    <!-- Header: Sequence No & Actions -->
                    <div class="flex items-center justify-between mb-4 pl-7">
                        <span class="w-9 h-9 rounded-2xl bg-slate-900 text-white font-black text-sm flex items-center justify-center shadow">
                            {{ sprintf('%02d', $candidate->nomor_urut) }}
                        </span>

                        <div class="flex items-center space-x-1">
                            <button type="button" @click="activeCandidate = {{ json_encode($candidate) }}; editModalOpen = true" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition" title="Edit Calon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>

                            <form action="{{ route('admin.candidates.destroy', $candidate->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus calon formatur ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition" title="Hapus Calon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="w-full aspect-square rounded-2xl overflow-hidden bg-slate-100 mb-4 border border-slate-200 shadow-inner">
                        @if($candidate->foto)
                            <img src="{{ asset('storage/' . $candidate->foto) }}" alt="{{ $candidate->nama }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Candidate Name & Class -->
                    <div class="text-center mb-3">
                        <h3 class="font-extrabold text-slate-900 text-base leading-tight">{{ $candidate->nama }}</h3>
                        <p class="text-xs font-semibold text-emerald-600 mt-0.5">Kelas: {{ $candidate->kelas }}</p>
                    </div>
                </div>

                <!-- Votes Stats Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500">Perolehan Suara:</span>
                    <span class="font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl">
                        {{ $candidate->vote_details_count }} Suara ({{ $candidate->percentage }}%)
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-slate-200 text-slate-400">
                Belum ada calon formatur yang ditambahkan.
            </div>
        @endforelse
    </div>

    <!-- Add Candidate Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="addModalOpen = false"></div>
            
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10 space-y-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-extrabold text-slate-900">Tambah Calon Formatur</h3>
                
                <form action="{{ route('admin.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nomor Urut</label>
                            <input type="number" name="nomor_urut" required value="{{ count($candidates) + 1 }}" min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kelas</label>
                            <input type="text" name="kelas" required placeholder="Cth: XI TKJ 1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required placeholder="Nama Siswa Calon" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Foto Calon (Otomatis Kompres Kantor / HP)</label>
                        <input type="file" name="foto" accept="image/*" onchange="autoCompressPhoto(this)" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <p class="text-[10px] text-slate-400 mt-1">Foto besar dari HP/Kamera akan dikompres otomatis oleh sistem agar upload ultra-cepat.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Visi</label>
                        <textarea name="visi" rows="3" placeholder="Tuliskan visi..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Misi</label>
                        <textarea name="misi" rows="3" placeholder="Tuliskan misi..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-extrabold rounded-xl hover:bg-emerald-700">Simpan Calon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="editModalOpen = false"></div>
            
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10 space-y-4 max-h-[90vh] overflow-y-auto" x-show="activeCandidate">
                <h3 class="text-lg font-extrabold text-slate-900">Edit Calon Formatur</h3>
                
                <form :action="'/admin/candidates/' + (activeCandidate ? activeCandidate.id : '')" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nomor Urut</label>
                            <input type="number" name="nomor_urut" :value="activeCandidate ? activeCandidate.nomor_urut : 1" required min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kelas</label>
                            <input type="text" name="kelas" :value="activeCandidate ? activeCandidate.kelas : ''" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" :value="activeCandidate ? activeCandidate.nama : ''" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Foto Baru (opsional - Otomatis Kompres)</label>
                        <input type="file" name="foto" accept="image/*" onchange="autoCompressPhoto(this)" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Status Calon</label>
                        <select name="status" :value="activeCandidate ? activeCandidate.status : 'active'" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Visi</label>
                        <textarea name="visi" rows="3" x-text="activeCandidate ? activeCandidate.visi : ''" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Misi</label>
                        <textarea name="misi" rows="3" x-text="activeCandidate ? activeCandidate.misi : ''" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium"></textarea>
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
                        <h3 class="text-lg font-extrabold text-slate-900">Import Calon Formatur</h3>
                        <p class="text-xs text-slate-500">Upload file Excel (.xlsx / .xls / .csv)</p>
                    </div>
                </div>

                {{-- Format Info --}}
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2">
                    <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Format Kolom Excel (Baris 3 = Header)</p>
                    <div class="grid grid-cols-3 gap-2 text-left">
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom A</p>
                            <p class="text-xs font-black text-slate-800">No Urut</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom B</p>
                            <p class="text-xs font-black text-slate-800">Nama</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom C</p>
                            <p class="text-xs font-black text-slate-800">Kelas</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom D</p>
                            <p class="text-xs font-black text-slate-800">NIS (Opt)</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom E</p>
                            <p class="text-xs font-black text-slate-800">Visi (Opt)</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl px-2.5 py-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kolom F</p>
                            <p class="text-xs font-black text-slate-800">Misi (Opt)</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan template yang telah disediakan di bawah agar data sesuai.</p>

                    {{-- Download Template Button --}}
                    <a href="{{ route('admin.candidates.import.template') }}"
                        class="mt-2 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Template Excel Calon (.xlsx)
                    </a>
                </div>

                <form action="{{ route('admin.candidates.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">File Excel / CSV</label>
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
                                    <p class="text-[10px] text-slate-500">Calon baru ditambahkan, calon duplikat dilewati</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition">
                                <input type="radio" name="mode" value="update" class="mt-0.5 accent-amber-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Update</p>
                                    <p class="text-[10px] text-slate-500">Calon duplikat akan diupdate data visi, misi & kelasnya</p>
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

<script>
/**
 * Kompres otomatis file foto calon (max 800x800 px JPEG, 80% quality)
 * Mengubah file 5MB-15MB dari kamera HP menjadi ~100KB agar tidak menabrak batas limit Nginx server.
 */
function autoCompressPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const maxWidth = 800;
            const maxHeight = 800;
            let width = img.width;
            let height = img.height;

            if (width > maxWidth || height > maxHeight) {
                if (width > height) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                } else {
                    width = Math.round((width * maxHeight) / height);
                    height = maxHeight;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(function(blob) {
                if (blob && blob.size < file.size) {
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    input.files = dataTransfer.files;
                }
            }, 'image/jpeg', 0.82);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endsection
