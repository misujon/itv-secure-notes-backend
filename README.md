# Secure Note-Taking Application — Backend API

A secure, maintainable REST API backend for a note-taking platform built with **Laravel 13**, **PHP 8.4**, **MongoDB Community Server**, **`mongodb/laravel-mongodb`**, and **JWT authentication** (`php-open-source-saver/jwt-auth`).

## Installation

### Prerequisites

- PHP 8.3+ (tested on PHP 8.4.23) with the `mongodb` PHP extension
- Composer
- MongoDB Community Server (running locally or via connection URI)

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configure MongoDB and CORS in `.env`:

```env
DB_CONNECTION=mongodb
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=secure_notes
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

Run database migrations to create collections and MongoDB indexes:

```bash
php artisan migrate
```

Create the initial admin account (interactive or via flags; no credentials are hardcoded):

```bash
php artisan app:create-admin --name="Admin User" --email="admin@example.com" --password="StrongPassword123!"
```

Start the API server:

```bash
php artisan serve
```

## Project Architecture

The application follows a layered request lifecycle:

`API Route -> Middleware (auth:api, role, object.id, throttle) -> Controller -> Form Request Validation -> Service -> Repository -> MongoDB Eloquent Model -> MongoDB`

- **Controllers** (`app/Http/Controllers/Api/V1/`): Thin HTTP handlers delegating validation to Form Requests, business logic to Services, and formatting output with API Resources and `App\Support\ApiResponse`.
- **Services** (`app/Services/`): `AuthService`, `UserService`, `NoteService`, `PostService`, `AggregationService`.
- **Repositories** (`app/Repositories/`): `UserRepository`, `NoteRepository`, `PostRepository` encapsulating Eloquent queries, pagination, and native MongoDB aggregation pipelines.
- **Models** (`app/Models/`): `User` (extends `MongoDB\Laravel\Auth\User` and implements `JWTSubject`), `Note`, and `Post` (extend `MongoDB\Laravel\Eloquent\Model` with `ObjectId` casting on `user_id`).
- **Authorization**: `NotePolicy` enforces owner-only note mutations and read isolation; `EnsureUserHasRole` middleware enforces `admin` role boundaries.
- **Security**: `Hash::make()` / `Hash::check()` password hashing, `ValidateObjectId` middleware for 24-hex ObjectId validation, login rate limiting (`5/min`), restricted CORS (`config/cors.php`), and centralized exception handling (`bootstrap/app.php`) that never leaks raw database errors.

## API Endpoint Summary

All endpoints are prefixed with `/api/v1`.

### Authentication

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/auth/register` | Public | Register a new `user` account and issue JWT |
| `POST` | `/auth/login` | Public (throttled) | Authenticate user and issue JWT |
| `GET` | `/auth/me` | `auth:api` | Get current authenticated user profile |
| `POST` | `/auth/logout` | `auth:api` | Invalidate current JWT |
| `POST` | `/auth/refresh` | `auth:api` | Refresh current JWT |

### Notes (Authenticated Owner)

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/notes` | `auth:api` | List authenticated user's notes (paginated) |
| `POST` | `/notes` | `auth:api` | Create a note owned by the authenticated user |
| `GET` | `/notes/{id}` | `auth:api` | Retrieve a specific note (owner or admin) |
| `PATCH` | `/notes/{id}` | `auth:api` | Update an owned note |
| `DELETE` | `/notes/{id}` | `auth:api` | Delete an owned note |

### Admin Management (`role:admin`)

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/users` | `auth:api`, `role:admin` | List all users (paginated) |
| `POST` | `/admin/users` | `auth:api`, `role:admin` | Create a user or admin |
| `GET` | `/admin/users/{id}` | `auth:api`, `role:admin` | View user details |
| `PATCH` | `/admin/users/{id}` | `auth:api`, `role:admin` | Update user (protects last admin) |
| `DELETE` | `/admin/users/{id}` | `auth:api`, `role:admin` | Delete user (protects last admin) |
| `GET` | `/admin/notes` | `auth:api`, `role:admin` | List all users' notes (read-only, paginated) |
| `GET` | `/admin/notes/{id}` | `auth:api`, `role:admin` | View any note with safe user summary |
| `GET` | `/admin/aggregations/interests` | `auth:api`, `role:admin` | Group users by interests (paginated) |

