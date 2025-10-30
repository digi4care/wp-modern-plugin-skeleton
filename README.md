# WordPress Modern Plugin Skeleton

A professional starting point for building modern WordPress plugins with clean architecture, dependency injection, and modern tooling. This template provides a solid foundation for developing WordPress plugins with a React-based frontend and modern PHP backend.

## ✨ Features

### 🚀 Core Features

- Modern WordPress plugin development with best practices
- Clean architecture with separation of concerns
- Dependency injection for better testability
- Secure WordPress coding standards compliance

### 🎨 Frontend & Blocks

- Modern development with Vite and React
- Gutenberg block development ready
- Hot module replacement for development
- WordPress components and hooks integration

### 🛠️ Development Tools

- Built-in testing and code quality tools
- i18n support out of the box
- Automated build and release process
- Composer for PHP dependency management

## 🚀 Quick Start

### 1. Create a New Plugin

#### Option 1: GitHub Template

1. Click "Use this template" at the top of this repository
2. Name your new repository (e.g., `my-awesome-plugin`)
3. Clone your new repository locally

#### Option 2: Manual Setup

```bash
# Clone this repository (don't fork!)
git clone --depth=1 https://github.com/digi4care/wp-modern-plugin-skeleton.git my-awesome-plugin
cd my-awesome-plugin
rm -rf .git
git init
git add .
git commit -m "Initial commit"
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

1. Update `composer.json` with your plugin details:

    ```json
    {
        "name": "your-vendor/your-plugin-slug",
        "description": "Your plugin description",
        "authors": [{"name": "Your Name", "email": "your.email@example.com"}],
        "extra": {
            "plugin-name": "Your Plugin Name",
            "plugin-uri": "https://your-plugin-url.com",
            "text-domain": "your-plugin-slug"
        }
    }
    ```

2. Initialize the plugin:

    ```bash
    composer init-plugin
    ```

    This will automatically:
    - Create main plugin file with proper headers
    - Set up frontend package.json
    - Configure CI/CD workflow

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

### 1. Development Structure

- PHP code: `/src` directory
- JavaScript/React: `/frontend` directory
- Run `npm run dev` in `/frontend` for live development

### 2. Version Management

Use Composer commands to manage versions:

```bash
composer version:patch  # 0.0.1 → 0.0.2
composer version:minor  # 0.1.0 → 0.2.0
composer version:major  # 1.0.0 → 2.0.0
```

This will update versions in `composer.json`, plugin headers, and create git commits/tags.

### 3. Building for Production

```bash
# Build frontend assets
cd frontend
npm run build
cd ..

# Create distribution
./build/build.sh  # Outputs to dist/
```

### 4. Creating a Release

1. Bump version using above commands
2. Push changes and tags:

   ```bash
   git push && git push --tags
   ```

3. GitHub Actions will create a release automatically

### 5. Frontend Package Management

The `bin/update-frontend-package.php` script keeps frontend configuration in sync. It runs automatically during plugin initialization or manually via:

```bash
php bin/update-frontend-package.php
```

## 🏗️ Project Structure

```text
├── bin/                      # Helper scripts
│   ├── generate-header.php      # Generates plugin header
│   ├── init-plugin.php         # Initializes a new plugin
│   └── update-frontend-package.php # Syncs frontend package.json
│
├── build/                    # Build scripts
│   └── build.sh              # Production build script
│
├── cache/                    # Cache directory
│   └── container/            # Dependency injection container cache
│
├── frontend/                 # Frontend application
│   ├── blocks/               # Gutenberg blocks
│   │   └── example-block/    # Example block
│   │       ├── src/          # Block source files
│   │       │   ├── edit.js   # Block editor component
│   │       │   ├── save.js   # Block frontend component
│   │       │   └── index.js  # Block registration
│   │       └── block.json    # Block configuration
│   │
│   ├── components/           # Reusable React components
│   ├── translations/         # i18n translation files
│   ├── App.jsx               # Root React component
│   ├── main.jsx              # Application entry point
│   ├── index.html            # HTML template
│   ├── index.css             # Global styles
│   └── vite.config.js        # Vite configuration
│
├── src/                      # PHP source code
│   ├── Adapter/              # WordPress adapters
│   ├── Application/          # Application layer
│   ├── Blocks/               # PHP block handling
│   ├── Domain/               # Domain logic
│   └── Infrastructure/       # Infrastructure code
│
├── tests/                    # Test files
│   ├── Unit/                 # Unit tests
│   └── Integration/          # Integration tests
│
├── vendor/                   # Composer dependencies
├── .gitignore               # Git ignore rules
├── composer.json            # PHP dependencies
├── package.json             # Frontend dependencies
└── README.md                # This file
```

## 🧱 Gutenberg Blocks

This plugin includes a modern block development environment with the following features:

- 🚀 Automatic block registration
- ⚡ Lazy loading (only loads when Gutenberg is active)
- 🔄 Hot module replacement in development
- 🎨 Built-in support for React and modern JavaScript

### Block Structure

Each block should be placed in its own directory under `frontend/blocks/` with this structure:

```text
block-name/
├── src/
│   ├── edit.js    # Editor component
│   ├── save.js    # Frontend component
│   ├── index.js   # Block registration
│   └── style.scss # Block styles
└── block.json     # Block configuration
```

### Creating a New Block

1. **Create a new directory** for your block in `frontend/blocks/`

2. **Add a `block.json` file**:

    ```json
    {
        "$schema": "https://schemas.wp.org/trunk/block.json",
        "apiVersion": 3,
        "name": "your-plugin/block-name",
        "title": "Block Name",
        "category": "widgets",
        "icon": "smiley",
        "editorScript": "file:./build/index.js"
    }
    ```

3. **Create the block components**:

    - `edit.js` - Controls the block's appearance in the editor
    - `save.js` - Controls the block's output on the frontend
    - `index.js` - Registers the block

4. **The block will be automatically registered** when Gutenberg is active

### Block Development

```bash
# Start development server with HMR
cd frontend
npm run dev

