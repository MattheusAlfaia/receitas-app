<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @extends Factory<Categoria>
     * @return array<string, mixed>
     */

    protected $model = Categoria::class;
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
        ];
    }
}
