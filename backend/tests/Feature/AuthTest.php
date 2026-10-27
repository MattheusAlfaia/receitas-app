<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_se_cadastrar_e_fica_autenticado(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => 'SenhaForte123',
            'password_confirmation' => 'SenhaForte123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'maria@example.com');

        $this->assertDatabaseHas('users', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
        ]);
        $this->assertAuthenticated('web');
    }

    public function test_cadastro_rejeita_dados_invalidos(): void
    {
        User::factory()->create(['email' => 'existente@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'existente@example.com',
            'password' => 'curta',
            'password_confirmation' => 'diferente',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    public function test_usuario_pode_fazer_login(): void
    {
        $user = User::factory()->create([
            'email' => 'maria@example.com',
            'password' => 'SenhaForte123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'SenhaForte123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_rejeita_credenciais_invalidas(): void
    {
        User::factory()->create([
            'email' => 'maria@example.com',
            'password' => 'SenhaForte123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'maria@example.com',
            'password' => 'senha-incorreta',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest('web');
    }

    public function test_usuario_autenticado_pode_consultar_seu_usuario_atual(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_usuario_nao_autenticado_nao_pode_consultar_usuario_atual(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_usuario_autenticado_pode_fazer_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        $this->assertGuest('web');
    }

    public function test_logout_exige_autenticacao(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }
}
