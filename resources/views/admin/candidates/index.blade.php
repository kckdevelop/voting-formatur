@extends('layouts.admin', ['headerTitle' => 'Manajemen Data Calon Formatur'])

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, activeCandidate: null }">
    
    <!-- Top Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Daftar Calon Formatur IPM</h2>
            <p class="text-xs text-slate-500">Total calon terdaftar: {{ count($candidates) }} siswa | Total suara masuk: {{ $totalVotes }} suara</p>
        </div>

        <button type="button" @click="addModalOpen = true" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-2xl shadow-md transition flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Calon Formatur
        </button>
    </div>

    <!-- Candidate Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($candidates as $candidate)
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <!-- Header: Sequence No & Actions -->
                    <div class="flex items-center justify-between mb-4">
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
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Foto Calon (JPG/PNG/WEBP max 2MB)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
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
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Foto Baru (opsional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
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

</div>
@endsection
