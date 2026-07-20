<?php

declare(strict_types=1);
/**
 * Hook Deprecations Unit Tests
 *
 * @since      1.3.0
 */

namespace ArtisanPackUI\Hooks\Tests\Unit;

use ArtisanPackUI\Hooks\Action;
use ArtisanPackUI\Hooks\Filter;
use ArtisanPackUI\Hooks\HookDeprecations;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

beforeEach(function (): void {
    // Default env: logging on at info level. Individual tests override via config().
    config()->set('artisanpack.hooks.deprecation_level', 'info');

    $this->deprecations = $this->app->make(HookDeprecations::class);
    $this->actions      = $this->app->make(Action::class);
    $this->filters      = $this->app->make(Filter::class);
});

test('resolve returns the same hook name when no alias is registered', function (): void {
    expect($this->deprecations->resolve('some.hook'))->toBe('some.hook');
});

test('resolve returns the canonical name for a registered alias', function (): void {
    $this->deprecations->alias('old.hook', 'new.hook');

    expect($this->deprecations->resolve('old.hook'))->toBe('new.hook');
});

test('resolveSilent does not emit a log entry', function (): void {
    Log::spy();

    $this->deprecations->alias('old.silent', 'new.silent');
    $this->deprecations->resolveSilent('old.silent');

    Log::shouldNotHaveReceived('log');
});

test('aliasesFor returns every old name pointing at a canonical', function (): void {
    $this->deprecations->alias('a', 'canonical');
    $this->deprecations->alias('b', 'canonical');
    $this->deprecations->alias('c', 'other');

    $aliases = $this->deprecations->aliasesFor('canonical');
    sort($aliases);

    expect($aliases)->toBe(['a', 'b']);
});

test('alias chains collapse to the final canonical', function (): void {
    $this->deprecations->alias('old', 'middle');
    $this->deprecations->alias('middle', 'newest');

    expect($this->deprecations->resolve('old'))->toBe('newest');
    expect($this->deprecations->resolve('middle'))->toBe('newest');
    expect($this->deprecations->aliasesFor('newest'))->toContain('old', 'middle');
});

test('alias rejects cycles instead of creating self-aliases', function (): void {
    $this->deprecations->alias('a', 'b');

    expect(fn () => $this->deprecations->alias('b', 'a'))
        ->toThrow(InvalidArgumentException::class);
});

test('hasAliases reflects registration state', function (): void {
    expect($this->deprecations->hasAliases())->toBeFalse();
    $this->deprecations->alias('old', 'new');
    expect($this->deprecations->hasAliases())->toBeTrue();
});

test('old-name addAction fires when canonical doAction fires', function (): void {
    $this->deprecations->alias('order.placed', 'order.created');

    $fired = false;
    $this->actions->add('order.placed', function () use (&$fired): void {
        $fired = true;
    });

    $this->actions->do('order.created');

    expect($fired)->toBeTrue();
});

test('canonical-name addAction fires when old-name doAction fires', function (): void {
    $this->deprecations->alias('order.placed', 'order.created');

    $fired = false;
    $this->actions->add('order.created', function () use (&$fired): void {
        $fired = true;
    });

    $this->actions->do('order.placed');

    expect($fired)->toBeTrue();
});

test('callbacks registered before deprecation still fire via belt-and-suspenders fan-out', function (): void {
    $fired = false;
    // Subscriber registered under the old name before the rename shipped.
    $this->actions->add('legacy.name', function () use (&$fired): void {
        $fired = true;
    });

    $this->deprecations->alias('legacy.name', 'modern.name');

    $this->actions->do('modern.name');

    expect($fired)->toBeTrue();
});

test('same callback registered under both names fires only once per dispatch', function (): void {
    $count = 0;
    $cb    = function () use (&$count): void {
        $count++;
    };

    // Registered under old name before the alias exists.
    $this->actions->add('shared.legacy', $cb);
    $this->deprecations->alias('shared.legacy', 'shared.canonical');
    // Then also registered under the canonical name.
    $this->actions->add('shared.canonical', $cb);

    $this->actions->do('shared.canonical');

    expect($count)->toBe(1);
});

test('deliberately double-adding a callback in the same bucket still fires it twice', function (): void {
    // Ensure the cross-bucket dedup does not accidentally dedupe within a
    // single bucket — pre-1.3 double-adds must still fire twice.
    $count = 0;
    $cb    = function () use (&$count): void {
        $count++;
    };

    $this->actions->add('same.bucket', $cb);
    $this->actions->add('same.bucket', $cb);

    $this->actions->do('same.bucket');

    expect($count)->toBe(2);
});

test('filters resolve aliases and fold callbacks in strict insertion order within a priority', function (): void {
    // Callbacks at the same priority fire in registration order regardless
    // of which bucket (canonical or alias) they belong to.
    $this->filters->add('old.filter', fn (string $v) => $v.':A', 10); // t=0, bucket=old
    $this->deprecations->alias('old.filter', 'new.filter');
    $this->filters->add('new.filter', fn (string $v) => $v.':B', 10); // t=1, bucket=canonical
    $this->filters->add('old.filter', fn (string $v) => $v.':C', 10); // t=2, bucket=canonical (post-alias route)

    expect($this->filters->apply('new.filter', 'seed'))->toBe('seed:A:B:C');
});