# Build for production
npm run build
```

### Best Practices

1. **Keep blocks independent** - Each block should work on its own
2. **Use WordPress components** - Leverage `@wordpress/components` when possible
3. **Lazy load assets** - Only load what's needed for each block
4. **Support i18n** - Use `@wordpress/i18n` for translatable strings

### Example Block

See `frontend/blocks/example-block/` for a complete example. You can use this as a starting point for your own blocks.

## 🎨 Frontend Development

The frontend is built using React with Vite. The main entry points are:

- `frontend/main.jsx` - Application entry point
- `frontend/App.jsx` - Root React component
- `frontend/components/` - Reusable React components
- `frontend/translations/` - Translation files

### Development Workflow

1. **Start the development server**:

   ```bash
   cd frontend
   npm run dev
   ```

2. **Build for production**:

   ```bash
   npm run build
   ```

3. **Check for issues**:

   ```bash
   npm run lint    # Check code style
   npm run typecheck  # Type checking
   ```

### Managing Frontend Dependencies

- Add new dependencies using npm:

  ```bash
  cd frontend
  npm install package-name
  ```

- Update dependencies:

  ```bash
  npm update
  ```

### Styling

- Main styles are located in `frontend/index.css`
- Block-specific styles should be placed in their respective block directories
- Uses PostCSS with modern CSS features
- Supports CSS Modules for component-scoped styles

The frontend is built using React with Vite. The main entry points are:

- `frontend/main.jsx` - Application entry point
- `frontend/App.jsx` - Root React component
- `frontend/components/` - Reusable React components
- `frontend/translations/` - Translation files

Run the development server with:

```bash
cd frontend
npm run dev
```

## 🔄 Local Development Workflow

1. **Start the development environment**:

   ```bash
   # Install PHP dependencies
   composer install
   
   # Install frontend dependencies
   cd frontend
   npm install
   
   # Start the development server
   npm run dev
   ```

2. **Running Tests**:

   ```bash
   # Run PHPUnit tests
   composer test
   
   # Run PHPStan (static analysis)
   composer stan
   
   # Check code style
   composer cs
   ```

### Building for Production

1. **Build the frontend assets**:

   ```bash
   cd frontend
   npm run build
   ```

2. **Create a production build**:

   ```bash
   # From the project root
   ./build/build.sh
   ```

   This will create a production-ready build in the `dist/` directory.

### Version Management

Use Composer to manage versions:

```bash
# Bump patch version (0.0.1 → 0.0.2)
composer version:patch

# Bump minor version (0.1.0 → 0.2.0)
composer version:minor

# Bump major version (1.0.0 → 2.0.0)
composer version:major
```

These commands will update the version in `composer.json`, update the plugin header, create a git commit, and tag the release.

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
