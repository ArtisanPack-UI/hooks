---
title: Blade Directives
---

You can trigger actions and apply filters directly within Blade views using two directives: `@action` and `@filter`.

See also: [[Actions]], [[Filters]], and [[Facades]].

## @action
Calls `doAction('hook', $args...)`.

```blade
{{-- Trigger an action --}}
@action('view.rendering', $post)
```

## @filter
Echoes the result of `applyFilters('hook', $value, $args...)`.

```blade
{{-- Apply a filter and echo the result --}}
@filter('title.display', $post->title)
```

## Tips
- Use actions for side-effects (e.g., logging, dispatching jobs).
- Use filters when you want to transform values for display.

---
Continue to [[Testing]] →
