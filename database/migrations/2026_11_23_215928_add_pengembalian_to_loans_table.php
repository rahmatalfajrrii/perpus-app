<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration: menambah dua kolom baru pada tabel loans.
     */
    public function up(): void
    {
        // Schema::table digunakan karena tabel loans sudah ada.
        Schema::table('loans', function (Blueprint $table) {

            // Kolom tanggal buku benar-benar dikembalikan.
            $table->date('tanggal_kembali')
                ->nullable()
                ->after('tanggal_kembali_rencana');

            // Kolom denda dalam rupiah.
            $table->unsignedInteger('denda')
                ->default(0)
                ->after('status');
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {

            // Menghapus dua kolom yang telah ditambahkan.
            $table->dropColumn(['tanggal_kembali', 'denda']);
        });
    }
};