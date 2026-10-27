<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Receita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// php artisan test --filter=ReceitaTest

class ReceitaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_pode_cadastrar_receita(): void
    {
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/receitas', [
            'categoria_id' => $categoria->id,
            'nome' => 'Bolo de chocolate',
            'tempo_preparo_minutos' => 45,
            'porcoes' => 8,
            'modo_preparo' => 'Misture e asse.',
            'ingredientes' => 'Farinha, ovos e chocolate.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.nome', 'Bolo de chocolate');

        $this->assertDatabaseHas('receitas', [
            'user_id' => $user->id,
            'nome' => 'Bolo de chocolate',
        ]);
    }

    public function test_usuario_autenticado_pode_cadastrar_receita_com_imagem(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create();

        $response = $this->actingAs($user)->post('/api/receitas', [
            'categoria_id' => $categoria->id,
            'nome' => 'Bolo de chocolate',
            'tempo_preparo_minutos' => 45,
            'porcoes' => 8,
            'modo_preparo' => 'Misture e asse.',
            'ingredientes' => 'Farinha, ovos e chocolate.',
            'imagem' => UploadedFile::fake()->image('bolo.png'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        Storage::disk('public')->assertExists(
            "receitas/imagem_{$response->json('data.id')}.png"
        );
    }

    public function test_usuario_nao_pode_editar_receita_de_outro_usuario(): void
    {
        $dono = User::factory()->create();
        $outroUsuario = User::factory()->create();
        $categoria = Categoria::factory()->create();

        $receita = Receita::factory()->create([
            'user_id' => $dono->id,
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->actingAs($outroUsuario)->putJson(
            "/api/receitas/{$receita->id}",
            [
                'categoria_id' => $categoria->id,
                'nome' => 'Nome alterado',
                'tempo_preparo_minutos' => 30,
                'porcoes' => 4,
                'modo_preparo' => 'Modo atualizado.',
                'ingredientes' => 'Ingredientes atualizados.',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('receitas', [
            'id' => $receita->id,
            'user_id' => $dono->id,
            'nome' => $receita->nome,
        ]);
    }

    public function test_usuario_nao_autenticado_nao_pode_listar_receitas(): void
    {
        $this->getJson('/api/receitas')
            ->assertUnauthorized();
    }

    public function test_usuario_autenticado_pode_listar_categorias_com_apenas_id_e_nome(): void
    {
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create(['nome' => 'Sobremesas']);

        $response = $this->actingAs($user)->getJson('/api/categorias');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $categoria->id)
            ->assertJsonPath('0.nome', 'Sobremesas');

        $this->assertSame(
            ['id', 'nome'],
            array_keys($response->json('0'))
        );
    }

    public function test_usuario_autenticado_pode_filtrar_receitas_por_nome_ingrediente_categoria_e_tempo(): void
    {
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create();
        Receita::factory()->create([
            'user_id' => $user->id,
            'categoria_id' => $categoria->id,
            'nome' => 'Moqueca capixaba de camarão',
            'ingredientes' => 'Camarão, tomate e coentro.',
            'tempo_preparo_minutos' => 50,
        ]);
        Receita::factory()->create([
            'user_id' => $user->id,
            'categoria_id' => $categoria->id,
            'nome' => 'Bolo de chocolate',
            'ingredientes' => 'Farinha e chocolate.',
            'tempo_preparo_minutos' => 60,
        ]);

        $response = $this->actingAs($user)->getJson('/api/receitas?nome=camarão&tempo_maximo=50');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Moqueca capixaba de camarão')
            ->assertJsonPath('data.0.imagem_url', null);
    }

    public function test_usuario_autenticado_pode_consultar_sua_receita(): void
    {
        $user = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson("/api/receitas/{$receita->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $receita->id)
            ->assertJsonPath('data.categoria.id', $receita->categoria_id);
    }

    public function test_usuario_nao_pode_consultar_receita_de_outro_usuario(): void
    {
        $dono = User::factory()->create();
        $outroUsuario = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $dono->id]);

        $this->actingAs($outroUsuario)
            ->getJson("/api/receitas/{$receita->id}")
            ->assertForbidden();
    }

    public function test_usuario_autenticado_pode_atualizar_sua_receita(): void
    {
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create();
        $receita = Receita::factory()->create([
            'user_id' => $user->id,
            'categoria_id' => $categoria->id,
        ]);

        $this->actingAs($user)->putJson("/api/receitas/{$receita->id}", [
            'categoria_id' => $categoria->id,
            'nome' => 'Receita atualizada',
            'tempo_preparo_minutos' => 25,
            'porcoes' => 3,
            'modo_preparo' => 'Prepare e sirva.',
            'ingredientes' => 'Ingredientes novos.',
        ])
            ->assertOk()
            ->assertJsonPath('data.nome', 'Receita atualizada');

        $this->assertDatabaseHas('receitas', [
            'id' => $receita->id,
            'nome' => 'Receita atualizada',
            'tempo_preparo_minutos' => 25,
        ]);
    }

    public function test_usuario_autenticado_pode_excluir_sua_receita(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $user->id]);
        Storage::disk('public')->put(
            "receitas/imagem_{$receita->id}.png",
            'conteudo da imagem'
        );

        $this->actingAs($user)
            ->deleteJson("/api/receitas/{$receita->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('receitas', ['id' => $receita->id]);
        Storage::disk('public')->assertMissing(
            "receitas/imagem_{$receita->id}.png"
        );
    }

    public function test_atualizar_receita_substitui_imagem_e_remove_extensao_anterior(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $user->id]);
        Storage::disk('public')->put(
            "receitas/imagem_{$receita->id}.png",
            'imagem antiga'
        );

        $this->actingAs($user)->post(
            "/api/receitas/{$receita->id}",
            [
                '_method' => 'PUT',
                'categoria_id' => $receita->categoria_id,
                'nome' => $receita->nome,
                'tempo_preparo_minutos' => $receita->tempo_preparo_minutos,
                'porcoes' => $receita->porcoes,
                'modo_preparo' => $receita->modo_preparo,
                'ingredientes' => $receita->ingredientes,
                'imagem' => UploadedFile::fake()->image('nova.jpg'),
            ],
            ['Accept' => 'application/json']
        )->assertOk();

        Storage::disk('public')->assertExists(
            "receitas/imagem_{$receita->id}.jpg"
        );
        Storage::disk('public')->assertMissing(
            "receitas/imagem_{$receita->id}.png"
        );
    }

    public function test_usuario_nao_pode_excluir_receita_de_outro_usuario(): void
    {
        $dono = User::factory()->create();
        $outroUsuario = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $dono->id]);

        $this->actingAs($outroUsuario)
            ->deleteJson("/api/receitas/{$receita->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('receitas', ['id' => $receita->id]);
    }

    public function test_cadastro_de_receita_rejeita_dados_invalidos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/receitas', [
            'categoria_id' => 999,
            'nome' => str_repeat('a', 151),
            'tempo_preparo_minutos' => 0,
            'porcoes' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'categoria_id',
                'nome',
                'tempo_preparo_minutos',
                'porcoes',
                'modo_preparo',
                'ingredientes',
            ]);
    }

    public function test_usuario_autenticado_pode_consultar_imagem_da_sua_receita(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $user->id]);
        Storage::disk('public')->put(
            "receitas/imagem_{$receita->id}.png",
            'conteudo da imagem'
        );

        $this->actingAs($user)
            ->get("/api/receitas/{$receita->id}/imagem")
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_imagem_inexistente_retorna_not_found(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $receita = Receita::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get("/api/receitas/{$receita->id}/imagem")
            ->assertNotFound();
    }

    public function test_usuario_nao_autenticado_nao_pode_listar_categorias(): void
    {
        $this->getJson('/api/categorias')
            ->assertUnauthorized();
    }
}