### Posts & User Posts Lookup

| Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/posts` | Public | List public posts (paginated) |
| `POST` | `/posts` | `auth:api` | Create a public post |
| `GET` | `/posts/{id}` | Public | View a specific post |
| `GET` | `/users/{id}/posts` | Public | Retrieve user and their posts via `$lookup` |

## MongoDB Index Strategy

Indexes are defined explicitly in Laravel MongoDB schema migrations (`database/migrations/`):

1. **`users` collection**:
   - Default `_id_` index (automatic).
   - `users_email_unique` (`{ email: 1 }`, unique): Supports login lookups by email and enforces email uniqueness at the database level during registration and admin user management.
2. **`notes` collection**:
   - Default `_id_` index (automatic).
   - `notes_user_created_id_idx` (`{ user_id: 1, created_at: -1, _id: -1 }`): Supports `GET /api/v1/notes` by filtering on `user_id` and satisfying the chronological `ORDER BY created_at DESC, _id DESC` sort directly from the index (`IXSCAN`) without an in-memory sort.
3. **`posts` collection**:
   - Default `_id_` index (automatic).
   - `posts_user_created_id_idx` (`{ user_id: 1, created_at: -1, _id: -1 }`): Supports the `$lookup` join on `posts.user_id` and chronological sorting of a user's posts in `GET /api/v1/users/{id}/posts`.

**Why additional indexes were not added**: No indexes were added on `role`, `title`, `content`, `interests`, or standalone timestamps because no required query filters or sorts on those fields independently. Keeping the index footprint minimal avoids unnecessary write amplification and memory overhead.

## Aggregation Pipelines

1. **Group Users by Interests (`UserRepository::groupUsersByInterests`)**:
   - Uses a single native `aggregate()` call on `users`: `$unwind` on `$interests` -> `$group` by interest with `$sum` and safe user projection (excluding `password`) -> `$project` -> `$sort` (`user_count DESC, interest ASC`) -> `$facet` for paginated `metadata` and `data`.
2. **User Posts with `$lookup` (`PostRepository::getUserPostsWithLookup`)**:
   - Uses a single native `aggregate()` call on `users`: `$match` on `_id` (`BSON\ObjectId`) -> `$lookup` into `posts` matching `localField: _id` to `foreignField: user_id` (using `posts_user_created_id_idx`) with an inner `$sort` and `$facet` for paginated posts -> `$project` returning safe user details, posts, and total count (gracefully handling users with zero posts).

## Testing

Tests run against a dedicated `secure_notes_test` MongoDB database configured in `phpunit.xml` (never SQLite or the development database):

```bash
php artisan test --compact
```

Test suites cover:
- `tests/Feature/AuthTest.php`: Registration, role escalation prevention, duplicate email, login, wrong password, rate limiting, missing/invalid/expired JWT, and logout invalidation.
- `tests/Feature/NoteTest.php`: Owner CRUD, pagination, cross-user read/update/delete prevention (`403`), and ObjectId validation (`422`).
- `tests/Feature/AdminTest.php`: Role restrictions, user CRUD, last-admin protection, admin note viewing, and `app:create-admin`.
- `tests/Feature/PostAndAggregationTest.php`: Public posts, interest-group aggregation (multi-interest users, pagination, no password exposure), and `$lookup` user posts aggregation (including empty posts and missing users).
- `tests/Feature/IndexVerificationTest.php`: `listIndexes` verification and `explain()` plan assertions confirming `IXSCAN` usage.

## Assessment Deviations

- **Laravel + `mongodb/laravel-mongodb` instead of Node.js/Mongoose**: The original interview prompt references Mongoose and `schema.index()`. Because this backend is implemented in Laravel (PHP), indexes are defined using `MongoDB\Laravel\Schema\Blueprint` in Laravel migrations (`database/migrations/`) rather than Mongoose schemas.
- **Backend-only scope**: Per the master prompt, only the Laravel REST API backend was implemented; the Next.js frontend is intentionally omitted.
