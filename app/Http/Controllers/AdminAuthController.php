<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        return view('auth.admin-login', compact('schoolName', 'electionName'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();

            AuditLogService::log('ADMIN_LOGIN', "Admin {$admin->name} ({$admin->email}) berhasil login.", $admin->name);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password panitia salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            AuditLogService::log('ADMIN_LOGOUT', "Admin {$admin->name} logout.", $admin->name);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
