<?php

declare(strict_types=1);
/**
 * Hook Deprecations Manager
 *
 * Tracks aliases between old and canonical hook names so that renames
 * can ship without silently breaking existing subscribers.
 *
 * @since      1.3.0
 */

namespace ArtisanPackUI\Hooks;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Registers aliases from old hook names to canonical ones and logs each
 * resolution at most once per unique alias per request.
 *
 * @since 1.3.0
 */
class HookDeprecations
{
    /**
     * Map of old hook name to canonical hook name.
     *
     * @since 1.3.0
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Reverse index of canonical hook name to its old-name aliases.
     *
     * Maintained in alias() so aliasesFor() is O(1) instead of O(N) per
     * call — aliasesFor() runs on every hook dispatch, alias() is write-rare.
     *
     * @since 1.3.0
     *
     * @var array<string, array<int, string>>
     */
    protected array $reverseIndex = [];

    /**
     * Set of old hook names that have already been logged this request.
     *
     * Cleared by resetLogState() on Octane RequestReceived and Queue
     * Looping/JobProcessing events so operators see the notice on every
     * request/job boundary rather than only once at worker boot.
     *
     * @since 1.3.0
     *
     * @var array<string, true>
     */
    protected array $logged = [];

    /**
     * Register a rename from an old hook name to a canonical one.
     *
     * Chains are collapsed so that resolving an alias always returns the
     * ultimate canonical name in a single lookup. Cycles (a rename whose
     * canonical would transitively point back at the old name) are rejected
     * — silently accepting them produces self-aliases that log nonsensical
     * "Hook X deprecated; use X instead" notices forever.
     *
     * @since 1.3.0
     *
     * @throws InvalidArgumentException When the rename would create a cycle.
     */
    public function alias(string $old, string $new): void
    {
        if ($old === $new) {
            return;
        }

        // Collapse chains so aliases[$old] points at the ultimate canonical
        // in one hop. If $new already resolves to $old, that's a cycle.
        $canonical = $this->aliases[$new] ?? $new;

        if ($canonical === $old) {
            throw new InvalidArgumentException(
                "Refusing to alias hook \"{$old}\" to \"{$new}\": would create a cycle "
                ."(\"{$new}\" already resolves to \"{$old}\").",
            );
        }

        // If the old name was already the target of some earlier alias, walk
        // those pointers forward to the new canonical.
        if (isset($this->reverseIndex[$old])) {
            foreach ($this->reverseIndex[$old] as $priorOld) {
                $this->aliases[$priorOld]         = $canonical;
                $this->reverseIndex[$canonical][] = $priorOld;
            }
            unset($this->reverseIndex[$old]);
        }

        $this->aliases[$old]              = $canonical;
        $this->reverseIndex[$canonical][] = $old;
    }

    /**
     * Resolve a hook name to its canonical form and emit a deprecation
     * notice the first time each unique alias is resolved this request.
     *
     * Called from dispatch paths (Action::do, Filter::apply) — routing
     * add/remove through here would strip stack context from the log entry
     * and exhaust the once-per-request budget before any real hook fires.
     *
     * @since 1.3.0
     */
    public function resolve(string $hook): string
    {
        if (! isset($this->aliases[$hook])) {
            return $hook;
        }

        $canonical = $this->aliases[$hook];

        if (! isset($this->logged[$hook])) {
            $this->logged[$hook] = true;
            $this->log($hook, $canonical);
        }

        return $canonical;
    }

    /**
     * Resolve a hook name without logging. Used by add/remove/removeAll so
     * registration bookkeeping does not consume the log-once budget.
     *
     * @since 1.3.0
     */
    public function resolveSilent(string $hook): string
    {
        return $this->aliases[$hook] ?? $hook;
    }

    /**
     * Return every old-name alias that resolves to the given canonical hook.
     *
     * Backed by a reverse index so this is O(1) — it runs on every hook
     * dispatch when aliases are in play.
     *
     * @since 1.3.0
     *
     * @return array<int, string>
     */
    public function aliasesFor(string $canonical): array
    {
        return $this->reverseIndex[$canonical] ?? [];
    }

    /**
     * Whether any aliases have been registered. Consumers can short-circuit
     * to the pre-1.3 fast path when this is false.
     *
     * @since 1.3.0
     */
    public function hasAliases(): bool
    {
        return ! empty($this->aliases);
    }

    /**
     * Clear the "seen this alias" set so the deprecation notice fires again
     * on the next resolution.
     *
     * Bound to Octane RequestReceived and Queue Looping events by the
     * service provider so long-lived workers do not swallow every notice
     * after the first request/job.
     *
     * @since 1.3.0
     */
    public function resetLogState(): void
    {
        $this->logged = [];
    }

    /**
     * Emit a deprecation log at the configured level.
     *
     * Reads config('artisanpack.hooks.deprecation_level') so the value
     * survives config:cache and works on the modern Laravel default of
     * Env::disablePutenv(). Falls back to $_ENV / getenv() (in that order)
     * when the Laravel config helper is unavailable — keeps the primitive
     * usable outside a booted framework, e.g. in standalone Orchestra
     * Testbench tests.
     *
     * Any unknown value falls back to "info"; "off" suppresses the log.
     *
     * @since 1.3.0
     */
    protected function log(string $old, string $canonical): void
    {
        $level = strtolower((string) $this->configuredLevel());

        if ('off' === $level) {
            return;
        }

        // PSR-3 levels — accept the full set so operators can escalate a
        // deprecation to "error" (say, in CI) without the primitive
        // silently downgrading them.
        $psrLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        if (! in_array($level, $psrLevels, true)) {
            $level = 'info';
        }

        Log::log(
            $level,
            "Hook \"{$old}\" is deprecated; use \"{$canonical}\" instead.",
            ['old' => $old, 'canonical' => $canonical],
        );
    }

    /**
     * Read the configured deprecation level, preferring Laravel's config
     * repository and falling back to raw env access.
     *
     * @since 1.3.0
     */
    protected function configuredLevel(): string
    {
        if (function_exists('config')) {
            $fromConfig = config('artisanpack.hooks.deprecation_level');

            if (null !== $fromConfig && '' !== $fromConfig) {
                return (string) $fromConfig;
            }
        }

        $fromEnv = $_ENV['HOOKS_DEPRECATION_LEVEL']
            ?? $_SERVER['HOOKS_DEPRECATION_LEVEL']
            ?? getenv('HOOKS_DEPRECATION_LEVEL');

        return false === $fromEnv || null === $fromEnv || '' === $fromEnv
            ? 'info'
            : (string) $fromEnv;
    }
}
