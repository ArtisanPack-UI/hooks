## ArtisanPack UI Hooks

This package provides WordPress-style actions and filters for Laravel applications with helper functions, Facades, and Blade directives.

### Features

- **Actions**: Register and trigger named hooks to execute callbacks at specific points in your application.

@verbatim
<code-snippet name="Using Actions" lang="php">
// Register a callback
addAction('order.placed', function ($order) {
    // Send email, fire a job, log, etc.
});

// Trigger the action
doAction('order.placed', $order);
</code-snippet>
@endverbatim

- **Filters**: Pass values through callbacks to transform or modify them.

@verbatim
<code-snippet name="Using Filters" lang="php">
// Register a filter
addFilter('price.display', function (string $price, string $currency) {
    return $currency.' '.$price;
});

// Apply the filter
$display = applyFilters('price.display', '49.00', 'USD');
</code-snippet>
@endverbatim

- **Priorities**: Control execution order using priority values (lower numbers run first, default is 10).

@verbatim
<code-snippet name="Using Priorities" lang="php">
addAction('order.placed', fn () => logger('runs first'), 5);
addAction('order.placed', fn () => logger('runs second')); // 10
addAction('order.placed', fn () => logger('runs last'), 20);
</code-snippet>
@endverbatim

- **Removing Callbacks** (since 1.1.0): Remove specific callbacks or all callbacks from a hook.

@verbatim
<code-snippet name="Removing Actions" lang="php">
$callback = fn () => logger('temp');
addAction('order.placed', $callback);

// Remove specific callback
removeAction('order.placed', $callback);

// Remove all callbacks at priority 20
removeAllActions('order.placed', 20);

// Remove all callbacks for the hook
removeAllActions('order.placed');
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Removing Filters" lang="php">
$fn = fn (string $v) => strtoupper($v);
addFilter('text.process', $fn, 20);

// Remove specific callback
removeFilter('text.process', $fn);

// Remove all callbacks at priority 20
removeAllFilters('text.process', 20);

// Remove all callbacks for the hook
removeAllFilters('text.process');
</code-snippet>
@endverbatim

- **Facades**: Use static Facades instead of helper functions.

@verbatim
<code-snippet name="Using Facades" lang="php">
use ArtisanPackUI\Hooks\Facades\Action;
use ArtisanPackUI\Hooks\Facades\Filter;

Action::add('user.registered', fn ($user) => \Log::info('Registered: '.$user->id));
Action::do('user.registered', $user);

Filter::add('content.summary', fn ($text) => str($text)->limit(120));
$summary = Filter::apply('content.summary', $text);
</code-snippet>
@endverbatim

- **Blade Directives**: Trigger actions and apply filters directly in Blade views.

@verbatim
<code-snippet name="Blade Directives" lang="blade">
{{-- Trigger an action --}}
@action('view.rendering', $post)

{{-- Apply a filter and echo the result --}}
@filter('title.display', $post->title)
</code-snippet>
@endverbatim

### Best Practices

- Use descriptive hook names with dot notation (e.g., `order.placed`, `user.registered`)
- Register actions and filters in service providers for consistency
- Lower priority numbers run first; default priority is 10
- Filters must return a value; actions do not return values
- Use `@action` for side effects, `@filter` for transforming and displaying values
