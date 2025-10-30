<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$composer = json_decode(file_get_contents($rootDir . '/composer.json'), true);
$slug = basename($composer['name'] ?? 'plugin');

// try default plugin-skeleton.php first for backwards compatibility
$file = $rootDir . '/plugin-skeleton.php';
if (!file_exists($file)) {
    $file = $rootDir . '/' . $slug . '.php';
}

exec('git describe --tags --abbrev=0 2>/dev/null', $out, $exit);
$version = $exit === 0 ? trim($out[0]) : '0.1.0';

$name = $composer['name'] ?? 'wp-skeleton';
$description = $composer['description'] ?? 'Modern WordPress plugin skeleton';
$authorName = $composer['authors'][0]['name'] ?? 'Unknown';

$header = <<<PHP
/**
 * Plugin Name: WP Modern Plugin Skeleton
 * Description: {$description}
 * Version: {$version}
 * Author: {$authorName}
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: wp-modern-skeleton
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 8.2
 * Stable tag: {$version}
 */
PHP;

// Als het bestand niet bestaat, maak het dan aan
if (!file_exists($file)) {
    $code = "<?php\n" . $header . "\n\n// Hier komt je plugin code\n";
    echo "Created new file: $file\n";
} else {
    // Bestand bestaat al, lees de inhoud
    $code = file_get_contents($file);
    if ($code === false) {
        fwrite(STDERR, "Could not read $file\n");
        exit(1);
    }
    
    // Verwijder bestaande header
    $code = preg_replace('/^<\?php\s*\/\*\*.*?\*\/\s*/s', '', $code);
    
    // Voeg nieuwe header toe
    $code = "<?php\n" . $header . "\n\n" . ltrim($code);
    
    echo "Updated header in $file\n";
}

// Schrijf de inhoud naar het bestand
if (file_put_contents($file, $code) === false) {
    fwrite(STDERR, "Could not write to $file\n");
    exit(1);
}
