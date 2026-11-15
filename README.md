# Secure Notes — Laravel & MongoDB Backend

REST API and Blade Administration Portal for the **Secure Notes** application, built with **Laravel 13**, **PHP 8.4**, **MongoDB (`mongodb/laravel-mongodb`)**, and **JWT Authentication (`php-open-source-saver/jwt-auth`)**.

---

## 1. Prerequisites

- **PHP 8.4+** with the `mongodb` PHP extension installed and enabled (`php -m | grep mongodb`)
- **Composer 2.x**
- **MongoDB 7.x+** running locally (default: `mongodb://127.0.0.1:27017`)
- **Node.js 20+ & npm** (for Vite/Tailwind assets in the Blade Admin Portal)

---

## 2. Local Installation & Setup

1. **Navigate to the backend directory:**
   ```bash
   cd secure-notes-backend
   ```

2. **Install PHP and frontend dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Create and configure `.env`:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

   Update your `.env` with the MongoDB connection settings:
   ```dotenv
   APP_NAME="Secure Notes"
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mongodb
   MONGODB_URI=mongodb://127.0.0.1:27017
   MONGODB_DATABASE=secure_notes

   # Use file/cookie or mongodb-compatible session/cache drivers for local development
   SESSION_DRIVER=file
   CACHE_STORE=file
   ```

4. **Run database migrations (creates MongoDB collections & `schema.index` indexes):**
   ```bash
   php artisan migrate
   ```

5. **Seed roles, default interests, and demo accounts:**
   ```bash
   php artisan db:seed
   ```

6. **Build admin portal assets and start the server:**
   ```bash
   npm run build
   php artisan serve --host=127.0.0.1 --port=8000
   ```

   - **REST API Base URL:** `http://127.0.0.1:8000/api/v1`
   - **Blade Admin Portal:** `http://127.0.0.1:8000/admin/login`

---

## 3. Seeded Credentials

Running `php artisan db:seed` executes `DatabaseSeeder` and `RolePermissionSeeder`, creating:

| Role | Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@test.com` | `Hello@123!` | Blade Admin Portal (`/admin/login`) + REST API |
| **User** | `user@test.com` | `Hello@123!` | REST API / Next.js User Workspace |

Default seeded interests: `chess`, `reading`, `coding`, `photography`, `music`.

---

## 4. Architecture & Key Directories

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/          # JWT-authenticated & public REST API controllers
│   │   └── Admin/           # Session-authenticated Blade Admin Portal controllers
│   ├── Requests/            # FormRequest validation classes
│   └── Resources/           # Eloquent API Resources
├── Models/                  # MongoDB models (User, Note, Post, Interest, Role, Permission)
├── Policies/                # NotePolicy, PostPolicy (ownership + admin authorization)
├── Repositories/            # MongoDB queries & aggregation pipelines
└── Services/                # Business logic (AuthService, NoteService, PostService, UserService, AggregationService)
database/
├── migrations/              # Collection & schema.index() definitions
└── seeders/                 # RolePermissionSeeder & DatabaseSeeder
```

---

## 5. Requirements Met

- **JWT Authentication (`/api/v1/auth/*`)**:
  - `POST /auth/register`, `POST /auth/login` (rate-limited), `GET /auth/me`, `PATCH /auth/me`, `PATCH /auth/password`, `POST /auth/refresh`, `POST /auth/logout`.
  - Role escalation is blocked on registration and profile updates (`role` is always `user` on signup and ignored on profile update).
- **Role-Based Access Control (RBAC)**:
  - `user` and `admin` roles managed via `Role` / `Permission` models and `EnsureRole` middleware.
  - Regular users can only read/update/delete their own private notes (`NotePolicy`).
  - Administrators have full access in the Blade Admin Portal (`/admin/*`) to manage users, all notes, all posts, and interests.
- **Notes & Public Posts CRUD + Rich Text**:
  - Private Notes (`GET/POST /api/v1/notes`, `GET/PATCH/DELETE /api/v1/notes/{id}`).
  - Public Posts (`GET/POST /api/v1/posts`, `GET/PATCH /api/v1/posts/{id}`).
  - Sanitized HTML storage via `HtmlSanitizer` for rich-text content across both API and Blade Admin Portal (TinyMCE/Quill-compatible rich editor).
- **Soft Deletes & Trash Restore**:
  - `SoftDeletes` enabled on `User`, `Note`, `Post`, and `Interest`, with Trash view (`?trashed=1`) and Restore actions in the Blade Admin Portal.
- **Interests CRUD & User Selection**:
  - Admin CRUD for interests at `/admin/interests/manage` (`ManageInterestController`).
  - Public endpoint `GET /api/v1/interests` consumed by the Next.js frontend during signup and profile updates.
- **MongoDB Indexing (`schema.index`)**:
  - Explicit indexes defined in `database/migrations/*` using `$collection->index(...)` and `$collection->unique(...)` for users, notes, posts, interests, roles, and permissions to support all list, read, and aggregation queries.
- **MongoDB Aggregation Pipelines**:
  - **Scenario 1 — Group by Interests**: `UserRepository::groupUsersByInterests()` uses a single `collection->aggregate()` pipeline (`$match`, `$unwind`, `$group`, `$sort`, `$facet`) exposed via `GET /api/v1/admin/aggregations/interests` and `/admin/interests`.
  - **Scenario 2 — User Posts (`$lookup`)**: `PostRepository::getUserPostsWithLookup()` uses a single `collection->aggregate()` pipeline with `$lookup` exposed via `GET /api/v1/users/{id}/posts` and `/admin/users/{user}/posts`.
- **Pagination**:
  - All list endpoints and admin tables are paginated (`per_page` clamped between `1` and `100`).

---

## 6. Testing & Code Style

```bash
# Run PHPUnit feature & unit tests
php artisan test --compact

# Format code with Laravel Pint
vendor/bin/pint --dirty --format agent
```
