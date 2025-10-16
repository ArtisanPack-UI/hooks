# ArtisanPack UI Hooks

WordPress-style actions and filters for Laravel applications — with convenient helper functions, Facades, and Blade directives.

This package lets you register callbacks on named hooks (actions) and filter values (filters) anywhere in your app. It’s great for modular packages, plug-in style extensions, and clean separation of concerns.

## Table of contents
- Features
- Requirements
- Installation
- Laravel package discovery
- Quick start
  - Actions
  - Filters
- Priorities and execution order
- Using Facades
- Blade directives
- Testing locally
- Contributing
- Security
- Changelog
- License

## Features
- Simple API for registering and dispatching actions and filters
- Helper functions: `addAction`, `doAction`, `addFilter`, `applyFilters`
- Static Facades for Laravel: `Action` and `Filter`
- Blade directives: `@action` and `@filter`
- Runs callbacks in predictable priority order (lower numbers first)
- Fully framework-native for Laravel with auto-discovery

## Requirements
- PHP 8.2+
- Laravel (tested with 10.x and 11.x)

## Installation
Install via Composer:

```bash
composer require artisanpack-ui/hooks
```

## Laravel package discovery
This package supports Laravel’s package discovery and will automatically register:
- Service providers: `HooksServiceProvider`, `BladeDirectiveServiceProvider`
- Facade aliases: `Action`, `Filter`

No manual changes to `config/app.php` are required in a standard Laravel app.

## Quick start

### Actions
Register a callback on a named action and dispatch it later.

```php
use function addAction;
use function doAction;

addAction('order.placed', function ($order) {
    // Send email, fire a job, log, etc.
});

// Somewhere else in your code when the order is placed:
doAction('order.placed', $order);
```

You can also provide a priority (lower numbers run first; default is 10):

```php
addAction('order.placed', fn () => logger('low priority first'), 5);
addAction('order.placed', fn () => logger('default priority next')); // 10
addAction('order.placed', fn () => logger('higher number last'), 20);
```

### Filters
Filters pass a value through one or more callbacks. Each callback receives the current value as the first argument and must return the (possibly modified) value.

```php
use function addFilter;
use function applyFilters;

addFilter('price.display', function (string $price, string $currency) {
    return $currency.' '.$price; // e.g., "USD 49.00"
});

$display = applyFilters('price.display', '49.00', 'USD');
```

## Priorities and execution order
- Lower priority numbers run first (5 runs before 10).
- Callbacks with the same priority run in the order they were added.

## Using Facades
If you prefer Facades over helper functions, use the provided `Action` and `Filter` Facades.

```php
use ArtisanPackUI\Hooks\Facades\Action;
use ArtisanPackUI\Hooks\Facades\Filter;

Action::add('user.registered', fn ($user) => \Log::info('Registered: '.$user->id));
Action::do('user.registered', $user);

Filter::add('content.summary', fn ($text) => str($text)->limit(120));
$summary = Filter::apply('content.summary', $text);
```

## Blade directives
You can trigger actions and apply filters directly within Blade views.

```blade
{{-- Trigger an action --}}
@action('view.rendering', $post)

{{-- Apply a filter and echo the result --}}
@filter('title.display', $post->title)
```

- `@action('hook', $args...)` calls `doAction('hook', $args...)`.
- `@filter('hook', $value, $args...)` echoes `applyFilters('hook', $value, $args...)`.

## Testing locally
This repository uses Pest. Run the test suite with:

```bash
composer test
```

## Contributing
As an open source project, this package welcomes contributions. Please read the [contributing guidelines](CONTRIBUTING.md) before submitting issues or pull requests.

## Security
If you discover a security vulnerability, please review the Security section in the contributing guidelines and contact the maintainer directly. Do not open a public issue for security reports.

## Changelog
See [CHANGELOG.md](CHANGELOG.md) for a history of notable changes.

## License
This package is open-sourced software licensed under the [MIT license](LICENSE).
