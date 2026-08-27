<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class StudentAuthController extends Controller
{
    /**
     * Display student login page.
     */
    public function showLoginForm()
    {
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($student->has_voted) {
                return redirect()->route('student.voted');
            }
            return redirect()->route('student.voting');
        }

        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');
        $logoPath = Setting::get('logo_path', null);
        $electionStatus = Setting::get('election_status', 'open');

        return view('auth.student-login', compact('schoolName', 'electionName', 'logoPath', 'electionStatus'));
    }

    /**
     * Handle student login submission (manual form or QR payload).
     */
    public function login(Request $request)
    {
        $request->validate([
            'nis' => 'required|string',
            'token' => 'required|string',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'token.required' => 'Token wajib diisi.',
        ]);

        $throttleKey = Str::lower($request->input('nis')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'nis' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput();
        }

        $nis = trim($request->input('nis'));
        $token = trim($request->input('token'));

        $student = Student::where('nis', $nis)->first();

        if (!$student) {
            RateLimiter::hit($throttleKey);
            return back()->withErrors(['nis' => 'NIS tidak ditemukan.'])->withInput();
        }

        if ($student->status !== 'active') {
            return back()->withErrors(['nis' => 'Akun siswa Anda sedang tidak aktif. Hubungi panitia.'])->withInput();
        }

        // Verify token with Hash::check or fallback to plain_token
        $tokenValid = Hash::check($token, $student->token) || ($student->plain_token && $student->plain_token === $token);

        if (!$tokenValid) {
            RateLimiter::hit($throttleKey);
            return back()->withErrors(['token' => 'Token yang Anda masukkan salah.'])->withInput();
        }

        RateLimiter::clear($throttleKey);

        // Authenticate student guard
        Auth::guard('student')->login($student);
        session(['student_login_time' => now()->timestamp]);

        AuditLogService::log(
            'STUDENT_LOGIN',
            "Siswa {$student->nama} (NIS: {$student->nis}) berhasil login.",
            $student->nama . ' (' . $student->nis . ')'
        );

        if ($student->has_voted) {
            return redirect()->route('student.voted');
        }

        return redirect()->route('student.voting');
    }

    /**
     * Handle QR Code JSON login endpoint (AJAX or form post).
     */
    public function loginQr(Request $request)
    {
        $request->validate([
            'qr_payload' => 'required|string',
        ]);

        $payloadRaw = trim($request->input('qr_payload'));
        $data = json_decode($payloadRaw, true);

        $nis = null;
        $token = null;

        if (is_array($data) && isset($data['nis']) && isset($data['token'])) {
            $nis = trim((string) $data['nis']);
            $token = trim((string) $data['token']);
        } else {
            // Fallback: parse NIS:TOKEN or NIS|TOKEN or NIS,TOKEN
            $parts = preg_split('/[:|,\s]+/', $payloadRaw);
            if (count($parts) >= 2) {
                $nis = trim($parts[0]);
                $token = trim($parts[1]);
            }
        }

        if (empty($nis) || empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Format QR Code tidak valid. QR Code harus berisi data NIS dan Token.',
            ], 422);
        }

        $student = Student::where('nis', $nis)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'NIS dari QR Code tidak terdaftar dalam sistem.',
            ], 404);
        }

        if ($student->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun siswa dari QR Code ini sedang tidak aktif.',
            ], 403);
        }

        $tokenValid = Hash::check($token, $student->token) || ($student->plain_token && $student->plain_token === $token);

        if (!$tokenValid) {
            return response()->json([
                'success' => false,
                'message' => 'Token pada QR Code tidak cocok.',
            ], 401);
        }

        Auth::guard('student')->login($student);
        session(['student_login_time' => now()->timestamp]);

        AuditLogService::log(
            'STUDENT_QR_LOGIN',
            "Siswa {$student->nama} (NIS: {$student->nis}) berhasil login melalui QR Code.",
            $student->nama . ' (' . $student->nis . ')'
        );

        $redirectUrl = $student->has_voted ? route('student.voted') : route('student.voting');

        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Log out student.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            AuditLogService::log('STUDENT_LOGOUT', "Siswa {$student->nama} logout.", $student->nama);
        }

        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
