---
title: Hook Naming and Deprecations
---

Since 1.3.0, ArtisanPack UI Hooks ships a `HookDeprecations` primitive and a `deprecateHook()` helper for renaming a hook you have already shipped without breaking existing subscribers.

See also: [[Actions]], [[Filters]], and [[Facades]].

## Naming convention

Hook names should be namespaced with dot notation and lower camelCase segments — for example, `order.placed`, `user.registered`, `ap.icons.registerIconSets`.

The `ap.<domain>.<event>` prefix is reserved for cross-package hooks that any ArtisanPack UI package (or downstream app) may listen to. Two of these are intentionally shared today:

- `ap.google.scopes` — filter for augmenting the requested Google OAuth scopes.
- `ap.icons.registerIconSets` — filter for registering additional icon sets.

## Renaming a hook

If you rename a hook, register the old name as an alias so existing subscribers keep firing:

```php
use function deprecateHook;

deprecateHook('order.placed', 'order.created');
```

After that call:

- `addAction('order.placed', $fn)` transparently attaches to `order.created`.
- `doAction('order.placed')` fires every callback bound to either name.
- Any callback registered under `order.placed` **before** the `deprecateHook` call still fires when `order.created` is dispatched.
- A callback registered under **both** names (before *and* after the rename) fires **once**, not twice — the dispatcher deduplicates across canonical + alias buckets by identity. Deliberately double-registering under the *same* name still fires twice (pre-1.3 behavior).
- Insertion order within a priority is preserved regardless of which bucket a callback lives in.
- Cycles are rejected: `deprecateHook('a', 'b')` followed by `deprecateHook('b', 'a')` throws `InvalidArgumentException`.

Both `Action` and `Filter` route `add()`/`remove()`/`removeAll()` through the alias map, so the same rules apply to filters.

## Deprecation logging

The first time each unique alias is resolved during a request, a deprecation notice is logged. Registrations (`add`/`remove`) never log; only dispatch (`do`/`apply`) does, so the notice is anchored to real hook use, not to boot-time registration.

Configure the log level by publishing the config file:

```bash
php artisan vendor:publish --tag=hooks-config
```

Then set `deprecation_level` in `config/artisanpack/hooks.php`, or set `HOOKS_DEPRECATION_LEVEL` in `.env`:

| Value                                                 | Behavior                     |
|-------------------------------------------------------|------------------------------|
| any PSR-3 level (`emergency`…`debug`; default `info`) | log at that level            |
| `off`                                                 | suppress the deprecation log |

## Octane and queue workers

The "log once per unique alias" dedup is scoped per request, not per process. The service provider clears it on Octane `RequestReceived` and Queue `JobProcessing` events so long-lived workers do not swallow every notice after the first request/job.

## Direct access

`HookDeprecations` is container-resolvable via `app(HookDeprecations::class)` if you need direct access to `alias()`, `resolve()`, `resolveSilent()`, `aliasesFor()`, `hasAliases()`, or `resetLogState()`. There is intentionally no dedicated Facade — `deprecateHook()` is the whole public surface.

---
Return to [[Home]].
