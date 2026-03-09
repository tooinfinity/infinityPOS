# infinityPOS - Project Context

## Project Overview

infinityPOS is a **Point of Sale and Inventory Management System** built with modern Laravel and React technologies. It provides comprehensive business management features including sales, purchases, inventory tracking, stock transfers, customer/supplier management, and financial reporting.

### Technology Stack

**Backend:**
- Laravel 12 (PHP 8.4+)
- Inertia.js v2 for server-driven SPA architecture
- Laravel Fortify for authentication
- Spatie packages (laravel-data, laravel-medialibrary, laravel-permission)
- Pest v4 for testing

**Frontend:**
- React 19 with TypeScript
- Inertia.js v2 for seamless Laravel-React integration
- Tailwind CSS v4 for styling
- Radix UI components (shadcn/ui style)
- Vite 7 for bundling

**Database:**
- SQLite (development) / Configurable for production
- Eloquent ORM with strict typing

## Building and Running

### Initial Setup

```bash
# Install dependencies and setup
composer setup

# Or manually:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
bun install
bun run build
```

### Development Server

```bash
# Start all development services (server, queue, logs, vite)
composer run dev

# Alternative with Bun:
bun run dev
```

### Build Commands

```bash
# Production build
bun run build

# SSR build
bun run build:ssr
```

### Testing

```bash
# Run all tests (type coverage, unit, lint, types)
composer test

# Individual test suites
composer run test:unit        # Pest tests with coverage
composer run test:types       # PHPStan + TypeScript
composer run test:lint        # Pint + Rector + ESLint
composer run test:type-coverage  # Pest type coverage (100% required)
```

### Code Quality

```bash
# Format and lint code
composer run lint

# PHP formatting
vendor/bin/pint

# TypeScript linting
bun run lint
```

## Development Conventions

### PHP/Laravel

- **Strict typing**: All files use `declare(strict_types=1)`
- **Final classes**: All classes are `final` by default (Pint rule)
- **Constructor property promotion**: Use PHP 8+ syntax
- **Return types**: All methods must have explicit return types
- **No else statements**: Prefer early returns (happy path last)
- **Curly braces**: Always use for control structures
- **Action pattern**: Business logic lives in `app/Actions` classes with single `handle()` method
- **Form Requests**: Validation in dedicated Form Request classes
- **Model casts**: Use `casts()` method, not `$casts` property

### Frontend (React/TypeScript)

- **TypeScript**: Strict mode enabled, noEmit
- **Component structure**: Functional components with proper typing
- **Path aliases**: `@/` maps to `resources/js/`
- **UI components**: Radix UI primitives with Tailwind CSS v4
- **Inertia**: Use Wayfinder-generated route functions from `@/actions/` or `@/routes/`

### Testing (Pest)

- **100% type coverage** required
- **100% code coverage** required for unit tests
- Use `RefreshDatabase` trait for database tests
- Fake time, HTTP requests, and processes in tests
- Follow arrange-act-assert pattern

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `UserController`, `OrderStatus` |
| Methods/Variables | camelCase | `getUserName`, `$firstName` |
| Routes (URL) | kebab-case | `/open-source`, `/user-profile` |
| Routes (name) | camelCase | `->name('userProfile')` |
| Config files | kebab-case | `pdf-generator.php` |
| Config keys | snake_case | `chrome_path` |
| Artisan commands | kebab-case | `delete-old-records` |
| Enum values | PascalCase | `Pending`, `Completed` |

### Directory Structure

```
app/
├── Actions/          # Business logic (Action pattern)
├── Data/            # Spatie data transfer objects
├── Enums/           # Type-safe enumerations
├── Exceptions/      # Custom exception classes
├── Http/
│   ├── Controllers/ # Request handlers
│   ├── Middleware/  # HTTP middleware
│   └── Requests/    # Form request validation
├── Models/          # Eloquent models
├── Providers/       # Service providers
├── Rules/           # Custom validation rules
└── Services/        # Service classes

resources/js/
├── components/      # React components
├── hooks/           # Custom React hooks
├── layouts/         # Page layouts
├── lib/             # Utilities
├── pages/           # Inertia page components
└── types/           # TypeScript type definitions
```

### Key Architectural Patterns

1. **Action Pattern**: Business logic encapsulated in single-purpose action classes
   - Located in `app/Actions/`
   - Single `handle()` method
   - Dependencies injected via constructor
   - Create with: `php artisan make:action "{name}" --no-interaction`

2. **Inertia.js SPA**: Server-rendered React components
   - Pages in `resources/js/pages/`
   - Use `Inertia::render()` in controllers
   - Wayfinder for type-safe route functions

3. **Media Library**: Spatie media library for file attachments
   - Products: thumbnails
   - Brands: logos
   - Purchases: attachments

4. **Inventory Management**:
   - Batch-based stock tracking
   - Stock movements and transfers
   - Low stock alerts
   - FIFO/LIFO support via batches

### Important Notes

- **Frontend changes not showing?** Run `bun run build` or `bun run dev`
- **Vite manifest errors?** Run `bun run build`
- **Always run tests** before committing changes
- **Use Artisan make commands** for generating files
- **Search Laravel docs** using Boost's `search-docs` tool for version-specific guidance
