<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receita;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreReceitaRequest;
use App\Http\Requests\UpdateReceitaRequest;
use App\Services\ReceitaImageService;
use OpenApi\Attributes as OA;

class ReceitaController extends Controller
{
    public function __construct(
        private readonly ReceitaImageService $imageService
    ) {
    }

    #[OA\Get(
        path: '/api/receitas',
        operationId: 'listarReceitas',
        summary: 'Listar receitas do usuário autenticado',
        tags: ['Receitas'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de receitas'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'nome' => ['sometimes', 'string', 'max:150'],
            'categoria_id' => ['sometimes', 'integer', 'exists:categorias,id'],
            'tempo_maximo' => ['sometimes', 'integer', 'min:1'],
        ]);

        $receitas = $this->filteredQuery($request, $filters)
            ->paginate(10)
            ->withQueryString();

        $this->appendImageUrls($receitas);

        return response()->json($receitas);
    }

    #[OA\Post(
        path: '/api/receitas',
        operationId: 'cadastrarReceita',
        summary: 'Cadastrar uma nova receita',
        tags: ['Receitas'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [
                        'categoria_id',
                        'nome',
                        'tempo_preparo_minutos',
                        'porcoes',
                        'modo_preparo',
                        'ingredientes',
                    ],
                    properties: [
                        new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                        new OA\Property(property: 'nome', type: 'string', example: 'Bolo de Chocolate'),
                        new OA\Property(property: 'tempo_preparo_minutos', type: 'integer', example: 45),
                        new OA\Property(property: 'porcoes', type: 'integer', example: 4),
                        new OA\Property(property: 'modo_preparo', type: 'string', example: 'Misture os ingredientes e asse por 35 minutos.'),
                        new OA\Property(property: 'ingredientes', type: 'string', example: 'Farinha, ovos, açúcar, leite e chocolate.'),
                        new OA\Property(property: 'imagem', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Receita cadastrada com sucesso'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 422,
                description: 'Erro de validação'
            )
        ]
    )]
    public function store(StoreReceitaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $image = $request->file('imagem');
        unset($validated['imagem']);

        $storedPath = null;

        try {
            $receita = DB::transaction(function () use ($request, $validated, $image, &$storedPath): Receita {
                $receita = $request->user()->receitas()->create($validated);

                if ($image !== null) {
                    $storedPath = $this->imageService->store($receita, $image);
                }

                return $receita;
            });
        } catch (\Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        $receita->load('categoria');

        return response()->json([
            'message' => 'Receita cadastrada com sucesso.',
            'data' => $receita,
        ], 201);
    }

    public function image(Receita $receita): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $receita);

        $path = $this->imageService->existingPath($receita);

        if ($path !== null) {
            $stream = Storage::disk('public')->readStream($path);

            if ($stream === false) {
                abort(404);
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);

            return response()->stream(function () use ($stream): void {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => "image/{$extension}",
            ]);
        }

        abort(404);
    }

    #[OA\Get(
        path: '/api/receitas/{receita}',
        operationId: 'obterReceita',
        summary: 'Obter detalhes de uma receita',
        tags: ['Receitas'],
        parameters: [
            new OA\Parameter(
                name: 'receita',
                in: 'path',
                description: 'ID da receita',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes da receita'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Receita não encontrada'
            )
        ]
    )]
    public function show(Receita $receita): JsonResponse
    {
        $this->authorize('view', $receita);

        $receita->load('categoria');

        return response()->json([
            'data' => $receita,
        ]);
    }

    #[OA\Put(
        path: '/api/receitas/{receita}',
        operationId: 'atualizarReceita',
        summary: 'Atualizar uma receita',
        tags: ['Receitas'],
        parameters: [
            new OA\Parameter(
                name: 'receita',
                in: 'path',
                description: 'ID da receita',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [
                        'categoria_id',
                        'nome',
                        'tempo_preparo_minutos',
                        'porcoes',
                        'modo_preparo',
                        'ingredientes',
                    ],
                    properties: [
                        new OA\Property(property: 'nome', type: 'string', example: 'Bolo de Chocolate'),
                        new OA\Property(property: 'tempo_preparo_minutos', type: 'integer', example: 45),
                        new OA\Property(property: 'porcoes', type: 'integer', example: 4),
                        new OA\Property(property: 'modo_preparo', type: 'string', example: 'Misture os ingredientes e asse por 35 minutos.'),
                        new OA\Property(property: 'ingredientes', type: 'string', example: 'Farinha, ovos, açúcar, leite e chocolate.'),
                        new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                        new OA\Property(property: 'imagem', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Receita atualizada com sucesso'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Receita não encontrada'
            ),
            new OA\Response(
                response: 422,
                description: 'Erro de validação'
            )
        ]
    )]
    public function update(
        UpdateReceitaRequest $request,
        Receita $receita
    ): JsonResponse {
        $this->authorize('update', $receita);

        $validated = $request->validated();
        $image = $request->file('imagem');
        unset($validated['imagem']);

        $receita->update($validated);

        if ($image !== null) {
            $storedPath = $this->imageService->store($receita, $image);
            $this->imageService->deleteOtherExtensions($receita, $storedPath);
        }

        $receita->load('categoria');

        return response()->json([
            'message' => 'Receita atualizada com sucesso.',
            'data' => $receita,
        ]);
    }

    #[OA\Delete(
        path: '/api/receitas/{receita}',
        operationId: 'excluirReceita',
        summary: 'Excluir uma receita',
        tags: ['Receitas'],
        parameters: [
            new OA\Parameter(
                name: 'receita',
                in: 'path',
                description: 'ID da receita',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Receita excluída com sucesso'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Receita não encontrada'
            )
        ]
    )]
    public function destroy(Receita $receita): Response
    {
        $this->authorize('delete', $receita);

        $this->imageService->delete($receita);
        $receita->delete();

        return response()->noContent();
    }


    #[OA\Get(
        path: '/api/receitas/search',
        operationId: 'pesquisarReceitas',
        summary: 'Pesquisar receitas',
        tags: ['Receitas'],
        parameters: [
            new OA\Parameter(
                name: 'nome',
                in: 'query',
                description: 'Nome da receita',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'categoria_id',
                in: 'query',
                description: 'ID da categoria',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'tempo_maximo',
                in: 'query',
                description: 'Tempo máximo de preparo (em minutos)',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Receitas encontradas'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            )
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'nome' => ['sometimes', 'string', 'max:150'],
            'categoria_id' => ['sometimes', 'integer', 'exists:categorias,id'],
            'tempo_maximo' => ['sometimes', 'integer', 'min:1'],
        ]);

        $receitas = $this->filteredQuery($request, $filters)
            ->paginate(10)
            ->withQueryString();

        $this->appendImageUrls($receitas);

        return response()->json($receitas);
    }

    private function appendImageUrls(\Illuminate\Pagination\LengthAwarePaginator $receitas): void
    {
        $receitas->getCollection()->transform(function (Receita $receita): Receita {
            $imagemUrl = null;

            if ($this->imageService->existingPath($receita) !== null) {
                $imagemUrl = route('receitas.image', ['receita' => $receita]);
            }

            $receita->setAttribute('imagem_url', $imagemUrl);

            return $receita;
        });
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function filteredQuery(Request $request, array $filters): HasMany
    {
        return $request->user()
            ->receitas()
            ->with('categoria')
            ->when(
                $filters['nome'] ?? null,
                fn (Builder $query, string $nome) => $query->where(function (Builder $query) use ($nome): void {
                    $query->where('nome', 'like', "%{$nome}%")
                        ->orWhere('ingredientes', 'like', "%{$nome}%");
                })
            )
            ->when(
                $filters['categoria_id'] ?? null,
                fn (Builder $query, int $categoriaId) => $query->where('categoria_id', $categoriaId)
            )
            ->when(
                $filters['tempo_maximo'] ?? null,
                fn (Builder $query, int $tempo) => $query->where('tempo_preparo_minutos', '<=', $tempo)
            )
            ->latest();
    }
}
