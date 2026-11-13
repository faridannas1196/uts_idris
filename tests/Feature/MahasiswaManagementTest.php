<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
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

    public function test_student_can_register_for_a_class_and_view_its_roster(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $programStudi = $this->createProgramStudi('IF');
        $user = User::query()->create([
            'name' => 'Budi Santoso',
            'username' => '20260001',
            'email' => 'budi@example.test',
            'password' => 'password',
            'role' => 'mahasiswa',
        ]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260001',
            'email' => 'budi@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $user->mahasiswa()->save($mahasiswa);

        $dosen = Dosen::query()->create([
            'nip' => '19850101',
            'nama' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
        ]);
        $akunDosen = User::query()->create([
            'name' => $dosen->nama,
            'username' => $dosen->nip,
            'email' => 'siti.akun@example.test',
            'password' => 'password',
            'role' => 'dosen',
        ]);
        $dosen->user()->associate($akunDosen)->save();
        $mataKuliah = $this->createMataKuliah('IF101', 'Algoritma dan Pemrograman', 3, $programStudi);
        $kelas = $this->createKelas('IF101-A', $mataKuliah, $programStudi);
        $kelas->dosens()->attach($dosen);

        $mahasiswaLain = Mahasiswa::query()->create([
            'nama' => 'Rina Putri',
            'nim' => '20260002',
            'email' => 'rina@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswaLain->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
            'status' => 'disetujui',
        ]);

        $mataKuliahTanpaDosen = $this->createMataKuliah('IF102', 'Pengantar Komputasi', 2, $programStudi);
        $this->createKelas('IF102-A', $mataKuliahTanpaDosen, $programStudi);

        $programStudiLain = $this->createProgramStudi('SI');
        $mataKuliahLain = $this->createMataKuliah('SI101', 'Analisis Sistem', 3, $programStudiLain);
        $kelasProgramLain = $this->createKelas('SI101-A', $mataKuliahLain, $programStudiLain);
        $kelasProgramLain->dosens()->attach($dosen);

        $this->actingAs($user)
            ->get(route('mahasiswa.kelas'))
            ->assertOk()
            ->assertSee('Algoritma dan Pemrograman')
            ->assertDontSee($mataKuliahTanpaDosen->nama)
            ->assertDontSee($mataKuliahLain->nama);

        $this->post(route('mahasiswa.krs.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('mahasiswa.kelas'));

        $this->assertDatabaseHas('krs', [
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
            'status' => 'menunggu',
        ]);

        $this->get(route('mahasiswa.kelas'))
            ->assertOk()
            ->assertSee('Menunggu persetujuan')
            ->assertDontSee('Rina Putri')
            ->assertDontSee('Pengantar Komputasi');

        $this->actingAs($akunDosen)
            ->get(route('dosen.kelas'))
            ->assertOk()
            ->assertSee('Pengajuan pendaftaran mahasiswa')
            ->assertSee('Budi Santoso')
            ->assertSee('Algoritma dan Pemrograman');

        $pengajuan = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->firstOrFail();

        $this->put(route('dosen.krs.update', $pengajuan), ['status' => 'disetujui'])
            ->assertRedirect(route('dosen.kelas'));

        $this->assertDatabaseHas('krs', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
        ]);

        $this->actingAs($user)
            ->post(route('mahasiswa.krs.store'), ['kelas_id' => $kelasProgramLain->id])
            ->assertNotFound();

        $this->assertDatabaseMissing('krs', [
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelasProgramLain->id,
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.kelas'))
            ->assertOk()
            ->assertSee('Algoritma dan Pemrograman')
            ->assertSee('Siti Rahmawati')
            ->assertSee('Rina Putri');

        $this->travelBack();
    }

    public function test_student_cannot_register_for_the_same_class_twice_in_one_period(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $programStudi = $this->createProgramStudi('IF');
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260003',
            'email' => 'budi.duplicate@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $user = User::query()->create([
            'name' => $mahasiswa->nama,
            'username' => $mahasiswa->nim,
            'email' => $mahasiswa->email,
            'password' => 'password',
            'role' => 'mahasiswa',
        ]);
        $user->mahasiswa()->save($mahasiswa);
        $mataKuliah = $this->createMataKuliah('IF103', 'Matematika Diskrit', 3, $programStudi);
        $kelas = $this->createKelas('IF103-A', $mataKuliah, $programStudi);
        $dosen = Dosen::query()->create([
            'nip' => '19850102',
            'nama' => 'Dosen Matematika',
            'email' => 'dosen.matematika@example.test',
        ]);
        $kelas->dosens()->attach($dosen);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
        ]);

        $this->actingAs($user)
            ->from(route('mahasiswa.kelas'))
            ->post(route('mahasiswa.krs.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('mahasiswa.kelas'))
            ->assertSessionHasErrors('kelas_id');

        $this->assertSame(1, Krs::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->count());

        $this->travelBack();
    }

    public function test_student_cannot_request_a_class_without_an_assigned_teacher(): void
    {
        $programStudi = $this->createProgramStudi('IF');
        $user = User::query()->create([
            'name' => 'Budi Santoso',
            'username' => '20260004',
            'email' => 'budi.unstaffed@example.test',
            'password' => 'password',
            'role' => 'mahasiswa',
        ]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260004',
            'email' => 'budi.unstaffed@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $user->mahasiswa()->save($mahasiswa);
        $mataKuliah = $this->createMataKuliah('IF104', 'Kelas Belum Berpengajar', 2, $programStudi);
        $kelas = $this->createKelas('IF104-A', $mataKuliah, $programStudi);

        $this->actingAs($user)
            ->from(route('mahasiswa.kelas'))
            ->post(route('mahasiswa.krs.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('mahasiswa.kelas'))
            ->assertSessionHasErrors('kelas_id');

        $this->assertDatabaseMissing('krs', [
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
        ]);
    }

    public function test_student_can_resubmit_a_rejected_class_request(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $programStudi = $this->createProgramStudi('IF');
        $user = User::query()->create([
            'name' => 'Budi Santoso',
            'username' => '20260005',
            'email' => 'budi.reapply@example.test',
            'password' => 'password',
            'role' => 'mahasiswa',
        ]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260005',
            'email' => 'budi.reapply@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $user->mahasiswa()->save($mahasiswa);
        $dosen = Dosen::query()->create([
            'nip' => '19850103',
            'nama' => 'Dosen Pembimbing Kelas',
            'email' => 'dosen.reapply@example.test',
        ]);
        $mataKuliah = $this->createMataKuliah('IF105', 'Sistem Digital', 3, $programStudi);
        $kelas = $this->createKelas('IF105-A', $mataKuliah, $programStudi);
        $kelas->dosens()->attach($dosen);
        $pengajuan = Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
            'status' => 'ditolak',
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.kelas'))
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('Ajukan kembali');

        $this->post(route('mahasiswa.krs.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('mahasiswa.kelas'));

        $this->assertDatabaseHas('krs', [
            'id' => $pengajuan->id,
            'status' => 'menunggu',
        ]);

        $this->travelBack();
    }

    public function test_current_academic_period_changes_at_february_and_august(): void
    {
        $this->travelTo('2027-01-31 12:00:00');
        $this->assertSame(['tahun_ajaran' => '2026/2027', 'semester' => 1], Krs::periodeAktif());

        $this->travelTo('2027-02-01 12:00:00');
        $this->assertSame(['tahun_ajaran' => '2026/2027', 'semester' => 2], Krs::periodeAktif());

        $this->travelTo('2027-08-01 12:00:00');
        $this->assertSame(['tahun_ajaran' => '2027/2028', 'semester' => 1], Krs::periodeAktif());

        $this->travelBack();
    }

    public function test_non_student_cannot_access_class_registration(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('mahasiswa.kelas'))
            ->assertForbidden();

        $this->post(route('mahasiswa.krs.store'))
            ->assertForbidden();

        $this->assertDatabaseCount('krs', 0);
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

    private function createProgramStudi(string $kode): ProgramStudi
    {
        return ProgramStudi::query()->firstOrCreate(
            ['kode' => $kode],
            ['nama' => $kode === 'IF' ? 'Informatika' : 'Sistem Informasi'],
        );
    }

    private function createMataKuliah(string $kode, string $nama, int $sks, ProgramStudi $programStudi): MataKuliah
    {
        $mataKuliah = MataKuliah::query()->create([
            'kode' => $kode,
            'nama' => $nama,
            'sks' => $sks,
        ]);
        $mataKuliah->programStudis()->attach($programStudi);

        return $mataKuliah;
    }

    private function createKelas(string $kode, MataKuliah $mataKuliah, ProgramStudi $programStudi): Kelas
    {
        return Kelas::query()->create([
            'kode' => $kode,
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ]);
    }
}
