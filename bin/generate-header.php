<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$composer = json_decode(file_get_contents($rootDir . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$slug = basename($composer['name'] ?? 'plugin');

// Determine the plugin file - use the slug-based name
$file = $rootDir . '/' . $slug . '.php';

// If the plugin file doesn't exist yet, we can't update the header
if (!file_exists($file)) {
    fwrite(STDERR, "❌ Plugin file not found: {$file}\n");
    fwrite(STDERR, "💡 Run 'composer init-plugin' first to create the plugin file\n");
    exit(1);
}

// Get version from git tag or use 0.1.0 as fallback
exec('git describe --tags --abbrev=0 2>/dev/null', $out, $exit);
$version = $exit === 0 ? trim($out[0]) : '0.1.0';

echo "🔍 Detected version: {$version}\n";

// Get plugin metadata from composer.json
$extra = $composer['extra'] ?? [];
$authors = $composer['authors'] ?? [];
$author = $authors[0] ?? ['name' => 'Unknown'];

// Build author string with all available author info
$authorString = $author['name'];
if (!empty($author['email'])) {
    $authorString .= ' <' . $author['email'] . '>';
}
if (!empty($author['homepage'])) {
    $authorString .= ' (' . $author['homepage'] . ')';
}

// Build the plugin header
$header = "<?php\n";
$header .= "/**\n";
$header .= ' * Plugin Name:       ' . ($extra['plugin-name'] ?? $composer['name'] ?? 'WP Plugin') . "\n";
$header .= ' * Plugin URI:        ' . ($extra['plugin-uri'] ?? '') . "\n";
$header .= ' * Description:       ' . ($extra['plugin-description'] ?? $composer['description'] ?? '') . "\n";
$header .= ' * Version:           ' . $version . "\n";
$header .= ' * Author:            ' . $authorString . "\n";
$header .= ' * Author URI:        ' . ($author['homepage'] ?? '') . "\n";
$header .= " * License:           GPL-2.0-or-later\n";
$header .= " * License URI:       https://www.gnu.org/licenses/gpl-2.0.html\n";
$header .= ' * Text Domain:       ' . ($extra['text-domain'] ?? $slug) . "\n";
$header .= " * Domain Path:       /languages\n";
$header .= ' * Requires at least: ' . ($extra['minimum-wp-version'] ?? '6.0') . "\n";
$header .= ' * Requires PHP:      ' . ($extra['minimum-php-version'] ?? '8.2') . "\n";
$header .= ' * Tested up to:      ' . ($extra['tested-up-to'] ?? '6.8') . "\n";
$header .= ' * Stable tag:        ' . $version . "\n";
$header .= " */\n";

echo "📝 Updating header in: {$file}\n";

// Read existing content
$code = file_get_contents($file);
if ($code === false) {
    fwrite(STDERR, "❌ Could not read {$file}\n");
    exit(1);
}

// Find where the header ends (after the first comment block)
$lines = explode("\n", $code);
$headerEndIndex = 0;

// Skip the opening <?php line and find the end of the header comment
for ($i = 0; $i < min(20, count($lines)); $i++) {
    if (strpos($lines[$i], '*/') !== false) {
        $headerEndIndex = $i;
        break;
    }
}

// If we found a header, replace everything from start to header end
if ($headerEndIndex > 0) {
    // Keep everything after the header
    $restOfCode = implode("\n", array_slice($lines, $headerEndIndex + 1));
    $newCode = $header . $restOfCode;
} else {
    // No header found, just prepend the new header
    $newCode = $header . $code;
}

// Write the updated content back to the file
if (file_put_contents($file, $newCode) === false) {
    fwrite(STDERR, "❌ Could not write to {$file}\n");
    exit(1);
}

echo "✅ Plugin header successfully updated!\n";

// Verify the version was updated
if (preg_match('/Version:\s*([0-9.]+)/', $newCode, $matches)) {
    echo "✅ Final version in file: {$matches[1]}\n";
}
