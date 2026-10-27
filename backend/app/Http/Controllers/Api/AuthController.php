<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'register',
        tags: ['Autenticação'],
        summary: 'Cadastrar usuário',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maria'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SenhaForte123!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'SenhaForte123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário cadastrado e autenticado'
            ),
            new OA\Response(response: 422, description: 'Dados inválidos')
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Cadastro realizado com sucesso.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'login',
        tags: ['Autenticação'],
        summary: 'Entrar na aplicação',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SenhaForte123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login realizado'),
            new OA\Response(response: 422, description: 'Credenciais inválidas')
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou senha incorretos.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'currentUser',
        tags: ['Autenticação'],
        summary: 'Obter usuário autenticado',
        responses: [
            new OA\Response(response: 200, description: 'Usuário autenticado'),
            new OA\Response(response: 401, description: 'Não autenticado')
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'logout',
        tags: ['Autenticação'],
        summary: 'Encerrar sessão',
        responses: [
            new OA\Response(response: 200, description: 'Logout realizado'),
            new OA\Response(response: 401, description: 'Não autenticado')
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
