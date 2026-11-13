<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_program_cannot_change_when_its_krs_has_students_from_another_program(): void
    {
        $this->actingAs($this->createAdmin());
        $informatika = $this->createProgramStudi('IF', 'Informatika');
        $sistemInformasi = $this->createProgramStudi('SI', 'Sistem Informasi');
        $mataKuliah = MataKuliah::query()->create([
            'kode' => 'IF601',
            'nama' => 'Kecerdasan Buatan',
            'sks' => 3,
        ]);
        $mataKuliah->programStudis()->attach([$informatika->id, $sistemInformasi->id]);
        $kelas = Kelas::query()->create([
            'kode' => 'IF601-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $informatika->id,
        ]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Rina Putri',
            'nim' => '20260006',
            'email' => 'rina.prodi@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $informatika->id,
        ]);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
        ]);

        $this->from(route('kelas.edit', $kelas))
            ->put(route('kelas.update', $kelas), [
                'kode' => 'IF601-A',
                'mata_kuliah_id' => $mataKuliah->id,
                'program_studi_id' => $sistemInformasi->id,
            ])
            ->assertRedirect(route('kelas.edit', $kelas))
            ->assertSessionHasErrors('program_studi_id');

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'program_studi_id' => $informatika->id,
        ]);
    }

    private function createAdmin(): User
    {
        return User::query()->create([
            'name' => 'Admin SIKAD',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    private function createProgramStudi(string $kode, string $nama): ProgramStudi
    {
        return ProgramStudi::query()->create([
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }
}
