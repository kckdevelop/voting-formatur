<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Support\Facades\DB;
use Exception;

class VotingService
{
    /**
     * Process student vote submission.
     *
     * @param Student $student
     * @param array $candidateIds
     * @return Vote
     * @throws Exception
     */
    public function submitVote(Student $student, array $candidateIds): Vote
    {
        // 1. Election status check
        $electionStatus = Setting::get('election_status', 'open');
        if ($electionStatus !== 'open') {
            throw new Exception("Pemilihan saat ini sedang ditutup atau tidak aktif.");
        }

        // 2. Student status check
        if ($student->status !== 'active') {
            throw new Exception("Akun siswa Anda sedang tidak aktif.");
        }

        if ($student->has_voted) {
            throw new Exception("Anda sudah menggunakan hak pilih Anda.");
        }

        // 3. Choice count check
        $maxChoices = (int) Setting::get('max_choices', 9);
        $candidateIds = array_unique(array_map('intval', $candidateIds));

        if (count($candidateIds) !== $maxChoices) {
            throw new Exception("Anda harus memilih tepat {$maxChoices} calon formatur.");
        }

        // 4. Candidates validation
        $validCandidates = Candidate::whereIn('id', $candidateIds)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (count($validCandidates) !== $maxChoices) {
            throw new Exception("Terdapat calon yang tidak valid atau sudah tidak aktif.");
        }

        // 5. DB Transaction & Lock execution
        return DB::transaction(function () use ($student, $candidateIds) {
            // Re-fetch student with pessimistic lock to prevent concurrent double-vote race conditions
            $lockedStudent = Student::where('id', $student->id)->lockForUpdate()->first();

            if (!$lockedStudent || $lockedStudent->has_voted) {
                throw new Exception("Anda sudah menggunakan hak pilih Anda.");
            }

            // Create vote record
            $vote = Vote::create([
                'student_id' => $lockedStudent->id,
                'voted_at' => now(),
            ]);

            // Create vote details
            $detailsData = [];
            foreach ($candidateIds as $candidateId) {
                $detailsData[] = [
                    'vote_id' => $vote->id,
                    'candidate_id' => $candidateId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            VoteDetail::insert($detailsData);

            // Update student record
            $lockedStudent->update([
                'has_voted' => true,
                'voted_at' => now(),
            ]);

            // Write Audit Log
            AuditLogService::log(
                'STUDENT_VOTE_SUBMITTED',
                "Siswa {$lockedStudent->nama} (NIS: {$lockedStudent->nis}) telah berhasil memberikan suara.",
                $lockedStudent->nama . ' (' . $lockedStudent->nis . ')'
            );

            return $vote;
        });
    }
}
