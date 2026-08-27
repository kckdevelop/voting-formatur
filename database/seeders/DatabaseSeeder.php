<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Settings
        Setting::set('school_name', 'SMK Muhammadiyah 1 Bantul');
        Setting::set('election_name', 'Pemilihan Ketua & Formatur IPM');
        Setting::set('election_year', '2026/2027');
        Setting::set('max_choices', 9, 'integer');
        Setting::set('election_status', 'open');
        Setting::set('public_results', true, 'boolean');

        // 2. Admin Account
        Admin::updateOrCreate(
            ['email' => 'admin@smkmuh1bantul.sch.id'],
            [
                'name' => 'Panitia Pemilihan IPM',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
            ]
        );

        // 3. Candidates Seeder (12 Formatur Candidates)
        $candidatesData = [
            ['nomor_urut' => 1, 'nama' => 'Ahmad Fauzan', 'nis' => '10101', 'kelas' => 'XI TKJ 1', 'visi' => 'Mewujudkan IPM SMK Muhammadiyah 1 Bantul yang berakhlak mulia, adaptif, dan berjiwa kepemimpinan islami.', 'misi' => "1. Mengembangkan potensi kepemimpinan kader melalui pelatihan berkala.\n2. Mengoptimalkan kegiatan keislaman dan literasi digital di lingkungan sekolah."],
            ['nomor_urut' => 2, 'nama' => 'Budi Santoso', 'nis' => '10102', 'kelas' => 'XI TKJ 2', 'visi' => 'Menjadikan IPM sebagai wadah kreasi, inovasi, dan kolaborasi bagi seluruh pelajar.', 'misi' => "1. Mengadakan perlombaan minat bakat antar kelas secara berkala.\n2. Mendorong keaktifan komunitas ekstrakurikuler sekolah."],
            ['nomor_urut' => 3, 'nama' => 'Citra Dewi', 'nis' => '10103', 'kelas' => 'XI AKL 1', 'visi' => 'IPM yang responsif, inklusif, dan menjunjung tinggi nilai persaudaraan Muhammadiyah.', 'misi' => "1. Membuka kanal aspirasi pelajar secara terbuka.\n2. Menggalakkan gerakan peduli sesama dan bakti sosial."],
            ['nomor_urut' => 4, 'nama' => 'Dimas Pratama', 'nis' => '10104', 'kelas' => 'XI DKV 1', 'visi' => 'Pelajar Muhammadiyah kreatif, menguasai teknologi, dan berwawasan global.', 'misi' => "1. Pelatihan desain grafis & media sosial bagi anggota IPM.\n2. Pembuatan konten dakwah kreatif digital."],
            ['nomor_urut' => 5, 'nama' => 'Eka Nurhaliza', 'nis' => '10105', 'kelas' => 'XI AKL 2', 'visi' => 'Terwujudnya ketauladanan pelajar yang disiplin, berprestasi, dan mandiri.', 'misi' => "1. Program pendampingan kelompok belajar siswa.\n2. Penguatan kajian keislaman remaja rutin."],
            ['nomor_urut' => 6, 'nama' => 'Farhan Hidayat', 'nis' => '10106', 'kelas' => 'XI TBSM 1', 'visi' => 'Mewujudkan IPM yang aktif, solidaritas tinggi, dan mandiri berwirausaha.', 'misi' => "1. Pengembangan unit kewirausahaan IPM Mart.\n2. Kegiatan olahraga dan kebugaran jasmani pelajar."],
            ['nomor_urut' => 7, 'nama' => 'Gilang Ramadhan', 'nis' => '10107', 'kelas' => 'XI TBSM 2', 'visi' => 'Membangun karakter kader yang solid, religius, dan berprestasi.', 'misi' => "1. Pembiasaan ibadah bersama dan kajian rutin.\n2. Penguatan solidaritas antar angkatan."],
            ['nomor_urut' => 8, 'nama' => 'Hani Rahmawati', 'nis' => '10108', 'kelas' => 'XI DKV 2', 'visi' => 'Menjadi pelopor gerakan pelajar berakhlak terpuji dan berwawasan lingkungan.', 'misi' => "1. Gerakan sekolah hijau dan bebas sampah plastik.\n2. Pekan seni dan kebudayaan islami."],
            ['nomor_urut' => 9, 'nama' => 'Irfan Hakim', 'nis' => '10109', 'kelas' => 'XI TKJ 1', 'visi' => 'IPM Cerdas: Cepat, Empati, Religius, Disiplin, Adaptif, dan Solutif.', 'misi' => "1. Penggunaan aplikasi digital untuk kegiatan organisasi.\n2. Gerakan solidaritas bencana dan kemanusiaan."],
            ['nomor_urut' => 10, 'nama' => 'Jihan Fahira', 'nis' => '10110', 'kelas' => 'XI AKL 1', 'visi' => 'Menguatkan sinergi pelajar Muhammadiyah yang santun dan unggul.', 'misi' => "1. Pembentukan forum diskusi pelajar aktif.\n2. Pelatihan publik speaking dan kepemimpinan."],
            ['nomor_urut' => 11, 'nama' => 'Kiki Rizky', 'nis' => '10111', 'kelas' => 'XI DKV 1', 'visi' => 'Inovasi karya pelajar untuk memajukan almamater sekolah.', 'misi' => "1. Pameran karya siswa dan pentas seni sekolah.\n2. Majalah dinding dan buletin sekolah digital."],
            ['nomor_urut' => 12, 'nama' => 'Luqman Hakim', 'nis' => '10112', 'kelas' => 'XI TBSM 1', 'visi' => 'Kader teladan yang menjunjung tinggi kebersamaan dan integritas.', 'misi' => "1. Pendampingan kegiatan ekstrakurikuler keagamaan.\n2. Program bakti sosial masyarakat binaan."],
        ];

        foreach ($candidatesData as $cData) {
            Candidate::updateOrCreate(
                ['nomor_urut' => $cData['nomor_urut']],
                array_merge($cData, ['status' => 'active'])
            );
        }

        // 4. Seed Students (30 Demo Students)
        $classes = ['X TKJ 1', 'X TKJ 2', 'XI TKJ 1', 'XI AKL 1', 'XI DKV 1', 'XI TBSM 1'];
        $sampleNames = [
            'Aditya Pratama', 'Anisa Rahma', 'Bagas Wijaya', 'Bintang Kartika', 'Daffa Al-Faqih',
            'Dewi Anggraini', 'Fajar Ramadhan', 'Fitri Handayani', 'Galang Kurnia', 'Indah Permata',
            'Kurniawan Sudi', 'Lestari Putri', 'Muhammad Alif', 'Nabila Syahrani', 'Oki Setiawan',
            'Putri Ayu', 'Qonita Az-Zahra', 'Rahmat Hidayat', 'Rizky Febrian', 'Siti Nurhaliza',
            'Tegar Saputra', 'Umar Faruq', 'Vina Panduwinata', 'Wahyu Hidayat', 'Xena Gabriella',
            'Yusuf Maulana', 'Zahra Amelia', 'Aris Munandar', 'Bayu Nurhadi', 'Cahyo Santoso'
        ];

        foreach ($sampleNames as $idx => $nama) {
            $nis = (string) (20000 + $idx + 1);
            $plainToken = strtoupper(Str::random(8));
            $kelas = $classes[$idx % count($classes)];

            Student::updateOrCreate(
                ['nis' => $nis],
                [
                    'nama' => $nama,
                    'kelas' => $kelas,
                    'token' => Hash::make($plainToken),
                    'plain_token' => $plainToken,
                    'status' => 'active',
                    'has_voted' => false,
                ]
            );
        }
    }
}
