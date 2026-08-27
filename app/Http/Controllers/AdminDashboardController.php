<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Vote;
use App\Models\VoteDetail;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $votedStudents = Student::where('has_voted', true)->count();
        $unvotedStudents = $activeStudents - $votedStudents;
        if ($unvotedStudents < 0) $unvotedStudents = 0;

        $participationRate = $activeStudents > 0 ? round(($votedStudents / $activeStudents) * 100, 2) : 0;
        $totalCandidates = Candidate::count();
        $activeCandidates = Candidate::where('status', 'active')->count();
        $totalVotesCount = Vote::count();

        $electionStatus = Setting::get('election_status', 'open');
        $maxChoices = Setting::get('max_choices', 9);

        // Recent Audit Logs
        $recentLogs = \App\Models\AuditLog::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'votedStudents',
            'unvotedStudents',
            'participationRate',
            'totalCandidates',
            'activeCandidates',
            'totalVotesCount',
            'electionStatus',
            'maxChoices',
            'recentLogs'
        ));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:draft,open,paused,closed',
        ]);

        $oldStatus = Setting::get('election_status', 'open');
        $newStatus = $request->input('status');

        Setting::set('election_status', $newStatus, 'string');

        AuditLogService::log(
            'UPDATE_ELECTION_STATUS',
            "Status pemilihan diubah dari {$oldStatus} ke {$newStatus}."
        );

        return back()->with('success', "Status pemilihan berhasil diubah menjadi: " . strtoupper($newStatus));
    }

    public function resetVotes(Request $request)
    {
        $request->validate([
            'confirm_text' => 'required|string|in:RESET-PEMILIHAN',
        ], [
            'confirm_text.in' => 'Konfirmasi kata sandi reset tidak sesuai. Ketik RESET-PEMILIHAN untuk melanjutkan.',
        ]);

        DB::transaction(function () {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            VoteDetail::query()->delete();
            Vote::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Student::query()->update([
                'has_voted' => false,
                'voted_at' => null,
            ]);
        });

        AuditLogService::log(
            'RESET_ALL_VOTES',
            "Seluruh hasil suara dan status voting siswa telah di-reset oleh admin."
        );

        return back()->with('success', 'Seluruh data suara dan status voting siswa telah berhasil di-reset!');
    }
}
