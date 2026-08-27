<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat student_id pada tabel votes nullable dan menghapus unique constraint
     * agar bisa menyimpan vote dummy untuk keperluan pengaturan hasil manual.
     */
    public function up(): void
    {
        // Handled directly during votes table creation
    }

    public function down(): void
    {
        //
    }
};
