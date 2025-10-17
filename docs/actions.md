---
title: Actions
---

Actions let you register callbacks on named hooks and execute them later. Use them for decoupled event-style triggers inside your Laravel app.

See also: [[Filters]] and [[Priorities and Execution Order]].

## API
- addAction(string $hook, callable $callback, int $priority = 10): void
- doAction(string $hook, mixed ...$args): void
- removeAction(string $hook, callable $callback, int $priority = 10): bool  (since 1.1.0)
- removeAllActions(string $hook, int|false $priority = false): bool  (since 1.1.0)

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

## Removing callbacks
You can remove a specific callback, or remove all callbacks for an action (optionally for a specific priority). These functions were added in 1.1.0.

```php
use function removeAction;
use function removeAllActions;

$callback = fn () => logger('will be removed');

addAction('user.registered', $callback);
addAction('user.registered', fn () => logger('still here'), 20);

// Remove a specific callback (returns true when removed)
$removed = removeAction('user.registered', $callback); // true

// Remove all callbacks at a given priority
removeAllActions('user.registered', 20);

// Remove all callbacks for the hook
removeAllActions('user.registered');
```

- removeAction(...) returns a boolean indicating whether a matching callback was removed.
- removeAllActions(...) returns true if callbacks existed and were removed.

## Best Practices
- Keep callbacks small and focused.
- Prefer job dispatching for heavy work inside callbacks.
- Namespacing hook names (e.g., "order.placed" or "user.registered") avoids collisions.

---
Continue to [[Filters]] →
