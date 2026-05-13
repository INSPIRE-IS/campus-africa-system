# AGENTS.md

## Project overview
- Laravel 8 application (PHP ^7.3 or ^8.0)
- Frontend: Laravel Mix + Vue 2 + Bootstrap 4 + jQuery
- Database: MySQL 5.7 (see docker-compose)

## Session Definition
- A "session" means this single Codex chat thread/conversation. If a new chat starts (or context feels incomplete), treat it as a new session.

## Session Checklist (Do This First)
1) Re-open `AGENTS.md` (this file) before answering repo-specific questions.
   - This file should stay mostly stable (project setup + safety rules). Add day-to-day notes to `temp/agent.md` instead.
2) Open `temp/agent.md` for the current "working notes" context (business rules, routes, investigations). If it doesn't exist, create it (see template in `temp/session-template.md` or just create an empty file).
3) Open the latest `temp/session-YYYY-MM-DD.md` for recent context.
4) If today's session file does not exist yet, create it from `temp/session-template.md`.
5) Open/update `temp/session-contents.md` (high-level index of sessions).
6) Check `temp/TODOS.md` for the current action plan and priority items.

## No-Surprises Rules
- Do not auto-commit. Only commit when explicitly asked.
- Do not change production/live behavior unless it is gated/flagged and approved.
- Do not delete/rename large folders or run destructive git commands unless explicitly asked.

## Core principles
- Always think like a senior developer when making technical decisions.
- All BA/design choices should reflect senior‑level UI/UX and senior‑marketer thinking (clarity, conversion, and user trust).
- Default to the best possible UI/UX unless there is a clear constraint.

## Purpose
Document everything for this repo.


## Repo layout
- app/: application code (models, controllers, services, jobs, traits)
- routes/: web and API routes
- resources/: Blade views, JS, and SCSS sources
- public/: built assets and public entrypoint
- database/: migrations, factories, seeders
- tests/: PHPUnit tests

## Setup (local)
- Install PHP deps: `composer install`
- Install JS deps: `npm install` (or `yarn`)
- Update `.env` values for local settings
- Generate app key if needed: `php artisan key:generate`

## Setup (Docker)
- Start: `docker compose up --build`
- App: http://localhost:8000
- MySQL: localhost:33066, db `campus_africa`, user `root`, password `root`

## Common commands
- Dev server: `php artisan serve`
- Migrate: `php artisan migrate`
- Queue worker: `php artisan queue:work`
- Build assets: `npm run dev` / `npm run prod`
- Watch assets: `npm run watch` / `npm run hot`

## Deployment notes (CI/CD -> Forge -> Envoyer)
- Deployment flow: merge to git (CI/CD) -> Laravel Forge server -> Envoyer deploy pipeline.
- Deployment hooks (Envoyer). Always work inside the new release directory:

```bash
cd {{ release }}

npm run production

# Clear ALL cached / compiled Laravel artifacts
php artisan optimize:clear

# Run migrations safely
php artisan migrate --force

# Rebuild optimized caches
php artisan optimize

# Storage symlink (safe to re-run)
php artisan storage:link || true

# Restart queues AFTER everything is ready
php artisan queue:restart
```

