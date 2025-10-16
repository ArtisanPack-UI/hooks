---
title: Getting Started
---

Welcome to ArtisanPack UI Hooks. This guide will help you get up and running quickly.

See also: [[Actions]], [[Filters]], and [[Blade Directives]].

## Requirements
- PHP 8.2+
- Laravel (tested with 10.x and 11.x)

## Installation
Install via Composer:

```bash
composer require artisanpack-ui/hooks
```

## Package Discovery
This package supports Laravel’s package discovery and will automatically register:
- Service providers: `HooksServiceProvider`, `BladeDirectiveServiceProvider`
- Facade aliases: `Action`, `Filter`

No manual changes to `config/app.php` are required in a standard Laravel app.

## Quick Start

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

Read more in [[Actions]].

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

Read more in [[Filters]].

### Priorities
Callbacks run in ascending priority order (lower numbers first). See [[Priorities and Execution Order]].

### Facades and Blade
Prefer Facades? Try `Action` and `Filter`. Rendering in Blade? Use `@action` and `@filter`. See [[Facades]] and [[Blade Directives]].

---
Continue to [[Actions]] →
