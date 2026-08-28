<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\VoteDetail;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nama.required' => 'Nama calon wajib diisi.',
            'kelas.required' => 'Kelas wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto yang diizinkan: JPG, JPEG, PNG, WEBP.',
            'foto.max' => 'Ukuran foto maksimal 10MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $this->compressAndStoreImage($request->file('foto'), 'candidates');
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nama.required' => 'Nama calon wajib diisi.',
            'kelas.required' => 'Kelas wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Format foto yang diizinkan: JPG, JPEG, PNG, WEBP.',
            'foto.max' => 'Ukuran foto maksimal 10MB.',
            'status.required' => 'Status calon wajib dipilih.',
        ]);

        $fotoPath = $candidate->foto;
        if ($request->hasFile('foto')) {
            if ($candidate->foto && Storage::disk('public')->exists($candidate->foto)) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $fotoPath = $this->compressAndStoreImage($request->file('foto'), 'candidates');
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

    private function compressAndStoreImage($file, string $directory = 'candidates'): string
    {
        $filename = \Illuminate\Support\Str::random(40) . '.jpg';
        $fullDirectory = storage_path('app/public/' . $directory);

        if (!file_exists($fullDirectory)) {
            mkdir($fullDirectory, 0755, true);
        }

        $targetPath = $fullDirectory . '/' . $filename;

        if (extension_loaded('gd')) {
            try {
                $imageInfo = @getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $mime = $imageInfo['mime'];
                    $sourceImg = null;

                    switch ($mime) {
                        case 'image/jpeg':
                            $sourceImg = @imagecreatefromjpeg($file->getRealPath());
                            break;
                        case 'image/png':
                            $sourceImg = @imagecreatefrompng($file->getRealPath());
                            break;
                        case 'image/webp':
                            $sourceImg = @imagecreatefromwebp($file->getRealPath());
                            break;
                    }

                    if ($sourceImg) {
                        $origWidth = imagesx($sourceImg);
                        $origHeight = imagesy($sourceImg);

                        $maxWidth = 800;
                        $maxHeight = 800;

                        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
                            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
                            $newWidth = (int) round($origWidth * $ratio);
                            $newHeight = (int) round($origHeight * $ratio);
                        } else {
                            $newWidth = $origWidth;
                            $newHeight = $origHeight;
                        }

                        $newImg = imagecreatetruecolor($newWidth, $newHeight);
                        $white = imagecolorallocate($newImg, 255, 255, 255);
                        imagefill($newImg, 0, 0, $white);
                        imagecopyresampled($newImg, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                        imagejpeg($newImg, $targetPath, 82);
                        imagedestroy($sourceImg);
                        imagedestroy($newImg);

                        return $directory . '/' . $filename;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to standard Laravel file store if GD process fails
            }
        }

        return $file->store($directory, 'public');
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
        $filePath = public_path('templates/template_import_kandidat_ipm.xlsx');
        
        if (file_exists($filePath)) {
            return response()->download($filePath, 'template_import_kandidat_ipm.xlsx', [
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

            if (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsvFile($file->getRealPath());
            } else {
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
                    return back()->with('error', 'Gagal membaca file Excel (' . $e->getMessage() . '). Jika server tidak mendukung ekstensi php-zip, silakan simpan file sebagai format .csv lalu coba upload ulang.');
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

            $maxNomorUrut = Candidate::max('nomor_urut') ?? 0;

            foreach ($rows as $rowIndex => $row) {
                $rawNomorUrut = (string) ($row['A'] ?? $row[0] ?? '');
                $rawNama      = (string) ($row['B'] ?? $row[1] ?? '');
                $rawKelas     = (string) ($row['C'] ?? $row[2] ?? '');
                $rawNis       = (string) ($row['D'] ?? $row[3] ?? '');
                $rawVisi      = (string) ($row['E'] ?? $row[4] ?? '');
                $rawMisi      = (string) ($row['F'] ?? $row[5] ?? '');

                $nomorUrutRaw = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawNomorUrut));
                $nama         = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawNama));
                $kelas        = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawKelas));
                $nis          = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawNis));
                $visi         = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawVisi));
                $misi         = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $rawMisi));

                if ($firstRow) {
                    $firstRow = false;
                    $cellA = strtoupper($nomorUrutRaw);
                    if (str_contains($cellA, 'TEMPLATE') || str_contains($cellA, 'NOMOR') || str_contains($cellA, 'NO')) {
                        continue;
                    }
                }

                if (strtoupper($nomorUrutRaw) === 'NOMOR URUT' || strtoupper($nama) === 'NAMA LENGKAP' || str_contains(strtoupper($nomorUrutRaw), 'BARIS')) {
                    continue;
                }

                if (empty($nama) && empty($kelas)) {
                    continue;
                }

                if (empty($nama) || empty($kelas)) {
                    $errors[] = "Baris " . ($rowIndex + 1) . ": Nama dan Kelas tidak boleh kosong.";
                    $skipped++;
                    continue;
                }

                $nomorUrut = is_numeric($nomorUrutRaw) && intval($nomorUrutRaw) > 0 
                    ? intval($nomorUrutRaw) 
                    : ++$maxNomorUrut;

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
            if (!empty($errors)) $message .= '. Info: ' . implode('; ', array_slice($errors, 0, 3));

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengimport calon: ' . $e->getMessage());
        }
    }

    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = explode(';', $data[0]);
                } elseif (count($data) === 1 && str_contains($data[0], "\t")) {
                    $data = explode("\t", $data[0]);
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
