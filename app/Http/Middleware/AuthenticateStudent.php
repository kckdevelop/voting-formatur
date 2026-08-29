<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateStudent
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('student')->check()) {
            return redirect()->route('student.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $student = Auth::guard('student')->user();

        if ($student->status !== 'active') {
            Auth::guard('student')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('student.login')->with('error', 'Akun siswa Anda sudah tidak aktif.');
        }

        // Check voting session timeout limit
        // Use persistent cache to avoid DB query on every student page request
        $timeoutMinutes = Cache::remember('setting.voting_timeout_minutes', 300, function () {
            return (int) Setting::get('voting_timeout_minutes', 5);
        });
        $loginTime = session('student_login_time');

        if ($loginTime) {
            $elapsedSeconds = time() - $loginTime;
            $maxSeconds = $timeoutMinutes * 60;

            if ($elapsedSeconds > $maxSeconds) {
                Auth::guard('student')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('student.login')
                    ->with('error', "Waktu sesi voting Anda ({$timeoutMinutes} menit) telah habis. Silakan login kembali.");
            }
        } else {
            session(['student_login_time' => now()->timestamp]);
        }

        return $next($request);
    }
}
