# WordPress Modern Plugin Skeleton

A professional starting point for building modern WordPress plugins with clean architecture, dependency injection, and modern tooling. This is a template repository - use it as a base for your own WordPress plugins.

## 🚀 Quick Start: Create a New Plugin

### Option 1: Using GitHub Template

1. Click the "Use this template" button at the top of this repository
2. Name your new repository (e.g., `my-awesome-plugin`)
3. Clone your new repository locally

### Option 2: Manual Setup

```bash
# Clone this repository (don't fork it!)
git clone --depth=1 https://github.com/digi4care/wp-modern-plugin-skeleton.git my-awesome-plugin
cd my-awesome-plugin

# Remove the existing git history
rm -rf .git

# Initialize a new git repository
git init
git add .
git commit -m "Initial commit"

# Add your remote repository
git remote add origin https://github.com/your-username/my-awesome-plugin.git
git push -u origin main
```

## 🛠️ Development Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
cd frontend
npm install
```

### 2. Configure Your Plugin

1. **First, update `composer.json`**:

   - Open `composer.json`
   - Update these fields:

     ```json
     {
       "name": "your-vendor/your-plugin-slug",
       "description": "Your plugin description",
       "authors": [
         {
           "name": "Your Name",
           "email": "your.email@example.com"
         }
       ],
       "extra": {
         "plugin-name": "Your Plugin Name",
         "plugin-uri": "https://your-plugin-url.com",
         "text-domain": "your-plugin-slug"
       }
     }
     ```

2. **Run the initialization script**:

   ```bash
   # This will use the values from composer.json to set up everything
   composer init-plugin
   ```

   The script will automatically:
   - Create your main plugin file with proper headers
   - Set up the frontend package.json
   - Configure the CI/CD workflow
   - Generate all necessary configuration files

   You only need to update `composer.json` - all other files will be generated automatically based on these settings.

## 🧰 Available Commands

### Development Commands

#### Testing & Quality Assurance

```bash
# Run all PHPUnit tests
composer test
# - Verifies your code works as expected
# - Tests are located in the 'tests/' directory
# - Creates code coverage reports in 'coverage/'

# Check code style against WordPress Coding Standards
composer cs
# - Validates your code follows WordPress coding standards
# - Helps maintain consistent code style across the project
# - Will show errors and warnings for style violations

# Automatically fix code style issues
composer cbf
# - Fixes most code style issues automatically
# - Safe to run as it only changes formatting, not logic
# - Run this after 'composer cs' to fix reported issues

# Static code analysis with PHPStan
composer stan
# - Analyzes code for potential bugs and issues
# - Checks type safety and finds dead code
# - More thorough than basic syntax checking

# Run all checks (tests, code style, static analysis)
composer check
# - One command to run all quality checks
# - Perfect before committing code
# - Ensures all quality gates pass
```

#### Why WordPress Core in Development?

WordPress core is installed in the `wordpress/` directory for development purposes only. This helps with:

1. **Testing**: Run unit and integration tests without needing a full WordPress installation
2. **Code Completion**: Your IDE can provide better autocompletion and type hints
3. **Dependency Resolution**: Ensures all WordPress functions and classes are available during development
4. **Isolation**: Keeps your development environment self-contained

This is a development-only dependency (in `require-dev` in `composer.json`) and won't be included in your production build.

### Frontend Development

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

## 🔄 Development Workflow

### 1. Development

- Work in `/src` for PHP code
- Work in `/frontend` for JavaScript/React code
- Use `npm run dev` in the frontend directory for live development

### 2. Version Management

#### English

Manage your plugin version easily using Composer commands:

```bash
# Bump patch version (0.0.1 → 0.0.2)
composer version:patch

# Bump minor version (0.1.0 → 0.2.0)
composer version:minor

# Bump major version (1.0.0 → 2.0.0)
composer version:major
```

These commands will:

1. Update the version in `composer.json`
2. Update the version in your plugin header
3. Create a git commit with the version bump
4. Create a git tag for the new version

### 3. Frontend Package Management

The `bin/update-frontend-package.php` script ensures your frontend's `package.json` stays in sync with your plugin's configuration. It automatically:

- Updates the package name to match your plugin name
- Syncs the version number with your plugin version
- Preserves essential frontend dependencies and scripts

#### How It Works

1. **Automatic Execution**: Runs automatically when you initialize a new plugin using `composer init-plugin`
2. **Configuration Preservation**: Maintains critical frontend settings:
   - Build scripts (`dev`, `build`, `preview`)
   - Required dependencies (React, WordPress i18n)
   - Development tooling (Vite, Tailwind CSS)

#### Manual Execution

If you need to update the frontend package manually:

```bash
php bin/update-frontend-package.php
```

This is useful if you've made changes to your plugin's name or version and need to sync these changes to the frontend configuration.

### 4. Building for Production

```bash
# 1. Build frontend assets
cd frontend
npm run build

# 2. Go back to root and create distribution
cd ..
./build/build.sh  # Creates a clean build in dist/
```

### 4. Creating a Release

1. Bump the version (see above)
2. Push your changes and tags:

   ```bash
   git push && git push --tags
   ```

3. GitHub Actions will automatically create a release with the built plugin

### 5. Testing the Build

You can test the built plugin by copying the contents of the `dist/` directory to your WordPress plugins directory.

## 🏗️ Project Structure

```tree
├── bin/                  # Helper scripts
├── build/               # Build scripts and assets
├── frontend/            # Frontend assets (React, CSS, JS)
│   ├── src/             # Frontend source files
│   └── public/          # Compiled frontend assets
├── src/                 # PHP source code
│   ├── Adapter/         # WordPress integrations
│   ├── Application/     # Application services
│   ├── Domain/          # Business logic and entities
│   ├── Infrastructure/  # External services and implementations
│   └── Shared/          # Shared utilities
├── tests/               # PHPUnit tests
├── .github/workflows/   # CI/CD configuration
├── composer.json        # PHP dependencies
└── phpunit.xml         # PHPUnit configuration
```

## 🔄 Continuous Integration

This project includes GitHub Actions workflows that automatically:

- Run tests on every push
- Check code style
- Perform static analysis
- Build the plugin on tags

## 📦 Creating a Release

1. Update the version in `composer.json`
2. Update `CHANGELOG.md`
3. Commit your changes
4. Create a git tag:

   ```bash
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```

5. GitHub Actions will automatically create a release with the built plugin

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and fix any issues
5. Submit a pull request

## 🙏 Credits

This project is based on the [WP Modern Plugin Skeleton](https://github.com/N3XT0R/wp-modern-plugin-skeleton) by [Ilya Beliaev (N3XT0R)](https://github.com/N3XT0R). We're grateful for their work in creating this excellent foundation for WordPress plugin development.

## 📄 License

MIT
