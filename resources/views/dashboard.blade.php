@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">BANDUNG · ADMINISTRATOR</p>
            <h1 class="h2 mb-1">Ringkasan akademik</h1>
            <p class="text-secondary mb-0">Pantau data mahasiswa dan program studi.</p>
        </div>
        <a href="{{ route('mahasiswa.create') }}" class="btn btn-success">Tambah mahasiswa</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="small text-secondary mb-2">Total mahasiswa</p>
                    <p class="display-6 fw-semibold mb-0">{{ number_format($totalMahasiswa) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="small text-secondary mb-2">Mahasiswa aktif</p>
                    <p class="display-6 fw-semibold text-success mb-0">{{ number_format($mahasiswaAktif) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="small text-secondary mb-2">Program studi</p>
                    <p class="display-6 fw-semibold mb-0">{{ number_format($totalProgramStudi) }}</p>
                </div>
            </div>
        </div>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h5 mb-0">Mahasiswa terbaru</h2>
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-sm btn-outline-success">Semua mahasiswa</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIM</th>
                        <th>Nama</th>
                        <th>Program studi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswasTerbaru as $mahasiswa)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $mahasiswa->nim }}</td>
                            <td>{{ $mahasiswa->nama }}</td>
                            <td>{{ $mahasiswa->programStudi?->nama ?? 'Belum diatur' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'text-bg-success' => $mahasiswa->status === 'aktif',
                                    'text-bg-warning' => $mahasiswa->status === 'cuti',
                                    'text-bg-secondary' => $mahasiswa->status === 'tidak_aktif',
                                ])>{{ str_replace('_', ' ', ucfirst($mahasiswa->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">Belum ada data mahasiswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection