# ArtisanPack UI Hooks Changelog

## [1.3.0] - July 20, 2026

### Added
- `HookDeprecations` primitive for backwards-compatible hook renaming
  - `deprecateHook(string $old, string $new): void` helper registers a rename in one line
  - `Action` and `Filter` transparently route `add()`/`remove()`/`removeAll()` through the alias map
  - `do()` / `apply()` fan out across canonical and alias buckets so pre-deprecation subscribers still fire
  - Callbacks registered under both the old and canonical names are de-duplicated by identity within a single dispatch (closures, invokable objects, `[$obj, 'method']`, `[Class::class, 'staticMethod']`, and string function names all key correctly). Deliberate double-registrations within a single bucket still fire twice
  - Stable insertion-order tie-break within a priority is preserved via a monotonic sequence counter, even when a callback is routed through an alias bucket
  - Cycles are rejected: `deprecateHook('a', 'b')` followed by `deprecateHook('b', 'a')` throws `InvalidArgumentException` instead of silently creating a `b → b` self-alias
  - Deprecation notices log only on dispatch (`do`/`apply`), never on `add`/`remove` — so the log entry is anchored to real hook use, not to boot-time registration
  - Log gate reads from the published `config/artisanpack/hooks.php` (`deprecation_level` key), falling back to `HOOKS_DEPRECATION_LEVEL` env for zero-config use. Any PSR-3 level (`emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`) is accepted; `off` suppresses the log
  - "Log once per unique alias" is scoped per request, not per process: the service provider resets the dedup on Octane `RequestReceived` and Queue `JobProcessing` so long-lived workers do not swallow every notice after the first request/job
  - `HookDeprecations` is container-resolvable via `app(HookDeprecations::class)` — no new Facade or helper surface beyond `deprecateHook()`
- Config file at `config/hooks.php`, publishable via `php artisan vendor:publish --tag=hooks-config`
- `src/Concerns/ManagesHookBuckets.php` trait shared by `Action` and `Filter`, so bucket / alias / dedup / removal logic lives in one place instead of duplicated across both classes
- README section documenting the naming convention and shared cross-package hooks (`ap.google.scopes`, `ap.icons.registerIconSets`)

## [1.2.1] - June 8, 2026

### Added
- Laravel 13 support
  - `illuminate/support` constraint now accepts `^13.0`
  - PHP 8.3+ is required when installing on Laravel 13 (enforced by Laravel itself; PHP 8.2 remains supported on Laravel 11/12)

### Changed
- Aligned `illuminate/support` constraint to `^11.0|^12.0|^13.0` to match the actively tested matrix
- Updated supported Laravel versions in README to 11.x, 12.x, and 13.x

### Removed
- Dropped explicit declaration of support for Laravel < 11
  - The previous `>=5.3` constraint was overly permissive; the package was only ever tested against Laravel 10/11. Users on Laravel 10 or earlier should pin to `^1.2.0`.

## [1.2.0] - November 22, 2025

### Added
- Laravel Boost AI guidelines integration
  - Added `resources/boost/guidelines/core.blade.php` for AI-assisted development
  - Comprehensive package documentation for Laravel Boost users
  - Code examples for actions, filters, priorities, and Blade directives
- Laravel Pint code style support
  - Added `laravel/pint` package for PHP code formatting
  - Added `artisanpack-ui/code-style-pint` package for ArtisanPackUI preset
  - Added `pint-setup.php` script for generating Pint configuration
  - Added `pint.json` configuration with ArtisanPackUI coding standards
- GitLab CI Pint code style job
  - Automated code style checking using Pint in CI pipeline
  - Configured as non-blocking (allow_failure: true)

### Changed
- GitLab CI configuration standardized to PHP 8.4
  - Updated build stage to use PHP 8.4 with Composer
  - Updated test stage to use PHP 8.4
  - Updated code-style stage to use PHP 8.4
  - Added installation of git, unzip, and PHP zip extension in build stage
  - Ensures consistent PHP version across all CI jobs
- Updated Symfony dependencies to v7.3.7 (security update)
  - Fixed security advisory PKSA-365x-2zjk-pt47 in symfony/http-foundation

### Development
- Improved CI/CD pipeline reliability
- Enhanced code quality tools with Pint integration

## [1.1.0] - October 17, 2025

### Added
- Action removal APIs:
  - `Action::remove(string $hook, callable $callback, int $priority = 10): bool`
  - `Action::removeAll(string $hook, int|false $priority = false): bool`
- Filter removal APIs:
  - `Filter::remove(string $hook, callable $callback, int $priority = 10): bool`
  - `Filter::removeAll(string $hook, int|false $priority = false): bool`
- Helper functions:
  - `removeAction()`, `removeAllActions()`, `removeFilter()`, `removeAllFilters()`
- Facade methods documented: `Action::remove()`, `Action::removeAll()`, `Filter::remove()`, `Filter::removeAll()`
- Unit tests for removal behaviors
- Documentation updates with examples and API references

## [1.0.0] - October 16, 2025

### Added
- Initial release of ArtisanPack UI Hooks package
- WordPress-style actions and filters system for Laravel applications
- Action hooks management with priority-based execution
  - `Action` class for registering and executing action hooks
  - Support for callback priorities (lower numbers execute first)
- Filter system for value transformation through callback chains
  - `Filter` class for registering and applying filters
  - Chained value processing with multiple callbacks
- Helper functions for easy integration:
  - `addAction(string $hook, callable $callback, int $priority = 10)` - Register action callbacks
  - `doAction(string $hook, mixed ...$args)` - Execute action callbacks
  - `addFilter(string $hook, callable $callback, int $priority = 10)` - Register filter callbacks
  - `applyFilters(string $hook, mixed $value, mixed ...$args)` - Apply filter callbacks
- Laravel Facades for static access:
  - `Action` facade for action management
  - `Filter` facade for filter management
- Blade directives for template integration:
  - `@action('hook_name', $args...)` - Execute actions in Blade views
  - `@filter('hook_name', $value, $args...)` - Apply filters and echo results in Blade views
- Service providers with automatic Laravel package discovery:
  - `HooksServiceProvider` - Registers Action and Filter singletons
  - `BladeDirectiveServiceProvider` - Registers custom Blade directives
- Automatic facade alias registration for `Action` and `Filter`
- PSR-4 autoloading with namespace `ArtisanPackUI\Hooks\`
- Comprehensive test suite using Pest testing framework
- Full documentation with examples and usage guidelines

### Requirements
- PHP 8.2 or higher
- Laravel 5.3+ (tested with Laravel 10.x and 11.x)
- Illuminate/Support package

### Features
- Priority-based callback execution (lower numbers run first)
- Modular architecture supporting plugin-style extensions
- Clean separation of concerns for maintainable code
- Framework-native Laravel integration
- MIT license for open source usage

