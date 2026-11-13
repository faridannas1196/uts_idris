<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_if($user === null, 403);

        if ($user->role === 'admin') {
            return view('dashboard', [
                'totalMahasiswa' => Mahasiswa::query()->count(),
                'mahasiswaAktif' => Mahasiswa::query()->where('status', 'aktif')->count(),
                'totalProgramStudi' => ProgramStudi::query()->count(),
                'mahasiswasTerbaru' => Mahasiswa::query()->with('programStudi')->latest()->limit(5)->get(),
            ]);
        }

        if ($user->role === 'dosen') {
            $dosen = $user->dosen()->with('mahasiswas.programStudi')->first();
            abort_if($dosen === null, 403);

            return view('dashboard.dosen', compact('dosen'));
        }

        if ($user->role === 'mahasiswa') {
            $mahasiswa = $user->mahasiswa()->with(['programStudi', 'dosen'])->first();
            abort_if($mahasiswa === null, 403);

            return view('dashboard.mahasiswa', compact('mahasiswa'));
        }

        abort(403);
    }
}
