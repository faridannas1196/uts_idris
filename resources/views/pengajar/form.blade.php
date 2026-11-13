@extends('layouts.app')

@section('title', $pengajar->exists ? 'Edit Pengajar' : 'Tambah Pengajar')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Pengajar</p>
        <h1 class="h2 mb-1">{{ $pengajar->exists ? 'Edit pengajar' : 'Tambah pengajar' }}</h1>
        <p class="text-secondary mb-0">Akun masuk pengajar dibuat menggunakan NIP dan kata sandi.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Periksa kembali data yang ditandai.</div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $pengajar->exists ? route('pengajar.update', $pengajar) : route('pengajar.store') }}" method="POST">
                @csrf
                @if ($pengajar->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="nip" class="form-label">NIP / ID masuk</label>
                        <input id="nip" name="nip" value="{{ old('nip', $pengajar->nip) }}" class="form-control @error('nip') is-invalid @enderror" required maxlength="30">
                        @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="nama" class="form-label">Nama lengkap</label>
                        <input id="nama" name="nama" value="{{ old('nama', $pengajar->nama) }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="255">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $pengajar->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label">{{ $pengajar->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" @required(! $pengajar->exists || $pengajar->user_id === null) minlength="12" autocomplete="new-password">
                        <div class="form-text">Minimal 12 karakter. Akun pengajar masuk menggunakan NIP.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" @required(! $pengajar->exists || $pengajar->user_id === null) minlength="12" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                    <a href="{{ route('pengajar.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">{{ $pengajar->exists ? 'Simpan perubahan' : 'Simpan pengajar' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
