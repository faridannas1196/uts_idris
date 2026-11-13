<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nip', 'nama', 'email'])]
class Dosen extends Model
{
    protected $table = 'dosens';

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'dosen_kelas');
    }

    public function pengajuanKelas(): HasMany
    {
        return $this->hasMany(PengajuanKelas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
