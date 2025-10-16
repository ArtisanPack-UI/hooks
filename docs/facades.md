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

Filter::add('content.summary', fn ($text) => str($text)->limit(120));
$summary = Filter::apply('content.summary', $text);
```

## When to Use
- Use Facades when you prefer a static, expressive API in Laravel style.
- Use helpers when you want compact, framework-agnostic calls.

---
Continue to [[Blade Directives]] →
