<?php

namespace Tests\Feature;

use App\Models\UnmatchedNip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnmatchedNipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/laporan/unmatched-nip');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_unmatched_nip_page_and_see_status_pegawai(): void
    {
        $user = User::factory()->create();

        UnmatchedNip::create([
            'nip' => '196808182025211039',
            'nama' => 'FACHRUL RAZY, S.Sos',
            'status_pegawai' => 'PPPK',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
            'keterangan' => 'NIP tidak ditemukan di master tabel pegawai.',
        ]);

        UnmatchedNip::create([
            'nip' => '198001012005011001',
            'nama' => 'BUDI SANTOSO',
            'status_pegawai' => 'PNS',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
            'keterangan' => 'NIP tidak ditemukan di master tabel pegawai.',
        ]);

        $response = $this->actingAs($user)->get('/laporan/unmatched-nip');

        $response->assertStatus(200);
        $response->assertSee('Log NIP Tidak Ditemukan');
        $response->assertSee('Status Pegawai');
        $response->assertSee('FACHRUL RAZY, S.Sos');
        $response->assertSee('BUDI SANTOSO');
        $response->assertSee('PPPK');
        $response->assertSee('PNS');
    }

    public function test_can_filter_by_status_pegawai(): void
    {
        $user = User::factory()->create();

        UnmatchedNip::create([
            'nip' => '196808182025211039',
            'nama' => 'FACHRUL RAZY, S.Sos',
            'status_pegawai' => 'PPPK',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
        ]);

        UnmatchedNip::create([
            'nip' => '198001012005011001',
            'nama' => 'BUDI SANTOSO',
            'status_pegawai' => 'PNS',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
        ]);

        // Filter only PPPK
        $responsePppk = $this->actingAs($user)->get('/laporan/unmatched-nip?status_pegawai=PPPK');
        $responsePppk->assertStatus(200);
        $responsePppk->assertSee('FACHRUL RAZY, S.Sos');
        $responsePppk->assertDontSee('BUDI SANTOSO');

        // Filter only PNS
        $responsePns = $this->actingAs($user)->get('/laporan/unmatched-nip?status_pegawai=PNS');
        $responsePns->assertStatus(200);
        $responsePns->assertSee('BUDI SANTOSO');
        $responsePns->assertDontSee('FACHRUL RAZY, S.Sos');
    }

    public function test_can_search_by_nip_or_nama(): void
    {
        $user = User::factory()->create();

        UnmatchedNip::create([
            'nip' => '196808182025211039',
            'nama' => 'FACHRUL RAZY, S.Sos',
            'status_pegawai' => 'PPPK',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
        ]);

        UnmatchedNip::create([
            'nip' => '198001012005011001',
            'nama' => 'BUDI SANTOSO',
            'status_pegawai' => 'PNS',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
        ]);

        $response = $this->actingAs($user)->get('/laporan/unmatched-nip?search=FACHRUL');
        $response->assertStatus(200);
        $response->assertSee('FACHRUL RAZY, S.Sos');
        $response->assertDontSee('BUDI SANTOSO');
    }

    public function test_can_truncate_all_unmatched_nip_logs(): void
    {
        $user = User::factory()->create();

        UnmatchedNip::create([
            'nip' => '196808182025211039',
            'nama' => 'FACHRUL RAZY, S.Sos',
            'status_pegawai' => 'PPPK',
            'jenis_file' => 'Gaji',
            'periode' => '2026-09',
        ]);

        $this->assertDatabaseCount('unmatched_nips', 1);

        $response = $this->actingAs($user)->delete('/laporan/unmatched-nip/clear');
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('unmatched_nips', 0);
    }
}
