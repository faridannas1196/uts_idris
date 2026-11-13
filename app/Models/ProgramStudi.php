<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama'])]
class ProgramStudi extends Model
{
    protected $table = 'program_studis';

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_program_studi');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
