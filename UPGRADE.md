# Laravel 10 Upgrade Notes (Step 1 + Step 3)
Date: 2026-03-11
Branch: upgrade

This document captures Step 1 (dependency audit) and Step 3 (code scan) only. Step 2 (attempted upgrade) is intentionally deferred.

## Step 1 - Dependency / Platform audit (what will NOT work as-is)

### Hard blockers from `composer why-not laravel/framework ^10`
- Root requirement: `laravel/framework` is pinned to `^8.54` in `composer.json`.
- `barryvdh/laravel-dompdf` v0.9.0 -> `illuminate/support` `^5.5|^6|^7|^8`
- `facade/ignition` v2.17.7 -> `illuminate/support` `^7|^8` (Laravel 10 uses `spatie/laravel-ignition`)
- `facade/flare-client-php` v1.10.0 -> `illuminate/pipeline` `^5.5|^6.0|^7.0|^8.0`
- `fruitcake/laravel-cors` v2.2.0 -> `illuminate/contracts`/`illuminate/support` `^6|^7|^8|^9`
- `juanparati/sendinblue` 8.1 -> `illuminate/mail` `^8`
- `laravel/sanctum` v2.15.1 -> `illuminate/*` `^6.9|^7.0|^8.0|^9.0`
- `laravel/ui` v3.4.6 -> `illuminate/*` `^8.42|^9.0`

### Transitive upgrades required by Laravel 10 (per composer)
- `league/flysystem` 1.1.10 -> needs `^3.8`
- `monolog/monolog` 2.10.0 -> needs `^3.0`
- `symfony/*` 5.4.* -> needs `^6.2` (console, error-handler, finder, http-foundation, http-kernel, mime, process, routing, var-dumper)
- `egulias/email-validator` 2.1.25 -> needs `^3.2.1|^4.0`
- `voku/portable-ascii` 1.6.1 -> needs `^2.0`
- `brick/math` 0.13.1 -> Laravel 10.0.0 expects `^0.9.3|^0.10.2|^0.11` (exact constraint may differ by target 10.x)

### PHP platform
- Laravel 10 requires PHP >= 8.1. Update root constraint from `^7.3|^8.0` to `^8.1`.
- `composer why-not php ^8.1` reports `lcobucci/clock` requiring `~8.2.0`, so standardizing on PHP 8.2 is safest.

## 8 -> 9 assessment (Step 1)

### Hard blockers from `composer why-not laravel/framework ^9`
- Root requirement: `laravel/framework` is pinned to `^8.54` in `composer.json`.
- `barryvdh/laravel-dompdf` v0.9.0 -> `illuminate/support` `^5.5|^6|^7|^8`
- `facade/ignition` v2.17.7 -> `illuminate/support` `^7|^8`
- `facade/flare-client-php` v1.10.0 -> `illuminate/pipeline` `^5.5|^6.0|^7.0|^8.0`
- `juanparati/sendinblue` 8.1 -> `illuminate/mail` `^8`

### Transitive upgrades required by Laravel 9 (per composer)
- `league/flysystem` 1.1.10 -> needs `^3.0`
- `symfony/*` 5.4.* -> needs `^6.0` (console, error-handler, finder, http-foundation, http-kernel, mime, process, routing, var-dumper)
- `egulias/email-validator` 2.1.25 -> needs `^3.1`
- `voku/portable-ascii` 1.6.1 -> needs `^2.0`

### Notes specific to 8 -> 9
- `fruitcake/laravel-cors` supports Laravel 9, so it is NOT a blocker for 9, but will be for 10.
- `laravel/sanctum` v2 supports up to Laravel 9; upgrade not required yet.
- `laravel/ui` supports Laravel 9; upgrade not required yet (but remains a long‑term blocker for 10).

## 9 -> 10 assessment (Step 1)

### Additional blockers when moving from 9 to 10
- `fruitcake/laravel-cors` v2.2.0 (supports up to 9; must switch to framework CORS)
- `laravel/sanctum` v2.15.1 (supports up to 9; move to sanctum v3+)
- `laravel/ui` v3.4.6 (supports up to 9; replace with Breeze/Jetstream or custom auth)

### Additional transitive upgrades for 10 (beyond 9)
- `symfony/*` must be `^6.2` (vs `^6.0` for 9)
- `monolog/monolog` must be `^3.0`
- `brick/math` constraint differs for 10.x (composer will resolve compatible versions)

## Step 3 - Code scan for likely breaks / adjustments

### Confirmed touch points
- Auth scaffolding: `routes/web.php:99` uses `Auth::routes()` (laravel/ui). This ties us to `laravel/ui`, which currently blocks Laravel 10. Plan: upgrade `laravel/ui` (if supported) or swap to Breeze/Jetstream and rewire auth routes/controllers.
- CORS middleware: `app/Http/Kernel.php:19` uses `\Fruitcake\Cors\HandleCors::class`. Laravel 10 uses the built-in `\Illuminate\Http\Middleware\HandleCors::class` and the fruitcake package should be removed.
- Route namespace: `app/Providers/RouteServiceProvider.php` still calls `->namespace($this->namespace)` but controllers are already referenced via FQCN arrays in `routes/web.php` and `routes/api.php`. No change required unless string-based routes are reintroduced.

### No hits found (good news)
- No `Controller@method` string routes.
- No `dispatchNow` / `dispatch_now`.
- No deprecated string/array helpers (`str_*`, `array_*`) in app code.

## Deferred / Out of scope for this pass
- UI stack modernization (React/Tailwind/Inertia) is intentionally deferred.
- Pulse (and its Livewire dashboard) is a Laravel 10+ concern; evaluate after core upgrade feasibility is clearer.

## Risk / time comparison: direct 8 -> 10 vs staged 8 -> 9 -> 10

### Risk (relative)
- Direct 8 -> 10: higher risk. You must upgrade all blockers plus PHP 8.1+ and the heavier transitive changes (Flysystem 3, Symfony 6.2, Monolog 3) in one step.
- Staged 8 -> 9 -> 10: lower risk. You resolve the biggest package blockers first and validate at 9 before taking the final step to 10.

### Time (relative)
- Direct 8 -> 10: fewer upgrade steps, but higher chance of longer debugging cycles if multiple breaking changes collide.
- Staged 8 -> 9 -> 10: typically ~30–60% more upgrade effort due to two rounds of dependency updates and validation, but faster issue isolation and lower regression risk.
