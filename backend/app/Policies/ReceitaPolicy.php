<?php

namespace App\Policies;

use App\Models\Receita;
use App\Models\User;

class ReceitaPolicy
{
    /**
     * Create a new policy instance.
     * Um usuário só pode acessar uma receita quando o autor.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Receita $receita): bool
    {
        return $user->id === $receita->user_id;
    }

    public function update(User $user, Receita $receita): bool
    {
        return $user->id === $receita->user_id;
    }

    public function delete(User $user, Receita $receita): bool
    {
        return $user->id === $receita->user_id;
    }
}
