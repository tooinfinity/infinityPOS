# Rovodev Guidelines

This document contains curated guidelines for working with this Laravel application. Follow these guidelines to ensure consistency and quality in your development work.

## Foundational Context

This application is a Laravel application with the following main packages & versions:

- **PHP** - 8.4.15
- **Laravel Framework** - v12
- **Inertia.js** (Laravel & React) - v2
- **React** - v19
- **Tailwind CSS** - v4
- **Pest** - v4
- **Laravel Wayfinder** - v0
- **Laravel Fortify** - v1
- **Laravel Pint** - v1
- **PHPUnit** - v12
- **Rector** - v2
- **Larastan** - v3
- **Laravel Prompts** - v0
- **Laravel MCP** - v0
- **ESLint** - v9
- **Prettier** - v3

## Core Principles

### Follow Existing Conventions
- Always follow existing code conventions used in this application
- When creating or editing a file, check sibling files for the correct structure, approach, and naming
- Use descriptive names for variables and methods (e.g., `isRegisteredForDiscounts`, not `discount()`)
- Check for existing components to reuse before writing a new one

### Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval
- Do not change the application's dependencies without approval

### Be Concise
- Focus on what's important rather than explaining obvious details
- Only create documentation files if explicitly requested by the user

### Verification
- Do not create verification scripts or tinker when tests cover that functionality
- Unit and feature tests are more important than manual verification

## Action Pattern

This application uses the Action pattern extensively for business logic:

- Actions live in `app/Actions`, named based on what they do (no suffix)
- Actions are called from many places: jobs, commands, HTTP requests, API requests, MCP requests
- Create dedicated Action classes with a single `handle()` method
- Inject dependencies via constructor using private properties
- Create new actions with `php artisan make:action "{name}" --no-interaction`
- Wrap complex operations in `DB::transaction()` when multiple models are involved

**Example Action:**
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

## PHP Guidelines

### General
- Always use curly braces for control structures, even for one line
- Don't include superfluous PHP annotations, except `@` for typing variables

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`
- Do not allow empty `__construct()` methods with zero parameters

```php
public function __construct(public GitHub $github) { }
```

### Type Declarations
- Always use explicit return type declarations for methods and functions
- Use appropriate PHP type hints for method parameters

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    // ...
}
```

### Comments & Documentation
- Prefer PHPDoc blocks over comments
- Never use comments within code unless something is _very_ complex
- Add useful array shape type definitions for arrays when appropriate

### Enums
- Keys should be TitleCase (e.g., `FavoritePerson`, `BestLake`, `Monthly`)

## Laravel Best Practices

### Do Things the Laravel Way
- Use `php artisan make:` commands to create new files
- Pass `--no-interaction` to all Artisan commands
- Pass correct `--options` to ensure correct behavior
- For generic PHP classes, use `php artisan make:class`

