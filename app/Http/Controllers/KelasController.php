<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\PengajuanKelas;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $daftarKelas = Kelas::query()
            ->with(['mataKuliah', 'programStudi', 'dosens'])
            ->withCount('krs')
            ->orderBy('kode')
            ->paginate(10);
        $pengajuans = PengajuanKelas::query()
            ->with(['dosen', 'kelas.mataKuliah', 'kelas.programStudi'])
            ->where('status', 'menunggu')
            ->oldest()
            ->get();

        return view('kelas.index', compact('daftarKelas', 'pengajuans'));
    }

    public function create(): View
    {
        return view('kelas.form', [
            'kelas' => new Kelas,
            'mataKuliahs' => MataKuliah::query()->with('programStudis')->orderBy('kode')->get(),
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
            'pengajars' => Dosen::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());
        $this->ensureCourseBelongsToProgram($validated);

        DB::transaction(function () use ($validated): void {
            $kelas = Kelas::query()->create([
                'kode' => $validated['kode'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'program_studi_id' => $validated['program_studi_id'],
            ]);
            $kelas->dosens()->sync($validated['dosen_ids'] ?? []);
        });

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('kelas.form', [
            'kelas' => $kelas->load('dosens'),
            'mataKuliahs' => MataKuliah::query()->with('programStudis')->orderBy('kode')->get(),
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
            'pengajars' => Dosen::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($kelas));
        $this->ensureCourseBelongsToProgram($validated);
        $this->ensureSectionScopeCanChange($kelas, $validated);

        DB::transaction(function () use ($validated, $kelas): void {
            $kelas->update([
                'kode' => $validated['kode'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'program_studi_id' => $validated['program_studi_id'],
            ]);
            $kelas->dosens()->sync($validated['dosen_ids'] ?? []);
        });

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if ($kelas->krs()->exists() || $kelas->pengajuanKelas()->exists()) {
            return redirect()
                ->route('kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki transaksi KRS atau pengajuan mengajar.');
        }

        $kelas->delete();

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    private function validationRules(?Kelas $kelas = null): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelas', 'kode')->ignore($kelas?->id),
            ],
            'mata_kuliah_id' => ['required', 'integer', 'exists:mata_kuliahs,id'],
            'program_studi_id' => ['required', 'integer', 'exists:program_studis,id'],
            'dosen_ids' => ['sometimes', 'array'],
            'dosen_ids.*' => ['required', 'integer', 'distinct', 'exists:dosens,id'],
        ];
    }

    /**
     * @param  array{mata_kuliah_id: int, program_studi_id: int}  $validated
     */
    private function ensureCourseBelongsToProgram(array $validated): void
    {
        $courseIsAssignedToProgram = MataKuliah::query()
            ->findOrFail($validated['mata_kuliah_id'])
            ->programStudis()
            ->whereKey($validated['program_studi_id'])
            ->exists();

        if (! $courseIsAssignedToProgram) {
            throw ValidationException::withMessages([
                'mata_kuliah_id' => 'Mata kuliah belum terhubung dengan program studi yang dipilih.',
            ]);
        }
    }

    /**
     * @param  array{mata_kuliah_id: int|string, program_studi_id: int|string}  $validated
     */
    private function ensureSectionScopeCanChange(Kelas $kelas, array $validated): void
    {
        if (! $kelas->krs()->exists()) {
            return;
        }

        if ((int) $kelas->mata_kuliah_id !== (int) $validated['mata_kuliah_id']) {
            throw ValidationException::withMessages([
                'mata_kuliah_id' => 'Mata kuliah tidak dapat diganti karena kelas sudah memiliki transaksi KRS.',
            ]);
        }

        $hasStudentFromAnotherProgram = $kelas->krs()
            ->whereHas('mahasiswa', function (Builder $query) use ($validated): void {
                $query->whereNull('program_studi_id')
                    ->orWhere('program_studi_id', '!=', $validated['program_studi_id']);
            })
            ->exists();

        if ($hasStudentFromAnotherProgram) {
            throw ValidationException::withMessages([
                'program_studi_id' => 'Program studi tidak dapat diubah karena kelas memiliki mahasiswa dari program studi lain.',
            ]);
        }
    }
}
