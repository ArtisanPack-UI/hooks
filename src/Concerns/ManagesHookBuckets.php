<?php

declare(strict_types=1);
/**
 * Shared bucket management for hook registries.
 *
 * Owns the callback storage, insertion-sequence counter, add/remove/removeAll
 * semantics, and alias resolution. Action and Filter compose this trait and
 * only implement their dispatch primitive (do / apply) on top.
 *
 * @since 1.3.0
 */

namespace ArtisanPackUI\Hooks\Concerns;

use ArtisanPackUI\Hooks\HookDeprecations;
use Closure;

/**
 * @internal
 */
trait ManagesHookBuckets
{
    /**
     * Registered callbacks keyed as [hook][priority][ [seq, callable], ... ].
     *
     * The seq tuple is a monotonic insertion counter that guarantees stable
     * FIFO ordering within a priority even after alias fan-out.
     *
     * @since 1.3.0
     */
    protected array $callbacks = [];

    /**
     * Monotonic insertion counter across all hooks / priorities.
     *
     * @since 1.3.0
     */
    protected int $sequence = 0;

    /**
     * The hook deprecations manager.
     *
     * @since 1.3.0
     */
    protected ?HookDeprecations $deprecations;

    /**
     * Register a callback for a hook.
     *
     * Deprecated hook names are transparently routed to their canonical
     * bucket. Registrations do NOT emit a deprecation log — that is
     * reserved for dispatch so notices are traceable to real hook use.
     *
     * @since 1.0.0
     */
    public function add(string $hook, callable $callback, int $priority = 10): void
    {
        $canonical                                = $this->resolveSilent($hook);
        $this->callbacks[$canonical][$priority][] = [$this->sequence++, $callback];
    }

