# Projeto Fullstack Receitas Culinárias
- Backend: Laravel + MySQL + Sanctum 
- Frontend: Vue 3 + Vite + TailwindCSS

## Setup do projeto com Docker
Serviços utilizados:
- MySQL
- PhpMyAdmin
- Nginx
- PHP-FPM
- Node.js

### 1. Clonar o repositório
```bash
git clone <URL_DO_REPOSITORIO>
cd <NOME_DO_PROJETO>
```

### 2. Construir as imagens e iniciar os serviços
```bash
docker compose up -d --build
```

##### 3. Configurar o backend
```bash
docker compose exec backend cp .env.example .env
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

##### 4. Configurar o frontend (opcional)
```bash
docker compose exec frontend cp .env.example .env
```

#### 5. Acessar o projeto
- Frontend: [http://localhost:5173](http://localhost:5173)
- Backend: [http://localhost:8000](http://localhost:8000)
- Swagger: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
- PhpMyAdmin: [http://localhost:8080](http://localhost:8080)
- PhpMyAdmin usuário: `root`, senha: `root_password`

### 6. Testes Backend
Executar todos os testes:
```bash
docker compose exec backend php artisan test
```
Executar somente testes unitários:
```bash
docker compose exec backend php artisan test --testsuite=Unit
```
Executar somente testes de integração:
```bash
docker compose exec backend php artisan test --testsuite=Feature
```

### 7. Testes Frontend
Executar todos os testes unitários:

```bash
docker compose exec frontend npm run test:unit -- --run
```