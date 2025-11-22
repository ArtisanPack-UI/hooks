<?php

declare(strict_types=1);
/**
 * Filter Unit Tests
 *
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Tests\Unit;

use ArtisanPackUI\Hooks\Filter;

beforeEach(function (): void {
    $this->filterManager = $this->app->make(Filter::class);
});

test('it applies a simple filter', function (): void {
    $this->filterManager->add('simple_filter', function ($value) {
        return $value.'_changed';
    });

    $result = $this->filterManager->apply('simple_filter', 'initial');

    expect($result)->toBe('initial_changed');
});

test('it applies filters in priority order', function (): void {
    $this->filterManager->add('priority_filter', function ($value) {
        return $value.'_first';
    }, 10);

    $this->filterManager->add('priority_filter', function ($value) {
        return $value.'_second';
    }, 20);

    $result = $this->filterManager->apply('priority_filter', 'initial');

    expect($result)->toBe('initial_first_second');
});

test('it passes additional arguments to filters', function (): void {
    $this->filterManager->add('argument_filter', function ($value, $arg1, $arg2) {
        return $value.'_'.$arg1.'_'.$arg2;
    });

    $result = $this->filterManager->apply('argument_filter', 'initial', 'hello', 'world');

    expect($result)->toBe('initial_hello_world');
});

test('it returns the original value if no filters are registered', function (): void {
    $result = $this->filterManager->apply('unregistered_filter', 'initial');

    expect($result)->toBe('initial');
});

test('it removes a specific filter', function (): void {
    $callback1 = function ($value) {
        return $value.'_one';
    };
    $callback2 = function ($value) {
        return $value.'_two';
    };

    $this->filterManager->add('removable_filter', $callback1);
    $this->filterManager->add('removable_filter', $callback2);

    // Remove the first callback.
    $removed = $this->filterManager->remove('removable_filter', $callback1);

    $result = $this->filterManager->apply('removable_filter', 'initial');

    expect($removed)->toBeTrue();
    expect($result)->toBe('initial_two'); // Only callback2 should have run.
});

test('it removes all filters for a specific priority', function (): void {
    $this->filterManager->add('remove_all_priority', fn ($v) => $v.'_p10', 10);
    $this->filterManager->add('remove_all_priority', fn ($v) => $v.'_p20', 20);

    $this->filterManager->removeAll('remove_all_priority', 10);
    $result = $this->filterManager->apply('remove_all_priority', 'initial');

    expect($result)->toBe('initial_p20');
});

test('it removes all filters for a hook', function (): void {
    $this->filterManager->add('remove_all_hook', fn ($v) => $v.'_p10', 10);
    $this->filterManager->add('remove_all_hook', fn ($v) => $v.'_p20', 20);

    $this->filterManager->removeAll('remove_all_hook');
    $result = $this->filterManager->apply('remove_all_hook', 'initial');

    expect($result)->toBe('initial');
});
