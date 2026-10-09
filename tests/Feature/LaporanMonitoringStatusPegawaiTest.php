<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMonitoringStatusPegawaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/laporan/monitoring-status-pegawai');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_monitoring_status_rekap_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=rekap');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Status Pegawai & Pensiun');
        $response->assertSee('Rekapitulasi per SKPD');
    }

    public function test_authenticated_user_can_view_nominatif_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=nominatif');

        $response->assertStatus(200);
        $response->assertSee('Nominatif Pegawai Non-Aktif');
    }

    public function test_authenticated_user_can_view_proyeksi_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=proyeksi');

        $response->assertStatus(200);
        $response->assertSee('Proyeksi Pensiun Mendatang');
    }

    public function test_authenticated_user_can_view_anomali_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=anomali');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Anomali Penggajian Pasca Stop');
    }

    public function test_authenticated_user_can_export_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai/export/excel?tab=rekap');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_authenticated_user_can_paginate_nominatif_with_custom_per_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=nominatif&page=2&per_page=25');

        $response->assertStatus(200);
        $response->assertSee('Per hal:');
        $response->assertSee('pagination-container');
    }

    public function test_authenticated_user_can_paginate_proyeksi_and_anomali(): void
    {
        $user = User::factory()->create();

        $responseProyeksi = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=proyeksi&page=1&per_page=50');
        $responseProyeksi->assertStatus(200);
        $responseProyeksi->assertSee('Proyeksi Pensiun Mendatang');

        $responseAnomali = $this->actingAs($user)->get('/laporan/monitoring-status-pegawai?tab=anomali&page=1&per_page=25');
        $responseAnomali->assertStatus(200);
        $responseAnomali->assertSee('Monitoring Anomali Penggajian Pasca Stop');
    }
}
