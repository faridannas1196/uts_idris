@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-success mb-2">Akademik</p>
            <h1 class="h2 mb-1">Data kelas</h1>
            <p class="text-secondary mb-0">Kelola section mata kuliah menurut program studi dan dosen pengajar.</p>
        </div>
        <a href="{{ route('kelas.create') }}" class="btn btn-success">Tambah kelas</a>
    </div>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body border-bottom">
            <h2 class="h5 mb-1">Pengajuan mengajar</h2>
            <p class="text-secondary mb-0">Tinjau permohonan dosen untuk mengajar kelas.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Dosen</th>
                        <th>Kelas</th>
                        <th>Diajukan</th>
                        <th class="text-end pe-3">Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuans as $pengajuan)
                        <tr>
                            <td class="ps-3">{{ $pengajuan->dosen->nama }} <span class="text-secondary">({{ $pengajuan->dosen->nip }})</span></td>
                            <td>{{ $pengajuan->kelas->kode }} · {{ $pengajuan->kelas->mataKuliah->nama }} ({{ $pengajuan->kelas->programStudi?->kode ?? 'Belum dipetakan' }})</td>
                            <td>{{ $pengajuan->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <form action="{{ route('pengajuan-kelas.update', $pengajuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="disetujui">
                                    <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                                </form>
                                <form action="{{ route('pengajuan-kelas.update', $pengajuan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="ditolak">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada pengajuan yang menunggu.</td>
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
                        <th>Pengajar</th>
                        <th>Transaksi KRS</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarKelas as $kelas)
                        <tr>
                            <td class="ps-3 text-nowrap">{{ $kelas->kode }}</td>
                            <td>{{ $kelas->mataKuliah->kode }} · {{ $kelas->mataKuliah->nama }} ({{ $kelas->mataKuliah->sks }} SKS)</td>
                            <td>{{ $kelas->programStudi?->kode ?? 'Belum dipetakan' }}</td>
                            <td>{{ $kelas->dosens->pluck('nama')->join(', ') }}</td>
                            <td>{{ $kelas->krs_count }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('kelas.edit', $kelas) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('kelas.destroy', $kelas) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada data kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarKelas->hasPages())
            <div class="card-body border-top">{{ $daftarKelas->links() }}</div>
        @endif
    </section>
@endsection
