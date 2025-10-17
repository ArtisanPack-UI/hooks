<?php
/**
 * Action Unit Tests
 *
 * @package    ArtisanPackUI\Hooks\Tests\Unit
 * @since      1.0.0
 */

namespace ArtisanPackUI\Hooks\Tests\Unit;

use ArtisanPackUI\Hooks\Action;

beforeEach(function () {
	$this->actionManager = $this->app->make(Action::class);
});

test('it executes a simple action', function () {
	$result = 'initial';

	$this->actionManager->add('simple_action', function () use (&$result) {
		$result = 'changed';
	});

	$this->actionManager->do('simple_action');

	expect($result)->toBe('changed');
});

test('it executes actions in priority order', function () {
	$result = [];

	$this->actionManager->add('priority_action', function () use (&$result) {
		$result[] = 'second';
	}, 20);

	$this->actionManager->add('priority_action', function () use (&$result) {
		$result[] = 'first';
	}, 10);

	$this->actionManager->do('priority_action');

	expect($result)->toBe(['first', 'second']);
});

test('it passes arguments to actions', function () {
	$result = '';

	$this->actionManager->add('argument_action', function ($arg1, $arg2) use (&$result) {
		$result = $arg1 . $arg2;
	});

	$this->actionManager->do('argument_action', 'hello', 'world');

	expect($result)->toBe('helloworld');
});

test('it removes a specific action', function () {
	$result = 0;

	$callback1 = function () use (&$result) {
		$result += 5;
	};
	$callback2 = function () use (&$result) {
		$result += 10;
	};

	$this->actionManager->add('removable_action', $callback1);
	$this->actionManager->add('removable_action', $callback2);

	// Remove the first callback.
	$removed = $this->actionManager->remove('removable_action', $callback1);

	$this->actionManager->do('removable_action');

	expect($removed)->toBeTrue();
	expect($result)->toBe(10); // Only callback2 should have run.
});

test('it removes all actions for a specific priority', function () {
	$result = 0;
	$this->actionManager->add('remove_all_priority', function () use (&$result) {
		$result += 1;
	}, 10);
	$this->actionManager->add('remove_all_priority', function () use (&$result) {
		$result += 10;
	}, 20);

	$this->actionManager->removeAll('remove_all_priority', 10);
	$this->actionManager->do('remove_all_priority');

	expect($result)->toBe(10);
});

test('it removes all actions for a hook', function () {
	$result = 0;
	$this->actionManager->add('remove_all_hook', function () use (&$result) {
		$result += 1;
	}, 10);
	$this->actionManager->add('remove_all_hook', function () use (&$result) {
		$result += 10;
	}, 20);

	$this->actionManager->removeAll('remove_all_hook');
	$this->actionManager->do('remove_all_hook');

	expect($result)->toBe(0);
});