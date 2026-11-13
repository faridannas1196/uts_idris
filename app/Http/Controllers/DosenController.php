<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(): View
    {
        $pengajars = Dosen::query()
            ->with('user')
            ->withCount(['kelas', 'mahasiswas'])
            ->orderBy('nama')
            ->paginate(10);

        return view('pengajar.index', compact('pengajars'));
    }

    public function create(): View
    {
        return view('pengajar.form', ['pengajar' => new Dosen]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        DB::transaction(function () use ($validated): void {
            $pengajar = Dosen::query()->create([
                'nip' => $validated['nip'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
            ]);

            $user = User::query()->create([
                'name' => $validated['nama'],
                'username' => $validated['nip'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'dosen',
            ]);

            $pengajar->user()->associate($user);
            $pengajar->save();
        });

        return redirect()->route('pengajar.index')->with('success', 'Data pengajar berhasil ditambahkan.');
    }

    public function edit(Dosen $pengajar): View
    {
        return view('pengajar.form', compact('pengajar'));
    }

    public function update(Request $request, Dosen $pengajar): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($pengajar));

        DB::transaction(function () use ($validated, $pengajar): void {
            $pengajar->update([
                'nip' => $validated['nip'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
            ]);

            $user = $pengajar->user;

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $validated['nama'],
                    'username' => $validated['nip'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => 'dosen',
                ]);

                $pengajar->user()->associate($user);
                $pengajar->save();

                return;
            }

            $user->update([
                'name' => $validated['nama'],
                'username' => $validated['nip'],
                'email' => $validated['email'],
                'role' => 'dosen',
            ]);

            if (($validated['password'] ?? '') !== '') {
                $user->update(['password' => $validated['password']]);
            }
        });

        return redirect()->route('pengajar.index')->with('success', 'Data pengajar berhasil diperbarui.');
    }

    public function destroy(Dosen $pengajar): RedirectResponse
    {
        DB::transaction(function () use ($pengajar): void {
            $pengajar->user?->delete();
            $pengajar->delete();
        });

        return redirect()->route('pengajar.index')->with('success', 'Data pengajar berhasil dihapus.');
    }

    private function validationRules(?Dosen $pengajar = null): array
    {
        $userId = $pengajar?->user_id;
        $requiresPassword = $pengajar === null || $userId === null;

        return [
            'nip' => [
                'required',
                'string',
                'max:30',
                Rule::unique('dosens', 'nip')->ignore($pengajar?->id),
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('dosens', 'email')->ignore($pengajar?->id),
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [$requiresPassword ? 'required' : 'nullable', 'string', 'min:12', 'confirmed'],
        ];
    }
}
