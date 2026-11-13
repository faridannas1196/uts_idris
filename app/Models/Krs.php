<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mahasiswa_id', 'kelas_id', 'mata_kuliah_id', 'tahun_ajaran', 'semester', 'nilai', 'status'])]
class Krs extends Model
{
    protected $table = 'krs';

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * @return array{tahun_ajaran: string, semester: int}
     */
    public static function periodeAktif(): array
    {
        $tanggal = now();
        $bulan = $tanggal->month;
        $tahunMulai = $bulan >= 8 ? $tanggal->year : $tanggal->year - 1;
        $semester = $bulan >= 8 || $bulan === 1 ? 1 : 2;

        return [
            'tahun_ajaran' => $tahunMulai.'/'.($tahunMulai + 1),
            'semester' => $semester,
        ];
    }
}
