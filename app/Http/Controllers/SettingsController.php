<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $schoolName = Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');
        $electionYear = Setting::get('election_year', date('Y'));
        $maxChoices = Setting::get('max_choices', 9);
        $votingTimeout = (int) Setting::get('voting_timeout_minutes', 5);
        $electionStatus = Setting::get('election_status', 'open');
        $publicResults = Setting::get('public_results', true);
        $showVisiMisi = Setting::get('show_visi_misi', true);
        $logoPath = Setting::get('logo_path', null);

        return view('admin.settings', compact(
            'schoolName',
            'electionName',
            'electionYear',
            'maxChoices',
            'votingTimeout',
            'electionStatus',
            'publicResults',
            'showVisiMisi',
            'logoPath'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'election_name' => 'required|string|max:255',
            'election_year' => 'required|string|max:20',
            'max_choices' => 'required|integer|min:1|max:50',
            'voting_timeout_minutes' => 'required|integer|min:1|max:60',
            'public_results' => 'nullable|boolean',
            'show_visi_misi' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        Setting::set('school_name', $request->input('school_name'));
        Setting::set('election_name', $request->input('election_name'));
        Setting::set('election_year', $request->input('election_year'));
        Setting::set('max_choices', (int) $request->input('max_choices'), 'integer');
        Setting::set('voting_timeout_minutes', (int) $request->input('voting_timeout_minutes', 5), 'integer');
        Setting::set('public_results', $request->boolean('public_results'), 'boolean');
        Setting::set('show_visi_misi', $request->boolean('show_visi_misi'), 'boolean');

        if ($request->hasFile('logo')) {
            $existingLogo = Setting::get('logo_path');
            if ($existingLogo && Storage::disk('public')->exists($existingLogo)) {
                Storage::disk('public')->delete($existingLogo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo_path', $path);
        }

        AuditLogService::log('UPDATE_SETTINGS', 'Memperbarui pengaturan aplikasi pemilihan.');

        // Flush cached settings so middleware/voting picks up latest values immediately
        Cache::forget('setting.voting_timeout_minutes');
        Setting::flushCache();

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan!');
    }
}
