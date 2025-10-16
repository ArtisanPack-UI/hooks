---
title: Actions
---

Actions let you register callbacks on named hooks and execute them later. Use them for decoupled event-style triggers inside your Laravel app.

See also: [[Filters]] and [[Priorities and Execution Order]].

## API
- addAction(string $hook, callable $callback, int $priority = 10): void
- doAction(string $hook, mixed ...$args): void

## Usage

```php
use function addAction;
use function doAction;

addAction('order.placed', function ($order) {
    // Handle the order placed event
});

// Later, when the event occurs
doAction('order.placed', $order);
```

## Priorities
You can control execution order with the optional $priority parameter. Lower numbers run first.

```php
addAction('order.placed', fn () => logger('first'), 5);
addAction('order.placed', fn () => logger('second'), 10);
addAction('order.placed', fn () => logger('third'), 20);
```

Read more in [[Priorities and Execution Order]].

## Best Practices
- Keep callbacks small and focused.
- Prefer job dispatching for heavy work inside callbacks.
- Namespacing hook names (e.g., "order.placed" or "user.registered") avoids collisions.

---
Continue to [[Filters]] →
