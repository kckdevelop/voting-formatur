<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
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
        if (class_exists('ZipArchive')) {
            try {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                // ── Sheet 1: Template Data ──────────────────────────────────────────
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Data Siswa');

                // Styling helper
                $headerStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => '065F46']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                     'color'       => ['rgb' => '047857']]],
                ];

                $dataStyle = [
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                     'color'       => ['rgb' => 'CBD5E1']]],
                ];

                $noteStyle = [
                    'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 9],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                               'startColor' => ['rgb' => 'F0FDF4']],
                ];

                // Judul
                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA — E-Voting Formatur IPM');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '065F46']],
                    'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Catatan
                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', '⚠ Baris 1 = Judul (baris ini). Baris 2 = Catatan. Baris 3 = HEADER. Data mulai baris 4. Jangan ubah urutan kolom!');
                $sheet->getStyle('A2')->applyFromArray($noteStyle);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Header kolom (baris 3)
                $sheet->setCellValue('A3', 'NIS');
                $sheet->setCellValue('B3', 'NAMA LENGKAP');
                $sheet->setCellValue('C3', 'KELAS');
                $sheet->getStyle('A3:C3')->applyFromArray($headerStyle);
                $sheet->getRowDimension(3)->setRowHeight(22);

                // Contoh data (baris 4–6)
                $contohData = [
                    ['2024001', 'Ahmad Fauzan', 'XI TKJ 1'],
                    ['2024002', 'Budi Santoso', 'XI TKJ 2'],
                    ['2024003', 'Citra Dewi',   'XI AKL 1'],
                ];

                $altStyle = [
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                               'startColor' => ['rgb' => 'F8FAFC']],
                ];

                foreach ($contohData as $i => $row) {
                    $rowNum = $i + 4;
                    $sheet->setCellValue("A{$rowNum}", $row[0]);
                    $sheet->setCellValue("B{$rowNum}", $row[1]);
                    $sheet->setCellValue("C{$rowNum}", $row[2]);
                    $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray($dataStyle);
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray($altStyle);
                    }
                    $sheet->getRowDimension($rowNum)->setRowHeight(18);
                }

                // Placeholder baris kosong tambahan (baris 7–50)
                for ($r = 7; $r <= 50; $r++) {
                    $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($dataStyle);
                    $sheet->getRowDimension($r)->setRowHeight(17);
                }

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(18);  // NIS
                $sheet->getColumnDimension('B')->setWidth(35);  // Nama
                $sheet->getColumnDimension('C')->setWidth(20);  // Kelas

                // Freeze pane (header tetap saat scroll)
                $sheet->freezePane('A4');

                // ── Sheet 2: Petunjuk Pengisian ─────────────────────────────────────
                $infoSheet = $spreadsheet->createSheet();
                $infoSheet->setTitle('Petunjuk');

                $infoSheet->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE IMPORT');
                $infoSheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '065F46']],
                ]);
                $infoSheet->getRowDimension(1)->setRowHeight(24);

                $petunjuk = [
                    [2,  'Kolom', 'Keterangan', 'Contoh'],
                    [3,  'A - NIS', 'Nomor Induk Siswa. Harus unik, tidak boleh duplikat.', '2024001'],
                    [4,  'B - NAMA LENGKAP', 'Nama lengkap siswa sesuai data sekolah.', 'Ahmad Fauzan'],
                    [5,  'C - KELAS', 'Nama kelas siswa.', 'XI TKJ 1'],
                    [7,  'Aturan Umum:', '', ''],
                    [8,  '1.', 'Baris 3 adalah header, jangan dihapus.', ''],
                    [9,  '2.', 'Data dimulai dari baris 4.', ''],
                    [10, '3.', 'NIS yang sudah ada di sistem: tergantung mode import.', ''],
                    [11, '4.', 'Mode Tambahkan: NIS duplikat dilewati (tidak diubah).', ''],
                    [12, '5.', 'Mode Update: NIS duplikat akan diupdate nama & kelasnya.', ''],
                    [13, '6.', 'Token login akan digenerate otomatis untuk siswa baru.', ''],
                    [14, '7.', 'Format file yang didukung: .xlsx, .xls, .csv (maks 5MB).', ''],
                ];

                foreach ($petunjuk as $p) {
                    [$rowNum, $col1, $col2, $col3] = $p;
                    $infoSheet->setCellValue("A{$rowNum}", $col1);
                    $infoSheet->setCellValue("B{$rowNum}", $col2);
                    $infoSheet->setCellValue("C{$rowNum}", $col3);
                }

                // Style header tabel petunjuk
                $infoSheet->getStyle('A2:C2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                               'startColor' => ['rgb' => '047857']],
                ]);

                // Style judul aturan
                $infoSheet->getStyle('A7')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                ]);

                $infoSheet->getColumnDimension('A')->setWidth(22);
                $infoSheet->getColumnDimension('B')->setWidth(60);
                $infoSheet->getColumnDimension('C')->setWidth(20);

                // Set active sheet ke sheet 1
                $spreadsheet->setActiveSheetIndex(0);

                // Tulis ke output
                $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $filename = 'template_import_siswa_ipm.xlsx';

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $filename, [
                    'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'Cache-Control'       => 'max-age=0',
                ]);
            } catch (\Throwable $e) {
                // Fallback ke CSV
            }
        }

        // Fallback CSV jika ZipArchive tidak terinstall di server
        $filename = 'template_import_siswa_ipm.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ];

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['NIS', 'NAMA LENGKAP', 'KELAS']);
            fputcsv($out, ['2024001', 'Ahmad Fauzan', 'XI TKJ 1']);
            fputcsv($out, ['2024002', 'Budi Santoso', 'XI TKJ 2']);
            fputcsv($out, ['2024003', 'Citra Dewi', 'XI AKL 1']);
            fclose($out);
        }, $filename, $headers);
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

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return back()->with('error', 'Format file tidak didukung. Harap upload file .xlsx, .xls, atau .csv.');
            }

            $mode = $request->input('mode', 'append');
            $rows = [];

            // 1. Coba baca dengan PhpSpreadsheet jika kelas/ekstensi tersedia
            try {
                if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $spreadsheet = IOFactory::load($file->getRealPath());
                    $sheet       = $spreadsheet->getActiveSheet();
                    $rows        = $sheet->toArray(null, true, true, true);
                }
            } catch (\Throwable $e) {
                // Fallback jika file CSV dan PhpSpreadsheet gagal (misal ZipArchive tidak terinstall)
                if ($extension === 'csv') {
                    $rows = $this->parseCsvFile($file->getRealPath());
                } else {
                    return back()->with('error', 'Gagal membaca file Excel (' . $e->getMessage() . '). Pastikan ekstensi php-zip aktif di server atau gunakan format .csv.');
                }
            }

            if (empty($rows)) {
                return back()->with('error', 'File Excel/CSV kosong atau tidak dapat dibaca.');
            }

            $imported = 0;
            $updated  = 0;
            $skipped  = 0;
            $errors   = [];
            $firstRow = true;

            foreach ($rows as $rowIndex => $row) {
                if ($firstRow) {
                    $firstRow = false;
                    continue;
                }

                // Normalisasi array dari PhpSpreadsheet (key A, B, C) atau array numerik (0, 1, 2)
                $nis   = trim((string) ($row['A'] ?? $row[0] ?? ''));
                $nama  = trim((string) ($row['B'] ?? $row[1] ?? ''));
                $kelas = trim((string) ($row['C'] ?? $row[2] ?? ''));

                // Lewati baris kosong
                if (empty($nis) && empty($nama)) {
                    continue;
                }

                // Validasi kolom wajib
                if (empty($nis) || empty($nama) || empty($kelas)) {
                    $errors[] = "Baris {$rowIndex}: NIS/Nama/Kelas tidak boleh kosong.";
                    $skipped++;
                    continue;
                }

                $existing = Student::where('nis', $nis)->first();

                if ($existing) {
                    if ($mode === 'update') {
                        $existing->update([
                            'nama'  => $nama,
                            'kelas' => $kelas,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $plainToken = strtoupper(Str::random(8));
                    Student::create([
                        'nis'         => $nis,
                        'nama'        => $nama,
                        'kelas'       => $kelas,
                        'token'       => Hash::make($plainToken),
                        'plain_token' => $plainToken,
                        'status'      => 'active',
                        'has_voted'   => false,
                    ]);
                    $imported++;
                }
            }

            AuditLogService::log(
                'IMPORT_STUDENTS',
                "Import file siswa — Mode: {$mode} | Ditambahkan: {$imported} | Diperbarui: {$updated} | Dilewati: {$skipped}"
            );

            $message = "Import selesai! Ditambahkan: {$imported} siswa";
            if ($updated > 0) $message .= ", Diperbarui: {$updated}";
            if ($skipped > 0) $message .= ", Dilewati: {$skipped}";
            if (!empty($errors)) $message .= '. Error: ' . implode('; ', array_slice($errors, 0, 3));

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
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    // Coba pisah dengan titik koma jika format CSV Indonesia (semicolon)
                    $data = explode(';', $data[0]);
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
