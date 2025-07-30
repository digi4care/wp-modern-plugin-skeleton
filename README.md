# WP Modern Plugin Skeleton

This repository provides a starting point for building modern WordPress plugins with a clean architecture and dependency injection.

## Features

- Hexagonal architecture structure (`Domain`, `Application`, `Infrastructure`)
- PHP-DI container configuration (`config/di.php`)
- PSR-4 autoloading via Composer
- GitHub Actions workflow for tests
- PHPUnit test setup with code coverage
- Build script for packaging the plugin
- Automatic plugin header generator (`generate-header.php`)

## Usage

1. Install dependencies
   ```bash
   composer install
   ```
2. Run tests
   ```bash
   composer test
   ```
3. Generate plugin header
   ```bash
   composer generate-header
   ```
4. Package plugin
   ```bash
   ./build/build.sh
   ```
