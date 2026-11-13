<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama', 'sks'])]
class MataKuliah extends Model
{
    protected $table = 'mata_kuliahs';

    public function programStudis(): BelongsToMany
    {
        return $this->belongsToMany(ProgramStudi::class, 'mata_kuliah_program_studi');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
