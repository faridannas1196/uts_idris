@extends('layouts.app')

@section('title', $programStudi->exists ? 'Edit Program Studi' : 'Tambah Program Studi')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Program Studi</p>
        <h1 class="h2 mb-1">{{ $programStudi->exists ? 'Edit program studi' : 'Tambah program studi' }}</h1>
        <p class="text-secondary mb-0">Lengkapi kode dan nama program studi.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Periksa kembali data yang ditandai.</div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $programStudi->exists ? route('program-studi.update', $programStudi) : route('program-studi.store') }}" method="POST">
                @csrf
                @if ($programStudi->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="kode" class="form-label">Kode program studi</label>
                        <input id="kode" name="kode" value="{{ old('kode', $programStudi->kode) }}" class="form-control @error('kode') is-invalid @enderror" required maxlength="10">
                        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-8">
                        <label for="nama" class="form-label">Nama program studi</label>
                        <input id="nama" name="nama" value="{{ old('nama', $programStudi->nama) }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="255">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                    <a href="{{ route('program-studi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">{{ $programStudi->exists ? 'Simpan perubahan' : 'Simpan program studi' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
