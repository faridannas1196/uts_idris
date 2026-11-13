@extends('layouts.app')

@section('title', $kelas->exists ? 'Edit Kelas' : 'Tambah Kelas')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Kelas</p>
        <h1 class="h2 mb-1">{{ $kelas->exists ? 'Edit kelas' : 'Tambah kelas' }}</h1>
        <p class="text-secondary mb-0">Buat section mata kuliah untuk satu program studi dan tentukan dosen pengajarnya.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Periksa kembali data yang ditandai.</div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $kelas->exists ? route('kelas.update', $kelas) : route('kelas.store') }}" method="POST">
                @csrf
                @if ($kelas->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="kode" class="form-label">Kode section</label>
                        <input id="kode" name="kode" value="{{ old('kode', $kelas->kode) }}" class="form-control @error('kode') is-invalid @enderror" required maxlength="80" placeholder="IF101-A">
                        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="program_studi_id" class="form-label">Program studi</label>
                        <select id="program_studi_id" name="program_studi_id" class="form-select @error('program_studi_id') is-invalid @enderror" required>
                            <option value="">Pilih program studi</option>
                            @foreach ($programStudis as $programStudi)
                                <option value="{{ $programStudi->id }}" @selected((string) old('program_studi_id', $kelas->program_studi_id) === (string) $programStudi->id)>{{ $programStudi->kode }} - {{ $programStudi->nama }}</option>
                            @endforeach
                        </select>
                        @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="mata_kuliah_id" class="form-label">Mata kuliah</label>
                        <select id="mata_kuliah_id" name="mata_kuliah_id" class="form-select @error('mata_kuliah_id') is-invalid @enderror" required>
                            <option value="">Pilih mata kuliah</option>
                            @foreach ($mataKuliahs as $mataKuliah)
                                <option value="{{ $mataKuliah->id }}" @selected((string) old('mata_kuliah_id', $kelas->mata_kuliah_id) === (string) $mataKuliah->id)>{{ $mataKuliah->kode }} - {{ $mataKuliah->nama }} ({{ $mataKuliah->programStudis->pluck('kode')->join(', ') ?: 'belum dipetakan' }})</option>
                            @endforeach
                        </select>
                        @error('mata_kuliah_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <span class="form-label d-block">Pengajar (opsional)</span>
                        @php
                            $selectedDosenIds = old('dosen_ids', $kelas->exists ? $kelas->dosens->modelKeys() : []);
                            $selectedDosenIds = is_array($selectedDosenIds) ? array_map('strval', $selectedDosenIds) : [];
                        @endphp
                        <div class="border rounded p-3 @error('dosen_ids') border-danger @enderror">
                            @foreach ($pengajars as $pengajar)
                                <div class="form-check">
                                    <input id="dosen_{{ $pengajar->id }}" name="dosen_ids[]" type="checkbox" value="{{ $pengajar->id }}" class="form-check-input" @checked(in_array((string) $pengajar->id, $selectedDosenIds, true))>
                                    <label for="dosen_{{ $pengajar->id }}" class="form-check-label">{{ $pengajar->nama }} ({{ $pengajar->nip }})</label>
                                </div>
                            @endforeach
                            @if ($pengajars->isEmpty())
                                <p class="text-secondary mb-0">Belum ada data pengajar. Kelas tetap dapat disimpan tanpa pengajar.</p>
                            @endif
                            @if ($pengajars->isEmpty())
                                <a href="{{ route('pengajar.create') }}" class="btn btn-sm btn-outline-success mt-2">Tambah pengajar</a>
                            @endif
                        </div>
                        <div class="form-text">Pengajar bersifat opsional. Dosen dapat mengajukan diri untuk mengajar section ini.</div>
                        @error('dosen_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error('dosen_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                    <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">{{ $kelas->exists ? 'Simpan perubahan' : 'Simpan kelas' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
