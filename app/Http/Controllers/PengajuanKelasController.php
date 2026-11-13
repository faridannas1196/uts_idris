<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengajuanKelasController extends Controller
{
    public function update(Request $request, PengajuanKelas $pengajuanKelas): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
        ]);

        DB::transaction(function () use ($validated, $pengajuanKelas): void {
            $pengajuanKelas = PengajuanKelas::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanKelas->id);

            if ($pengajuanKelas->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            if ($validated['status'] === 'disetujui') {
                $pengajuanKelas->kelas
                    ->dosens()
                    ->syncWithoutDetaching([$pengajuanKelas->dosen_id]);
            }

            $pengajuanKelas->update(['status' => $validated['status']]);
        });

        $message = $validated['status'] === 'disetujui'
            ? 'Pengajuan disetujui dan dosen ditambahkan ke kelas.'
            : 'Pengajuan mengajar ditolak.';

        return redirect()->route('kelas.index')->with('success', $message);
    }
}
