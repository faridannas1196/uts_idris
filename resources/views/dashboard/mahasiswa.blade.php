@extends('layouts.app')

@section('title', 'Profil Mahasiswa')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-success mb-2">BANDUNG · MAHASISWA</p>
        <h1 class="h2 mb-1">Profil mahasiswa</h1>
        <p class="text-secondary mb-0">Informasi akademik akun Anda.</p>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <p class="small text-secondary mb-1">Nama lengkap</p>
                    <h2 class="h4 mb-0">{{ $mahasiswa->nama }}</h2>
                </div>
                <span @class([
                    'badge align-self-start',
                    'text-bg-success' => $mahasiswa->status === 'aktif',
                    'text-bg-warning' => $mahasiswa->status === 'cuti',
                    'text-bg-secondary' => $mahasiswa->status === 'tidak_aktif',
                ])>{{ str_replace('_', ' ', ucfirst($mahasiswa->status)) }}</span>
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-4 py-2 border-top">NIM</dt>
                <dd class="col-sm-8 py-2 border-top mb-0">{{ $mahasiswa->nim }}</dd>
                <dt class="col-sm-4 py-2 border-top">Email</dt>
                <dd class="col-sm-8 py-2 border-top mb-0">{{ $mahasiswa->email }}</dd>
                <dt class="col-sm-4 py-2 border-top">Program studi</dt>
                <dd class="col-sm-8 py-2 border-top mb-0">{{ $mahasiswa->programStudi?->nama ?? 'Belum diatur' }}</dd>
                <dt class="col-sm-4 py-2 border-top">Dosen pembimbing</dt>
                <dd class="col-sm-8 py-2 border-top mb-0">{{ $mahasiswa->dosen?->nama ?? 'Belum diatur' }}</dd>
                <dt class="col-sm-4 py-2 border-top">ID pengguna</dt>
                <dd class="col-sm-8 py-2 border-top mb-0">{{ $mahasiswa->user_id }}</dd>
            </dl>
        </div>
    </section>
@endsection