<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Kelas;
use App\Models\PengajuanKelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DosenKelasController extends Controller
{
    public function __invoke(Request $request): View
    {
        $periode = Krs::periodeAktif();
        $dosen = $request->user()?->dosen()->with([
            'kelas' => function (Relation $query) use ($periode): void {
                $query->with([
                    'mataKuliah',
                    'programStudi',
                    'dosens:id,nama,nip',
                    'krs' => function (Relation $query) use ($periode): void {
                        $query->where('tahun_ajaran', $periode['tahun_ajaran'])
                            ->where('semester', $periode['semester'])
                            ->where('status', 'disetujui')
                            ->with('mahasiswa:id,nama,nim')
                            ->orderBy('mahasiswa_id');
                    },
                ]);
            },
        ])->first();

        abort_if($dosen === null, 403);

        $daftarKelas = Kelas::query()
            ->with(['mataKuliah', 'programStudi'])
            ->whereDoesntHave('dosens', fn (Builder $query) => $query->where('dosens.id', $dosen->id))
            ->orderBy('kode')
            ->get();
        $pengajuanByKelas = $dosen->pengajuanKelas()->get()->keyBy('kelas_id');
        $pengajuanKrs = Krs::query()
            ->with(['mahasiswa', 'kelas.mataKuliah', 'kelas.programStudi'])
            ->where('tahun_ajaran', $periode['tahun_ajaran'])
            ->where('semester', $periode['semester'])
            ->where('status', 'menunggu')
            ->whereHas('kelas.dosens', fn (Builder $query) => $query->where('dosens.id', $dosen->id))
            ->oldest()
            ->get();

        return view('dosen.kelas', compact('dosen', 'daftarKelas', 'pengajuanByKelas', 'pengajuanKrs', 'periode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dosen = $request->user()?->dosen()->first();

        abort_if($dosen === null, 403);

        $validated = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);

        DB::transaction(function () use ($dosen, $validated): void {
            $kelas = Kelas::query()
                ->lockForUpdate()
                ->findOrFail($validated['kelas_id']);

            if ($dosen->kelas()->where('kelas.id', $kelas->id)->exists()) {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Anda sudah ditugaskan untuk mengajar kelas ini.',
                ]);
            }

            $pengajuan = PengajuanKelas::query()
                ->where('dosen_id', $dosen->id)
                ->where('mata_kuliah_id', $kelas->mata_kuliah_id)
                ->lockForUpdate()
                ->first();

            if ($pengajuan?->status === 'menunggu') {
                throw ValidationException::withMessages([
                    'kelas_id' => 'Pengajuan untuk mata kuliah ini sedang menunggu persetujuan admin.',
                ]);
            }

            if ($pengajuan === null) {
                $kelas->pengajuanKelas()->create([
                    'dosen_id' => $dosen->id,
                    'mata_kuliah_id' => $kelas->mata_kuliah_id,
                    'status' => 'menunggu',
                ]);

                return;
            }

            $pengajuan->update([
                'kelas_id' => $kelas->id,
                'status' => 'menunggu',
            ]);
        });

        return redirect()->route('dosen.kelas')->with('success', 'Pengajuan mengajar berhasil dikirim.');
    }
}
