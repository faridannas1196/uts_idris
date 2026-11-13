<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramStudiManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_an_unused_program_studi(): void
    {
        $this->actingAs($this->createAdmin());

        $this->post(route('program-studi.store'), [
            'kode' => 'IF',
            'nama' => 'Informatika',
        ])->assertRedirect(route('program-studi.index'));

        $programStudi = ProgramStudi::query()->where('kode', 'IF')->firstOrFail();

        $this->assertDatabaseHas('program_studis', [
            'id' => $programStudi->id,
            'nama' => 'Informatika',
        ]);

        $this->put(route('program-studi.update', $programStudi), [
            'kode' => 'IF',
            'nama' => 'Teknik Informatika',
        ])->assertRedirect(route('program-studi.index'));

        $this->assertDatabaseHas('program_studis', [
            'id' => $programStudi->id,
            'nama' => 'Teknik Informatika',
        ]);

        $this->delete(route('program-studi.destroy', $programStudi))
            ->assertRedirect(route('program-studi.index'));

        $this->assertDatabaseMissing('program_studis', ['id' => $programStudi->id]);
    }

    public function test_program_studi_cannot_be_deleted_while_it_has_associated_records(): void
    {
        $this->actingAs($this->createAdmin());
        $programStudi = ProgramStudi::query()->create([
            'kode' => 'SI',
            'nama' => 'Sistem Informasi',
        ]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Rina Putri',
            'nim' => '20260001',
            'email' => 'rina@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $mataKuliah = MataKuliah::query()->create([
            'kode' => 'SI101',
            'nama' => 'Analisis Sistem',
            'sks' => 3,
        ]);
        $mataKuliah->programStudis()->attach($programStudi);
        $kelas = Kelas::query()->create([
            'kode' => 'SI101-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ]);

        $this->delete(route('program-studi.destroy', $programStudi))
            ->assertRedirect(route('program-studi.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('program_studis', ['id' => $programStudi->id]);
        $this->assertDatabaseHas('mahasiswas', ['id' => $mahasiswa->id]);
        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
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
}
