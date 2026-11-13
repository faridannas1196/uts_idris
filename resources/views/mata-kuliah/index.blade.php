@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik</p>
            <h1 class="h2 mb-1">Mata kuliah</h1>
            <p class="text-secondary mb-0">Kelola mata kuliah dan program studi yang menggunakannya.</p>
        </div>
        <a href="{{ route('mata-kuliah.create') }}" class="btn btn-success">Tambah mata kuliah</a>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama mata kuliah</th>
                        <th>SKS</th>
                        <th>Program studi</th>
                        <th>Kelas</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mataKuliahs as $mataKuliah)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $mataKuliah->kode }}</td>
                            <td>{{ $mataKuliah->nama }}</td>
                            <td>{{ $mataKuliah->sks }}</td>
                            <td>{{ $mataKuliah->programStudis->map(fn ($programStudi) => $programStudi->kode)->join(', ') ?: 'Belum diatur' }}</td>
                            <td>{{ $mataKuliah->kelas_count }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('mata-kuliah.edit', $mataKuliah) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('mata-kuliah.destroy', $mataKuliah) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mata kuliah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada mata kuliah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mataKuliahs->hasPages())
            <div class="card-body border-top">{{ $mataKuliahs->links() }}</div>
        @endif
    </section>
@endsection
