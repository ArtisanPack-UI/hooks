<?php

declare(strict_types=1);
/**
 * Integration Tests
 *
 * Verifies that the package's service providers, facades, helpers, and
 * Blade directives are all working together correctly.
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Tests\Unit;

use ArtisanPackUI\Hooks\Action;
use ArtisanPackUI\Hooks\Filter;
use Illuminate\Support\Facades\Blade;

test('service provider correctly registers singletons', function (): void {
    $actionInstance1 = $this->app->make(Action::class);
    $actionInstance2 = $this->app->make(Action::class);

    $filterInstance1 = $this->app->make(Filter::class);
    $filterInstance2 = $this->app->make(Filter::class);

    // Assert that resolving the class multiple times returns the exact same object.
    expect($actionInstance1)->toBe($actionInstance2);
    expect($filterInstance1)->toBe($filterInstance2);
});

test('helper functions for actions work correctly', function (): void {
    $result = 'initial';

    // Use the global helper to add the action.
    addAction('helper_action', function ($arg) use (&$result): void {
        $result = $arg;
    });

    // Use the global helper to execute the action.
    doAction('helper_action', 'changed_by_helper');

    expect($result)->toBe('changed_by_helper');
});

test('helper functions for filters work correctly', function (): void {
    // Use the global helper to add the filter.
    addFilter('helper_filter', function ($value) {
        return $value.'_changed_by_helper';
    });

    // Use the global helper to apply the filter.
    $result = applyFilters('helper_filter', 'initial');

    expect($result)->toBe('initial_changed_by_helper');
});

test('blade action directive compiles correctly', function (): void {
    $bladeTemplate = "@action('my_blade_hook', 'value1', 123)";
    $expectedPhp   = "<?php doAction('my_blade_hook', 'value1', 123); ?>";

    $compiled = Blade::compileString($bladeTemplate);

    expect($compiled)->toBe($expectedPhp);
});

test('blade filter directive compiles correctly', function (): void {
    $bladeTemplate = "@filter('my_blade_filter', \$initialValue)";
    $expectedPhp   = "<?php echo applyFilters('my_blade_filter', \$initialValue); ?>";

    $compiled = Blade::compileString($bladeTemplate);

    expect($compiled)->toBe($expectedPhp);
});
