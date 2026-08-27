<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElectionResultController extends Controller
{
    public function index()
    {
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');
        $publicResults = Setting::get('public_results', true);

        return view('admin.results', compact('schoolName', 'electionName', 'publicResults'));
    }

    public function reveal()
    {
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        return view('admin.results-reveal', compact('schoolName', 'electionName'));
    }

    /**
     * Return JSON dataset for live charts & rankings.
     */
    public function apiData(): JsonResponse
    {
        $totalStudents = Student::where('status', 'active')->count();
        $votedStudents = Student::where('has_voted', true)->count();
        $participationRate = $totalStudents > 0 ? round(($votedStudents / $totalStudents) * 100, 2) : 0;
        $totalVotesCount = VoteDetail::count();

        $candidates = Candidate::withCount('voteDetails')
            ->orderBy('vote_details_count', 'desc')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        $labels = [];
        $votes = [];
        $percentages = [];

        foreach ($candidates as $candidate) {
            $candidateVotes = $candidate->vote_details_count;
            $percentage = $totalVotesCount > 0 ? round(($candidateVotes / $totalVotesCount) * 100, 1) : 0;

            $labels[] = sprintf('%02d. %s', $candidate->nomor_urut, $candidate->nama);
            $votes[] = $candidateVotes;
            $percentages[] = $percentage;
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_students' => $totalStudents,
                'voted_students' => $votedStudents,
                'unvoted_students' => max(0, $totalStudents - $votedStudents),
                'participation_rate' => $participationRate,
                'total_vote_entries' => $totalVotesCount,
                'last_updated' => now()->format('H:i:s'),
            ],
            'chart' => [
                'labels' => $labels,
                'votes' => $votes,
                'percentages' => $percentages,
            ],
            'ranking' => $candidates->map(function ($c, $idx) use ($totalVotesCount) {
                $votesCount = $c->vote_details_count;
                return [
                    'rank'       => $idx + 1,
                    'id'         => $c->id,
                    'nomor_urut' => sprintf('%02d', $c->nomor_urut),
                    'nama'       => $c->nama,
                    'kelas'      => $c->kelas,
                    'foto'       => $c->foto ? asset('storage/' . $c->foto) : null,
                    'votes'      => $votesCount,
                    'percentage' => $totalVotesCount > 0 ? round(($votesCount / $totalVotesCount) * 100, 1) : 0,
                ];
            }),
        ]);
    }
}
