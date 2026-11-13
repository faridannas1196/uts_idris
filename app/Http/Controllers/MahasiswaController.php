<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['aktif', 'tidak_aktif', 'cuti'])],
        ]);

        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $mahasiswas = Mahasiswa::query()
            ->with(['programStudi', 'dosen'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('mahasiswa.index', compact('mahasiswas', 'search', 'status'));
    }

    public function create(): View
    {
        return view('mahasiswa.form', [
            'mahasiswa' => new Mahasiswa,
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
            'dosens' => Dosen::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());
        $validated['wali_dosen'] = $validated['dosen_id'];

        Mahasiswa::query()->create($validated);

        return Redirect::route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        return view('mahasiswa.form', [
            'mahasiswa' => $mahasiswa,
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
            'dosens' => Dosen::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($mahasiswa));
        $validated['wali_dosen'] = $validated['dosen_id'];
        $mahasiswa->update($validated);

        return Redirect::route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->delete();

        return Redirect::route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    private function validationRules(?Mahasiswa $mahasiswa = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:30', Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('mahasiswas', 'email')->ignore($mahasiswa?->id)],
            'program_studi_id' => ['required', 'exists:program_studis,id'],
            'dosen_id' => ['required', 'exists:dosens,id'],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif', 'cuti'])],
        ];
    }
}
