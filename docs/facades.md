---
title: Facades
---

Prefer Laravel Facades? Use `Action` and `Filter` for a static API equivalent to the helper functions.

See also: [[Actions]], [[Filters]], and [[Blade Directives]].

## Usage

```php
use ArtisanPackUI\Hooks\Facades\Action;
use ArtisanPackUI\Hooks\Facades\Filter;

Action::add('user.registered', fn ($user) => \Log::info('Registered: '.$user->id));
Action::do('user.registered', $user);

// Removing action callbacks (since 1.1.0)
$callback = fn ($user) => \Log::info('Temp listener');
Action::add('user.registered', $callback);
Action::remove('user.registered', $callback);       // remove specific callback
Action::removeAll('user.registered', 20);            // remove all at a priority
Action::removeAll('user.registered');                // remove all for the hook

Filter::add('content.summary', fn ($text) => str($text)->limit(120));
$summary = Filter::apply('content.summary', $text);

// Removing filter callbacks (since 1.1.0)
$filter = fn (string $value) => strtoupper($value);
Filter::add('text.process', $filter, 20);
Filter::remove('text.process', $filter);
Filter::removeAll('text.process', 20);
Filter::removeAll('text.process');
```

## When to Use
- Use Facades when you prefer a static, expressive API in Laravel style.
- Use helpers when you want compact, framework-agnostic calls.

---
Continue to [[Blade Directives]] →
