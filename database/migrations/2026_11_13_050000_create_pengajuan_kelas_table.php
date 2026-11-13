<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->string('status')->default('menunggu');
            $table->timestamps();
            $table->unique(['dosen_id', 'mata_kuliah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kelas');
    }
};
