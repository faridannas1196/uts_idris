<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_student_list_render(): void
    {
        $this->actingAs($this->createAdmin());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan akademik');

        $this->get(route('mahasiswa.index'))
            ->assertOk()
            ->assertSee('Data mahasiswa');
    }

    public function test_student_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->createAdmin());

        $programStudi = ProgramStudi::query()->create(['kode' => 'IF', 'nama' => 'Informatika']);
        $dosen = Dosen::query()->create([
            'nip' => '19850101',
            'nama' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
        ]);
        $attributes = [
            'nama' => 'Budi Santoso',
            'nim' => '20260001',
            'email' => 'budi@example.test',
            'program_studi_id' => $programStudi->id,
            'dosen_id' => $dosen->id,
            'status' => 'aktif',
        ];

        $this->post(route('mahasiswa.store'), $attributes)
            ->assertRedirect(route('mahasiswa.index'));

        $this->assertDatabaseHas('mahasiswas', [
            'nim' => '20260001',
            'program_studi_id' => $programStudi->id,
            'dosen_id' => $dosen->id,
            'wali_dosen' => $dosen->id,
        ]);

        $mahasiswa = Mahasiswa::query()->where('nim', '20260001')->firstOrFail();

        $this->put(route('mahasiswa.update', $mahasiswa), [...$attributes, 'nama' => 'Budi Santoso Updated'])
            ->assertRedirect(route('mahasiswa.index'));

        $this->assertDatabaseHas('mahasiswas', ['id' => $mahasiswa->id, 'nama' => 'Budi Santoso Updated']);

        $this->delete(route('mahasiswa.destroy', $mahasiswa))
            ->assertRedirect(route('mahasiswa.index'));

        $this->assertDatabaseMissing('mahasiswas', ['id' => $mahasiswa->id]);
    }

    public function test_student_requires_existing_program_and_advisor(): void
    {
        $this->actingAs($this->createAdmin());

        $this->from(route('mahasiswa.create'))
            ->post(route('mahasiswa.store'), [
                'nama' => 'Budi Santoso',
                'nim' => '20260001',
                'email' => 'budi@example.test',
                'program_studi_id' => 999,
                'dosen_id' => 999,
                'status' => 'aktif',
            ])
            ->assertRedirect(route('mahasiswa.create'))
            ->assertSessionHasErrors(['program_studi_id', 'dosen_id']);

        $this->assertDatabaseCount('mahasiswas', 0);
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
