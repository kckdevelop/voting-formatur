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
        Schema::table('votes', function (Blueprint $table) {
            // Hapus foreign key & unique constraint lama
            $table->dropForeign(['student_id']);
            $table->dropUnique('votes_student_id_unique');

            // Buat ulang student_id sebagai nullable tanpa unique
            $table->unsignedBigInteger('student_id')->nullable()->change();

            // Tambahkan kembali foreign key tanpa unique
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['student_id']);

            $table->unsignedBigInteger('student_id')->nullable(false)->change();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->unique('student_id');
        });
    }
};
