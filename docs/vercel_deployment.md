# Deploying Laravel API to Vercel

This document outlines the step-by-step process for deploying this Laravel application to [Vercel](https://vercel.com).

---

## 1. Prerequisites

- A [Vercel Account](https://vercel.com/signup)
- A managed cloud database (e.g., PostgreSQL or MySQL hosted on [Supabase](https://supabase.com), [Neon](https://neon.tech), [PlanetScale](https://planetscale.com), [Aiven](https://aiven.io), or AWS RDS)
- Installed Vercel CLI (optional for CLI deployment):
  ```bash
  npm i -g vercel
  ```

---

## 2. Configuration Overview

The codebase includes the following Vercel-ready files:

1. **`api/index.php`**: Serverless function handler. Configures temporary writable storage in `/tmp/storage` for Blade view compilations, sessions, logs, and cache.
2. **`vercel.json`**: Configures Vercel routes, static asset serving (`/build/*`, `/css/*`, `/js/*`, `/images/*`), and routes API requests to `api/index.php`.
3. **`.vercelignore`**: Excludes unnecessary local files (`.env`, `vendor/`, `node_modules/`, etc.) from being uploaded during Vercel builds.

---

## 3. Environment Variables to Set in Vercel

Navigate to **Vercel Dashboard > Project Settings > Environment Variables** and add the following required variables:

| Key | Example / Description |
| :--- | :--- |
| `APP_NAME` | `Laravel eCommerce` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:...` (Generate locally via `php artisan key:generate --show`) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-project-name.vercel.app` |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | `postgresql://neondb_owner:npg_g0RXtUE5wsWm@ep-frosty-glade-b35p57bv-pooler.c-4.ap-southeast-1.aws.neon.tech/api_mobile?sslmode=require` |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_DRIVER` | `array` |
| `LOG_CHANNEL` | `stderr` |

*Note: Alternatively, instead of `DATABASE_URL`, you can set individual parameters:*
- `DB_CONNECTION=pgsql`
- `DB_HOST=ep-frosty-glade-b35p57bv-pooler.c-4.ap-southeast-1.aws.neon.tech`
- `DB_PORT=5432`
- `DB_DATABASE=api_mobile`
- `DB_USERNAME=neondb_owner`
- `DB_PASSWORD=npg_g0RXtUE5wsWm`
- `DB_SSLMODE=require`

---

## 4. Deployment Steps

### Option A: Deployment via Vercel GitHub Integration (Recommended)

1. Push your changes to your Git repository:
   ```bash
   git add .
   git commit -m "Prepare Laravel API for Vercel deployment"
   git push origin main
   ```
2. Open the [Vercel Dashboard](https://vercel.com/new).
3. Import your Git repository.
4. Set the **Framework Preset** to **Other** (leave root directory as `./`).
5. Add your Environment Variables in the project configuration step.
6. Click **Deploy**.

### Option B: Deployment via Vercel CLI

Run the following commands in your project root:

```bash
# Log in to Vercel
vercel login

# Deploy to Preview environment
vercel

# Deploy to Production
vercel --prod
```

---

## 5. Database Migrations

Because serverless environments on Vercel do not allow interactive CLI commands during runtime:

- Run database migrations locally or from CI/CD against your remote production database:
  ```bash
  php artisan migrate --force
  ```
- Alternatively, run migrations using your cloud database dashboard (e.g. Supabase SQL Editor / Neon Console).
