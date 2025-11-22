# ArtisanPack UI Hooks Changelog

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

