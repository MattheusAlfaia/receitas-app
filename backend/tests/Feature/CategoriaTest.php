<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_pode_listar_categorias_ordenadas(): void
    {
        $user = User::factory()->create();
        $primeira = Categoria::factory()->create(['nome' => 'Massas']);
        $segunda = Categoria::factory()->create(['nome' => 'Sobremesas']);

        $this->actingAs($user, 'web')
            ->getJson('/api/categorias')
            ->assertOk()
            ->assertJson([
                ['id' => $primeira->id, 'nome' => 'Massas'],
                ['id' => $segunda->id, 'nome' => 'Sobremesas'],
            ]);
    }

    public function test_lista_de_categorias_sem_registros_retorna_colecao_vazia(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->getJson('/api/categorias')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_usuario_nao_autenticado_nao_pode_listar_categorias(): void
    {
        $this->getJson('/api/categorias')->assertUnauthorized();
    }
}