### Database & Eloquent
- Always use proper Eloquent relationship methods with return type hints
- Prefer relationship methods over raw queries or manual joins
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`
- Generate code that prevents N+1 query problems by using eager loading
- Use Laravel's query builder only for very complex database operations

### Model Creation
- When creating new models, create useful factories and seeders too
- Casts should be set in a `casts()` method on models (follow existing conventions)

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning
- Follow existing API route conventions if they differ

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation
- Include both validation rules and custom error messages
- Check sibling Form Requests to see if the application uses array or string based validation rules

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface

### Authentication & Authorization
- Use Laravel's built-in features (gates, policies, Sanctum, etc.)

### URL Generation
- When generating links, prefer named routes and the `route()` function

### Configuration
- Use environment variables only in configuration files
- Never use `env()` directly outside of config files
- Always use `config('app.name')`, not `env('APP_NAME')`

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/` directory
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files
- `bootstrap/providers.php` contains application specific service providers
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php`
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available

### Database Migrations
- When modifying a column, include all attributes that were previously defined
- Otherwise, they will be dropped and lost
- Laravel 11+ allows limiting eagerly loaded records natively: `$query->latest()->limit(10)`

## Testing with Pest

### Test Enforcement
- **Every change must be programmatically tested**
- Write a new test or update an existing test
- Run the affected tests to ensure they pass
- Run the minimum number of tests needed for speed

### Pest Fundamentals
- All tests must be written using Pest: `php artisan make:test --pest {name}`
- Never remove tests or test files without approval
- Tests should test happy paths, failure paths, and weird paths
- Tests live in `tests/Feature` and `tests/Unit` directories

**Basic Pest Test:**
```php
it('is true', function () {
    expect(true)->toBeTrue();
});
```

### Running Tests
- Run all tests: `php artisan test`
- Run tests in a file: `php artisan test tests/Feature/ExampleTest.php`
- Filter by test name: `php artisan test --filter=testName`
- When tests pass, ask the user if they want to run the entire suite

### Pest Assertions
- Use specific methods like `assertForbidden`, `assertNotFound`
- Don't use generic `assertStatus(403)`

```php
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);
    
    $response->assertSuccessful();
});
```

### Mocking
- Import mock function: `use function Pest\Laravel\mock;`
- Or use `$this->mock()` if existing tests do
- Can create partial mocks using the same methods

### Datasets
- Use datasets to simplify tests with duplicated data
- Especially useful for testing validation rules

```php
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
```

### Test Factories
- When creating models for tests, use the factories
- Check if the factory has custom states before manual setup
- Use `$this->faker->word()` or `fake()->randomDigit()`
- Follow existing conventions for `$this->faker` vs `fake()`

### Pest 4 Browser Testing
- Browser tests live in `tests/Browser/`
- Use Laravel features like `Event::fake()`, `assertAuthenticated()`, model factories
- Use `RefreshDatabase` when needed for clean state
- Interact with the page: click, type, scroll, select, submit, drag-and-drop, touch
- Test on multiple browsers when requested (Chrome, Firefox, Safari)
- Test on different devices and viewports when requested
- Switch color schemes (light/dark mode) when appropriate

**Browser Test Example:**
```php
it('may reset the password', function () {
    Notification::fake();
    
    $this->actingAs(User::factory()->create());
    
    $page = visit('/sign-in');
    
    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')
    
    Notification::assertSent(ResetPassword::class);
});
```

## Inertia.js

### Core Concepts
- Inertia.js components should be placed in `resources/js/Pages` directory
- Use `Inertia::render()` for server-side routing instead of traditional Blade views

```php
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
```

### Inertia v2 Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props, add a nice empty state with pulsing/animated skeleton

### Inertia Forms
- Recommended way: use `<Form>` component
- Alternative: use `useForm` helper for more programmatic control
- Available options: `resetOnError`, `resetOnSuccess`, `setDefaultsOnSuccess`

### Inertia + React
- Use `router.visit()` or `<Link>` for navigation instead of traditional links

```tsx
import { Link } from '@inertiajs/react'
<Link href="/">Home</Link>
```

### Inertia React Forms
```tsx
import { Form } from '@inertiajs/react'

export default () => (
    <Form action="/users" method="post">
        {({
            errors,
            hasErrors,
            processing,
            wasSuccessful,
            recentlySuccessful,
            clearErrors,
            resetAndClearErrors,
            defaults
        }) => (
        <>
        <input type="text" name="name" />
        
        {errors.name && <div>{errors.name}</div>}
        
        <button type="submit" disabled={processing}>
            {processing ? 'Creating...' : 'Create User'}
        </button>
        
        {wasSuccessful && <div>User created successfully!</div>}
        </>
    )}
    </Form>
)
```

## Laravel Wayfinder

Wayfinder generates TypeScript functions and types for Laravel controllers and routes, providing type safety and automatic synchronization.

### Key Features
- **Form Support**: Use `.form()` with `--with-form` flag for HTML form attributes
- **HTTP Methods**: Call `.get()`, `.post()`, `.patch()`, `.put()`, `.delete()` for specific methods
- **Invokable Controllers**: Import and invoke directly as functions
- **Named Routes**: Import from `@/routes/` for non-controller routes
- **Parameter Binding**: Detects route keys (e.g., `{post:slug}`)
- **Query Parameters**: Pass `{ query: {...} }` in options to append params
- **Query Merging**: Use `mergeQuery` to merge with `window.location.search`
- **Route Objects**: Functions return `{ url, method }` shaped objects
- **URL Extraction**: Use `.url()` to get URL string

### Development Guidelines
- Always prefer named imports for tree-shaking
- Avoid default controller imports
- Run `php artisan wayfinder:generate` after route changes if Vite plugin isn't installed

### Usage Examples
```typescript
// Import controller methods (tree-shakable)
import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

