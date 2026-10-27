# Frontend — aplicação de receitas

Interface web da aplicação de receitas, construída com Vue 3, TypeScript e Vite.

## Funcionalidades

- cadastro e login de usuários;
- proteção de rotas autenticadas;
- listagem das receitas do usuário;
- pesquisa de receitas;
- criação e edição de receitas;
- upload de imagem;
- visualização e impressão de receitas;
- exclusão de receitas;
- mensagens de sucesso, erro e validação.

## Tecnologias

- Vue 3 com Composition API;
- TypeScript;
- Vite;
- Vue Router;
- Pinia;
- Axios;
- VeeValidate e Zod;
- Tailwind CSS;
- Vitest e Vue Test Utils;

## Requisitos

- Node.js `22.18+`;
- npm;
- backend disponível em `http://localhost:8000`.

## Configuração

Instale as dependências:

```bash
npm install
```

O frontend usa por padrão:

```text
http://localhost:8000
```

Para apontar para outra API, crie um arquivo `.env` neste diretório:

```env
VITE_API_URL=http://localhost:8000
```

## Desenvolvimento

Inicie o servidor Vite:

```bash
npm run dev
```

A aplicação ficará disponível em:

```text
http://localhost:5173
```

Para executar a aplicação completa com backend, banco e frontend via Docker,
use na raiz do repositório:

```bash
docker compose up -d --build
```

## Comunicação com a API

Os serviços ficam em `src/services`:

- `autenticacao.ts`: cadastro, login, usuário atual e logout;
- `servicoCategoria.ts`: listagem de categorias;
- `servicoReceita.ts`: listagem, busca, criação, edição e exclusão;
- `http.ts`: cliente Axios, cookies CSRF e tratamento de erros.

Os formulários usam schemas Zod para validação no cliente. A API continua
responsável pela validação final dos dados.

## Testes unitários

Executar todos os testes unitários:

```bash
npm run test:unit -- --run
```

Executar o Vitest em modo interativo:

```bash
npm run test:unit
```

Executar um arquivo específico:

```bash
npm run test:unit -- src/schemas/__tests__/receitaSchema.spec.ts
```

Os testes unitários cobrem schemas, services, stores e regras de transformação de dados sem depender do backend real.
