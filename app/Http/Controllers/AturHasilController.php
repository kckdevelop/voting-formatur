<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Student;
use App\Models\Vote;
use App\Models\VoteDetail;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AturHasilController extends Controller
{
    /**
     * Tampilkan halaman atur hasil perolehan suara.
     */
    public function index()
    {
        $candidates = Candidate::withCount('voteDetails')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        $totalVotes = VoteDetail::count();
        $totalStudents = Student::where('status', 'active')->count();
        $totalVoters = Vote::count();

        foreach ($candidates as $candidate) {
            $candidate->percentage = $totalVotes > 0
                ? round(($candidate->vote_details_count / $totalVotes) * 100, 2)
                : 0;
        }

        return view('admin.atur-hasil', compact(
            'candidates',
            'totalVotes',
            'totalStudents',
            'totalVoters'
        ));
    }

    /**
     * Set perolehan suara calon tertentu ke jumlah yang ditentukan.
     * Cara kerja: hapus semua vote_details calon ini, lalu buat entri baru
     * menggunakan vote-id siswa yang sudah memilih (atau buat vote dummy jika perlu).
     */
    public function updateVotes(Request $request, Candidate $candidate)
    {
        $request->validate([
            'jumlah_suara' => 'required|integer|min:0|max:9999',
        ], [
            'jumlah_suara.required' => 'Jumlah suara wajib diisi.',
            'jumlah_suara.integer'  => 'Jumlah suara harus berupa angka bulat.',
            'jumlah_suara.min'      => 'Jumlah suara minimal 0.',
            'jumlah_suara.max'      => 'Jumlah suara terlalu besar.',
        ]);

        $targetVotes = (int) $request->input('jumlah_suara');
        $currentVotes = VoteDetail::where('candidate_id', $candidate->id)->count();
        $oldVotes = $currentVotes;

        DB::transaction(function () use ($candidate, $targetVotes, $currentVotes) {
            if ($targetVotes < $currentVotes) {
                // Kurangi suara: hapus sebagian vote_details (pilih yang dummy dulu)
                $toDelete = $currentVotes - $targetVotes;
                $detailIds = VoteDetail::where('candidate_id', $candidate->id)
                    ->orderBy('id', 'desc')
                    ->limit($toDelete)
                    ->pluck('id');

                VoteDetail::whereIn('id', $detailIds)->delete();

            } elseif ($targetVotes > $currentVotes) {
                // Tambah suara: cari vote dummy yang tersedia atau buat vote dummy baru
                $toAdd = $targetVotes - $currentVotes;

                // Cari vote yang sudah ada namun belum memilih calon ini
                $existingVoteIds = VoteDetail::where('candidate_id', $candidate->id)
                    ->pluck('vote_id')
                    ->toArray();

                $availableVotes = Vote::whereNotIn('id', $existingVoteIds)
                    ->limit($toAdd)
                    ->pluck('id');

                $inserted = 0;

                foreach ($availableVotes as $voteId) {
                    VoteDetail::firstOrCreate([
                        'vote_id'      => $voteId,
                        'candidate_id' => $candidate->id,
                    ]);
                    $inserted++;
                }

                // Jika masih kurang, buat vote dummy dengan student_id = null
                $stillNeeded = $toAdd - $inserted;
                if ($stillNeeded > 0) {
                    for ($i = 0; $i < $stillNeeded; $i++) {
                        $vote = Vote::create([
                            'student_id' => null,
                            'voted_at'   => now(),
                        ]);

                        VoteDetail::create([
                            'vote_id'      => $vote->id,
                            'candidate_id' => $candidate->id,
                        ]);
                    }
                }
            }
            // Jika $targetVotes == $currentVotes, tidak ada perubahan
        });

        $newVotes = VoteDetail::where('candidate_id', $candidate->id)->count();

        AuditLogService::log(
            'ATUR_HASIL',
            "Mengubah suara calon [{$candidate->nomor_urut}] {$candidate->nama}: {$oldVotes} → {$newVotes} suara"
        );

        return back()->with('success', "Suara {$candidate->nama} berhasil diubah dari {$oldVotes} menjadi {$newVotes} suara.");
    }

    /**
     * Reset semua suara satu calon ke 0.
     */
    public function resetVotes(Candidate $candidate)
    {
        $oldVotes = VoteDetail::where('candidate_id', $candidate->id)->count();

        VoteDetail::where('candidate_id', $candidate->id)->delete();

        AuditLogService::log(
            'ATUR_HASIL_RESET',
            "Mereset suara calon [{$candidate->nomor_urut}] {$candidate->nama}: {$oldVotes} → 0 suara"
        );

        return back()->with('success', "Semua suara untuk {$candidate->nama} telah direset ke 0.");
    }

    /**
     * Atur semua suara sekaligus dari form batch.
     */
    public function updateAll(Request $request)
    {
        $request->validate([
            'suara'   => 'required|array',
            'suara.*' => 'required|integer|min:0|max:9999',
        ]);

        $candidates = Candidate::orderBy('nomor_urut')->get();
        $log = [];

        DB::transaction(function () use ($request, $candidates, &$log) {
            foreach ($candidates as $candidate) {
                $targetVotes = (int) ($request->input('suara')[$candidate->id] ?? 0);
                $currentVotes = VoteDetail::where('candidate_id', $candidate->id)->count();

                if ($targetVotes === $currentVotes) {
                    continue;
                }

                $oldVotes = $currentVotes;

                if ($targetVotes < $currentVotes) {
                    $toDelete = $currentVotes - $targetVotes;
                    $detailIds = VoteDetail::where('candidate_id', $candidate->id)
                        ->orderBy('id', 'desc')
                        ->limit($toDelete)
                        ->pluck('id');
                    VoteDetail::whereIn('id', $detailIds)->delete();

                } else {
                    $toAdd = $targetVotes - $currentVotes;
                    $existingVoteIds = VoteDetail::where('candidate_id', $candidate->id)
                        ->pluck('vote_id')->toArray();

                    $availableVotes = Vote::whereNotIn('id', $existingVoteIds)
                        ->limit($toAdd)->pluck('id');

                    $inserted = 0;
                    foreach ($availableVotes as $voteId) {
                        VoteDetail::firstOrCreate([
                            'vote_id'      => $voteId,
                            'candidate_id' => $candidate->id,
                        ]);
                        $inserted++;
                    }

                    $stillNeeded = $toAdd - $inserted;
                    if ($stillNeeded > 0) {
                        for ($i = 0; $i < $stillNeeded; $i++) {
                            $vote = Vote::create([
                                'student_id' => null,
                                'voted_at'   => now(),
                            ]);
                            VoteDetail::create([
                                'vote_id'      => $vote->id,
                                'candidate_id' => $candidate->id,
                            ]);
                        }
                    }
                }

                $newVotes = VoteDetail::where('candidate_id', $candidate->id)->count();
                $log[] = "[{$candidate->nomor_urut}] {$candidate->nama}: {$oldVotes}→{$newVotes}";
            }
        });

        if (!empty($log)) {
            AuditLogService::log(
                'ATUR_HASIL_BATCH',
                'Batch update suara: ' . implode(', ', $log)
            );
            return back()->with('success', 'Semua perolehan suara berhasil diperbarui.');
        }

        return back()->with('success', 'Tidak ada perubahan suara.');
    }
}
