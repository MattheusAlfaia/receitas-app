<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CategoriaController extends Controller
{
    #[OA\Get(
        path: '/api/categorias',
        operationId: 'listarCategorias',
        summary: 'Listar categorias',
        tags: ['Categorias'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de categorias com id e nome'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            Categoria::query()
                ->select(['id', 'nome'])
                ->orderBy('id', 'asc')
                ->get()
        );
    }
}
