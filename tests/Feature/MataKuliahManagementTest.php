<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MataKuliahManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_course_to_multiple_programs_and_update_the_links(): void
    {
        $this->actingAs($this->createAdmin());
        $informatika = $this->createProgramStudi('IF', 'Informatika');
        $sistemInformasi = $this->createProgramStudi('SI', 'Sistem Informasi');

        $this->post(route('mata-kuliah.store'), [
            'kode' => 'MK101',
            'nama' => 'Logika Informatika',
            'sks' => 3,
            'program_studi_ids' => [$informatika->id, $sistemInformasi->id],
        ])->assertRedirect(route('mata-kuliah.index'));

        $mataKuliah = MataKuliah::query()->where('kode', 'MK101')->firstOrFail();

        $this->assertDatabaseHas('mata_kuliah_program_studi', [
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $informatika->id,
        ]);
        $this->assertDatabaseHas('mata_kuliah_program_studi', [
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $sistemInformasi->id,
        ]);

        $this->put(route('mata-kuliah.update', $mataKuliah), [
            'kode' => 'MK101',
            'nama' => 'Logika dan Himpunan',
            'sks' => 3,
            'program_studi_ids' => [$sistemInformasi->id],
        ])->assertRedirect(route('mata-kuliah.index'));

        $this->assertDatabaseHas('mata_kuliahs', [
            'id' => $mataKuliah->id,
            'nama' => 'Logika dan Himpunan',
        ]);
        $this->assertDatabaseMissing('mata_kuliah_program_studi', [
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $informatika->id,
        ]);
        $this->assertDatabaseHas('mata_kuliah_program_studi', [
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $sistemInformasi->id,
        ]);
    }

    public function test_class_section_requires_a_program_linked_to_its_course(): void
    {
        $this->actingAs($this->createAdmin());
        $informatika = $this->createProgramStudi('IF', 'Informatika');
        $sistemInformasi = $this->createProgramStudi('SI', 'Sistem Informasi');
        $mataKuliah = MataKuliah::query()->create([
            'kode' => 'IF201',
            'nama' => 'Pemrograman Web',
            'sks' => 3,
        ]);
        $mataKuliah->programStudis()->attach($informatika);

        $this->from(route('kelas.create'))
            ->post(route('kelas.store'), [
                'kode' => 'IF201-SI-A',
                'mata_kuliah_id' => $mataKuliah->id,
                'program_studi_id' => $sistemInformasi->id,
            ])
            ->assertRedirect(route('kelas.create'))
            ->assertSessionHasErrors('mata_kuliah_id');

        $this->assertDatabaseMissing('kelas', ['kode' => 'IF201-SI-A']);
    }

    public function test_course_cannot_drop_a_program_used_by_one_of_its_sections(): void
    {
        $this->actingAs($this->createAdmin());
        $informatika = $this->createProgramStudi('IF', 'Informatika');
        $sistemInformasi = $this->createProgramStudi('SI', 'Sistem Informasi');
        $mataKuliah = MataKuliah::query()->create([
            'kode' => 'MK201',
            'nama' => 'Etika Profesi',
            'sks' => 2,
        ]);
        $mataKuliah->programStudis()->attach([$informatika->id, $sistemInformasi->id]);
        $kelas = Kelas::query()->create([
            'kode' => 'MK201-SI-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $sistemInformasi->id,
        ]);

        $this->from(route('mata-kuliah.edit', $mataKuliah))
            ->put(route('mata-kuliah.update', $mataKuliah), [
                'kode' => 'MK201',
                'nama' => 'Etika Profesi',
                'sks' => 2,
                'program_studi_ids' => [$informatika->id],
            ])
            ->assertRedirect(route('mata-kuliah.edit', $mataKuliah))
            ->assertSessionHasErrors('program_studi_ids');

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'program_studi_id' => $sistemInformasi->id,
        ]);
        $this->assertDatabaseHas('mata_kuliah_program_studi', [
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $sistemInformasi->id,
        ]);
    }

    public function test_course_cannot_be_deleted_while_it_has_class_sections(): void
    {
        $this->actingAs($this->createAdmin());
        $programStudi = $this->createProgramStudi('IF', 'Informatika');
        $mataKuliah = MataKuliah::query()->create([
            'kode' => 'IF301',
            'nama' => 'Basis Data',
            'sks' => 3,
        ]);
        $mataKuliah->programStudis()->attach($programStudi);
        $kelas = Kelas::query()->create([
            'kode' => 'IF301-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ]);

        $this->delete(route('mata-kuliah.destroy', $mataKuliah))
            ->assertRedirect(route('mata-kuliah.index'))
            ->assertSessionHas('error', 'Mata kuliah tidak dapat dihapus karena masih digunakan oleh kelas.');

        $this->assertDatabaseHas('mata_kuliahs', ['id' => $mataKuliah->id]);
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

    private function createProgramStudi(string $kode, string $nama): ProgramStudi
    {
        return ProgramStudi::query()->create([
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }
}
