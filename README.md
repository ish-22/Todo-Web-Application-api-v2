# TaskFlow — Backend API

REST API for the TaskFlow todo application, built with Laravel 12 and PostgreSQL.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Database | PostgreSQL |
| Authentication | Laravel Sanctum (Bearer token) |
| Language | PHP 8.2+ |

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php     # register, login, logout, me
│   │   └── TodoController.php     # index, store, update, destroy
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterRequest.php
│   │   └── Todo/
│   │       ├── IndexTodoRequest.php
│   │       ├── StoreTodoRequest.php
│   │       └── UpdateTodoRequest.php
│   └── Resources/
│       ├── TodoResource.php       # shapes todo API responses
│       └── UserResource.php       # shapes user API responses
├── Models/
│   ├── Todo.php
│   └── User.php
config/
├── cors.php                       # allows requests from FRONTEND_URL
routes/
└── api.php                        # all API routes
```

## Setup

### Prerequisites

- PHP 8.2+
- Composer
- PostgreSQL

### 1. Start PostgreSQL

Using Docker (recommended):

```bash
docker compose up -d
```

Or install [PostgreSQL](https://www.postgresql.org/download/) locally and create a database named `todo_app`.

If using XAMPP, enable the PostgreSQL PHP extensions in `C:\xampp\php\php.ini`:

```ini
extension=pdo_pgsql
extension=pgsql
```

Restart your terminal after enabling.

### 2. Configure environment

Copy `.env.example` to `.env` and set your values:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todo_app
DB_USERNAME=postgres
DB_PASSWORD=postgres

FRONTEND_URL=http://localhost:3000
```

### 3. Install, generate key, and migrate

```bash
composer install
php artisan key:generate
php artisan migrate
```

### 4. Run the server

```bash
php artisan serve
```

API base URL: `http://localhost:8000/api`

---

## Authentication

This API uses **Laravel Sanctum** with Bearer tokens.

- On login or register, the API returns a `token`.
- Include it in every protected request header:

```
Authorization: Bearer <token>
```

- Tokens are revoked on logout.

---

## API Endpoints

### Auth

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| POST | `/api/register` | No | Create a new account |
| POST | `/api/login` | No | Login and receive a token |
| POST | `/api/logout` | Yes | Revoke the current token |
| GET | `/api/me` | Yes | Get the authenticated user |

### Todos

| Method | Endpoint | Auth Required | Description |
|--------|----------|:---:|-------------|
| GET | `/api/todos` | Yes | List all todos (supports filters) |
| POST | `/api/todos` | Yes | Create a new todo |
| PUT | `/api/todos/{id}` | Yes | Update a todo |
| DELETE | `/api/todos/{id}` | Yes | Delete a todo |

---

## Request & Response Examples

### POST `/api/register`

**Request body**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response `201`**
```json
{
  "token": "1|abc123...",
  "user": {
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

---

### POST `/api/login`

**Request body**
```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response `200`**
```json
{
  "token": "2|xyz789...",
  "user": {
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

---

### GET `/api/todos`

Supports optional query parameters for filtering:

| Parameter | Type | Values | Description |
|-----------|------|--------|-------------|
| `search` | string | any | Search title and description |
| `status` | string | `all`, `pending`, `completed` | Filter by completion status |
| `priority` | string | `Low`, `Medium`, `High` | Filter by priority |

**Example:** `GET /api/todos?status=pending&priority=High`

**Response `200`**
```json
[
  {
    "id": 1,
    "title": "Finish report",
    "description": "Q3 financial summary",
    "priority": "High",
    "completed": false,
    "createdAt": "2 hours ago"
  }
]
```

---

### POST `/api/todos`

**Request body**
```json
{
  "title": "Finish report",
  "description": "Q3 financial summary",
  "priority": "High"
}
```

**Response `201`** — returns the created todo object.

---

### PUT `/api/todos/{id}`

All fields are optional. Send only what you want to change.

```json
{
  "title": "Updated title",
  "completed": true
}
```

**Response `200`** — returns the updated todo object.

---

### DELETE `/api/todos/{id}`

**Response `200`**
```json
{
  "message": "Todo deleted successfully."
}
```

---

## Todo Object Shape

```json
{
  "id": 1,
  "title": "string",
  "description": "string",
  "priority": "Low | Medium | High",
  "completed": false,
  "createdAt": "3 minutes ago"
}
```

---

## CORS

Allowed origin is controlled by `FRONTEND_URL` in `.env`. Default: `http://localhost:3000`.

To change it, update `.env`:

```env
FRONTEND_URL=http://your-frontend-url.com
```
