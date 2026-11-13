<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique();
            $table->string('nama');
            $table->string('email')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 15)->unique();
            $table->string('nama');
            $table->unsignedTinyInteger('sks');
            $table->timestamps();
        });

        Schema::rename('Mahasiswa', 'mahasiswas');

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
        });

        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->restrictOnDelete();
            $table->string('tahun_ajaran', 9);
            $table->unsignedTinyInteger('semester');
            $table->decimal('nilai', 4, 2)->nullable();
            $table->timestamps();
            $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs');
        Schema::dropIfExists('mata_kuliahs');

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dosen_id');
            $table->dropConstrainedForeignId('program_studi_id');
        });

        Schema::rename('mahasiswas', 'Mahasiswa');
        Schema::dropIfExists('dosens');
        Schema::dropIfExists('program_studis');
    }
};
