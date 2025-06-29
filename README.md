# Sistema de Reservas Corporativas - API REST

API REST desenvolvida em Laravel para gerenciamento de reservas de recursos corporativos como salas de reunião, equipamentos e vagas de estacionamento.

## 🚀 Tecnologias

- **Laravel 12** - Framework PHP
- **MySQL** - Banco de dados
- **Sanctum** - Autenticação de API
- **SQL Raw** - Queries diretas sem ORM

## 📋 Pré-requisitos

- PHP >= 8.3
- Composer
- MySQL
- Laravel 12

## 🔧 Instalação

1. Clone o repositório
```bash
git clone https://github.com/ItamarJuniorDEV/api-reserva-corporativa.git
cd reserva-recursos-corporativos-api
```

2. Instale as dependências
```bash
composer install
```

3. Configure o arquivo `.env`
```bash
cp .env.example .env
```

4. Configure o banco de dados no `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservas_db
DB_USERNAME=root
DB_PASSWORD=
```

5. Gere a chave da aplicação
```bash
php artisan key:generate
```

6. Execute as migrations e seeders
```bash
php artisan migrate:fresh --seed
```

7. Inicie o servidor
```bash
php artisan serve
```

## 🔐 Autenticação

A API utiliza Sanctum para autenticação. Todas as rotas (exceto login) requerem um token Bearer.

### Usuários de Teste
| Email | Senha |
|-------|-------|
| joao@empresa.com | 12345678 |
| maria@empresa.com | 12345678 |

## 📚 Endpoints da API

### Base URL
```
http://localhost:8000/api
```

### 1. Autenticação

#### Login
```http
POST /login
Content-Type: application/json

{
    "email": "joao@empresa.com",
    "password": "12345678"
}
```

**Resposta de sucesso (200):**
```json
{
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
    "message": "Login realizado com sucesso"
}
```

### 2. Recursos

#### Listar Recursos
```http
GET /recursos
Authorization: Bearer {token}
```

**Parâmetros de Query:**
- `page` (opcional) - Número da página para paginação

**Resposta de sucesso (200):**
```json
{
    "recursos": [
        {
            "id": 1,
            "nome": "Sala de Reunião 101",
            "tipo": "sala",
            "capacidade": 10,
            "ativo": 1
        }
    ],
    "total_registros": 7,
    "por_pagina": 10,
    "pagina_atual": 1,
    "ultima_pagina": 1
}
```

#### Verificar Disponibilidade
```http
GET /recursos/{id}/disponibilidade?data=2025-01-22
Authorization: Bearer {token}
```

**Parâmetros:**
- `id` - ID do recurso
- `data` (obrigatório) - Data no formato YYYY-MM-DD

**Resposta de sucesso (200):**
```json
{
    "recurso_id": "1",
    "data": "2025-01-22",
    "horarios_ocupados": [
        {
            "data_inicio": "2025-01-22 14:00:00",
            "data_fim": "2025-01-22 16:00:00"
        }
    ]
}
```

### 3. Reservas

#### Criar Reserva
```http
POST /reservas
Authorization: Bearer {token}
Content-Type: application/json

{
    "recurso_id": 1,
    "data_inicio": "2025-01-22 14:00:00",
    "data_fim": "2025-01-22 15:30:00"
}
```

**Resposta de sucesso (201):**
```json
{
    "message": "Reserva criada com sucesso",
    "reserva_id": 1
}
```

**Possíveis erros:**
- `409 Conflict` - Já existe reserva no horário
- `422 Unprocessable Entity` - Validação falhou
- `404 Not Found` - Recurso não encontrado

#### Listar Minhas Reservas
```http
GET /reservas/minhas
Authorization: Bearer {token}
```

**Resposta de sucesso (200):**
```json
[
    {
        "id": 1,
        "recurso_id": 1,
        "usuario_id": 1,
        "data_inicio": "2025-01-22 14:00:00",
        "data_fim": "2025-01-22 15:30:00",
        "recurso_nome": "Sala de Reunião 101",
        "tipo": "sala"
    }
]
```

