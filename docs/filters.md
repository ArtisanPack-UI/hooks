---
title: Filters
---

Filters transform values by passing them through one or more callbacks. Each callback receives the current value as the first argument and must return the (possibly modified) value.

See also: [[Actions]] and [[Priorities and Execution Order]].

## API
- addFilter(string $hook, callable $callback, int $priority = 10): void
- applyFilters(string $hook, mixed $value, mixed ...$args): mixed
- removeFilter(string $hook, callable $callback, int $priority = 10): bool  (since 1.1.0)
- removeAllFilters(string $hook, int|false $priority = false): bool  (since 1.1.0)

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

## Removing callbacks
You can remove a specific filter callback or remove all callbacks for a filter (optionally for a specific priority). These functions were added in 1.1.0.

```php
use function removeFilter;
use function removeAllFilters;

$callback = function (string $value) {
    return $value.'_removed';
};

addFilter('text.process', $callback);        // default priority 10
addFilter('text.process', fn ($v) => strtoupper($v), 20);

// Remove a specific callback (returns true when removed)
$removed = removeFilter('text.process', $callback); // true

// Remove all callbacks at a given priority
removeAllFilters('text.process', 20);

// Remove all callbacks for the hook
removeAllFilters('text.process');
```

- removeFilter(...) returns a boolean indicating whether a matching callback was removed.
- removeAllFilters(...) returns true if callbacks existed and were removed.

## Best Practices
- Always return the value in your callback.
- Keep filters deterministic; avoid side effects when possible.
- Use descriptive hook names to avoid conflicts.

---
Continue to [[Priorities and Execution Order]] →
