<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramStudiController extends Controller
{
    public function index(): View
    {
        $programStudis = ProgramStudi::query()
            ->withCount(['mahasiswas', 'mataKuliahs', 'kelas'])
            ->orderBy('nama')
            ->paginate(10);

        return view('program-studi.index', compact('programStudis'));
    }

    public function create(): View
    {
        return view('program-studi.form', ['programStudi' => new ProgramStudi]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProgramStudi::query()->create($request->validate($this->validationRules()));

        return redirect()->route('program-studi.index')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function edit(ProgramStudi $programStudi): View
    {
        return view('program-studi.form', compact('programStudi'));
    }

    public function update(Request $request, ProgramStudi $programStudi): RedirectResponse
    {
        $programStudi->update($request->validate($this->validationRules($programStudi)));

        return redirect()->route('program-studi.index')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(ProgramStudi $programStudi): RedirectResponse
    {
        if ($programStudi->mahasiswas()->exists() || $programStudi->mataKuliahs()->exists() || $programStudi->kelas()->exists()) {
            return redirect()->route('program-studi.index')
                ->with('error', 'Program studi tidak dapat dihapus karena masih digunakan mahasiswa, mata kuliah, atau kelas.');
        }

        $programStudi->delete();

        return redirect()->route('program-studi.index')->with('success', 'Program studi berhasil dihapus.');
    }

    private function validationRules(?ProgramStudi $programStudi = null): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('program_studis', 'kode')->ignore($programStudi?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }
}
