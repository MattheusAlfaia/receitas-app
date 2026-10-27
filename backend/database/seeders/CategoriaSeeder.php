<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Bolos e tortas doces',
            'Carnes',
            'Aves',
            'Peixes e frutos do mar',
            'Saladas, molhos e acompanhamentos',
            'Sopas',
            'Massas',
            'Bebidas',
            'Doces e sobremesas',
            'Lanches',
            'Prato Único',
            'Light',
            'Alimentação Saudável',
        ];

        foreach ($categorias as $nome) {
            Categoria::firstOrCreate([
                'nome' => $nome,
            ]);
        }
    }
}
