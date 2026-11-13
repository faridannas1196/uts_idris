@extends('layouts.app')

@section('title', 'Program Studi')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik</p>
            <h1 class="h2 mb-1">Program studi</h1>
            <p class="text-secondary mb-0">Kelola program studi dan keterkaitannya dengan mahasiswa, mata kuliah, serta kelas.</p>
        </div>
        <a href="{{ route('program-studi.create') }}" class="btn btn-success">Tambah program studi</a>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama program studi</th>
                        <th>Mahasiswa</th>
                        <th>Mata kuliah</th>
                        <th>Kelas</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programStudis as $programStudi)
                        <tr>
                            <td class="ps-3">{{ $programStudi->kode }}</td>
                            <td>{{ $programStudi->nama }}</td>
                            <td>{{ $programStudi->mahasiswas_count }}</td>
                            <td>{{ $programStudi->mata_kuliahs_count }}</td>
                            <td>{{ $programStudi->kelas_count }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('program-studi.edit', $programStudi) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('program-studi.destroy', $programStudi) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus program studi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada program studi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($programStudis->hasPages())
            <div class="card-body border-top">{{ $programStudis->links() }}</div>
        @endif
    </section>
@endsection
