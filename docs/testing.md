---
title: Testing
---

This repository uses Pest. You can run the test suite locally to verify everything is working as expected.

## Running Tests

```bash
composer test
```

## Writing Tests for Hooks
- For actions, assert side-effects (logs, dispatched jobs, method calls, etc.).
- For filters, assert input → output transformations.

Example with filters:

```php
use function addFilter;
use function applyFilters;

it('formats currency display', function () {
    addFilter('price.display', fn ($price, $currency) => $currency.' '.$price);

    $result = applyFilters('price.display', '49.00', 'USD');

    expect($result)->toBe('USD 49.00');
});
```

See also: [[Actions]] and [[Filters]].

---
Continue to [[FAQ]] →
