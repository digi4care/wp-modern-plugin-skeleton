# WP Modern Plugin Skeleton

This repository provides a starting point for building modern WordPress plugins with a clean architecture and dependency injection. It also includes a modern JavaScript frontend and utilities for scaffolding and packaging the plugin.

## Features

- Hexagonal architecture structure (`Adapter`, `Application`, `Domain`, `Infrastructure`, `Shared`)
- Modular dependency injection container using `ContainerProvider` and
  `*ContainerConfigurator` classes powered by [PHP-DI](https://php-di.org/)
- PSR-4 autoloading via Composer
- React-based frontend located in the `frontend/` directory using Vite and Tailwind CSS
- WordPress i18n integration for JavaScript via `frontend/i18n-loader.js`
- Script `bin/init-plugin.php` to bootstrap the plugin file and CI workflow
- GitHub Actions workflow for tests
- PHPUnit test setup with code coverage
- Build script for packaging the plugin
- Automatic plugin header generator (`generate-header.php`)

## Usage

1. Install PHP dependencies
   ```bash
   composer install
   ```
2. Initialize a plugin file and CI workflow (optional)
   ```bash
   composer init-plugin
   ```
3. Run tests
   ```bash
   composer test
   ```
4. Build the JavaScript frontend
   ```bash
   cd frontend
   npm install
   npm run build     # or npm run dev for development
   ```
5. Generate plugin header
   ```bash
   composer generate-header
   ```
6. Package plugin for distribution
   ```bash
   ./build/build.sh
   ```
