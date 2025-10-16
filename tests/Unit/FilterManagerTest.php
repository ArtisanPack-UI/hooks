<?php
/**
 * Filter Unit Tests
 *
 * @package    ArtisanPackUI\Hooks\Tests\Unit
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Tests\Unit;

use ArtisanPackUI\Hooks\Filter;

beforeEach(function () {
	$this->filterManager = $this->app->make(Filter::class);
});

test('it applies a simple filter', function () {
	$this->filterManager->add('simple_filter', function ($value) {
		return $value . '_changed';
	});

	$result = $this->filterManager->apply('simple_filter', 'initial');

	expect($result)->toBe('initial_changed');
});

test('it applies filters in priority order', function () {
	$this->filterManager->add('priority_filter', function ($value) {
		return $value . '_first';
	}, 10);

	$this->filterManager->add('priority_filter', function ($value) {
		return $value . '_second';
	}, 20);

	$result = $this->filterManager->apply('priority_filter', 'initial');

	expect($result)->toBe('initial_first_second');
});

test('it passes additional arguments to filters', function () {
	$this->filterManager->add('argument_filter', function ($value, $arg1, $arg2) {
		return $value . '_' . $arg1 . '_' . $arg2;
	});

	$result = $this->filterManager->apply('argument_filter', 'initial', 'hello', 'world');

	expect($result)->toBe('initial_hello_world');
});

test('it returns the original value if no filters are registered', function () {
	$result = $this->filterManager->apply('unregistered_filter', 'initial');

	expect($result)->toBe('initial');
});