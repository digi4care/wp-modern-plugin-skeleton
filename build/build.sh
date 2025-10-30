#!/bin/bash
set -e

# Get plugin information
PLUGIN_NAME=$(composer config -f composer.json extra.plugin-name 2>/dev/null || basename "$PWD")
PLUGIN_SLUG=$(composer config -f composer.json extra.text-domain 2>/dev/null || basename "$PWD" | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr -cd '[:alnum:]_-')
DIST_DIR=dist
PLUGIN_DIR="$DIST_DIR/$PLUGIN_SLUG"
MAIN_PLUGIN_FILE="$PLUGIN_SLUG.php"

# Check if main plugin file exists
if [ ! -f "$MAIN_PLUGIN_FILE" ]; then
    echo "❌ Error: Main plugin file '$MAIN_PLUGIN_FILE' not found in the root directory!"
    echo "Please run 'composer init-plugin' to initialize the project first."
    exit 1
fi

# Create clean dist directory
rm -rf "$DIST_DIR"
mkdir -p "$PLUGIN_DIR"

echo "📦 Creating a clean copy of the plugin in $PLUGIN_DIR..."

# Create plugin directory structure
mkdir -p "$PLUGIN_DIR"

# Copy src directory (required)
if [ -d "src" ]; then
    cp -r src/ "$PLUGIN_DIR/"
else
    echo "⚠️  Warning: No src/ directory found!"
fi

# Copy frontend assets (optional)
if [ -d "frontend/dist" ]; then
    mkdir -p "$PLUGIN_DIR/assets"
    cp -r frontend/dist/* "$PLUGIN_DIR/assets/"
elif [ -d "frontend/public" ]; then
    mkdir -p "$PLUGIN_DIR/assets"
    cp -r frontend/public/* "$PLUGIN_DIR/assets/"
fi

# Copy individual root files
[ -f "composer.json" ] && cp composer.json "$PLUGIN_DIR/"
[ -f "*.php" ] && cp *.php "$PLUGIN_DIR/" 2>/dev/null || true  # Ignore if no PHP files exist

# Copy README.md if it exists
[ -f "README.md" ] && cp README.md "$PLUGIN_DIR/"

# Remove any .gitkeep files
find "$PLUGIN_DIR" -name '.gitkeep' -delete

echo "✅ Done! Plugin is ready in: $PLUGIN_DIR"
echo "📦 GitHub Actions will create a zip file when you create a release"
