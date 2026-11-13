<?php

namespace Tests\Feature;

use Tests\TestCase;

class GlobalNotificationsTest extends TestCase
{
    public function test_flash_messages_are_shown_as_sweetalert_toasts(): void
    {
        $response = $this->withSession([
            'success' => 'Data berhasil disimpan.',
            'error' => 'Aksi tidak dapat dilakukan.',
            'warning' => 'Periksa data.',
            'info' => '</script><script>alert(1)</script>',
        ])->get(route('login'));

        $response->assertOk()
            ->assertSee('sweetalert2@11.22.0')
            ->assertSee('showToast')
            ->assertSee('showSweetAlert')
            ->assertSee('Data berhasil disimpan.')
            ->assertSee('Aksi tidak dapat dilakukan.')
            ->assertSee('Periksa data.')
            ->assertDontSee('</script><script>alert(1)</script>', false);
    }

    public function test_validation_errors_are_shown_in_a_sweetalert_warning(): void
    {
        $response = $this->followingRedirects()
            ->withHeader('referer', route('login'))
            ->post(route('login.store'), [
                'identifier' => '',
                'password' => '',
            ]);

        $response->assertOk()
            ->assertSee('Validasi gagal')
            ->assertSee('Periksa kembali data yang ditandai.');
    }
}
