<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KrsController extends Controller
{
    public function index(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa()->first();

        abort_if($mahasiswa === null, 403);

        $periode = Krs::periodeAktif();
        if ($mahasiswa->program_studi_id === null) {
            $kelasTerdaftar = collect();
            $pengajuanKrs = collect();
            $mataKuliahYangSudahDiprosesIds = collect();
            $daftarKelas = collect();
        } else {
            $kelasTerdaftar = $mahasiswa->krs()
                ->where('tahun_ajaran', $periode['tahun_ajaran'])
                ->where('semester', $periode['semester'])
                ->where('status', 'disetujui')
                ->whereHas('kelas', fn (Builder $query) => $query->where('program_studi_id', $mahasiswa->program_studi_id))
                ->with([
                    'kelas.mataKuliah',
                    'kelas.programStudi',
                    'kelas.dosens:id,nama,nip',
                    'kelas.krs' => function (Relation $query) use ($periode): void {
                        $query->where('tahun_ajaran', $periode['tahun_ajaran'])
                            ->where('semester', $periode['semester'])
                            ->where('status', 'disetujui')
                            ->with('mahasiswa:id,nama,nim')
                            ->orderBy('mahasiswa_id');
                    },
                ])
                ->get();
            $pengajuanKrs = $mahasiswa->krs()
                ->where('tahun_ajaran', $periode['tahun_ajaran'])
                ->where('semester', $periode['semester'])
                ->whereIn('status', ['menunggu', 'ditolak'])
                ->whereHas('kelas', fn (Builder $query) => $query->where('program_studi_id', $mahasiswa->program_studi_id))
                ->with(['kelas.mataKuliah', 'kelas.dosens:id,nama,nip'])
                ->orderBy('created_at')
                ->get();
            $mataKuliahYangSudahDiprosesIds = $mahasiswa->krs()
                ->where('tahun_ajaran', $periode['tahun_ajaran'])
                ->where('semester', $periode['semester'])
                ->pluck('mata_kuliah_id');
            $daftarKelas = Kelas::query()
                ->where('program_studi_id', $mahasiswa->program_studi_id)
                ->whereNotIn('mata_kuliah_id', $mataKuliahYangSudahDiprosesIds)
                ->whereHas('mataKuliah.programStudis', function (Builder $query) use ($mahasiswa): void {
                    $query->whereKey($mahasiswa->program_studi_id);
                })
                ->whereHas('dosens')
                ->with(['mataKuliah', 'programStudi', 'dosens:id,nama,nip'])
                ->orderBy('kode')
                ->get();
        }

        return view('mahasiswa.kelas', compact('mahasiswa', 'periode', 'kelasTerdaftar', 'pengajuanKrs', 'daftarKelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa()->first();

        abort_if($mahasiswa === null, 403);

        $validated = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);
        $periode = Krs::periodeAktif();

        DB::transaction(function () use ($mahasiswa, $validated, $periode): void {
            if ($mahasiswa->program_studi_id === null) {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Akun mahasiswa belum terhubung dengan program studi.',
                ]);
            }

            $kelas = Kelas::query()
                ->with('mataKuliah')
                ->lockForUpdate()
                ->where('program_studi_id', $mahasiswa->program_studi_id)
                ->findOrFail($validated['kelas_id']);

            if (! $kelas->dosens()->exists()) {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Kelas ini belum memiliki dosen pengajar sehingga belum dapat diajukan.',
                ]);
            }

            if (! $kelas->mataKuliah->programStudis()->whereKey($mahasiswa->program_studi_id)->exists()) {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Kelas ini tidak tersedia untuk program studi Anda.',
                ]);
            }

            $krs = $mahasiswa->krs()
                ->where('mata_kuliah_id', $kelas->mata_kuliah_id)
                ->where('tahun_ajaran', $periode['tahun_ajaran'])
                ->where('semester', $periode['semester'])
                ->lockForUpdate()
                ->first();

            if ($krs?->status === 'disetujui') {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Anda sudah terdaftar di mata kuliah ini pada periode akademik saat ini.',
                ]);
            }

            if ($krs?->status === 'menunggu') {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Pengajuan kelas ini sedang menunggu persetujuan dosen pengajar.',
                ]);
            }

            if ($krs === null) {
                $mahasiswa->krs()->create([
                    'kelas_id' => $kelas->id,
                    'mata_kuliah_id' => $kelas->mata_kuliah_id,
                    'tahun_ajaran' => $periode['tahun_ajaran'],
                    'semester' => $periode['semester'],
                    'status' => 'menunggu',
                ]);

                return;
            }

            $krs->update([
                'kelas_id' => $kelas->id,
                'status' => 'menunggu',
            ]);
        });

        return redirect()->route('mahasiswa.kelas')
            ->with('success', 'Pengajuan kelas berhasil dikirim dan menunggu persetujuan dosen.');
    }
}
