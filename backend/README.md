# Backend — API de receitas

API REST da aplicação de receitas, construída com Laravel 13 e PHP 8.3.

## Tecnologias

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- MySQL 8.4 em ambiente Docker
- SQLite em memória nos testes
- Pest/PHPUnit
- L5 Swagger

## Requisitos

Para executar localmente:

- PHP 8.3 ou superior;
- Composer;
- extensão PDO compatível com o banco escolhido;

Para executar com Docker:

- Docker;
- Docker Compose.

## Configuração local

Dentro deste diretório:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Inicie a API:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

A API ficará disponível em:

```text
http://localhost:8000
```

## Documentação Swagger

A documentação OpenAPI gerada pelo L5 Swagger pode ser acessada em:

```text
http://localhost:8000/api/documentation
```

Se necessário, regenere a documentação com:

```bash
php artisan l5-swagger:generate
```

## Testes
Executar todos os testes:

```bash
php artisan test
```

Executar somente testes unitários:

```bash
php artisan test --testsuite=Unit
```

Executar somente testes de integração:

```bash
php artisan test --testsuite=Feature
```
