@extends('layouts.app')

@section('title', $mataKuliah->exists ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Mata Kuliah</p>
        <h1 class="h2 mb-1">{{ $mataKuliah->exists ? 'Edit mata kuliah' : 'Tambah mata kuliah' }}</h1>
        <p class="text-secondary mb-0">Pilih satu atau beberapa program studi yang menggunakan mata kuliah ini.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Periksa kembali data yang ditandai.</div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $mataKuliah->exists ? route('mata-kuliah.update', $mataKuliah) : route('mata-kuliah.store') }}" method="POST">
                @csrf
                @if ($mataKuliah->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="kode" class="form-label">Kode mata kuliah</label>
                        <input id="kode" name="kode" value="{{ old('kode', $mataKuliah->kode) }}" class="form-control @error('kode') is-invalid @enderror" required maxlength="15">
                        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="nama" class="form-label">Nama mata kuliah</label>
                        <input id="nama" name="nama" value="{{ old('nama', $mataKuliah->nama) }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="255">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="sks" class="form-label">Jumlah SKS</label>
                        <input id="sks" name="sks" type="number" value="{{ old('sks', $mataKuliah->sks) }}" class="form-control @error('sks') is-invalid @enderror" required min="1" max="255">
                        @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <span class="form-label d-block">Program studi</span>
                        @php
                            $selectedProgramStudiIds = old('program_studi_ids', $mataKuliah->exists ? $mataKuliah->programStudis->modelKeys() : []);
                            $selectedProgramStudiIds = is_array($selectedProgramStudiIds) ? array_map('strval', $selectedProgramStudiIds) : [];
                        @endphp
                        <div class="border rounded p-3 @error('program_studi_ids') border-danger @enderror">
                            @forelse ($programStudis as $programStudi)
                                <div class="form-check">
                                    <input id="program_studi_{{ $programStudi->id }}" name="program_studi_ids[]" type="checkbox" value="{{ $programStudi->id }}" class="form-check-input" @checked(in_array((string) $programStudi->id, $selectedProgramStudiIds, true))>
                                    <label for="program_studi_{{ $programStudi->id }}" class="form-check-label">{{ $programStudi->kode }} - {{ $programStudi->nama }}</label>
                                </div>
                            @empty
                                <p class="text-secondary mb-0">Tambahkan program studi sebelum mengaitkan mata kuliah.</p>
                            @endforelse
                        </div>
                        <div class="form-text">Setiap mata kuliah harus digunakan setidaknya oleh satu program studi.</div>
                        @error('program_studi_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error('program_studi_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
                    <a href="{{ route('mata-kuliah.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">{{ $mataKuliah->exists ? 'Simpan perubahan' : 'Simpan mata kuliah' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
