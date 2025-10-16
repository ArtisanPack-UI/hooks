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