# WordPress Modern Plugin Skeleton

A professional, production-ready foundation for building modern WordPress plugins with clean architecture, dependency injection, and modern development workflows. This skeleton implements a complete greeting functionality demo while providing an enterprise-grade structure for your custom plugins.

## 🎯 What This Plugin Does

This plugin demonstrates a complete modern WordPress plugin architecture with:

### ✨ Core Features

- **Greeting System**: Functional greeting shortcodes and REST API endpoints
- **Gutenberg Blocks**: Custom "Greeting Block" for the block editor
- **REST API**: Fully functional API endpoints at `/wp-json/wp-skeleton/v1/greeting`
- **Admin Interface**: Settings page with React-based administration
- **Cron System**: Configurable scheduled tasks system

### 🛠️ Technical Features

- **Clean Architecture**: Hexagonal architecture with proper separation of concerns
- **Dependency Injection**: PHP-DI container for better testability and maintainability
- **Modern Tooling**: Vite, React, PHPStan, PHPUnit, WordPress Coding Standards
- **Development Environment**: Built-in WordPress environment with `@wordpress/env`

## 🚀 Quick Start

### 1. Create Your Plugin

#### Option 1: GitHub Template (Recommended)

1. Click "Use this template" at the top of this repository
2. Name your new repository (e.g., `my-wordpress-plugin`)
3. Clone your new repository locally

#### Option 2: Manual Setup

```bash
# Clone without git history
git clone --depth=1 https://github.com/digi4care/wp-modern-plugin-skeleton.git my-plugin-name
cd my-plugin-name
rm -rf .git
git init
git add .
git commit -m "Initial commit"
```

### 2. Customize Plugin Identity

Before installation, update all references to match your plugin:

```bash
# Update all references from:
# "wp-skeleton" → "your-plugin-name"
# "WP Skeleton" → "Your Plugin Name"
# "wp-modern-plugin-skeleton" → "your-plugin-slug"
```

Update these files with your plugin details:

- `composer.json` - Plugin metadata and dependencies
- `wp-modern-plugin-skeleton.php` - Main plugin file header
- `package.json` - Frontend configuration
- All configuration files in `config/`

### 3. Installation & Setup

```bash
# Install PHP dependencies
composer install

# Install and configure the plugin
composer init-plugin

# Start development environment
npm run dev
```

The `composer init-plugin` command will:

- Create the main plugin file with proper headers
- Set up frontend package.json
- Configure all necessary files with your plugin name

## 🎮 Using the Plugin

### Shortcodes

The plugin provides ready-to-use shortcodes:

**Basic Greeting:**

```php
[skeleton_greet name="John"]
<!-- Output: <div class="wp-skeleton-greeting">Hello, John!</div> -->
```

**Advanced Multi-Greeting:**

```php
[skeleton_greet_advanced names="John, Jane, Bob" separator=" - "]
<!-- Output: <div class="wp-skeleton-advanced-greeting">Hello, John! - Hello, Jane! - Hello, Bob!</div> -->
```

### REST API Endpoints

**Get a greeting:**

```http
GET /wp-json/wp-skeleton/v1/greeting?name=John
```

Response:

```json
{
  "greeting": "Hello, John!",
  "name": "John",
  "timestamp": "2024-01-01 12:00:00"
}
```

**Get greeting by name:**

```http
GET /wp-json/wp-skeleton/v1/greeting/John
```

### Gutenberg Blocks

1. Edit any post or page
2. Click the "+" button to add a block
3. Search for "Greeting Block" in the block inserter
4. Add the block and configure the name in the block settings

### Admin Settings

1. Go to WordPress Admin → Settings → WP Skeleton
2. Configure default settings and enable/disable features
3. Use the React-based advanced settings interface

## 🛠️ Development Commands

### Testing & Quality Assurance

```bash
# Run all tests
composer test

# Check code style (WordPress Coding Standards)
composer cs

# Automatically fix code style issues
composer cbf

# Static analysis with PHPStan
composer stan

# Run all checks (tests, code style, static analysis)
composer check
```

### Frontend Development

```bash
# Start frontend development server
cd frontend
npm run dev

# Build for production
npm run build
```

### Environment Management

```bash
# Start WordPress development environment
npm run start

# Stop environment
npm run stop

# Clean and reset environment
npm run clean
```

## 🏗️ Architecture Overview

This plugin follows clean architecture principles:

### Domain Layer (`src/Domain/`)

- Business logic and entities
- `SampleService.php` - Greeting business logic
- `CronConfiguration.php` - Scheduled tasks configuration

### Application Layer (`src/Application/`)

- Use cases and application logic
- `GreetingApplication.php` - Coordinates greeting operations
- Service layer between domain and presentation

### Infrastructure Layer (`src/Infrastructure/`)

- WordPress-specific implementations
- REST API controllers
- Settings repository
- Block registration

### Adapter Layer (`src/Adapter/`)

