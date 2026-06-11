# Todo Web Application API (Laravel + PostgreSQL)

REST API backend for the Todo Web Application frontend.

## Stack

- Laravel 12
- PostgreSQL
- Laravel Sanctum (Bearer token authentication)

## Setup

### 1. Start PostgreSQL

Using Docker:

```bash
docker compose up -d
```

Or install [PostgreSQL](https://www.postgresql.org/download/) locally and create a database named `todo_app`.

Enable the PHP PostgreSQL extension in `php.ini` (XAMPP: `C:\xampp\php\php.ini`):

```ini
extension=pdo_pgsql
extension=pgsql
```

Restart your terminal after enabling the extension.

### 2. Configure environment

Copy `.env.example` to `.env` if needed, then set PostgreSQL credentials:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todo_app
DB_USERNAME=postgres
DB_PASSWORD=postgres
FRONTEND_URL=http://localhost:3000
```

### 3. Install and migrate

```bash
composer install
php artisan key:generate
php artisan migrate
```

### 4. Run the API server

```bash
php artisan serve
```

API base URL: `http://localhost:8000/api`

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | No | Register a new user |
| POST | `/api/login` | No | Login and get token |
| POST | `/api/logout` | Yes | Revoke current token |
| GET | `/api/me` | Yes | Get current user |
| GET | `/api/todos` | Yes | List todos |
| POST | `/api/todos` | Yes | Create todo |
| PUT | `/api/todos/{id}` | Yes | Update todo |
| DELETE | `/api/todos/{id}` | Yes | Delete todo |

Send the token in the `Authorization: Bearer {token}` header for protected routes.
