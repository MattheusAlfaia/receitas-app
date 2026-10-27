<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Table('receitas')]

#[Fillable([
    'user_id',
    'categoria_id',
    'nome',
    'tempo_preparo_minutos',
    'porcoes',
    'modo_preparo',
    'ingredientes',
])]

class Receita extends Model
{
    use HasFactory;
    protected function casts(): array
    {
        return [
            'tempo_preparo_minutos' => 'integer',
            'porcoes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
