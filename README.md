# Pritask

A lightweight issue tracker built with Laravel. Manage projects, track issues on a kanban board, label work with tags, assign team members, comment on issues, and receive notifications when you are assigned.

## Requirements

| Tool | Version |
|------|---------|
| PHP | 8.2+ (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`) |
| Composer | 2.x |
| Node.js & npm | 18+ |
| Database | MySQL 8+ (recommended) or SQLite |

Install PHP, Composer, and Node using your platform’s package manager (e.g. Homebrew on macOS, [php.net downloads](https://www.windows.php.net/download/) / [nodejs.org](https://nodejs.org/) on Windows, or Laravel Herd/Valet/Laragon if you prefer an all-in-one stack).

---

## Setup — macOS & Linux

### 1. Clone the repository

```bash
git clone https://github.com/SaimirBunjaku/Pritask.git
cd Pritask
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Set `APP_URL` in `.env`:

```env
APP_URL=http://localhost:8000
```

**MySQL (default)** — create a database, then set in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_issue_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```

```sql
CREATE DATABASE mini_issue_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**SQLite (optional)** — quick local setup without MySQL:

```bash
touch database/database.sqlite
```

```env
DB_CONNECTION=sqlite
```

(Remove or comment out `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.)

### 4. Migrate and seed

```bash
php artisan migrate
php artisan db:seed
```

Reset and re-seed anytime:

```bash
php artisan migrate:fresh --seed
```

### 5. Install frontend dependencies

```bash
npm install
```

### 6. Run the app

```bash
composer run dev
```

Open **http://localhost:8000**.

---

## Setup — Windows

Use **PowerShell** or **Windows Terminal**. (Git Bash can use the macOS/Linux commands above instead.)

### 1. Clone the repository

```powershell
git clone https://github.com/SaimirBunjaku/Pritask.git
cd Pritask
```

### 2. Install PHP dependencies

```powershell
composer install
```

### 3. Configure the environment

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Set `APP_URL` in `.env`:

```env
APP_URL=http://localhost:8000
```

**MySQL (default)** — create a database (MySQL Workbench, phpMyAdmin, or CLI), then set in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_issue_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```

```sql
CREATE DATABASE mini_issue_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**SQLite (optional)** — quick local setup without MySQL:

```powershell
New-Item -Path database/database.sqlite -ItemType File -Force
```

```env
DB_CONNECTION=sqlite
```

(Remove or comment out `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.)

### 4. Migrate and seed

```powershell
php artisan migrate
php artisan db:seed
```

Reset and re-seed anytime:

```powershell
php artisan migrate:fresh --seed
```

### 5. Install frontend dependencies

```powershell
npm install
```

### 6. Run the app

```powershell
composer run dev
```

Open **http://localhost:8000**.

---

## Same on all platforms

These commands and URLs are identical on macOS, Linux, and Windows.

### Demo accounts

Password for every demo user: **`password`**

| Email | Role |
|-------|------|
| `pritech@example.com` | Project owner (Website Redesign) |
| `jordan@example.com` | Project owner (Internal Tools Dashboard) |
| `sam@example.com` | Assignable team member |

Register a new account from the login page, or restore demo users without wiping data:

```bash
php artisan db:seed --class=DemoUserSeeder
```

### First-time setup shortcut

```bash
composer run setup
php artisan db:seed
composer run dev
```

> `composer run setup` installs deps, creates `.env`, generates a key, runs migrations, and builds assets — but does **not** seed demo data.

### Run services separately (optional)

Three terminals:

```bash
php artisan serve
php artisan queue:listen
npm run dev
```

Production-style assets (no Vite dev server):

```bash
npm run build
php artisan serve
```

### Tests

```bash
composer test
# or
php artisan test
```

---

## What you can do in the app

- **Projects** — create, edit, and delete projects (owners only)
- **Issues board** — kanban columns (To Do → In Progress → Blocked → QA → Prod)
- **Filters** — filter by project, status, priority, tag, and search (server-side, no full page reload)
- **Drag and drop** — move issues between columns on the board
- **Tags** — create tags and attach/detach them on issues
- **Members** — assign and remove users on issues
- **Comments** — add comments; edit or delete your own
- **Notifications** — bell icon alerts when you are assigned to an issue
- **Auth** — login, register, logout (Laravel Breeze)

## Project structure (high level)

```
app/
  Http/Controllers/   # Projects, issues, tags, comments, notifications
  Models/             # Eloquent models and relationships
  Policies/           # Authorization rules
database/
  migrations/         # Schema
  seeders/            # DemoUserSeeder, DatabaseSeeder
resources/
  views/              # Blade templates
  js/                 # Board, notifications, custom selects (Vite)
  css/                # App styles
routes/
  web.php             # Application routes
  auth.php            # Login / register routes
```

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank or unstyled pages | Ensure Vite is running (`npm run dev`) or run `npm run build` |
| Demo login does not work | Run `php artisan db:seed --class=DemoUserSeeder` |
| `SQLSTATE` / migration errors | Check `.env` database credentials; try `php artisan migrate:fresh --seed` on a local DB |
| Session/cache errors after migrate | Run `php artisan migrate` again (creates `sessions` and `cache` tables) |
| Port 8000 in use | `php artisan serve --port=8080` and set `APP_URL` to match |
| `composer` / `php` not found (Windows) | Add PHP and Composer to your system PATH, or use Laragon/Herd |

## License

MIT — see [opensource.org/licenses/MIT](https://opensource.org/licenses/MIT).
