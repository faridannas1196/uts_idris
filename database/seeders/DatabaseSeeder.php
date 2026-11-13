<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Tambahkan ini

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoPassword = config('sikad.demo_password');

        if (! is_string($demoPassword) || strlen($demoPassword) < 12) {
            throw new \RuntimeException('Set SIKAD_DEMO_PASSWORD to a password with at least 12 characters before seeding demo accounts.');
        }

        $programStudi = ProgramStudi::query()->firstOrCreate(['kode' => 'IF'], ['nama' => 'Informatika']);
        ProgramStudi::query()->firstOrCreate(['kode' => 'SI'], ['nama' => 'Sistem Informasi']);

        $dosen = Dosen::query()->firstOrCreate(
            ['nip' => '198501012010011001'],
            ['nama' => 'Dr. Siti Rahmawati', 'email' => 'siti.rahmawati@example.test'],
        );
        Dosen::query()->firstOrCreate(
            ['nip' => '198703152012121002'],
            ['nama' => 'Andi Pratama, M.Kom.', 'email' => 'andi.pratama@example.test'],
        );

        $mataKuliahPertama = MataKuliah::query()->firstOrCreate(
            ['kode' => 'IF101'],
            ['nama' => 'Pengantar Informatika', 'sks' => 3],
        );
        $mataKuliahKedua = MataKuliah::query()->firstOrCreate(
            ['kode' => 'IF102'],
            ['nama' => 'Algoritma dan Pemrograman', 'sks' => 4],
        );
        $mataKuliahPertama->programStudis()->syncWithoutDetaching([$programStudi->id]);
        $mataKuliahKedua->programStudis()->syncWithoutDetaching([$programStudi->id]);
        Kelas::query()->firstOrCreate(
            ['kode' => 'IF101-A'],
            [
                'mata_kuliah_id' => $mataKuliahPertama->id,
                'program_studi_id' => $programStudi->id,
            ],
        );
        Kelas::query()->firstOrCreate(
            ['kode' => 'IF102-A'],
            [
                'mata_kuliah_id' => $mataKuliahKedua->id,
                'program_studi_id' => $programStudi->id,
            ],
        );

        User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator SIKAD',
                'email' => 'admin@example.test',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ],
        );

        $dosenUser = User::query()->firstOrCreate(
            ['username' => $dosen->nip],
            [
                'name' => $dosen->nama,
                'email' => $dosen->email,
                'password' => Hash::make('dosen'),
                'role' => 'dosen',
            ],
        );
        $dosen->user()->associate($dosenUser);
        $dosen->save();

        $mahasiswa = Mahasiswa::query()->firstOrCreate(
            ['nim' => '20260001'],
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@example.test',
                'wali_dosen' => $dosen->id,
                'status' => 'aktif',
                'program_studi_id' => $programStudi->id,
                'dosen_id' => $dosen->id,
            ],
        );
        $mahasiswaUser = User::query()->firstOrCreate(
            ['username' => $mahasiswa->nim],
            [
                'name' => $mahasiswa->nama,
                'email' => $mahasiswa->email,
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
            ],
        );
        $mahasiswa->user()->associate($mahasiswaUser);
        $mahasiswa->save();
    }
}
