<?php

namespace Database\Factories;

use App\Models\Receita;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receita>
 */
class ReceitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @extends Factory<Receita>
     * @return array<string, mixed>
     */
    protected $model = Receita::class;
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory(),
            'nome' => fake()->sentence(3),
            'tempo_preparo_minutos' => fake()->numberBetween(10, 120),
            'porcoes' => fake()->numberBetween(1, 10),
            'modo_preparo' => fake()->paragraph(3),
            'ingredientes' => fake()->paragraph(2),
        ];
    }
}
