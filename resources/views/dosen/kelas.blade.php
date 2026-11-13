@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik / Pengajar</p>
            <h1 class="h2 mb-1">Kelas yang saya ajar</h1>
            <p class="text-secondary mb-0">{{ $dosen->nama }} · NIP {{ $dosen->nip }}</p>
            <p class="text-secondary mb-0">Periode {{ $periode['tahun_ajaran'] }} · Semester {{ $periode['semester'] }}</p>
        </div>
        <span class="badge text-bg-primary fs-6">{{ $dosen->kelas->count() }} kelas</span>
    </div>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Pengajuan pendaftaran mahasiswa</h2>
            <p class="text-secondary mb-0">Setujui atau tolak pengajuan untuk kelas yang Anda ampu. Dosen pengampu mana pun dapat memutuskan.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mahasiswa</th>
                        <th>Kelas</th>
                        <th>Diajukan</th>
                        <th class="text-end pe-3">Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuanKrs as $pengajuan)
                        <tr>
                            <td class="ps-3">{{ $pengajuan->mahasiswa->nama }} ({{ $pengajuan->mahasiswa->nim }})</td>
                            <td>{{ $pengajuan->kelas->kode }} · {{ $pengajuan->kelas->mataKuliah->nama }} ({{ $pengajuan->kelas->programStudi?->kode ?? 'Belum dipetakan' }})</td>
                            <td>{{ $pengajuan->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <form action="{{ route('dosen.krs.update', $pengajuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="disetujui">
                                    <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                                </form>
                                <form action="{{ route('dosen.krs.update', $pengajuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="ditolak">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada pengajuan pendaftaran mahasiswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
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
                    @forelse ($dosen->kelas as $kelas)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $kelas->kode }}</td>
                            <td>{{ $kelas->mataKuliah->kode }} · {{ $kelas->mataKuliah->nama }}</td>
                            <td>{{ $kelas->programStudi?->nama ?? 'Belum dipetakan' }}</td>
                            <td>{{ $kelas->mataKuliah->sks }}</td>
                            <td>{{ $kelas->dosens->pluck('nama')->join(', ') }}</td>
                            <td>
                                @forelse ($kelas->krs as $krs)
                                    <div>{{ $krs->mahasiswa->nama }} ({{ $krs->mahasiswa->nim }})</div>
                                @empty
                                    <span class="text-secondary">Belum ada mahasiswa.</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada kelas yang ditugaskan kepada Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm mt-4">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Ajukan mengajar kelas</h2>
            <p class="text-secondary mb-0">Pilih kelas yang ingin diajar. Pengajuan akan menunggu persetujuan admin.</p>
        </div>
        @if ($errors->has('kelas_id'))
            <div class="alert alert-danger m-3 mb-0" role="alert">{{ $errors->first('kelas_id') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode section</th>
                        <th>Mata kuliah</th>
                        <th>Program studi</th>
                        <th>SKS</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarKelas as $kelas)
                        @php($pengajuan = $pengajuanByKelas->get($kelas->id))
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $kelas->kode }}</td>
                            <td>{{ $kelas->mataKuliah->kode }} · {{ $kelas->mataKuliah->nama }}</td>
                            <td>{{ $kelas->programStudi?->kode ?? 'Belum dipetakan' }}</td>
                            <td>{{ $kelas->mataKuliah->sks }}</td>
                            <td>
                                @if ($pengajuan?->status === 'menunggu')
                                    <span class="badge text-bg-warning">Menunggu persetujuan</span>
                                @elseif ($pengajuan?->status === 'disetujui')
                                    <span class="badge text-bg-success">Disetujui</span>
                                @elseif ($pengajuan?->status === 'ditolak')
                                    <span class="badge text-bg-danger">Ditolak</span>
                                @else
                                    <span class="text-secondary">Belum diajukan</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if ($pengajuan === null || $pengajuan->status !== 'menunggu')
                                    <form action="{{ route('dosen.pengajuan-kelas.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            {{ $pengajuan === null ? 'Ajukan' : 'Ajukan kembali' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-secondary small">Menunggu keputusan admin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada kelas yang tersedia untuk diajukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