- WordPress integration points
- `WordpressPlugin.php` - Main plugin bootstrap
- `WordpressCron.php` - Cron job management

## 📁 Project Structure

```tree
├── .github/                 # GitHub workflows and templates
│   └── workflows/           # CI/CD pipelines
│       └── ci.template.yml  # GitHub Actions workflow
├── bin/                     # Development scripts
│   ├── benchmark.php        # Performance testing
│   ├── clean-files.php      # Cleanup utility
│   ├── generate-header.php  # Plugin header generator
│   └── init-plugin.php      # Plugin initialization
├── build/                   # Build configuration
│   └── build.sh             # Build script
├── cache/                   # Cache directory
│   └── container/           # DI container cache
│       ├── compiled/        # Compiled container files
│       └── proxies/         # Proxy classes
├── config/                  # Configuration files
│   └── di.php              # Dependency injection config
├── dist/                    # Built plugin (generated)
├── docs/                    # Documentation
├── frontend/                # Frontend application
│   ├── blocks/              # Gutenberg blocks
│   │   └── example-block/   # Example block
│   ├── components/          # React components
│   │   └── SettingsPage.jsx # Admin settings page
│   ├── translations/        # i18n files
│   │   ├── de_DE.json
│   │   ├── fr_FR.json
│   │   ├── it_IT.json
│   │   └── en_US.json
│   ├── App.jsx             # Main React component
│   ├── index.js            # Entry point
│   ├── index.html          # HTML template
│   ├── index.css           # Global styles
│   └── vite.config.js      # Vite configuration
├── src/                     # PHP source code
│   ├── Adapter/            # WordPress adapters
│   │   ├── WordpressCron.php
│   │   └── WordpressPlugin.php
│   ├── Application/        # Application layer
│   │   └── DI/             # Dependency injection
│   ├── Blocks/             # Block handling
│   │   └── Greeting/       # Greeting block
│   ├── Domain/             # Domain logic
│   │   ├── Configuration/
│   │   ├── DI/             # Domain DI config
│   │   └── SampleService.php
│   └── Shared/             # Shared components
│       └── Exception/      # Custom exceptions
│           ├── AssetLoadingException.php
│           ├── InvalidCronConfigurationException.php
│           └── InvalidNameException.php
├── tests/                  # Test suite
│   └── Unit/               # Unit tests
│       └── Shared/         # Shared test utilities
├── vendor/                 # Composer dependencies
├── .editorconfig           # Editor configuration
├── .gitignore             # Git ignore rules
├── .phpcs.xml             # PHP_CodeSniffer config
├── .wp-env.json           # WordPress environment config
├── composer.json          # PHP dependencies
├── package.json           # Frontend dependencies
├── phpstan.neon           # PHPStan config
├── phpunit.xml           # PHPUnit config
└── README.md             # This file
```

## 🔧 Customization Guide

### Adding New Features

1. **Domain Service** (Business Logic):

   ```php
   // src/Domain/YourFeatureService.php
   class YourFeatureService {
       public function doSomething(): string {
           return "Your business logic";
       }
   }
   ```

2. **Application Service** (Use Cases):

   ```php
   // src/Application/YourFeatureApplication.php
   class YourFeatureApplication {
       public function __construct(private YourFeatureService $service) {}
       
       public function executeFeature(): string {
           return $this->service->doSomething();
       }
   }
   ```

3. **Register in DI Container**:

   ```php
   // config/di.php
   YourFeatureService::class => \DI\create(YourFeatureService::class),
   YourFeatureApplication::class => \DI\create(YourFeatureApplication::class)
       ->constructor(\DI\get(YourFeatureService::class)),
   ```

4. **Create Shortcode or REST Endpoint** (if needed)

### Adding New Gutenberg Blocks

1. Create block in `frontend/blocks/your-block/`
2. Add `block.json` and React components
3. The block will be automatically registered

## 🚀 Production Deployment

### Building for Production

```bash
# Build frontend assets
cd frontend
npm run build

# Create production build
./build/build.sh
```

The build script creates a production-ready ZIP file in the `dist/` directory.

### Version Management

```bash
# Bump version (updates all files automatically)
composer version:patch  # 0.0.1 → 0.0.2
composer version:minor  # 0.1.0 → 0.2.0  
composer version:major  # 1.0.0 → 2.0.0
```

## 🐛 Troubleshooting

### Common Issues

**Circular Dependency Error:**

- Run `composer dump-autoload`
- Clear DI container cache: delete `cache/container/` directory

**Frontend Not Loading:**

- Run `cd frontend && npm install`
- Check that `npm run build` completed successfully

**Blocks Not Appearing:**

- Ensure Gutenberg is active
- Check browser console for JavaScript errors

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Credits

Built upon the [WP Modern Plugin Skeleton](https://github.com/N3XT0R/wp-modern-plugin-skeleton) by [Ilya Beliaev (N3XT0R)](https://github.com/N3XT0R).
