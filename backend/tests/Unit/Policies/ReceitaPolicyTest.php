<?php

namespace Tests\Unit\Policies;

use App\Models\Receita;
use App\Models\User;
use App\Policies\ReceitaPolicy;
use PHPUnit\Framework\TestCase;

class ReceitaPolicyTest extends TestCase
{
    public function test_proprietario_pode_visualizar_atualizar_e_excluir_sua_receita(): void
    {
        $user = new User();
        $user->id = 1;
        $receita = new Receita(['user_id' => 1]);
        $policy = new ReceitaPolicy();

        self::assertTrue($policy->view($user, $receita));
        self::assertTrue($policy->update($user, $receita));
        self::assertTrue($policy->delete($user, $receita));
    }

    public function test_usuario_diferente_nao_pode_visualizar_atualizar_ou_excluir_receita(): void
    {
        $user = new User();
        $user->id = 2;
        $receita = new Receita(['user_id' => 1]);
        $policy = new ReceitaPolicy();

        self::assertFalse($policy->view($user, $receita));
        self::assertFalse($policy->update($user, $receita));
        self::assertFalse($policy->delete($user, $receita));
    }
}