#### Cancelar Reserva
```http
DELETE /reservas/{id}
Authorization: Bearer {token}
```

**Resposta de sucesso (200):**
```json
{
    "message": "Reserva cancelada com sucesso"
}
```

**Erro (404):**
```json
{
    "message": "Reserva não encontrada"
}
```

## 📏 Regras de Negócio

### Tipos de Recursos
- **Salas de Reunião** - Com capacidade definida
- **Equipamentos** - Sem capacidade
- **Vagas de Estacionamento** - Capacidade = 1

### Regras de Reserva
- ⏱️ **Duração mínima:** 30 minutos
- ⏰ **Duração máxima:** 4 horas
- 📅 **Apenas horários futuros** podem ser reservados
- 🚫 **Sem conflitos** - Não é possível reservar um recurso já ocupado
- ❌ **Cancelamento** - Apenas o próprio usuário pode cancelar suas reservas

### Validação de Conflitos
O sistema verifica se existe sobreposição de horários considerando:
- Nova reserva começando durante uma existente
- Nova reserva terminando durante uma existente
- Nova reserva englobando uma existente
- Reserva existente dentro do período da nova

## 🗄️ Estrutura do Banco de Dados

### Tabela `users`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| name | varchar | Nome do usuário |
| email | varchar | Email único |
| password | varchar | Senha criptografada |

### Tabela `recursos`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| nome | varchar | Nome do recurso |
| tipo | enum | sala, equipamento, estacionamento |
| capacidade | integer | Capacidade (nullable) |
| ativo | boolean | Status do recurso |

### Tabela `reservas`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| recurso_id | bigint | FK para recursos |
| usuario_id | bigint | FK para users |
| data_inicio | datetime | Início da reserva |
| data_fim | datetime | Fim da reserva |

## 🧪 Testando a API

### Com Postman
1. Importe a collection ou crie manualmente as requisições
2. Configure a variável de ambiente `{{token}}` após o login
3. Adicione o header `Accept: application/json` em todas as requisições

### Exemplo de Fluxo Completo
```bash
# 1. Login
POST /api/login
Body: {"email": "joao@empresa.com", "password": "12345678"}

# 2. Listar recursos disponíveis
GET /api/recursos
Header: Authorization: Bearer {token}

# 3. Verificar disponibilidade
GET /api/recursos/1/disponibilidade?data=2025-01-22
Header: Authorization: Bearer {token}

# 4. Criar reserva
POST /api/reservas
Header: Authorization: Bearer {token}
Body: {
    "recurso_id": 1,
    "data_inicio": "2025-01-22 14:00:00",
    "data_fim": "2025-01-22 15:30:00"
}

# 5. Ver minhas reservas
GET /api/reservas/minhas
Header: Authorization: Bearer {token}

# 6. Cancelar reserva
DELETE /api/reservas/1
Header: Authorization: Bearer {token}
```

## 🔍 Códigos de Status HTTP

- `200 OK` - Requisição bem-sucedida
- `201 Created` - Recurso criado com sucesso
- `401 Unauthorized` - Token inválido ou ausente
- `404 Not Found` - Recurso não encontrado
- `409 Conflict` - Conflito de horário
- `422 Unprocessable Entity` - Validação falhou

## 📝 Observações

- Todas as queries são executadas usando SQL raw, sem uso de Eloquent ORM
- O sistema utiliza prepared statements para prevenir SQL Injection
- As datas devem estar no formato `Y-m-d H:i:s`
- A paginação retorna 10 itens por página

## 👥 Autor

Itamar Alves Ferreira Junior - [cdajuniorf@gmail.com](mailto:cdajuniorf@gmail.com)

## 📄 Licença

Este projeto está sob a licença MIT.