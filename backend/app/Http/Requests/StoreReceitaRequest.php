<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceitaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias,id',
            ],
            'nome' => [
                'required',
                'string',
                'max:150',
            ],
            'tempo_preparo_minutos' => [
                'required',
                'integer',
                'min:1',
            ],
            'porcoes' => [
                'required',
                'integer',
                'min:1',
            ],
            'modo_preparo' => [
                'required',
                'string',
            ],
            'ingredientes' => [
                'required',
                'string',
            ],
            'imagem' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:20480',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'O campo categoria é obrigatório.',
            'categoria_id.integer' => 'O campo categoria deve ser um número inteiro.',
            'categoria_id.exists' => 'A categoria selecionada não existe.',
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome não pode ter mais de 150 caracteres.',
            'tempo_preparo_minutos.required' => 'O campo tempo de preparo é obrigatório.',
            'tempo_preparo_minutos.integer' => 'O campo tempo de preparo deve ser um número inteiro.',
            'tempo_preparo_minutos.min' => 'O campo tempo de preparo deve ser no mínimo 1 minuto.',
            'porcoes.required' => 'O campo porções é obrigatório.',
            'porcoes.integer' => 'O campo porções deve ser um número inteiro.',
            'porcoes.min' => 'O campo porções deve ser no mínimo 1.',
            'modo_preparo.required' => 'O campo modo de preparo é obrigatório.',
            'modo_preparo.string' => 'O campo modo de preparo deve ser uma string.',
            'ingredientes.required' => 'O campo ingredientes é obrigatório.',
            'ingredientes.string' => 'O campo ingredientes deve ser uma string.',
            'imagem.file' => 'A imagem enviada é inválida.',
            'imagem.mimes' => 'A imagem deve estar no formato JPG, PNG, JPEG ou WEBP.',
            'imagem.max' => 'A imagem deve ter no máximo 20 MB.',
        ];
    }
}
