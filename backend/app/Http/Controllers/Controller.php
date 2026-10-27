<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Minha API',
    version: '1.0.0',
    description: 'Documentação da API Laravel'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Ambiente local'
)]

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
