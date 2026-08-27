<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\VoteDetail;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateManagementController extends Controller
{
    public function index()
    {
        $candidates = Candidate::withCount('voteDetails')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        $totalVotes = VoteDetail::count();

        foreach ($candidates as $candidate) {
            $candidate->percentage = $totalVotes > 0 
                ? round(($candidate->vote_details_count / $totalVotes) * 100, 2)
                : 0;
        }

        return view('admin.candidates.index', compact('candidates', 'totalVotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'kelas' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nama.required' => 'Nama calon wajib diisi.',
            'kelas.required' => 'Kelas wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto yang diizinkan: JPG, JPEG, PNG, WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('candidates', 'public');
        }

        $candidate = Candidate::create([
            'nomor_urut' => $request->input('nomor_urut'),
            'nama' => trim($request->input('nama')),
            'nis' => $request->input('nis') ? trim($request->input('nis')) : null,
            'kelas' => trim($request->input('kelas')),
            'foto' => $fotoPath,
            'visi' => $request->input('visi'),
            'misi' => $request->input('misi'),
            'status' => 'active',
        ]);

        AuditLogService::log('CREATE_CANDIDATE', "Menambahkan calon formatur: [{$candidate->nomor_urut}] {$candidate->nama}");

        return back()->with('success', "Calon formatur {$candidate->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Candidate $candidate)
    {
        $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'kelas' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $fotoPath = $candidate->foto;
        if ($request->hasFile('foto')) {
            if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $fotoPath = $request->file('foto')->store('candidates', 'public');
        }

        $candidate->update([
            'nomor_urut' => $request->input('nomor_urut'),
            'nama' => trim($request->input('nama')),
            'nis' => $request->input('nis') ? trim($request->input('nis')) : null,
            'kelas' => trim($request->input('kelas')),
            'foto' => $fotoPath,
            'visi' => $request->input('visi'),
            'misi' => $request->input('misi'),
            'status' => $request->input('status'),
        ]);

        AuditLogService::log('UPDATE_CANDIDATE', "Memperbarui calon formatur: [{$candidate->nomor_urut}] {$candidate->nama}");

        return back()->with('success', "Data calon {$candidate->nama} berhasil diperbarui.");
    }

    public function destroy(Candidate $candidate)
    {
        $nama = $candidate->nama;
        if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
            Storage::disk('public')->delete($candidate->foto);
        }
        $candidate->delete();

        AuditLogService::log('DELETE_CANDIDATE', "Menghapus calon formatur: {$nama}");

        return back()->with('success', "Calon formatur {$nama} berhasil dihapus.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:candidates,id',
        ]);

        $ids = $request->input('ids');
        $candidates = Candidate::whereIn('id', $ids)->get();

        foreach ($candidates as $candidate) {
            if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $candidate->delete();
        }

        $count = count($ids);
        AuditLogService::log('BULK_DELETE_CANDIDATES', "Hapus massal {$count} calon formatur. ID: " . implode(', ', $ids));

        return back()->with('success', "Berhasil menghapus {$count} calon formatur secara massal.");
    }
}
