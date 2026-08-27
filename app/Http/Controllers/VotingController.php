<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Setting;
use App\Services\VotingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class VotingController extends Controller
{
    /**
     * Display candidate selection page.
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        if ($student->has_voted) {
            return redirect()->route('student.voted');
        }

        $electionStatus = Setting::get('election_status', 'open');
        $maxChoices = (int) Setting::get('max_choices', 9);
        $votingTimeout = (int) Setting::get('voting_timeout_minutes', 5);
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');
        $showVisiMisi = Setting::get('show_visi_misi', true);

        $loginTime = session('student_login_time', time());
        $elapsed = time() - $loginTime;
        $remainingSeconds = max(0, ($votingTimeout * 60) - $elapsed);

        $candidates = Candidate::where('status', 'active')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        return view('voting.index', compact('student', 'candidates', 'maxChoices', 'electionStatus', 'schoolName', 'electionName', 'votingTimeout', 'remainingSeconds', 'showVisiMisi'));
    }

    /**
     * Display confirmation screen with chosen candidates.
     */
    public function confirm(Request $request)
    {
        $student = Auth::guard('student')->user();

        if ($student->has_voted) {
            return redirect()->route('student.voted');
        }

        $maxChoices = (int) Setting::get('max_choices', 9);
        $votingTimeout = (int) Setting::get('voting_timeout_minutes', 5);

        $loginTime = session('student_login_time', time());
        $elapsed = time() - $loginTime;
        $remainingSeconds = max(0, ($votingTimeout * 60) - $elapsed);

        $request->validate([
            'candidates' => 'required|array|size:' . $maxChoices,
            'candidates.*' => 'required|integer|exists:candidates,id',
        ], [
            'candidates.required' => "Anda belum memilih calon formatur.",
            'candidates.size' => "Anda harus memilih tepat {$maxChoices} calon formatur.",
        ]);

        $selectedIds = array_map('intval', $request->input('candidates'));

        $selectedCandidates = Candidate::whereIn('id', $selectedIds)
            ->where('status', 'active')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        if ($selectedCandidates->count() !== $maxChoices) {
            return back()->with('error', 'Beberapa calon yang Anda pilih tidak valid atau sudah tidak aktif.');
        }

        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        return view('voting.confirm', compact('student', 'selectedCandidates', 'selectedIds', 'maxChoices', 'schoolName', 'electionName', 'votingTimeout', 'remainingSeconds'));
    }

    /**
     * Submit final vote.
     */
    public function submit(Request $request, VotingService $votingService)
    {
        $student = Auth::guard('student')->user();

        if ($student->has_voted) {
            return redirect()->route('student.voted');
        }

        $maxChoices = (int) Setting::get('max_choices', 9);

        $request->validate([
            'candidates' => 'required|array|size:' . $maxChoices,
            'candidates.*' => 'required|integer|exists:candidates,id',
        ]);

        try {
            $votingService->submitVote($student, $request->input('candidates'));
            return redirect()->route('student.success');
        } catch (Exception $e) {
            return redirect()->route('student.voting')->with('error', $e->getMessage());
        }
    }

    /**
     * Display success page after vote submission.
     */
    public function success()
    {
        $student = Auth::guard('student')->user();
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        return view('voting.success', compact('student', 'schoolName', 'electionName'));
    }

    /**
     * Display page when student has already voted.
     */
    public function voted()
    {
        $student = Auth::guard('student')->user();
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        return view('voting.voted', compact('student', 'schoolName', 'electionName'));
    }
}
