<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_user_management(): void
    {
        $this->get('/setting/users')->assertRedirect('/login');
        $this->post('/setting/users', [])->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_user_list(): void
    {
        $user = User::factory()->create([
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@pemda.go.id',
        ]);

        $response = $this->actingAs($user)->get('/setting/users');
        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna Sistem');
        $response->assertSee('Bambang Sudirman');
        $response->assertSee('bambang@pemda.go.id');
        $response->assertSee('Anda');
    }

    public function test_authenticated_user_can_create_new_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/setting/users', [
            'name' => 'Ahmad Kasir',
            'email' => 'kasir@pemda.go.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/setting/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Ahmad Kasir',
            'email' => 'kasir@pemda.go.id',
        ]);

        $newUser = User::where('email', 'kasir@pemda.go.id')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(Hash::check('secret123', $newUser->password));
    }

    public function test_authenticated_user_cannot_create_user_with_duplicate_email(): void
    {
        $admin = User::factory()->create([
            'email' => 'existing@pemda.go.id',
        ]);

        $response = $this->actingAs($admin)->post('/setting/users', [
            'name' => 'Duplikat User',
            'email' => 'existing@pemda.go.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_update_user_name_and_email(): void
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@pemda.go.id',
            'password' => Hash::make('original-password'),
        ]);

        $response = $this->actingAs($admin)->put("/setting/users/{$targetUser->id}", [
            'name' => 'New Name Updated',
            'email' => 'new@pemda.go.id',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/setting/users');
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('New Name Updated', $targetUser->name);
        $this->assertEquals('new@pemda.go.id', $targetUser->email);
        $this->assertTrue(Hash::check('original-password', $targetUser->password));
    }

    public function test_authenticated_user_can_update_user_password(): void
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->put("/setting/users/{$targetUser->id}", [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect('/setting/users');
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertTrue(Hash::check('brand-new-password', $targetUser->password));
    }

    public function test_authenticated_user_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/setting/users/{$admin->id}");
        $response->assertRedirect('/setting/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_authenticated_user_can_delete_another_user(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create([
            'name' => 'User to Delete',
        ]);

        $response = $this->actingAs($admin)->delete("/setting/users/{$otherUser->id}");
        $response->assertRedirect('/setting/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }
}
