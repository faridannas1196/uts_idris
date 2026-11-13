<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function index(): View
    {
        $mataKuliahs = MataKuliah::query()
            ->with('programStudis')
            ->withCount('kelas')
            ->orderBy('nama')
            ->paginate(10);

        return view('mata-kuliah.index', compact('mataKuliahs'));
    }

    public function create(): View
    {
        return view('mata-kuliah.form', [
            'mataKuliah' => new MataKuliah,
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        DB::transaction(function () use ($validated): void {
            $mataKuliah = MataKuliah::query()->create([
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'sks' => $validated['sks'],
            ]);
            $mataKuliah->programStudis()->sync($validated['program_studi_ids']);
        });

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        return view('mata-kuliah.form', [
            'mataKuliah' => $mataKuliah->load('programStudis'),
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($mataKuliah));
        $kelasProgramStudiIds = $mataKuliah->kelas()
            ->whereNotNull('program_studi_id')
            ->pluck('program_studi_id')
            ->all();

        if (array_diff($kelasProgramStudiIds, $validated['program_studi_ids']) !== []) {
            throw ValidationException::withMessages([
                'program_studi_ids' => 'Program studi yang digunakan kelas tidak dapat dilepas dari mata kuliah.',
            ]);
        }

        DB::transaction(function () use ($validated, $mataKuliah): void {
            $mataKuliah->update([
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'sks' => $validated['sks'],
            ]);
            $mataKuliah->programStudis()->sync($validated['program_studi_ids']);
        });

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah): RedirectResponse
    {
        if ($mataKuliah->kelas()->exists()) {
            return redirect()
                ->route('mata-kuliah.index')
                ->with('error', 'Mata kuliah tidak dapat dihapus karena masih digunakan oleh kelas.');
        }

        $mataKuliah->delete();

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function validationRules(?MataKuliah $mataKuliah = null): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:15',
                Rule::unique('mata_kuliahs', 'kode')->ignore($mataKuliah?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:255'],
            'program_studi_ids' => ['required', 'array', 'min:1'],
            'program_studi_ids.*' => ['required', 'integer', 'distinct', 'exists:program_studis,id'],
        ];
    }
}
