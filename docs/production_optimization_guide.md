# Laravel Production Optimization & Performance Runbook

This guide details the exact steps and configurations to maximize performance, minimize TTFB (Time to First Byte), and prevent latency bottlenecks in production.

---

## 1. Automated Production Caching

During deployment, compile configuration, routes, events, and Blade templates into fast cached files so Laravel does not scan the filesystem on every incoming request.

```bash
# Clear any stale development caches
php artisan optimize:clear

# 1. Compile all config files into a single cached PHP array
php artisan config:cache

# 2. Compile all routes into a fast serialized lookup table
php artisan route:cache

# 3. Pre-compile all Blade templates to native PHP
php artisan view:cache

# 4. Cache event to listener mappings
php artisan event:cache

# (Alternative in Laravel 10+): Run all caching steps in one command:
php artisan optimize
```

> **Important:** Never use `env()` inside controllers, services, or Blade templates once `config:cache` is enabled. Always use `config('services.name')`.

---

## 2. Composer Autoloader Optimization

By default, Composer checks the filesystem for classes using PSR-4 paths. In production, build an authoritative classmap:

```bash
composer install --prefer-dist --no-dev -o --classmap-authoritative
```

* `-o` (`--optimize-autoloader`): Generates a classmap for PSR-0/4 rules.
* `--classmap-authoritative`: Prevents Composer from scanning the disk if a class is not found in the classmap.
* `--no-dev`: Excludes development-only testing and debugging packages.

---

## 3. Recommended PHP OPcache Configuration

Enable and configure PHP OPcache (`/etc/php/8.x/fpm/php.ini` or `/usr/local/etc/php/php.ini`):

```ini
[opcache]
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0    ; 0 in production: never check file change timestamps
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

> **Note:** When `opcache.validate_timestamps=0`, you must restart PHP-FPM or reload the web server during deployment:
> ```bash
> sudo systemctl reload php8.2-fpm
> ```

---

## 4. Background Queue Worker Setup

Never execute heavy jobs, emails, or third-party webhooks synchronously on the HTTP request thread.

### Step 1: Run Database Queue Migrations
```bash
php artisan migrate
```

### Step 2: Configure `.env`
```dotenv
QUEUE_CONNECTION=database
# Or for high concurrency:
# QUEUE_CONNECTION=redis
```

### Step 3: Supervisor Worker Configuration
Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/xylo/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/xylo/storage/logs/worker.log
stopwaitsecs=3600
```

Update Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 5. Summary of Application Optimizations Applied

| Bottleneck | Problem Identified | Solution Implemented |
| :--- | :--- | :--- |
| **N+1 Lazy Loading** | `CategoryController` loaded products without `thumbnail`, firing 1 query per product in card view. | Eager-loaded `thumbnail` and removed full `reviews` collection hydration; enabled `Model::preventLazyLoading` in `AppServiceProvider`. |
| **Attribute Loop Query** | `CartController::addToCart` queried `AttributeValue` inside a `foreach` loop. | Converted to single `whereIn('id', ...)` batch query with eager-loaded `attribute`. |
| **Search Rating N+1** | `SearchController::searchResults` lacked `withAvg('reviews', 'rating')`. | Added `withAvg` and properly enclosed search parameters. |
| **Missing DB Indexes** | Missing composite indexes on pivot tables and filters. | Added indexes on `order_details.product_id`, `product_attribute_values`, `recipes`, and `coupons`. |
| **Blocking Mail I/O** | `PasswordResetController` sent OTP email synchronously using `Mail::raw()`. | Converted to queued `PasswordResetOtpMail` implementing `ShouldQueue`. |
| **Uncached Queries** | `RecipeController` ran a `DISTINCT` query for cuisines on every index load. | Cached with `Cache::remember('recipe_distinct_cuisines', 3600)` with invalidation via `RecipeObserver`. |
