<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('voted')) {
            $voted = $request->input('voted') === '1';
            $query->where('has_voted', $voted);
        }

        $students = $query->orderBy('kelas', 'asc')->orderBy('nama', 'asc')->paginate(20)->withQueryString();
        $kelases = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.students.index', compact('students', 'kelases'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|unique:students,nis',
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:100',
        ]);

        $plainToken = strtoupper(Str::random(8));

        $student = Student::create([
            'nis' => trim($request->input('nis')),
            'nama' => trim($request->input('nama')),
            'kelas' => trim($request->input('kelas')),
            'token' => Hash::make($plainToken),
            'plain_token' => $plainToken,
            'status' => 'active',
            'has_voted' => false,
        ]);

        AuditLogService::log('CREATE_STUDENT', "Menambahkan siswa baru: {$student->nama} ({$student->nis}) - Kelas {$student->kelas}");

        return back()->with('success', "Siswa {$student->nama} berhasil ditambahkan! Token: {$plainToken}");
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nis' => 'required|string|unique:students,nis,' . $student->id,
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $student->update([
            'nis' => trim($request->input('nis')),
            'nama' => trim($request->input('nama')),
            'kelas' => trim($request->input('kelas')),
            'status' => $request->input('status'),
        ]);

        AuditLogService::log('UPDATE_STUDENT', "Memperbarui data siswa: {$student->nama} ({$student->nis})");

        return back()->with('success', "Data siswa {$student->nama} berhasil diperbarui.");
    }

    public function destroy(Student $student)
    {
        $nama = $student->nama;
        $nis = $student->nis;
        $student->delete();

        AuditLogService::log('DELETE_STUDENT', "Menghapus siswa: {$nama} ({$nis})");

        return back()->with('success', "Siswa {$nama} berhasil dihapus.");
    }

    public function regenerateToken(Student $student)
    {
        $plainToken = strtoupper(Str::random(8));
        $student->update([
            'token' => Hash::make($plainToken),
            'plain_token' => $plainToken,
        ]);

        AuditLogService::log('REGENERATE_TOKEN', "Regenerate token siswa: {$student->nama} ({$student->nis})");

        return back()->with('success', "Token siswa {$student->nama} berhasil diperbarui menjadi: {$plainToken}");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:students,id',
        ]);

        $ids = $request->input('ids');
        $count = Student::whereIn('id', $ids)->count();
        Student::whereIn('id', $ids)->delete();

        AuditLogService::log('BULK_DELETE_STUDENTS', "Hapus massal {$count} data siswa/voter. ID: " . implode(', ', $ids));

        return back()->with('success', "Berhasil menghapus {$count} data siswa/voter secara massal.");
    }

    public function clearAll(Request $request)
    {
        $count = Student::count();
        if ($count === 0) {
            return back()->with('error', 'Data siswa/voter sudah kosong.');
        }

        Student::query()->delete();

        AuditLogService::log('CLEAR_ALL_STUDENTS', "Mengosongkan seluruh data siswa/voter ({$count} siswa dihapus).");

        return back()->with('success', "Berhasil mengosongkan seluruh data siswa/voter ({$count} data siswa dihapus).");
    }

    public function bulkRegenerateTokens(Request $request)
    {
        $students = Student::all();
        $count = 0;

        foreach ($students as $student) {
            $plainToken = strtoupper(Str::random(8));
            $student->update([
                'token' => Hash::make($plainToken),
                'plain_token' => $plainToken,
            ]);
            $count++;
        }

        AuditLogService::log('BULK_REGENERATE_TOKENS', "Regenerate token massal untuk {$count} siswa.");

        return back()->with('success', "Berhasil me-regenerate token secara massal untuk {$count} siswa.");
    }

    public function resetVotingStatus(Student $student)
    {
        $student->update([
            'has_voted' => false,
            'voted_at' => null,
        ]);

        AuditLogService::log('RESET_STUDENT_VOTE', "Reset status voting siswa: {$student->nama} ({$student->nis})");

        return back()->with('success', "Status voting siswa {$student->nama} telah di-reset.");
    }

    public function qrCards(Request $request)
    {
        $query = Student::query();

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }

        $students = $query->orderBy('kelas', 'asc')->orderBy('nama', 'asc')->get();
        $kelases = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $schoolName = \App\Models\Setting::get('school_name', 'SMK Muhammadiyah 1 Bantul');
        $electionName = \App\Models\Setting::get('election_name', 'Pemilihan Ketua & Formatur IPM');

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 20])) {
            $perPage = 15;
        }

        return view('admin.students.qr-cards', compact('students', 'kelases', 'schoolName', 'electionName', 'perPage'));
    }

    public function exportCsv()
    {
        $students = Student::orderBy('kelas')->orderBy('nama')->get();
        $csvFileName = 'data_siswa_ipm_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$csvFileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['NIS', 'Nama', 'Kelas', 'Token', 'Status', 'Sudah Voting', 'Waktu Voting']);

        foreach ($students as $student) {
            fputcsv($handle, [
                $student->nis,
                $student->nama,
                $student->kelas,
                $student->plain_token ?? '[TERHASH]',
                $student->status,
                $student->has_voted ? 'Ya' : 'Belum',
                $student->voted_at ? $student->voted_at->format('Y-m-d H:i:s') : '-',
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($handle) {}, $csvFileName, $headers);
    }

    public function downloadImportTemplate()
    {
        $filePath = public_path('templates/template_import_siswa_ipm.xlsx');
        
        if (file_exists($filePath)) {
            return response()->download($filePath, 'template_import_siswa_ipm.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return back()->with('error', 'File template Excel (.xlsx) tidak ditemukan.');
    }

    public function importExcel(Request $request)
    {
        @ini_set('memory_limit', '256M');
        @set_time_limit(120);

        try {
            $request->validate([
                'file' => 'required|file|max:10240',
                'mode' => 'required|in:append,update',
            ], [
                'file.required' => 'File wajib dipilih.',
                'file.max'      => 'Ukuran file maksimal 10MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file upload: ' . $e->getMessage());
        }

        try {
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return back()->with('error', 'File yang diupload tidak valid atau melebihi batas ukuran server.');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt'])) {
                return back()->with('error', 'Format file tidak didukung. Harap upload file .xlsx, .xls, atau .csv.');
            }

            $mode = $request->input('mode', 'append');
            $rows = [];

            // Jika CSV / TXT, gunakan parser native CSV super ringan & cepat (bebas dependensi php-zip)
            if (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsvFile($file->getRealPath());
            } else {
                // Untuk XLSX / XLS gunakan PhpSpreadsheet dengan setReadDataOnly(true) menghemat RAM hingga 90%
                try {
                    if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                        $reader = IOFactory::createReaderForFile($file->getRealPath());
                        if (method_exists($reader, 'setReadDataOnly')) {
                            $reader->setReadDataOnly(true);
                        }
                        if (method_exists($reader, 'setReadEmptyCells')) {
                            $reader->setReadEmptyCells(false);
                        }
                        $spreadsheet = $reader->load($file->getRealPath());
                        $sheet       = $spreadsheet->getActiveSheet();
                        $rows        = $sheet->toArray(null, true, true, true);
                    } else {
                        return back()->with('error', 'Library PhpSpreadsheet tidak terinstall di server. Silakan gunakan format file .csv.');
                    }
                } catch (\Throwable $e) {
                    return back()->with('error', 'Gagal membaca file Excel (' . $e->getMessage() . '). Jika server tidak mendukung ekstensi php-zip, silakan simpan file sebagai format .csv (Comma Separated Values) lalu coba upload ulang.');
                }
            }

            if (empty($rows)) {
                return back()->with('error', 'File Excel/CSV kosong atau tidak dapat dibaca data di dalamnya.');
            }

            $imported = 0;
            $updated  = 0;
            $skipped  = 0;
            $errors   = [];

            $existingMap = Student::pluck('id', 'nis')->toArray();
            $newStudentsBatch = [];
            $seenNisInFile = [];
            $now = now()->toDateTimeString();

            DB::transaction(function () use (
                &$rows,
                &$existingMap,
                $mode,
                &$imported,
                &$updated,
                &$skipped,
                &$errors,
                &$newStudentsBatch,
                &$seenNisInFile,
                $now
            ) {
                $firstRow = true;

                foreach ($rows as $rowIndex => $row) {
                    // Normalisasi array dari PhpSpreadsheet (key A, B, C) atau array numerik (0, 1, 2)
                    $rawNis   = (string) ($row['A'] ?? $row[0] ?? '');
                    $rawNama  = (string) ($row['B'] ?? $row[1] ?? '');
                    $rawKelas = (string) ($row['C'] ?? $row[2] ?? '');

                    // Hapus UTF-8 BOM (\xEF\xBB\xBF) dan trim whitespace
                    $nis   = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawNis));
                    $nama  = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawNama));
                    $kelas = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawKelas));

                    if ($firstRow) {
                        $firstRow = false;
                        $cellA = strtoupper($nis);
                        if (str_contains($cellA, 'NIS') || str_contains($cellA, 'TEMPLATE') || str_contains($cellA, 'NO')) {
                            continue;
                        }
                    }

                    // Lewati header jika terbawa di baris lain
                    if (strtoupper($nis) === 'NIS' || strtoupper($nama) === 'NAMA LENGKAP' || strtoupper($nama) === 'NAMA') {
                        continue;
                    }

                    // Lewati baris kosong
                    if (empty($nis) && empty($nama)) {
                        continue;
                    }

                    // Validasi kolom wajib
                    if (empty($nis) || empty($nama) || empty($kelas)) {
                        $errors[] = "Baris " . ($rowIndex + 1) . ": NIS/Nama/Kelas tidak boleh kosong.";
                        $skipped++;
                        continue;
                    }

                    // Abaikan jika NIS duplikat di dalam file yang sama
                    if (isset($seenNisInFile[$nis])) {
                        $skipped++;
                        continue;
                    }
                    $seenNisInFile[$nis] = true;

                    if (isset($existingMap[$nis])) {
                        if ($mode === 'update') {
                            Student::where('id', $existingMap[$nis])->update([
                                'nama'  => $nama,
                                'kelas' => $kelas,
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        $plainToken = strtoupper(Str::random(8));
                        $newStudentsBatch[] = [
                            'nis'         => $nis,
                            'nama'        => $nama,
                            'kelas'       => $kelas,
                            'token'       => Hash::make($plainToken),
                            'plain_token' => $plainToken,
                            'status'      => 'active',
                            'has_voted'   => false,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                        $imported++;
                    }
                }

                // Batch insert dalam chunk 100 data sekaligus dengan insertOrIgnore untuk mencegah SQL duplicate error
                if (!empty($newStudentsBatch)) {
                    foreach (array_chunk($newStudentsBatch, 100) as $chunk) {
                        Student::insertOrIgnore($chunk);
                    }
                }
            });

            AuditLogService::log(
                'IMPORT_STUDENTS',
                "Import file siswa — Mode: {$mode} | Ditambahkan: {$imported} | Diperbarui: {$updated} | Dilewati: {$skipped}"
            );

            $message = "Import selesai! Ditambahkan: {$imported} siswa";
            if ($updated > 0) $message .= ", Diperbarui: {$updated}";
            if ($skipped > 0) $message .= ", Dilewati: {$skipped}";
            if (!empty($errors)) $message .= '. Info: ' . implode('; ', array_slice($errors, 0, 3));

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengimport data: ' . $e->getMessage());
        }
    }

    /**
     * Helper fallback baca file CSV secara native tanpa butuh ZipArchive
     */
    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    // Coba pisah dengan titik koma jika format CSV Indonesia (semicolon)
                    $data = explode(';', $data[0]);
                } elseif (count($data) === 1 && str_contains($data[0], "\t")) {
                    $data = explode("\t", $data[0]);
                }
                $rows[] = [
                    'A' => $data[0] ?? '',
                    'B' => $data[1] ?? '',
                    'C' => $data[2] ?? '',
                ];
            }
            fclose($handle);
        }
        return $rows;
    }
}
