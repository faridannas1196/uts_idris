<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KrsApprovalController extends Controller
{
    public function update(Request $request, Krs $krs): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
        ]);
        $dosen = $request->user()?->dosen()->first();

        abort_if($dosen === null, 403);

        $periode = Krs::periodeAktif();

        DB::transaction(function () use ($dosen, $krs, $periode, $validated): void {
            $krs = Krs::query()
                ->with('kelas')
                ->lockForUpdate()
                ->findOrFail($krs->id);

            abort_unless(
                $krs->kelas->dosens()->where('dosens.id', $dosen->id)->exists()
                && $krs->tahun_ajaran === $periode['tahun_ajaran']
                && (int) $krs->semester === $periode['semester'],
                404,
            );

            if ($krs->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $krs->update(['status' => $validated['status']]);
        });

        $message = $validated['status'] === 'disetujui'
            ? 'Pengajuan mahasiswa disetujui.'
            : 'Pengajuan mahasiswa ditolak.';

        return redirect()->route('dosen.kelas')->with('success', $message);
    }
}