test('filters honor priority across canonical + alias buckets', function (): void {
    $this->filters->add('old.filter', fn (string $v) => $v.':pre-old', 5);
    $this->deprecations->alias('old.filter', 'new.filter');
    $this->filters->add('new.filter', fn (string $v) => $v.':canonical', 10);
    $this->filters->add('old.filter', fn (string $v) => $v.':post-old', 20);

    expect($this->filters->apply('new.filter', 'seed'))->toBe('seed:pre-old:canonical:post-old');
});

test('add and remove do not log deprecations — only dispatch does', function (): void {
    Log::spy();

    $this->deprecations->alias('quiet.old', 'quiet.new');

    $cb = function (): void {};
    $this->actions->add('quiet.old', $cb);
    $this->actions->remove('quiet.old', $cb);

    Log::shouldNotHaveReceived('log');

    // Dispatch DOES log.
    $this->actions->do('quiet.old');
    Log::shouldHaveReceived('log')->once();
});

test('deprecation log fires exactly once per unique alias per request', function (): void {
    Log::spy();

    $this->deprecations->alias('a.old', 'a.new');
    $this->deprecations->alias('b.old', 'b.new');

    $this->deprecations->resolve('a.old');
    $this->deprecations->resolve('a.old');
    $this->deprecations->resolve('a.old');
    $this->deprecations->resolve('b.old');
    $this->deprecations->resolve('b.old');

    Log::shouldHaveReceived('log')->twice();
});

test('resetLogState re-arms the once-per-request dedup', function (): void {
    Log::spy();

    $this->deprecations->alias('reset.old', 'reset.new');

    $this->deprecations->resolve('reset.old');
    $this->deprecations->resolve('reset.old');
    Log::shouldHaveReceived('log')->once();

    $this->deprecations->resetLogState();

    $this->deprecations->resolve('reset.old');
    Log::shouldHaveReceived('log')->twice();
});

test('deprecation_level=off suppresses the deprecation log', function (): void {
    config()->set('artisanpack.hooks.deprecation_level', 'off');
    Log::spy();

    $this->deprecations->alias('quiet.old', 'quiet.new');
    $this->deprecations->resolve('quiet.old');

    Log::shouldNotHaveReceived('log');
});

test('deprecation_level accepts any PSR-3 level', function (): void {
    config()->set('artisanpack.hooks.deprecation_level', 'error');
    Log::spy();

    $this->deprecations->alias('escalate.old', 'escalate.new');
    $this->deprecations->resolve('escalate.old');

    Log::shouldHaveReceived('log')->once()->withArgs(function ($level): bool {
        return 'error' === $level;
    });
});

test('remove stops after the first matching bucket instead of clearing all copies', function (): void {
    // Callback registered in both buckets — remove must only take one out.
    $count = 0;
    $cb    = function () use (&$count): void {
        $count++;
    };

    $this->actions->add('shared.legacy', $cb);
    $this->deprecations->alias('shared.legacy', 'shared.canonical');
    $this->actions->add('shared.canonical', $cb);

    expect($this->actions->remove('shared.canonical', $cb))->toBeTrue();

    $this->actions->do('shared.canonical');

    // One copy survived, so the callback fires exactly once (cross-bucket
    // dedup does not apply because only one bucket now holds it).
    expect($count)->toBe(1);
});

test('removeAction works when called with the old alias name', function (): void {
    $this->deprecations->alias('old.remove', 'new.remove');

    $callback = function (): void {};
    $this->actions->add('new.remove', $callback);

    expect($this->actions->remove('old.remove', $callback))->toBeTrue();

    $fired = false;
    $this->actions->add('new.remove', function () use (&$fired): void {
        $fired = true;
    });
    $this->actions->do('new.remove');
    expect($fired)->toBeTrue();
});

test('removeFilter works when called with the canonical name after registering under the old name', function (): void {
    $callback = function (mixed $v): mixed { return $v; };
    $this->filters->add('old.remove', $callback);

    $this->deprecations->alias('old.remove', 'new.remove');

    expect($this->filters->remove('new.remove', $callback))->toBeTrue();
});

test('removeAll clears both canonical and alias buckets', function (): void {
    $this->filters->add('legacy.name', fn (mixed $v) => $v.'b');
    $this->deprecations->alias('legacy.name', 'canonical.name');
    $this->filters->add('legacy.name', fn (mixed $v) => $v.'a');

    expect($this->filters->apply('canonical.name', 'seed'))->not->toBe('seed');
    expect($this->filters->removeAll('canonical.name'))->toBeTrue();
    expect($this->filters->apply('canonical.name', 'seed'))->toBe('seed');
});

test('no-alias fast path preserves pre-1.3 dispatch semantics', function (): void {
    // With no aliases at all, dispatch should not touch the alias-fanout
    // machinery — behavior matches the pre-1.3 flat-priority path.
    $this->filters->add('plain.hook', fn (string $v) => strtoupper($v), 10);
    $this->filters->add('plain.hook', fn (string $v) => "[$v]", 20);

    expect($this->filters->apply('plain.hook', 'x'))->toBe('[X]');
});
