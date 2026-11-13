@extends('layouts.app')

@section('title', 'Data Mahasiswa')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik</p>
            <h1 class="h2 mb-1">Data mahasiswa</h1>
            <p class="text-secondary mb-0">Kelola identitas, program studi, dan dosen pembimbing.</p>
        </div>
        <a href="{{ route('mahasiswa.create') }}" class="btn btn-success">Tambah mahasiswa</a>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('mahasiswa.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label">Cari mahasiswa</label>
                    <input id="search" name="search" type="search" class="form-control" value="{{ $search }}" placeholder="Nama atau NIM">
                </div>
                <div class="col-12 col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                        <option value="cuti" @selected($status === 'cuti')>Cuti</option>
                        <option value="tidak_aktif" @selected($status === 'tidak_aktif')>Tidak aktif</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-success flex-grow-1" type="submit">Terapkan</button>
                    <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIM</th>
                        <th>Nama mahasiswa</th>
                        <th>Program studi</th>
                        <th>Dosen pembimbing</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswas as $mahasiswa)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $mahasiswa->nim }}</td>
                            <td>
                                <div class="fw-semibold">{{ $mahasiswa->nama }}</div>
                                <div class="small text-secondary">{{ $mahasiswa->email }}</div>
                            </td>
                            <td>{{ $mahasiswa->programStudi?->nama ?? 'Belum diatur' }}</td>
                            <td>{{ $mahasiswa->dosen?->nama ?? 'Belum diatur' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'text-bg-success' => $mahasiswa->status === 'aktif',
                                    'text-bg-warning' => $mahasiswa->status === 'cuti',
                                    'text-bg-secondary' => $mahasiswa->status === 'tidak_aktif',
                                ])>{{ str_replace('_', ' ', ucfirst($mahasiswa->status)) }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('mahasiswa.edit', $mahasiswa) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('mahasiswa.destroy', $mahasiswa) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada data yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mahasiswas->hasPages())
            <div class="card-body border-top">{{ $mahasiswas->links() }}</div>
        @endif
    </section>
@endsection
