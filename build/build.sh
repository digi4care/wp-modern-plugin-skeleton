#!/bin/bash
set -e

# Haal plugin gegevens op
PLUGIN_NAME=$(composer config -f composer.json extra.plugin-name 2>/dev/null || basename "$PWD")
PLUGIN_SLUG=$(composer config -f composer.json extra.text-domain 2>/dev/null || basename "$PWD" | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr -cd '[:alnum:]_-')
DIST_DIR=dist
PLUGIN_DIR="$DIST_DIR/$PLUGIN_SLUG"

# Maak een schone dist map
rm -rf "$DIST_DIR"
mkdir -p "$PLUGIN_DIR"

echo "📦 Maakt een schone kopie van de plugin in $PLUGIN_DIR..."

# Maak de plugin map aan
mkdir -p "$PLUGIN_DIR"

# Kopieer de src map (verplicht)
if [ -d "src" ]; then
    cp -r src/ "$PLUGIN_DIR/"
else
    echo "⚠️  Waarschuwing: Geen src/ map gevonden!"
fi

# Kopieer de frontend (optioneel)
if [ -d "frontend/dist" ]; then
    mkdir -p "$PLUGIN_DIR/assets"
    cp -r frontend/dist/* "$PLUGIN_DIR/assets/"
elif [ -d "frontend/public" ]; then
    mkdir -p "$PLUGIN_DIR/assets"
    cp -r frontend/public/* "$PLUGIN_DIR/assets/"
fi

# Kopieer individuele bestanden die in de root moeten komen
[ -f "composer.json" ] && cp composer.json "$PLUGIN_DIR/"
[ -f "*.php" ] && cp *.php "$PLUGIN_DIR/" 2>/dev/null || true  # Negeer fouten als er geen PHP bestanden zijn

# Kopieer README.md als die bestaat
[ -f "README.md" ] && cp README.md "$PLUGIN_DIR/"

# Verwijder eventuele .gitkeep bestanden
find "$PLUGIN_DIR" -name '.gitkeep' -delete

echo "✅ Klaar! De plugin is klaar in: $PLUGIN_DIR"
echo "📦 GitHub Actions zorgt voor het maken van een zip-bestand bij een release"
