@extends('layouts.app')

@section('title', $mahasiswa->exists ? 'Edit Mahasiswa' : 'Tambah Mahasiswa')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Mahasiswa</p>
        <h1 class="h2 mb-1">{{ $mahasiswa->exists ? 'Edit mahasiswa' : 'Tambah mahasiswa' }}</h1>
        <p class="text-secondary mb-0">Lengkapi informasi mahasiswa dan relasi akademiknya.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Periksa kembali data yang ditandai.</div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $mahasiswa->exists ? route('mahasiswa.update', $mahasiswa) : route('mahasiswa.store') }}" method="POST">
                @csrf
                @if ($mahasiswa->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="nim" class="form-label">NIM</label>
                        <input id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" class="form-control @error('nim') is-invalid @enderror" required maxlength="30">
                        @error('nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="nama" class="form-label">Nama lengkap</label>
                        <input id="nama" name="nama" value="{{ old('nama', $mahasiswa->nama) }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="255">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $mahasiswa->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="status" class="form-label">Status mahasiswa</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">Pilih status</option>
                            <option value="aktif" @selected(old('status', $mahasiswa->status) === 'aktif')>Aktif</option>
                            <option value="cuti" @selected(old('status', $mahasiswa->status) === 'cuti')>Cuti</option>
                            <option value="tidak_aktif" @selected(old('status', $mahasiswa->status) === 'tidak_aktif')>Tidak aktif</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="program_studi_id" class="form-label">Program studi</label>
                        <select id="program_studi_id" name="program_studi_id" class="form-select @error('program_studi_id') is-invalid @enderror" required>
                            <option value="">Pilih program studi</option>
                            @foreach ($programStudis as $programStudi)
                                <option value="{{ $programStudi->id }}" @selected((string) old('program_studi_id', $mahasiswa->program_studi_id) === (string) $programStudi->id)>{{ $programStudi->kode }} - {{ $programStudi->nama }}</option>
                            @endforeach
                        </select>
                        @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="dosen_id" class="form-label">Dosen pembimbing</label>
                        <select id="dosen_id" name="dosen_id" class="form-select @error('dosen_id') is-invalid @enderror" required>
                            <option value="">Pilih dosen</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" @selected((string) old('dosen_id', $mahasiswa->dosen_id) === (string) $dosen->id)>{{ $dosen->nama }} ({{ $dosen->nip }})</option>
                            @endforeach
                        </select>
                        @error('dosen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                    <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">{{ $mahasiswa->exists ? 'Simpan perubahan' : 'Simpan mahasiswa' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