    /**
     * Remove a specific callback registration.
     *
     * Returns true after removing the first match; further matches (in the
     * same bucket, in an alias bucket, or at a higher priority) are left
     * intact — preserving the pre-1.3 "remove one exact registration"
     * contract.
     *
     * @since 1.1.0
     */
    public function remove(string $hook, callable $callback, int $priority = 10): bool
    {
        $canonical = $this->resolveSilent($hook);

        foreach ($this->bucketNamesFor($canonical) as $bucket) {
            if (! isset($this->callbacks[$bucket][$priority])) {
                continue;
            }

            foreach ($this->callbacks[$bucket][$priority] as $key => $entry) {
                if ($entry[1] === $callback) {
                    unset($this->callbacks[$bucket][$priority][$key]);
                    $this->pruneBucket($bucket, $priority);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove every callback for a hook, or every callback at a specific
     * priority. Cascades into alias buckets so the migration guarantee
     * ("remove works with either name") is symmetrical with add().
     *
     * @since 1.1.0
     */
    public function removeAll(string $hook, int|false $priority = false): bool
    {
        $canonical = $this->resolveSilent($hook);
        $removed   = false;

        foreach ($this->bucketNamesFor($canonical) as $bucket) {
            if (! isset($this->callbacks[$bucket])) {
                continue;
            }

            if (false !== $priority) {
                if (isset($this->callbacks[$bucket][$priority])) {
                    unset($this->callbacks[$bucket][$priority]);
                    $removed = true;
                    $this->pruneBucket($bucket);
                }

                continue;
            }

            unset($this->callbacks[$bucket]);
            $removed = true;
        }

        return $removed;
    }

    /**
     * Collect the callback list for a dispatch, in priority + insertion
     * order, deduplicated across alias buckets.
     *
     * The fast path (no aliases in the whole app, or none for this hook)
     * skips both the reverse-lookup and the dedup pass so the common case
     * pays only for a single isset() branch beyond the pre-1.3 cost.
     *
     * @since 1.3.0
     *
     * @return array<int, callable>
     */
    protected function collectForDispatch(string $hook): array
    {
        $canonical = null === $this->deprecations
            ? $hook
            : $this->deprecations->resolve($hook);

        // Fast path: no aliases in play — behave exactly like pre-1.3 storage
        // (one bucket, sort priorities, flatten, drop the seq tuple).
        $aliases = null === $this->deprecations || ! $this->deprecations->hasAliases()
            ? []
            : $this->deprecations->aliasesFor($canonical);

        if (empty($aliases)) {
            if (! isset($this->callbacks[$canonical])) {
                return [];
            }

            $bucket = $this->callbacks[$canonical];
            ksort($bucket);

            $out = [];
            foreach ($bucket as $entries) {
                foreach ($entries as $entry) {
                    $out[] = $entry[1];
                }
            }

            return $out;
        }

        // Alias fan-out: collect entries across canonical + every alias, sort
        // by (priority, insertion-seq) for stable FIFO, and skip callables
        // already contributed by an earlier bucket (cross-bucket dedup).
        $entries = [];
        foreach (array_merge([$canonical], $aliases) as $bucket) {
            if (! isset($this->callbacks[$bucket])) {
                continue;
            }

            foreach ($this->callbacks[$bucket] as $priority => $list) {
                foreach ($list as [$seq, $cb]) {
                    $entries[] = [$priority, $seq, $cb, $bucket];
                }
            }
        }

        if (empty($entries)) {
            return [];
        }

        usort($entries, static fn (array $a, array $b): int => $a[0] <=> $b[0] ?: $a[1] <=> $b[1]);

        $seenBuckets = [];
        $seenKeys    = [];
        $out         = [];

        foreach ($entries as [$priority, $seq, $cb, $bucket]) {
            $key = $this->callableKey($cb);

            // Within one bucket, allow duplicate registrations (someone who
            // explicitly did add() twice expects two fires). Across buckets,
            // skip the callable if a prior bucket already contributed it.
            if (isset($seenKeys[$key]) && ! isset($seenBuckets[$bucket][$key])) {
                continue;
            }

            $seenKeys[$key]             = true;
            $seenBuckets[$bucket][$key] = true;
            $out[]                      = $cb;
        }

        return $out;
    }

    /**
     * Resolve a hook name without emitting a log entry.
     *
     * Used by add/remove/removeAll so registration bookkeeping does not
     * burn the "log once per unique alias" budget before any real dispatch
     * has happened — that would strip stack context and make the notice
     * untraceable to the code that fires the hook.
     *
     * @since 1.3.0
     */
    protected function resolveSilent(string $hook): string
    {
        return null === $this->deprecations
            ? $hook
            : $this->deprecations->resolveSilent($hook);
    }

    /**
     * Canonical + every alias pointing at it, for fan-out during remove/removeAll.
     *
     * @since 1.3.0
     *
     * @return array<int, string>
     */
    protected function bucketNamesFor(string $canonical): array
    {
        if (null === $this->deprecations || ! $this->deprecations->hasAliases()) {
            return [$canonical];
        }

        return array_merge([$canonical], $this->deprecations->aliasesFor($canonical));
    }

    /**
     * Compute a stable key for a callable so cross-bucket duplicates can be
     * detected by identity, not by whatever string PHP would produce.
     *
     * Covers every shape callable syntax accepts:
     * - string function name
     * - Closure
     * - invokable object
     * - [object, methodName]
     * - [ClassName::class, staticMethod]
     *
     * @since 1.3.0
     */
    protected function callableKey(callable $cb): string
    {
        if (is_string($cb)) {
            return 'fn:'.$cb;
        }

        if ($cb instanceof Closure) {
            return 'closure:'.spl_object_id($cb);
        }

        if (is_object($cb)) {
            return 'invokable:'.spl_object_id($cb);
        }

        // Array form: [class-or-object, methodName].
        [$target, $method] = $cb;

        return is_object($target)
            ? 'method:'.spl_object_id($target).'::'.$method
            : 'static:'.$target.'::'.$method;
    }

    /**
     * Clean up empty priority arrays (and the whole hook entry when its last
     * priority is removed) so removeAll semantics match the pre-1.3 contract.
     *
     * @since 1.3.0
     */
    protected function pruneBucket(string $bucket, ?int $priority = null): void
    {
        if (null !== $priority && empty($this->callbacks[$bucket][$priority])) {
            unset($this->callbacks[$bucket][$priority]);
        }

        if (empty($this->callbacks[$bucket])) {
            unset($this->callbacks[$bucket]);
        }
    }
}
