<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\VoteDetail;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateManagementController extends Controller
{
    public function index()
    {
        $candidates = Candidate::withCount('voteDetails')
            ->orderBy('nomor_urut', 'asc')
            ->get();

        $totalVotes = VoteDetail::count();

        foreach ($candidates as $candidate) {
            $candidate->percentage = $totalVotes > 0 
                ? round(($candidate->vote_details_count / $totalVotes) * 100, 2)
                : 0;
        }

        return view('admin.candidates.index', compact('candidates', 'totalVotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'kelas' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nama.required' => 'Nama calon wajib diisi.',
            'kelas.required' => 'Kelas wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto yang diizinkan: JPG, JPEG, PNG, WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('candidates', 'public');
        }

        $candidate = Candidate::create([
            'nomor_urut' => $request->input('nomor_urut'),
            'nama' => trim($request->input('nama')),
            'nis' => $request->input('nis') ? trim($request->input('nis')) : null,
            'kelas' => trim($request->input('kelas')),
            'foto' => $fotoPath,
            'visi' => $request->input('visi'),
            'misi' => $request->input('misi'),
            'status' => 'active',
        ]);

        AuditLogService::log('CREATE_CANDIDATE', "Menambahkan calon formatur: [{$candidate->nomor_urut}] {$candidate->nama}");

        return back()->with('success', "Calon formatur {$candidate->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Candidate $candidate)
    {
        $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'kelas' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $fotoPath = $candidate->foto;
        if ($request->hasFile('foto')) {
            if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $fotoPath = $request->file('foto')->store('candidates', 'public');
        }

        $candidate->update([
            'nomor_urut' => $request->input('nomor_urut'),
            'nama' => trim($request->input('nama')),
            'nis' => $request->input('nis') ? trim($request->input('nis')) : null,
            'kelas' => trim($request->input('kelas')),
            'foto' => $fotoPath,
            'visi' => $request->input('visi'),
            'misi' => $request->input('misi'),
            'status' => $request->input('status'),
        ]);

        AuditLogService::log('UPDATE_CANDIDATE', "Memperbarui calon formatur: [{$candidate->nomor_urut}] {$candidate->nama}");

        return back()->with('success', "Data calon {$candidate->nama} berhasil diperbarui.");
    }

    public function destroy(Candidate $candidate)
    {
        $nama = $candidate->nama;
        if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
            Storage::disk('public')->delete($candidate->foto);
        }
        $candidate->delete();

        AuditLogService::log('DELETE_CANDIDATE', "Menghapus calon formatur: {$nama}");

        return back()->with('success', "Calon formatur {$nama} berhasil dihapus.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:candidates,id',
        ]);

        $ids = $request->input('ids');
        $candidates = Candidate::whereIn('id', $ids)->get();

        foreach ($candidates as $candidate) {
            if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $candidate->delete();
        }

        $count = count($ids);
        AuditLogService::log('BULK_DELETE_CANDIDATES', "Hapus massal {$count} calon formatur. ID: " . implode(', ', $ids));

        return back()->with('success', "Berhasil menghapus {$count} calon formatur secara massal.");
    }

    public function downloadImportTemplate()
    {
        if (class_exists('ZipArchive')) {
            try {
                $spreadsheet = new Spreadsheet();

                // ── Sheet 1: Template Data ──────────────────────────────────────────
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Data Calon Formatur');

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
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'TEMPLATE IMPORT CALON FORMATUR — E-Voting Formatur IPM');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '065F46']],
                    'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'D1FAE5']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Catatan
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', '⚠ Baris 1 = Judul. Baris 2 = Catatan. Baris 3 = HEADER. Data mulai baris 4. Jangan ubah urutan kolom!');
                $sheet->getStyle('A2')->applyFromArray($noteStyle);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Header kolom (baris 3)
                $sheet->setCellValue('A3', 'NOMOR URUT');
                $sheet->setCellValue('B3', 'NAMA LENGKAP');
                $sheet->setCellValue('C3', 'KELAS');
                $sheet->setCellValue('D3', 'NIS (OPSIONAL)');
                $sheet->setCellValue('E3', 'VISI');
                $sheet->setCellValue('F3', 'MISI');
                $sheet->getStyle('A3:F3')->applyFromArray($headerStyle);
                $sheet->getRowDimension(3)->setRowHeight(22);

                // Contoh data (baris 4–6)
                $contohData = [
                    ['1', 'Ahmad Fauzan', 'XI TKJ 1', '2024001', 'Mewujudkan IPM yang aktif dan inovatif.', '1. Mengadakan pelatihan leadership.\n2. Menguatkan ukhuwah.'],
                    ['2', 'Budi Santoso', 'XI TKJ 2', '2024002', 'Menjadikan kader IPM berakhlak mulia.', '1. Pembiasaan ibadah bersama.\n2. Mengembangkan minat bakat.'],
                    ['3', 'Citra Dewi',   'XI AKL 1', '2024003', 'IPM Unggul dalam prestasi dan kreasi.', '1. Workshop digital kreatif.\n2. Bakti sosial sekolah.'],
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
                    $sheet->setCellValue("D{$rowNum}", $row[3]);
                    $sheet->setCellValue("E{$rowNum}", $row[4]);
                    $sheet->setCellValue("F{$rowNum}", $row[5]);
                    $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray($dataStyle);
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray($altStyle);
                    }
                    $sheet->getRowDimension($rowNum)->setRowHeight(22);
                }

                // Placeholder baris kosong tambahan (baris 7–30)
                for ($r = 7; $r <= 30; $r++) {
                    $sheet->getStyle("A{$r}:F{$r}")->applyFromArray($dataStyle);
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(15);  // No Urut
                $sheet->getColumnDimension('B')->setWidth(30);  // Nama
                $sheet->getColumnDimension('C')->setWidth(18);  // Kelas
                $sheet->getColumnDimension('D')->setWidth(18);  // NIS
                $sheet->getColumnDimension('E')->setWidth(35);  // Visi
                $sheet->getColumnDimension('F')->setWidth(40);  // Misi

                // Freeze pane
                $sheet->freezePane('A4');

                $writer   = new Xlsx($spreadsheet);
                $filename = 'template_import_kandidat_ipm.xlsx';

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $filename, [
                    'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'Cache-Control'       => 'max-age=0',
                ]);
            } catch (\Throwable $e) {
                // Fallback ke CSV jika terjadi error saat simpan Xlsx
            }
        }

        // Fallback CSV jika ZipArchive tidak terinstall di server
        $filename = 'template_import_kandidat_ipm.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ];

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['NOMOR URUT', 'NAMA LENGKAP', 'KELAS', 'NIS (OPSIONAL)', 'VISI', 'MISI']);
            fputcsv($out, ['1', 'Ahmad Fauzan', 'XI TKJ 1', '2024001', 'Mewujudkan IPM yang aktif dan inovatif.', '1. Mengadakan pelatihan leadership. 2. Menguatkan ukhuwah.']);
            fputcsv($out, ['2', 'Budi Santoso', 'XI TKJ 2', '2024002', 'Menjadikan kader IPM berakhlak mulia.', '1. Pembiasaan ibadah bersama. 2. Mengembangkan minat bakat.']);
            fputcsv($out, ['3', 'Citra Dewi', 'XI AKL 1', '2024003', 'IPM Unggul dalam prestasi dan kreasi.', '1. Workshop digital kreatif. 2. Bakti sosial sekolah.']);
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

            try {
                if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $spreadsheet = IOFactory::load($file->getRealPath());
                    $sheet       = $spreadsheet->getActiveSheet();
                    $rows        = $sheet->toArray(null, true, true, true);
                }
            } catch (\Throwable $e) {
                if ($extension === 'csv') {
                    $rows = $this->parseCsvFile($file->getRealPath());
                } else {
                    return back()->with('error', 'Gagal membaca file Excel (' . $e->getMessage() . '). Gunakan format .csv jika ekstensi php-zip tidak tersedia di server.');
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

            // Dapatkan nomor urut tertinggi jika mode append
            $maxNomorUrut = Candidate::max('nomor_urut') ?? 0;

            foreach ($rows as $rowIndex => $row) {
                // Lewati baris 1-3 jika format template (A1 Judul, A2 Catatan, A3 Header)
                // atau lewati baris 1 jika file standar
                if ($firstRow) {
                    $firstRow = false;
                    // Jika baris 1 mengandung kata TEMPLATE / NOMOR, ini header
                    $cellA = strtoupper(trim((string)($row['A'] ?? $row[0] ?? '')));
                    if (str_contains($cellA, 'TEMPLATE') || str_contains($cellA, 'NOMOR') || str_contains($cellA, 'NO')) {
                        continue;
                    }
                }

                $nomorUrutRaw = trim((string) ($row['A'] ?? $row[0] ?? ''));
                $nama         = trim((string) ($row['B'] ?? $row[1] ?? ''));
                $kelas        = trim((string) ($row['C'] ?? $row[2] ?? ''));
                $nis          = trim((string) ($row['D'] ?? $row[3] ?? ''));
                $visi         = trim((string) ($row['E'] ?? $row[4] ?? ''));
                $misi         = trim((string) ($row['F'] ?? $row[5] ?? ''));

                // Lewati header (Baris "NOMOR URUT", "NAMA LENGKAP", dsb)
                if (strtoupper($nomorUrutRaw) === 'NOMOR URUT' || strtoupper($nama) === 'NAMA LENGKAP' || str_contains(strtoupper($nomorUrutRaw), 'BARIS')) {
                    continue;
                }

                // Lewati baris kosong
                if (empty($nama) && empty($kelas)) {
                    continue;
                }

                // Validasi nama & kelas wajib
                if (empty($nama) || empty($kelas)) {
                    $errors[] = "Baris {$rowIndex}: Nama dan Kelas tidak boleh kosong.";
                    $skipped++;
                    continue;
                }

                $nomorUrut = is_numeric($nomorUrutRaw) && intval($nomorUrutRaw) > 0 
                    ? intval($nomorUrutRaw) 
                    : ++$maxNomorUrut;

                // Cari berdasarkan nomor urut atau nama
                $existing = Candidate::where('nomor_urut', $nomorUrut)
                    ->orWhere(function($q) use ($nama) {
                        $q->where('nama', $nama);
                    })->first();

                if ($existing) {
                    if ($mode === 'update') {
                        $existing->update([
                            'nomor_urut' => $nomorUrut,
                            'nama'       => $nama,
                            'kelas'      => $kelas,
                            'nis'        => !empty($nis) ? $nis : $existing->nis,
                            'visi'       => !empty($visi) ? $visi : $existing->visi,
                            'misi'       => !empty($misi) ? $misi : $existing->misi,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    Candidate::create([
                        'nomor_urut' => $nomorUrut,
                        'nama'       => $nama,
                        'kelas'      => $kelas,
                        'nis'        => !empty($nis) ? $nis : null,
                        'visi'       => !empty($visi) ? $visi : null,
                        'misi'       => !empty($misi) ? $misi : null,
                        'status'     => 'active',
                    ]);
                    $imported++;
                }
            }

            AuditLogService::log(
                'IMPORT_CANDIDATES',
                "Import calon formatur — Mode: {$mode} | Ditambahkan: {$imported} | Diperbarui: {$updated} | Dilewati: {$skipped}"
            );

            $message = "Import selesai! Ditambahkan: {$imported} calon";
            if ($updated > 0) $message .= ", Diperbarui: {$updated}";
            if ($skipped > 0) $message .= ", Dilewati: {$skipped}";
            if (!empty($errors)) $message .= '. Error: ' . implode('; ', array_slice($errors, 0, 3));

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengimport calon: ' . $e->getMessage());
        }
    }

    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = explode(';', $data[0]);
                }
                $rows[] = [
                    'A' => $data[0] ?? '',
                    'B' => $data[1] ?? '',
                    'C' => $data[2] ?? '',
                    'D' => $data[3] ?? '',
                    'E' => $data[4] ?? '',
                    'F' => $data[5] ?? '',
                ];
            }
            fclose($handle);
        }
        return $rows;
    }
}
