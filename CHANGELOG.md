# ArtisanPack UI Hooks Changelog

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

