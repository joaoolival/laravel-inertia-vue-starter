# Laravel + Inertia + Vue Starter

An opinionated Laravel + Inertia + Vue starter kit with strict defaults, full TypeScript generation, and comprehensive code quality tooling.

## Stack

- **PHP 8.4** / **Laravel 13** / **Inertia v2** / **Vue 3** / **Tailwind CSS v4**
- **PostgreSQL 18** via Docker (Laravel Sail)
- **Redis** for caching and sessions
- **Meilisearch** for search
- **Mailpit** for local email testing
- **Wayfinder (dev-next)** for full TypeScript generation (routes, actions, models, enums, Inertia props)

## Features

- **Type-safe full stack** — Strict PHP 8.4 typing + strict TypeScript with no `any` types
- **Wayfinder code generation** — Routes, controller actions, models, and enums as TypeScript imports
- **Authentication** — Laravel Fortify with login, registration, email verification, password reset, and two-factor authentication (2FA)
- **Server-side rendering** — SSR support with Inertia
- **UI components** — Reka UI (shadcn-vue inspired) + Lucide icons + dark/light mode
- **Code quality automation** — Rector, Pint, PHPStan (level 6), ESLint, Prettier, vue-tsc
- **Testing** — Pest framework with feature and unit tests
- **Actions pattern** — Business logic in dedicated Action classes with single `handle()` methods
- **Settings pages** — User profile, account management, appearance, and 2FA setup

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

## Getting Started

```bash
# Clone the repository
git clone <repo-url> && cd laravel-inertia-vue-starter

# Copy environment file
cp .env.example .env

# Start Docker containers
./vendor/bin/sail up -d

# Install dependencies and set up the project
sail composer setup
```

This runs composer/npm install, generates an app key, runs migrations, and builds frontend assets.

Once setup is complete, the application is available at [http://localhost](http://localhost).

## Development

```bash
# Start the full dev environment (server, queue, logs, Vite)
sail composer dev

# Start with server-side rendering
sail composer dev:ssr
```

## Commands

All commands should be run through Sail to ensure correct platform bindings and database access.

| Command | Description |
|---------|-------------|
| `sail up -d` | Start Docker containers |
| `sail composer dev` | Start full dev environment |
| `sail composer lint` | Auto-fix PHP and JS/TS issues |
| `sail composer test` | Run full test suite (unit + lint + types) |
| `sail npm run dev` | Regenerate Wayfinder types + start Vite |
| `sail npm run build` | Build frontend assets |

### Individual test commands

| Command | Description |
|---------|-------------|
| `sail composer test:unit` | Run Pest tests |
| `sail composer test:lint` | Check linting and formatting |
| `sail composer test:types` | Run PHPStan static analysis |
| `sail artisan test --filter=testName` | Run a specific test |

## Project Structure

```
app/
├── Actions/            # Business logic (single handle() method per class)
├── Concerns/           # Shared traits
├── Http/Controllers/   # Inertia controllers
├── Models/             # Eloquent models
└── Providers/          # Service providers

resources/js/
├── pages/              # Vue page components (Inertia)
├── components/         # Reusable Vue components
│   └── ui/             # Reka UI base components
├── layouts/            # Page layouts (app, auth, settings)
├── composables/        # Vue composables
├── types/              # Global TypeScript types
└── wayfinder/          # Generated types (gitignored)

tests/
├── Feature/            # Feature tests
└── Unit/               # Unit tests
```

## Code Quality

The project enforces strict code quality through automated tooling:

- **[Rector](https://getrector.com/)** — Automated PHP refactoring (dead code removal, type declarations, early returns)
- **[Pint](https://laravel.com/docs/pint)** — PHP code formatting with strict rules
- **[PHPStan](https://phpstan.org/)** (via Larastan) — Static analysis at level 6
- **[ESLint](https://eslint.org/)** — Vue/JS/TS linting with import ordering
- **[Prettier](https://prettier.io/)** — Code formatting with Tailwind CSS plugin
- **[vue-tsc](https://github.com/vuejs/language-tools)** — TypeScript type checking for Vue components

Run all checks at once:

```bash
sail composer test
```

## Wayfinder

[Wayfinder](https://github.com/laravel/wayfinder) generates TypeScript types from your Laravel application. All generated types live under `resources/js/wayfinder/` (gitignored) and are regenerated automatically when Vite is running.

```ts
// Import routes
import { dashboard } from '@/wayfinder/routes'

// Import controller actions
import { show } from '@/wayfinder/App/Http/Controllers/PostController'

// Import types (models, enums, Inertia page props)
import { App } from '@/wayfinder/types'

// Use with Inertia forms
const form = useForm({ name: '' })
form.submit(store())
```

If Vite isn't running, regenerate manually:

```bash
sail artisan wayfinder:generate
```

## Docker Services

| Service | Port | Description |
|---------|------|-------------|
| laravel.test | 80 | Application |
| pgsql | 5432 | PostgreSQL 18 |
| redis | 6379 | Redis |
| meilisearch | 7700 | Meilisearch |
| mailpit | 8025 | Email dashboard |

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
