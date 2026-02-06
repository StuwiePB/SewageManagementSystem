# Setup after pulling from Git

Run these in **PowerShell** or **Command Prompt** from the project folder (`SewageManagementSystem`).

## 1. Install PHP dependencies

```powershell
composer install
```

*(Requires [PHP](https://windows.php.net/) and [Composer](https://getcomposer.org/) installed.)*

## 2. Environment file

If you don't have a `.env` file (it's in `.gitignore`, so it won't be in the repo):

```powershell
copy .env.example .env
```

Then generate the application key:

```powershell
php artisan key:generate
```

## 3. Database (SQLite)

The project uses SQLite. In `.env`, ensure you have:

```
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite  (uncomment if needed)
```

Create the database file:

```powershell
# Create the file (empty is fine; migrations will set it up)
New-Item -ItemType File -Path database\database.sqlite -Force
```

Or on Command Prompt:

```cmd
type nul > database\database.sqlite
```

Then run migrations:

```powershell
php artisan migrate
```

## 4. (Optional) Frontend assets

If you use npm for CSS/JS:

```powershell
npm install
npm run build
```

## 5. Run the app

**Option A – Laravel Herd**  
If you use [Laravel Herd](https://herd.laravel.com/), the site may already be available at something like:

`http://sewagemanagementsystem.test`

**Option B – Built-in server**

```powershell
php artisan serve
```

Then open: **http://127.0.0.1:8000**

Workers page: **http://127.0.0.1:8000/operations/workers**

---

## Quick checklist

| Step              | Command                    |
|-------------------|----------------------------|
| Dependencies      | `composer install`         |
| Copy env          | `copy .env.example .env`   |
| App key           | `php artisan key:generate` |
| Create SQLite DB  | `New-Item -ItemType File -Path database\database.sqlite -Force` |
| Run migrations    | `php artisan migrate`      |
| Start server      | `php artisan serve`        |

If something fails, note the exact error message (e.g. "PHP not found", "SQLite driver not loaded") and fix that step first.
