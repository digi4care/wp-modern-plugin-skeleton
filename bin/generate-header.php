<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$composer = json_decode(file_get_contents($rootDir . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$slug = basename($composer['name'] ?? 'plugin');

// Try default plugin-skeleton.php first for backwards compatibility
$file = $rootDir . '/plugin-skeleton.php';
if (!file_exists($file)) {
    $file = $rootDir . '/' . $slug . '.php';
}

// Get version from git tag or use 0.1.0 as fallback
exec('git describe --tags --abbrev=0 2>/dev/null', $out, $exit);
$version = $exit === 0 ? trim($out[0]) : '0.1.0';

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
$header = [];
$header[] = '/**';
$header[] = ' * Plugin Name:       ' . ($extra['plugin-name'] ?? $composer['name'] ?? 'WP Plugin');
$header[] = ' * Plugin URI:        ' . ($extra['plugin-uri'] ?? '');
$header[] = ' * Description:       ' . ($extra['plugin-description'] ?? $composer['description'] ?? '');
$header[] = ' * Version:           ' . $version;
$header[] = ' * Author:            ' . $authorString;
$header[] = ' * Author URI:        ' . ($author['homepage'] ?? '');
$header[] = ' * License:           GPL-2.0-or-later';
$header[] = ' * License URI:       https://www.gnu.org/licenses/gpl-2.0.html';
$header[] = ' * Text Domain:       ' . ($extra['text-domain'] ?? $slug);
$header[] = ' * Domain Path:       /languages';
$header[] = ' * Requires at least: ' . ($extra['minimum-wp-version'] ?? '6.0');
$header[] = ' * Requires PHP:      ' . ($extra['minimum-php-version'] ?? '8.2');
$header[] = ' * Tested up to:      ' . ($extra['tested-up-to'] ?? '6.8');
$header[] = ' * Stable tag:        ' . $version;
$header[] = ' */';

$header = implode("\n", $header) . "\n";

// If the file doesn't exist, create it
if (!file_exists($file)) {
    $code = "<?php\n" . $header . "\n\n// Your plugin code goes here\n";
    echo "Created new file: $file\n";
} else {
    // File exists, read its contents
    $code = file_get_contents($file);
    if ($code === false) {
        fwrite(STDERR, "Could not read $file\n");
        exit(1);
    }
    
    // Remove existing header
    $code = preg_replace('/^<\?php\s*\/\*\*.*?\*\//s', '', $code);
    
    // Add new header
    $code = "<?php\n" . $header . "\n" . ltrim($code);
    
    echo "Updated header in $file\n";
}

// Write the contents to the file
if (file_put_contents($file, $code) === false) {
    fwrite(STDERR, "Could not write to $file\n");
    exit(1);
}
