@extends('layouts.app')

@section('title', 'KRS dan Kelas')

@section('content')
    @if ($mahasiswa->program_studi_id === null)
        <div class="alert alert-warning" role="alert">
            Data program studi Anda belum diatur. Hubungi admin agar dapat mengakses kelas yang sesuai.
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Mahasiswa</p>
            <h1 class="h2 mb-1">KRS dan kelas</h1>
            <p class="text-secondary mb-0">{{ $mahasiswa->nama }} · NIM {{ $mahasiswa->nim }}</p>
        </div>
        <span class="badge text-bg-primary fs-6">{{ $periode['tahun_ajaran'] }} · Semester {{ $periode['semester'] }}</span>
    </div>

    @if ($errors->has('kelas_id'))
        <div class="alert alert-danger" role="alert">{{ $errors->first('kelas_id') }}</div>
    @endif

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Kelas yang saya ikuti</h2>
            <p class="text-secondary mb-0">Daftar mahasiswa dan dosen hanya tersedia setelah pengajuan kelas disetujui.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode section</th>
                        <th>Mata kuliah</th>
                        <th>Program studi</th>
                        <th>SKS</th>
                        <th>Dosen pengajar</th>
                        <th>Mahasiswa terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelasTerdaftar as $krs)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $krs->kelas->kode }}</td>
                            <td>{{ $krs->kelas->mataKuliah->kode }} · {{ $krs->kelas->mataKuliah->nama }}</td>
                            <td>{{ $krs->kelas->programStudi?->nama ?? 'Belum dipetakan' }}</td>
                            <td>{{ $krs->kelas->mataKuliah->sks }}</td>
                            <td>
                                @forelse ($krs->kelas->dosens as $dosen)
                                    <div>{{ $dosen->nama }} ({{ $dosen->nip }})</div>
                                @empty
                                    <span class="text-secondary">Belum ada dosen.</span>
                                @endforelse
                            </td>
                            <td>
                                @forelse ($krs->kelas->krs as $peserta)
                                    <div>{{ $peserta->mahasiswa->nama }} ({{ $peserta->mahasiswa->nim }})</div>
                                @empty
                                    <span class="text-secondary">Belum ada mahasiswa.</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Anda belum terdaftar di kelas pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Pengajuan kelas</h2>
            <p class="text-secondary mb-0">Akses data kelas dibuka setelah disetujui salah satu dosen pengajar.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode section</th>
                        <th>Mata kuliah</th>
                        <th>Dosen pengajar</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuanKrs as $pengajuan)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $pengajuan->kelas->kode }}</td>
                            <td>{{ $pengajuan->kelas->mataKuliah->kode }} · {{ $pengajuan->kelas->mataKuliah->nama }}</td>
                            <td>{{ $pengajuan->kelas->dosens->pluck('nama')->join(', ') }}</td>
                            <td>
                                @if ($pengajuan->status === 'menunggu')
                                    <span class="badge text-bg-warning">Menunggu persetujuan</span>
                                @else
                                    <span class="badge text-bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if ($pengajuan->status === 'ditolak')
                                    <form action="{{ route('mahasiswa.krs.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="kelas_id" value="{{ $pengajuan->kelas_id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Ajukan kembali</button>
                                    </form>
                                @else
                                    <span class="text-secondary small">Menunggu dosen pengajar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada pengajuan kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Kelas tersedia</h2>
            <p class="text-secondary mb-0">Daftarkan diri ke kelas yang memiliki dosen pengajar. Periode KRS ditentukan otomatis.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode section</th>
                        <th>Mata kuliah</th>
                        <th>Program studi</th>
                        <th>SKS</th>
                        <th>Dosen pengajar</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarKelas as $kelas)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $kelas->kode }}</td>
                            <td>{{ $kelas->mataKuliah->kode }} · {{ $kelas->mataKuliah->nama }}</td>
                            <td>{{ $kelas->programStudi->nama }}</td>
                            <td>{{ $kelas->mataKuliah->sks }}</td>
                            <td>{{ $kelas->dosens->pluck('nama')->join(', ') }}</td>
                            <td class="text-end pe-3">
                                <form action="{{ route('mahasiswa.krs.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">Daftar kelas</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Tidak ada kelas tersedia untuk diajukan saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
