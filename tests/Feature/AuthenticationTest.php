<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_for_protected_pages(): void
    {

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('mahasiswa.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_sign_in_with_user_id_and_sign_out(): void
    {
        $user = $this->createUser('admin', 'admin');

        $this->post(route('login.store'), [
            'identifier' => 'admin',
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-confirm-logout', false)
            ->assertSee('Keluar dari akun?')
            ->assertSee('Batal');

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_password_does_not_authenticate_user(): void
    {
        $this->createUser('admin', 'admin');

        $this->post(route('login.store'), [
            'identifier' => 'admin',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_seeded_accounts_use_configured_password_and_link_domain_users(): void
    {
        config(['sikad.demo_password' => 'ConfiguredDemo123!']);
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $dosen = User::query()->where('username', '198501012010011001')->firstOrFail();
        $mahasiswa = User::query()->where('username', '20260001')->firstOrFail();

        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('dosens', ['nip' => '198501012010011001', 'user_id' => $dosen->id]);
        $this->assertDatabaseHas('mahasiswas', ['nim' => '20260001', 'user_id' => $mahasiswa->id]);

        $this->post(route('login.store'), [
            'identifier' => 'admin',
            'password' => 'ConfiguredDemo123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_student_can_only_view_their_own_dashboard(): void
    {
        $user = $this->createUser('mahasiswa', '20260001');
        $programStudi = ProgramStudi::query()->create(['kode' => 'IF', 'nama' => 'Informatika']);
        $mahasiswa = Mahasiswa::query()->create([
            'nama' => 'Budi Santoso',
            'nim' => '20260001',
            'email' => 'budi@example.test',
            'wali_dosen' => 0,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
        ]);
        $user->mahasiswa()->save($mahasiswa);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Profil mahasiswa')
            ->assertSee('20260001');

        $this->get(route('mahasiswa.index'))->assertForbidden();
    }

    public function test_lecturer_sees_assigned_students_but_cannot_manage_all_students(): void
    {
        $user = $this->createUser('dosen', '19850101');
        $dosen = Dosen::query()->create([
            'nip' => '19850101',
            'nama' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
        ]);
        $user->dosen()->save($dosen);
        $programStudi = ProgramStudi::query()->create(['kode' => 'SI', 'nama' => 'Sistem Informasi']);
        Mahasiswa::query()->create([
            'nama' => 'Rina Putri',
            'nim' => '20260002',
            'email' => 'rina@example.test',
            'wali_dosen' => $dosen->id,
            'status' => 'aktif',
            'program_studi_id' => $programStudi->id,
            'dosen_id' => $dosen->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mahasiswa bimbingan')
            ->assertSee('Rina Putri');

        $this->get(route('mahasiswa.index'))->assertForbidden();
    }

    private function createUser(string $role, string $username): User
    {
        return User::query()->create([
            'name' => ucfirst($role).' SIKAD',
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => 'secret123',
            'role' => $role,
        ]);
    }
}
