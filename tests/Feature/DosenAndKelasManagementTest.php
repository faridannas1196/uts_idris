<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\PengajuanKelas;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DosenAndKelasManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_pengajar_and_login_account(): void
    {
        $this->actingAs($this->createAdmin());

        $attributes = [
            'nip' => '19900001',
            'nama' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
            'password' => 'pengajar-kuat-123',
            'password_confirmation' => 'pengajar-kuat-123',
        ];

        $this->post(route('pengajar.store'), $attributes)
            ->assertRedirect(route('pengajar.index'));

        $pengajar = Dosen::query()->where('nip', '19900001')->firstOrFail();
        $akun = User::query()->where('username', '19900001')->firstOrFail();

        $this->assertSame($pengajar->id, $akun->dosen->id);
        $this->assertSame('dosen', $akun->role);
        $this->assertTrue(Hash::check('pengajar-kuat-123', $akun->password));

        $this->put(route('pengajar.update', $pengajar), [
            'nip' => '19900001',
            'nama' => 'Siti Rahmawati Updated',
            'email' => 'siti.updated@example.test',
        ])->assertRedirect(route('pengajar.index'));

        $this->assertDatabaseHas('dosens', [
            'id' => $pengajar->id,
            'nama' => 'Siti Rahmawati Updated',
            'email' => 'siti.updated@example.test',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $akun->id,
            'name' => 'Siti Rahmawati Updated',
            'email' => 'siti.updated@example.test',
        ]);

        $this->delete(route('pengajar.destroy', $pengajar))
            ->assertRedirect(route('pengajar.index'));

        $this->assertDatabaseMissing('dosens', ['id' => $pengajar->id]);
        $this->assertDatabaseMissing('users', ['id' => $akun->id]);
    }

    public function test_admin_can_manage_kelas_and_sync_pengajars(): void
    {
        $this->actingAs($this->createAdmin());

        $pengajarPertama = $this->createPengajar('19850001', 'pertama@example.test');
        $pengajarKedua = $this->createPengajar('19850002', 'kedua@example.test');
        $programStudi = $this->createProgramStudi();
        $mataKuliah = $this->createMataKuliah('IF201', 'Pemrograman Web', 3, [$programStudi]);
        $attributes = [
            'kode' => 'IF201-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
            'dosen_ids' => [$pengajarPertama->id, $pengajarKedua->id],
        ];

        $this->post(route('kelas.store'), $attributes)
            ->assertRedirect(route('kelas.index'));

        $kelas = Kelas::query()->where('kode', 'IF201-A')->firstOrFail();
        $this->assertDatabaseHas('dosen_kelas', [
            'dosen_id' => $pengajarPertama->id,
            'kelas_id' => $kelas->id,
        ]);
        $this->assertDatabaseHas('dosen_kelas', [
            'dosen_id' => $pengajarKedua->id,
            'kelas_id' => $kelas->id,
        ]);

        $this->put(route('kelas.update', $kelas), [
            ...$attributes,
            'kode' => 'IF201-A',
            'dosen_ids' => [$pengajarKedua->id],
        ])->assertRedirect(route('kelas.index'));

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'program_studi_id' => $programStudi->id,
        ]);
        $this->assertDatabaseMissing('dosen_kelas', [
            'dosen_id' => $pengajarPertama->id,
            'kelas_id' => $kelas->id,
        ]);
        $this->assertDatabaseHas('dosen_kelas', [
            'dosen_id' => $pengajarKedua->id,
            'kelas_id' => $kelas->id,
        ]);

        $this->delete(route('kelas.destroy', $kelas))
            ->assertRedirect(route('kelas.index'));

        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
        $this->assertDatabaseMissing('dosen_kelas', ['kelas_id' => $kelas->id]);
    }

    public function test_admin_can_create_and_edit_a_class_without_assigned_teachers(): void
    {
        $this->actingAs($this->createAdmin());
        $programStudi = $this->createProgramStudi();
        $mataKuliah = $this->createMataKuliah('IF202', 'Pemrograman Mobile', 3, [$programStudi]);

        $this->post(route('kelas.store'), [
            'kode' => 'IF202-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ])->assertRedirect(route('kelas.index'));

        $kelas = Kelas::query()->where('kode', 'IF202-A')->firstOrFail();
        $this->assertDatabaseMissing('dosen_kelas', ['kelas_id' => $kelas->id]);

        $this->get(route('kelas.edit', $kelas))
            ->assertOk()
            ->assertSee('Pemrograman Mobile');

        $this->put(route('kelas.update', $kelas), [
            'kode' => 'IF202-A',
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ])->assertRedirect(route('kelas.index'));

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'mata_kuliah_id' => $mataKuliah->id,
        ]);
        $this->assertDatabaseMissing('dosen_kelas', ['kelas_id' => $kelas->id]);
    }

    public function test_teacher_can_apply_to_teach_a_class_and_duplicate_pending_application_is_rejected(): void
    {
        $dosen = $this->createPengajar('19850006', 'pengaju@example.test');
        $akun = $this->createDosenAccount($dosen);
        $kelas = $this->createKelas('IF601', 'IF601-A', 'Kecerdasan Buatan');

        $this->actingAs($akun)
            ->get(route('dosen.kelas'))
            ->assertOk()
            ->assertSee('Kecerdasan Buatan')
            ->assertSee('Ajukan');

        $this->actingAs($akun)
            ->post(route('dosen.pengajuan-kelas.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('dosen.kelas'));

        $this->assertDatabaseHas('pengajuan_kelas', [
            'dosen_id' => $dosen->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'kelas_id' => $kelas->id,
            'status' => 'menunggu',
        ]);

        $this->post(route('dosen.pengajuan-kelas.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('dosen.kelas'))
            ->assertSessionHasErrors('kelas_id');

        $this->assertSame(1, PengajuanKelas::query()->where('dosen_id', $dosen->id)->count());
    }

    public function test_admin_approval_assigns_the_applicant_to_the_class(): void
    {
        $dosen = $this->createPengajar('19850007', 'disetujui@example.test');
        $kelas = $this->createKelas('IF701', 'IF701-A', 'Rekayasa Perangkat Lunak');
        $pengajuan = PengajuanKelas::query()->create([
            'dosen_id' => $dosen->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'kelas_id' => $kelas->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->createAdmin())
            ->get(route('kelas.index'))
            ->assertOk()
            ->assertSee('Rekayasa Perangkat Lunak')
            ->assertSee('Setujui');

        $this->put(route('pengajuan-kelas.update', $pengajuan), ['status' => 'disetujui'])
            ->assertRedirect(route('kelas.index'));

        $this->assertDatabaseHas('pengajuan_kelas', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
        ]);
        $this->assertDatabaseHas('dosen_kelas', [
            'dosen_id' => $dosen->id,
            'kelas_id' => $kelas->id,
        ]);
    }

    public function test_admin_rejection_does_not_assign_the_teacher_and_teacher_can_apply_again(): void
    {
        $dosen = $this->createPengajar('19850008', 'ditolak@example.test');
        $akun = $this->createDosenAccount($dosen);
        $kelas = $this->createKelas('IF801', 'IF801-A', 'Komputasi Awan');
        $pengajuan = PengajuanKelas::query()->create([
            'dosen_id' => $dosen->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'kelas_id' => $kelas->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->createAdmin())
            ->put(route('pengajuan-kelas.update', $pengajuan), ['status' => 'ditolak'])
            ->assertRedirect(route('kelas.index'));

        $this->assertDatabaseHas('pengajuan_kelas', [
            'id' => $pengajuan->id,
            'status' => 'ditolak',
        ]);
        $this->assertDatabaseMissing('dosen_kelas', [
            'dosen_id' => $dosen->id,
            'kelas_id' => $kelas->id,
        ]);

        $this->actingAs($akun)
            ->post(route('dosen.pengajuan-kelas.store'), ['kelas_id' => $kelas->id])
            ->assertRedirect(route('dosen.kelas'));

        $this->assertDatabaseHas('pengajuan_kelas', [
            'id' => $pengajuan->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_student_cannot_submit_a_teaching_application(): void
    {
        $mahasiswa = User::query()->create([
            'name' => 'Mahasiswa SIKAD',
            'username' => '20260002',
            'email' => 'mahasiswa@example.test',
            'password' => 'password',
            'role' => 'mahasiswa',
        ]);
        $kelas = $this->createKelas('IF901', 'IF901-A', 'Etika Profesi');

        $this->actingAs($mahasiswa)
            ->post(route('dosen.pengajuan-kelas.store'), ['kelas_id' => $kelas->id])
            ->assertForbidden();

        $this->assertDatabaseMissing('pengajuan_kelas', ['kelas_id' => $kelas->id]);
    }

    public function test_teacher_sees_current_class_roster_and_assigned_teachers(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $dosen = $this->createPengajar('19850009', 'wali-kelas@example.test');
        $akun = $this->createDosenAccount($dosen);
        $dosenPendamping = $this->createPengajar('19850010', 'pendamping@example.test');
        $kelas = $this->createKelas('IF1001', 'IF1001-A', 'Analisis Data');
        $kelas->dosens()->attach([$dosen->id, $dosenPendamping->id]);

        $mahasiswaAktif = Mahasiswa::query()->create([
            'nama' => 'Rina Putri',
            'nim' => '20261001',
            'email' => 'rina.kelas@example.test',
            'wali_dosen' => $dosen->id,
            'status' => 'aktif',
            'dosen_id' => $dosen->id,
        ]);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswaAktif->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
        ]);

        $mahasiswaPeriodeLalu = Mahasiswa::query()->create([
            'nama' => 'Budi Periode Lalu',
            'nim' => '20251001',
            'email' => 'budi.periode-lalu@example.test',
            'wali_dosen' => $dosen->id,
            'status' => 'aktif',
            'dosen_id' => $dosen->id,
        ]);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswaPeriodeLalu->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'tahun_ajaran' => '2025/2026',
            'semester' => 2,
        ]);

        $this->actingAs($akun)
            ->get(route('dosen.kelas'))
            ->assertOk()
            ->assertSee('Rina Putri')
            ->assertSee('Pengajar 19850009')
            ->assertSee('Pengajar 19850010')
            ->assertDontSee('Budi Periode Lalu');

        $this->travelBack();
    }

    public function test_any_assigned_teacher_can_approve_but_unassigned_teacher_cannot(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $dosenPertama = $this->createPengajar('19850011', 'dosen.pertama@example.test');
        $dosenKedua = $this->createPengajar('19850012', 'dosen.kedua@example.test');
        $dosenLain = $this->createPengajar('19850013', 'dosen.lain@example.test');
        $kelas = $this->createKelas('IF1101', 'IF1101-A', 'Pemodelan Data');
        $kelas->dosens()->attach([$dosenPertama->id, $dosenKedua->id]);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Rina Terdaftar',
            'nim' => '20261101',
            'email' => 'rina.terdaftar@example.test',
            'wali_dosen' => $dosenPertama->id,
            'status' => 'aktif',
            'dosen_id' => $dosenPertama->id,
        ]);
        $pengajuan = Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->createDosenAccount($dosenLain))
            ->put(route('dosen.krs.update', $pengajuan), ['status' => 'disetujui'])
            ->assertNotFound();

        $this->assertDatabaseHas('krs', [
            'id' => $pengajuan->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->createDosenAccount($dosenKedua))
            ->get(route('dosen.kelas'))
            ->assertOk()
            ->assertSee('Rina Terdaftar')
            ->assertSee('Setujui');

        $this->put(route('dosen.krs.update', $pengajuan), ['status' => 'disetujui'])
            ->assertRedirect(route('dosen.kelas'));

        $this->assertDatabaseHas('krs', [
            'id' => $pengajuan->id,
            'status' => 'disetujui',
        ]);

        $this->travelBack();
    }

    public function test_assigned_teacher_can_reject_a_pending_student_request(): void
    {
        $this->travelTo('2026-11-13 12:00:00');

        $dosen = $this->createPengajar('19850014', 'dosen.reject@example.test');
        $akunDosen = $this->createDosenAccount($dosen);
        $kelas = $this->createKelas('IF1102', 'IF1102-A', 'Arsitektur Komputer');
        $dosen->kelas()->attach($kelas);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Pemohon',
            'nim' => '20261102',
            'email' => 'budi.pemohon@example.test',
            'wali_dosen' => $dosen->id,
            'status' => 'aktif',
            'dosen_id' => $dosen->id,
        ]);
        $pengajuan = Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
            'status' => 'menunggu',
        ]);

        $this->actingAs($akunDosen)
            ->put(route('dosen.krs.update', $pengajuan), ['status' => 'ditolak'])
            ->assertRedirect(route('dosen.kelas'));

        $this->assertDatabaseHas('krs', [
            'id' => $pengajuan->id,
            'status' => 'ditolak',
        ]);

        $this->travelBack();
    }

    public function test_teacher_only_sees_assigned_kelas_and_cannot_access_admin_crud(): void
    {
        $pengajar = $this->createPengajar('19850003', 'pengajar@example.test');
        $akun = User::query()->create([
            'name' => $pengajar->nama,
            'username' => $pengajar->nip,
            'email' => $pengajar->email,
            'password' => 'password',
            'role' => 'dosen',
        ]);
        $pengajar->user()->associate($akun)->save();

        $kelasDiajar = $this->createKelas('IF301', 'IF301-A', 'Basis Data');
        $kelasLain = $this->createKelas('IF302', 'IF302-A', 'Jaringan Komputer');
        $pengajar->kelas()->attach($kelasDiajar);
        $this->createPengajar('19850004', 'lain@example.test')->kelas()->attach($kelasLain);

        $this->actingAs($akun)
            ->get(route('kelas.index'))
            ->assertForbidden();

        $this->get(route('dosen.kelas'))
            ->assertOk()
            ->assertSee('Basis Data')
            ->assertSee('Jaringan Komputer');
    }

    public function test_admin_cannot_assign_a_class_to_a_missing_teacher(): void
    {
        $this->actingAs($this->createAdmin());
        $programStudi = $this->createProgramStudi();
        $mataKuliah = $this->createMataKuliah('IF401', 'Keamanan Informasi', 3, [$programStudi]);

        $this->from(route('kelas.create'))
            ->post(route('kelas.store'), [
                'kode' => 'IF401-A',
                'mata_kuliah_id' => $mataKuliah->id,
                'program_studi_id' => $programStudi->id,
                'dosen_ids' => [999],
            ])
            ->assertRedirect(route('kelas.create'))
            ->assertSessionHasErrors(['dosen_ids.0']);

        $this->assertDatabaseMissing('kelas', ['kode' => 'IF401-A']);
    }

    public function test_class_with_krs_transaction_cannot_be_deleted(): void
    {
        $this->actingAs($this->createAdmin());

        $pengajar = $this->createPengajar('19850005', 'wali@example.test');
        $kelas = $this->createKelas('IF501', 'IF501-A', 'Sistem Operasi');
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260001',
            'email' => 'budi@example.test',
            'wali_dosen' => $pengajar->id,
            'status' => 'aktif',
            'dosen_id' => $pengajar->id,
        ]);
        Krs::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id' => $kelas->id,
            'mata_kuliah_id' => $kelas->mata_kuliah_id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 1,
        ]);

        $this->delete(route('kelas.destroy', $kelas))
            ->assertRedirect(route('kelas.index'))
            ->assertSessionHas('error', 'Kelas tidak dapat dihapus karena masih memiliki transaksi KRS atau pengajuan mengajar.');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
        $this->assertDatabaseHas('krs', ['kelas_id' => $kelas->id]);
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

    private function createPengajar(string $nip, string $email): Dosen
    {
        return Dosen::query()->create([
            'nip' => $nip,
            'nama' => 'Pengajar '.$nip,
            'email' => $email,
        ]);
    }

    private function createDosenAccount(Dosen $dosen): User
    {
        $akun = User::query()->create([
            'name' => $dosen->nama,
            'username' => $dosen->nip,
            'email' => $dosen->email,
            'password' => 'password',
            'role' => 'dosen',
        ]);
        $dosen->user()->associate($akun)->save();

        return $akun;
    }

    private function createProgramStudi(string $kode = 'IF'): ProgramStudi
    {
        return ProgramStudi::query()->firstOrCreate(
            ['kode' => $kode],
            ['nama' => $kode === 'IF' ? 'Informatika' : 'Program '.$kode],
        );
    }

    /**
     * @param  list<ProgramStudi>  $programStudis
     */
    private function createMataKuliah(string $kode, string $nama, int $sks, array $programStudis): MataKuliah
    {
        $mataKuliah = MataKuliah::query()->create([
            'kode' => $kode,
            'nama' => $nama,
            'sks' => $sks,
        ]);
        $mataKuliah->programStudis()->sync(collect($programStudis)->pluck('id')->all());

        return $mataKuliah;
    }

    private function createKelas(string $kodeMataKuliah, string $kodeKelas, string $namaMataKuliah): Kelas
    {
        $programStudi = $this->createProgramStudi();
        $mataKuliah = $this->createMataKuliah($kodeMataKuliah, $namaMataKuliah, 3, [$programStudi]);

        return Kelas::query()->create([
            'kode' => $kodeKelas,
            'mata_kuliah_id' => $mataKuliah->id,
            'program_studi_id' => $programStudi->id,
        ]);
    }
}
