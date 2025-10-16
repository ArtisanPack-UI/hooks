---
title: Filters
---

Filters transform values by passing them through one or more callbacks. Each callback receives the current value as the first argument and must return the (possibly modified) value.

See also: [[Actions]] and [[Priorities and Execution Order]].

## API
- addFilter(string $hook, callable $callback, int $priority = 10): void
- applyFilters(string $hook, mixed $value, mixed ...$args): mixed

## Usage

```php
use function addFilter;
use function applyFilters;

addFilter('price.display', function (string $price, string $currency) {
    return $currency.' '.$price; // e.g., "USD 49.00"
});

$display = applyFilters('price.display', '49.00', 'USD');
```

## Chaining and Priorities
Multiple callbacks can modify the value; they run in ascending priority order. The output of one filter becomes the input to the next.

```php
addFilter('content.summary', fn ($text) => str($text)->limit(200), 5);
addFilter('content.summary', fn ($text) => trim($text));

$summary = applyFilters('content.summary', $post->body);
```

## Best Practices
- Always return the value in your callback.
- Keep filters deterministic; avoid side effects when possible.
- Use descriptive hook names to avoid conflicts.

---
Continue to [[Priorities and Execution Order]] →
