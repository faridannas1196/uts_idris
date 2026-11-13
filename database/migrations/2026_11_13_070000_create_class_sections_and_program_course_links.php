<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah_program_studi', function (Blueprint $table) {
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
            $table->primary(['mata_kuliah_id', 'program_studi_id']);
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->restrictOnDelete();
            $table->string('kode', 80)->unique();
            $table->timestamps();
        });

        Schema::create('dosen_kelas', function (Blueprint $table) {
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['dosen_id', 'kelas_id']);
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->restrictOnDelete();
        });

        Schema::table('pengajuan_kelas', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->restrictOnDelete();
        });

        $kelasByMataKuliah = [];
        $kelasByMataKuliahDanProdi = [];
        $kelasLegacyByMataKuliah = [];

        foreach (DB::table('mata_kuliahs')->orderBy('id')->get(['id', 'kode']) as $mataKuliah) {
            $programStudiIds = DB::table('krs')
                ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
                ->where('krs.mata_kuliah_id', $mataKuliah->id)
                ->whereNotNull('mahasiswas.program_studi_id')
                ->distinct()
                ->orderBy('mahasiswas.program_studi_id')
                ->pluck('mahasiswas.program_studi_id');

            foreach ($programStudiIds as $programStudiId) {
                DB::table('mata_kuliah_program_studi')->insert([
                    'mata_kuliah_id' => $mataKuliah->id,
                    'program_studi_id' => $programStudiId,
                ]);

                $kelasId = DB::table('kelas')->insertGetId([
                    'mata_kuliah_id' => $mataKuliah->id,
                    'program_studi_id' => $programStudiId,
                    'kode' => $mataKuliah->kode.'-'.$mataKuliah->id.'-'.$programStudiId.'-A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $kelasByMataKuliahDanProdi[$mataKuliah->id.':'.$programStudiId] = $kelasId;
                $kelasByMataKuliah[$mataKuliah->id][] = $kelasId;
            }

            $hasKrsWithoutProgram = DB::table('krs')
                ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
                ->where('krs.mata_kuliah_id', $mataKuliah->id)
                ->whereNull('mahasiswas.program_studi_id')
                ->exists();
            if ($programStudiIds->isEmpty() || $hasKrsWithoutProgram) {
                $kelasLegacyByMataKuliah[$mataKuliah->id] = DB::table('kelas')->insertGetId([
                    'mata_kuliah_id' => $mataKuliah->id,
                    'kode' => $mataKuliah->kode.'-'.$mataKuliah->id.'-LEGACY',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $kelasByMataKuliah[$mataKuliah->id][] = $kelasLegacyByMataKuliah[$mataKuliah->id];
            }
        }

        foreach (DB::table('krs')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
            ->get(['krs.id', 'krs.mata_kuliah_id', 'mahasiswas.program_studi_id']) as $krs) {
            $kelasId = $krs->program_studi_id === null
                ? $kelasLegacyByMataKuliah[$krs->mata_kuliah_id]
                : $kelasByMataKuliahDanProdi[$krs->mata_kuliah_id.':'.$krs->program_studi_id];

            DB::table('krs')->where('id', $krs->id)->update(['kelas_id' => $kelasId]);
        }

        foreach ($kelasByMataKuliah as $mataKuliahId => $kelasIds) {
            DB::table('pengajuan_kelas')
                ->where('mata_kuliah_id', $mataKuliahId)
                ->update(['kelas_id' => $kelasIds[0]]);

            foreach (DB::table('dosen_mata_kuliah')
                ->where('mata_kuliah_id', $mataKuliahId)
                ->get(['dosen_id', 'created_at', 'updated_at']) as $assignment) {
                foreach ($kelasIds as $kelasId) {
                    DB::table('dosen_kelas')->insert([
                        'dosen_id' => $assignment->dosen_id,
                        'kelas_id' => $kelasId,
                        'created_at' => $assignment->created_at,
                        'updated_at' => $assignment->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('pengajuan_kelas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
        });

        Schema::dropIfExists('dosen_kelas');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mata_kuliah_program_studi');
    }
};
