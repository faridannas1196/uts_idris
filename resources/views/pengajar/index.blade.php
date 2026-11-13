@extends('layouts.app')

@section('title', 'Data Pengajar')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik</p>
            <h1 class="h2 mb-1">Data pengajar</h1>
            <p class="text-secondary mb-0">Kelola akun pengajar dan kelas yang diampu.</p>
        </div>
        <a href="{{ route('pengajar.create') }}" class="btn btn-success">Tambah pengajar</a>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIP</th>
                        <th>Nama pengajar</th>
                        <th>Email</th>
                        <th>Kelas diajar</th>
                        <th>Mahasiswa bimbingan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajars as $pengajar)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $pengajar->nip }}</td>
                            <td>{{ $pengajar->nama }}</td>
                            <td>{{ $pengajar->email ?? 'Belum diatur' }}</td>
                            <td>{{ $pengajar->kelas_count }}</td>
                            <td>{{ $pengajar->mahasiswas_count }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('pengajar.edit', $pengajar) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('pengajar.destroy', $pengajar) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pengajar dan akun masuknya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada data pengajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengajars->hasPages())
            <div class="card-body border-top">{{ $pengajars->links() }}</div>
        @endif
    </section>
@endsection
