<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CandidateManagementController;
use App\Http\Controllers\ElectionResultController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\AturHasilController;
use Illuminate\Support\Facades\Route;

// Halaman awal dan /login langsung ke login siswa
Route::get('/', [StudentAuthController::class, 'showLoginForm']);
Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
Route::post('/login', [StudentAuthController::class, 'login'])->name('student.login.submit');
Route::post('/login/qr', [StudentAuthController::class, 'loginQr'])->name('student.login.qr');
Route::post('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

// Student Voting Routes (Protected)
Route::middleware('auth.student')->group(function () {
    Route::get('/voting', [VotingController::class, 'index'])->name('student.voting');
    Route::post('/voting/confirm', [VotingController::class, 'confirm'])->name('student.voting.confirm');
    Route::post('/voting/submit', [VotingController::class, 'submit'])->name('student.voting.submit');
    Route::get('/voting/success', [VotingController::class, 'success'])->name('student.success');
    Route::get('/voting/voted', [VotingController::class, 'voted'])->name('student.voted');
});

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Admin Portal Protected Routes
    Route::middleware('auth.admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/status', [AdminDashboardController::class, 'updateStatus'])->name('admin.status.update');
        Route::post('/reset-votes', [AdminDashboardController::class, 'resetVotes'])->name('admin.reset-votes');

        // Student Management
        Route::get('/students', [StudentManagementController::class, 'index'])->name('admin.students.index');
        Route::post('/students', [StudentManagementController::class, 'store'])->name('admin.students.store');
        Route::get('/students/export', [StudentManagementController::class, 'exportCsv'])->name('admin.students.export');
        Route::get('/students/import', fn() => redirect()->route('admin.students.index'));
        Route::post('/students/import', [StudentManagementController::class, 'importExcel'])->name('admin.students.import');
        Route::get('/students/import/template', [StudentManagementController::class, 'downloadImportTemplate'])->name('admin.students.import.template');
        Route::post('/students/bulk-regenerate-tokens', [StudentManagementController::class, 'bulkRegenerateTokens'])->name('admin.students.bulk-regenerate-tokens');
        Route::delete('/students/bulk-delete', [StudentManagementController::class, 'bulkDelete'])->name('admin.students.bulk-delete');
        Route::delete('/students/clear-all', [StudentManagementController::class, 'clearAll'])->name('admin.students.clear-all');
        Route::get('/students/qr-cards', [StudentManagementController::class, 'qrCards'])->name('admin.students.qr-cards');
        Route::put('/students/{student}', [StudentManagementController::class, 'update'])->name('admin.students.update');
        Route::delete('/students/{student}', [StudentManagementController::class, 'destroy'])->name('admin.students.destroy');
        Route::post('/students/{student}/regenerate-token', [StudentManagementController::class, 'regenerateToken'])->name('admin.students.regenerate-token');
        Route::post('/students/{student}/reset-vote', [StudentManagementController::class, 'resetVotingStatus'])->name('admin.students.reset-vote');

        // Candidate Management
        Route::get('/candidates', [CandidateManagementController::class, 'index'])->name('admin.candidates.index');
        Route::post('/candidates', [CandidateManagementController::class, 'store'])->name('admin.candidates.store');
        Route::get('/candidates/import', fn() => redirect()->route('admin.candidates.index'));
        Route::post('/candidates/import', [CandidateManagementController::class, 'importExcel'])->name('admin.candidates.import');
        Route::get('/candidates/import/template', [CandidateManagementController::class, 'downloadImportTemplate'])->name('admin.candidates.import.template');
        Route::delete('/candidates/bulk-delete', [CandidateManagementController::class, 'bulkDelete'])->name('admin.candidates.bulk-delete');
        Route::put('/candidates/{candidate}', [CandidateManagementController::class, 'update'])->name('admin.candidates.update');
        Route::delete('/candidates/{candidate}', [CandidateManagementController::class, 'destroy'])->name('admin.candidates.destroy');

        // Realtime Results & Live Reveal Show
        Route::get('/results', [ElectionResultController::class, 'index'])->name('admin.results');
        Route::get('/results/reveal', [ElectionResultController::class, 'reveal'])->name('admin.results.reveal');
        Route::get('/results/api', [ElectionResultController::class, 'apiData'])->name('admin.results.api');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');

        // Atur Hasil (Halaman Tersembunyi - Tidak Ada di Navigasi)
        Route::get('/atur-hasil', [AturHasilController::class, 'index'])->name('admin.atur-hasil');
        Route::patch('/atur-hasil/{candidate}', [AturHasilController::class, 'updateVotes'])->name('admin.atur-hasil.update');
        Route::delete('/atur-hasil/{candidate}/reset', [AturHasilController::class, 'resetVotes'])->name('admin.atur-hasil.reset');
        Route::post('/atur-hasil/batch/update-all', [AturHasilController::class, 'updateAll'])->name('admin.atur-hasil.update-all');
    });
});
