<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'mata_kuliah_id', 'program_studi_id'])]
class Kelas extends Model
{
    protected $table = 'kelas';

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function dosens(): BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'dosen_kelas');
    }

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class);
    }

    public function pengajuanKelas(): HasMany
    {
        return $this->hasMany(PengajuanKelas::class);
    }
}
