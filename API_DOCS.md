# API Documentation - Sistema Omnibus

## 🔐 Autenticação

### Secretaria (Web)
- Usa o modelo `User`
- Endpoints: `/api/register`, `/api/login` (já existentes no Laravel auth)
- Token: Sanctum

### Motoristas (App Mobile)
- Usa o modelo `Drivers`
- Endpoints específicos abaixo

---

## 📱 Rotas do Motorista (App Mobile)

### Login
```http
POST /api/drivers/login
Content-Type: application/json

{
  "email": "motorista@example.com",
  "password": "senha123"
}
```

**Resposta:**
```json
{
  "message": "Login realizado com sucesso.",
  "driver": {
    "id": 1,
    "name": "João Silva",
    "email": "motorista@example.com",
    "license_number": "12345678900",
    "phone_number": "11999999999"
  },
  "token": "1|AbC123..."
}
```

### Dados do Motorista Autenticado
```http
GET /api/drivers/me
Authorization: Bearer {token}
```

### Logout
```http
POST /api/drivers/logout
Authorization: Bearer {token}
```

### Listar Despesas do Motorista
```http
GET /api/drivers/expenses
Authorization: Bearer {token}
```

### Cadastrar Despesa
```http
POST /api/drivers/expenses
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_plate": "ABC1234",
  "value": 150.50,
  "proof_of_payment": "base64_da_imagem_ou_url"
}
```

### Ver Despesa Específica
```http
GET /api/drivers/expenses/{id}
Authorization: Bearer {token}
```

⚠️ **Importante**: Motoristas **NÃO** podem editar ou deletar despesas após cadastradas.

### Ver Total Mensal
```http
GET /api/drivers/expenses-monthly-total
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "month": "02",
  "year": "2026",
  "total": 450.75
}
```

---

## 🖥️ Rotas da Secretaria (Web)

Todas as rotas abaixo requerem autenticação via Sanctum.

### Motoristas

#### Listar Todos
```http
GET /api/drivers
Authorization: Bearer {token}
```

#### Cadastrar Motorista
```http
POST /api/drivers
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "name": "João Silva",
  "license_number": "12345678900",
  "phone_number": "11999999999",
  "email": "motorista@example.com",
  "password": "senha_inicial_123"
}
```

⚠️ **Importante**: A senha será hasheada automaticamente pelo Laravel.

#### Ver Motorista
```http
GET /api/drivers/{id}
Authorization: Bearer {token}
```

#### Atualizar Motorista
```http
PUT /api/drivers/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "João Silva Atualizado"
}
```

#### Deletar Motorista
```http
DELETE /api/drivers/{id}
Authorization: Bearer {token}
```

### Despesas

#### Listar Todas
```http
GET /api/expenses
Authorization: Bearer {token}
```

#### Ver Despesa Específica
```http
GET /api/expenses/{id}
Authorization: Bearer {token}
```

#### Deletar Despesa
```http
DELETE /api/expenses/{id}
Authorization: Bearer {token}
```

⚠️ **Importante**: Secretaria **NÃO** pode criar ou editar despesas, apenas visualizar e deletar.

### Limites de Gastos

#### Listar Limites
```http
GET /api/spending-limits
Authorization: Bearer {token}
```

#### Cadastrar Limite
```http
POST /api/spending-limits
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "limit_amount": 5000.00
}
```

**Resposta:**
```json
{
  "message": "Limite de gastos cadastrado com sucesso.",
  "data": {
    "id": 1,
    "user_id": 1,
    "limit_amount": 5000.00,
    "is_exceeded": false,
    "month": "02",
    "year": "2026",
    "created_at": "2026-02-26T10:30:00.000000Z"
  }
}
```

⚠️ **Nota**: 
- `month` e `year` são derivados automaticamente do `created_at`
- `is_exceeded` é calculado dinamicamente somando as despesas do mês
- Só pode haver um limite por usuário por mês

---

### Veículos

#### Listar Todos
```http
GET /api/buses
Authorization: Bearer {token}
```

#### Cadastrar Ônibus
```http
POST /api/buses
Authorization: Bearer {token}
Content-Type: application/json

{
    "driver_id": 1,
    "plate": "ABC1D23",
    "capacity": 45,
    "mainRoute": "Ingá - Centro"
}
```

#### Ver Ônibus
```http
GET /api/buses/{id}
Authorization: Bearer {token}
```

#### Atualizar Ônibus
```http
PUT /api/buses/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "mainRoute": "Ingá - Timbaúba - Centro"
}
```

#### Deletar Ônibus
```http
DELETE /api/drivers/{id}
Authorization: Bearer {token}
```

## 🔒 Segurança

### Tokens Sanctum
- Use o header: `Authorization: Bearer {token}`
- Motoristas só podem acessar suas próprias despesas
- Secretaria tem acesso total ao sistema

### Senhas
- Senhas são hasheadas automaticamente com bcrypt
- Mínimo de 6 caracteres

---

## 📊 Fluxo de Trabalho

1. **Secretaria cadastra motorista** → Define email e senha inicial
2. **Motorista faz login no app** → Usa email e senha definidos pela secretaria
3. **Motorista cadastra despesas** → Durante o mês
4. **Secretaria cadastra limite mensal** → Define valor estimado no início do mês
5. **Sistema verifica automaticamente** → Se total de despesas > limite (campo `is_exceeded`)

---

## 🛠️ Setup

```bash
# Rodar migrations
php artisan migrate:fresh

# Testar API
php artisan serve
```

---

## 📝 Validações

### Drivers
- `email`: único, válido
- `license_number`: único
- `password`: mínimo 6 caracteres

### Expenses
- `value`: numérico, entre 0 e 999999.99
- `vehicle_plate`: obrigatório

### Spending Limits
- `limit_amount`: numérico, entre 0 e 9999999.99
- Apenas um limite por usuário por mês

### Buses
- `plate`: único