---
title: Priorities and Execution Order
---

Hooks (both actions and filters) run callbacks in ascending priority order. Lower numbers are executed first. Callbacks with the same priority run in the order they were added.

See also: [[Actions]] and [[Filters]].

## How Priorities Work
- Default priority is 10.
- Smaller numbers run earlier; larger numbers run later.
- When priorities are equal, callbacks execute FIFO (first added, first run).

### Example (Actions)
```php
addAction('order.placed', fn () => logger('A: priority 5'), 5);
addAction('order.placed', fn () => logger('B: priority 10'));
addAction('order.placed', fn () => logger('C: priority 20'), 20);
```
Execution order: A, B, C.

### Example (Filters)
```php
addFilter('title.display', fn ($title) => strtoupper($title), 5);
addFilter('title.display', fn ($title) => $title.'!');

$result = applyFilters('title.display', 'Hello');
// 'HELLO!'
```

## Tips
- Reserve low numbers (e.g., 1–5) for foundational work.
- Keep defaults at 10 for most extensions.
- Use higher numbers (20+) for "final touches".

---
Continue to [[Facades]] →
