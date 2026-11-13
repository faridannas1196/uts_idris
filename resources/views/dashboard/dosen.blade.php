@extends('layouts.app')

@section('title', 'Mahasiswa Bimbingan')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">BANDUNG · DOSEN</p>
            <h1 class="h2 mb-1">Mahasiswa bimbingan</h1>
            <p class="text-secondary mb-0">{{ $dosen->nama }} · NIP {{ $dosen->nip }}</p>
        </div>
        <span class="badge text-bg-primary fs-6">{{ $dosen->mahasiswas->count() }} mahasiswa</span>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIM</th>
                        <th>Nama mahasiswa</th>
                        <th>Program studi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dosen->mahasiswas as $mahasiswa)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $mahasiswa->nim }}</td>
                            <td>{{ $mahasiswa->nama }}</td>
                            <td>{{ $mahasiswa->programStudi?->nama ?? 'Belum diatur' }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($mahasiswa->status)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">Belum ada mahasiswa bimbingan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection