# Project Guidelines

## Stack

- **PHP 8.4** / **Laravel 13** / **Inertia v2** / **Vue 3** / **Tailwind CSS v4**
- **Database**: PostgreSQL (via Laravel Sail/Docker)
- **Package manager**: npm (not bun/pnpm)
- **Dev environment**: Laravel Sail (`sail up -d`)
- **Wayfinder**: dev-next branch (generates full TypeScript: routes, actions, models, enums, Inertia props, broadcast channels, env vars)

## Commands

All commands should be run through Sail to ensure correct platform bindings and database access.

- `sail up -d` — Start Docker containers
- `sail composer dev` — Start full dev environment (server, queue, logs, vite)
- `sail composer test` — Run full test suite (unit + lint + types)
- `sail composer lint` — Auto-fix: rector + pint + eslint
- `sail npm install` — Install npm dependencies (run once after cloning, or after package.json changes)
- `sail npm run dev` — Regenerate Wayfinder types + start Vite dev server
- `sail npm run build` — Build frontend assets (required before running tests)
- `composer update:requirements` — Bump composer.json + package.json versions (run manually)

**Important**: Always use `sail npm` instead of bare `npm` — native bindings (ESLint resolvers, etc.) are platform-specific and must match the container's Linux architecture.

## Architecture

### Actions Pattern

- Business logic lives in `app/Actions` as dedicated Action classes.
- Actions have a single `handle()` method and no suffix in the name.
- Create with `php artisan make:action "{name}" --no-interaction`.
- Inject dependencies via constructor using private properties.
- Wrap complex operations in `DB::transaction()` when multiple models are involved.
- Actions are called from jobs, commands, HTTP requests, API requests, MCP requests, etc.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class CreateFavorite
{
    public function __construct(private FavoriteService $favorites)
    {
        //
    }

    public function handle(User $user, string $favorite): bool
    {
        return $this->favorites->add($user, $favorite);
    }
}
```

### Controllers & Validation

- Always create Form Request classes for validation — no inline validation in controllers.
- Use `Inertia::render()` for server-side routing, not Blade views.
- Use `php artisan make:` commands for new files. Pass `--no-interaction` to all artisan commands.

### Models & Database

- Use Eloquent relationships with return type hints. Prefer `Model::query()` over `DB::`.
- Prevent N+1 queries — use eager loading (`$with` or `::with()`).
- When creating models, also create factories and seeders.
- Casts go in a `casts()` method, not the `$casts` property.

## PHP Style

- `declare(strict_types=1)` in all PHP files.
- `final` classes by default. `final readonly` for Actions and value objects.
- Strict comparisons (`===`), no loose checks.
- Use PHP 8 constructor property promotion.
- Always use explicit return type declarations and type hints.
- Always use curly braces for control structures, even single-line.
- No superfluous annotations — only `@` annotations for typing variables.
- PHPDoc blocks over inline comments. No comments within code unless genuinely complex.
- Enum keys in TitleCase (e.g., `FavoritePerson`, `Monthly`).
- Use descriptive names: `isRegisteredForDiscounts`, not `discount()`.

## Frontend Style

- Vue components in `resources/js/pages/` for pages, `resources/js/components/` for reusable components.
- TypeScript everywhere — no `any` types.
- Import Wayfinder from `@/wayfinder/` paths (not `@/routes/` or `@/actions/`).
- Prefer named imports for tree-shaking: `import { show } from '@/wayfinder/App/Http/Controllers/PostController'`.
- Use `useForm` from Inertia with Wayfinder: `form.submit(store())`.
- ESLint + Prettier + vue-tsc for linting/formatting/type checking.

## Wayfinder (dev-next)

- All generated types live under `resources/js/wayfinder/` (gitignored).
- Routes: `import { dashboard } from '@/wayfinder/routes'`
- Actions: `import { show } from '@/wayfinder/App/Http/Controllers/PostController'`
- Named routes: `import { edit } from '@/wayfinder/routes/profile'`
- Form variants: `<form v-bind="store.form()">`
- Types: `import { App } from '@/wayfinder/types'` for model types, enums, Inertia page props.
- Run `php artisan wayfinder:generate` after route/controller changes if Vite isn't running.

## Code Quality Tools

- **Pint**: Code formatter with strict rules (strict_types, final_class, strict_comparison, ordered class elements). Config in `pint.json`.
- **Rector**: Automated refactoring (dead code, type declarations, early returns, code quality). Config in `rector.php`.
- **Larastan/PHPStan**: Static analysis at level 6. Config in `phpstan.neon`.
- **ESLint**: Vue/JS/TS linting with import ordering.
- **Prettier**: Code formatting with Tailwind CSS plugin.
- **vue-tsc**: TypeScript type checking for Vue.

## Testing

- Tests use **Pest** (not PHPUnit). Run through Sail: `sail composer test`.
- Tests require PostgreSQL (Docker via Sail) and built Vite assets (`npm run build`).
- Every change must be tested. Write or update a test, then run it.
- Create tests with `php artisan make:test --pest {name}` (feature) or `php artisan make:test --pest --unit {name}` (unit).
- Use factories for model creation in tests.
- Run minimal tests with filter: `sail artisan test --compact --filter=testName`.
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`.
- Use Pest syntax: `test('name', fn () => ...)` or `it('does something', function () { ... })`.
- Use `expect()` API for assertions where appropriate.
- Use datasets for tests with duplicated data (e.g., validation rules).

## Environment & Config

- Never use `env()` outside config files. Use `config('app.name')`.
- Use environment variables only in configuration files.
- `.env` is gitignored. `.env.example` is committed.

## Before Finalizing Changes

1. Run `composer lint` to auto-fix PHP and JS issues.
2. Run `npm run build` to rebuild frontend assets.
3. Run `sail composer test` to verify everything passes.

## Do Not

- Do not change dependencies without approval.
- Do not create new base folders without approval.
- Do not remove tests without approval.
- Do not create documentation files unless explicitly requested.
- Do not use raw DB queries — use Eloquent.
- Do not use inline validation — use Form Requests.