// Get route object with URL and method
show(1) // { url: "/posts/1", method: "get" }

// Get just the URL
show.url(1) // "/posts/1"

// Use specific HTTP methods
show.get(1) // { url: "/posts/1", method: "get" }
show.head(1) // { url: "/posts/1", method: "head" }

// Import named routes
import { show as postShow } from '@/routes/post' // For route name 'post.show'
postShow(1) // { url: "/posts/1", method: "get" }
```

### Wayfinder + Inertia Forms
```tsx
<Form {...store.form()}><input name="title" /></Form>
```

## Tailwind CSS

### Core Principles
- Use Tailwind CSS classes to style HTML
- Check and use existing Tailwind conventions within the project
- Offer to extract repeated patterns into components
- Think through class placement, order, priority, and defaults
- Remove redundant classes, add classes to parent or child carefully
- Group elements logically

### Spacing
- When listing items, use gap utilities for spacing, not margins

```html
<div class="flex gap-8">
    <div>Superior</div>
    <div>Michigan</div>
    <div>Erie</div>
</div>
```

### Dark Mode
- If existing pages support dark mode, new pages must support it similarly
- Typically using `dark:` prefix

### Tailwind v4 Specifics
- Configuration is CSS-first using `@theme` directive
- No separate `tailwind.config.js` file needed
- `corePlugins` is not supported in v4

```css
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
```

- Import Tailwind using CSS `@import` statement:

```css
/* v4 way */
@import "tailwindcss";

/* NOT v3 way */
/* @tailwind base; */
/* @tailwind components; */
/* @tailwind utilities; */
```

### Replaced Utilities in v4
Do not use deprecated utilities - use replacements:

| Deprecated | Replacement |
|------------|-------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

## Laravel Fortify

Fortify is a headless authentication backend for Laravel applications.

### Configuration & Setup
- Check `config/fortify.php` to see what's enabled
- Enable features in the `'features' => []` array
- Set `'views' => false` if handling views yourself

### Customization
- Customize views in `FortifyServiceProvider`'s `boot()` method
- Use `Fortify::authenticateUsing()` for custom authentication logic
- Modify actions in `app/Actions/Fortify/` to change feature behavior

### Available Features
- `Features::registration()` - user registration
- `Features::emailVerification()` - verify new user emails
- `Features::twoFactorAuthentication()` - 2FA with QR codes and recovery codes
  - Options: `['confirmPassword' => true, 'confirm' => true]`
- `Features::updateProfileInformation()` - let users update profiles
- `Features::updatePasswords()` - let users change passwords
- `Features::resetPasswords()` - password reset via email

## Code Formatting

### Laravel Pint
- Run `vendor/bin/pint --dirty` before finalizing changes
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix formatting

## Frontend Bundling

- If frontend changes aren't reflected in UI, user may need to run:
  - `npm run build`
  - `npm run dev`
  - `composer run dev`

## Common Errors

### Vite Manifest Error
If you receive "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest":
- Run `npm run build` or ask user to run `npm run dev` or `composer run dev`

---

**Remember**: These guidelines ensure consistency, quality, and maintainability. Follow them closely for the best development experience.
