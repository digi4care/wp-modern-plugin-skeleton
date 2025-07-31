<?php

declare(strict_types=1);

$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
$slug = basename($composer['name'] ?? 'plugin');

// try default plugin-skeleton.php first for backwards compatibility
$file = __DIR__ . '/plugin-skeleton.php';
if (!file_exists($file)) {
    $file = __DIR__ . '/' . $slug . '.php';
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

$code = file_get_contents($file);
if ($code === false) {
    fwrite(STDERR, "Could not read $file\n");
    exit(1);
}

$pattern = '/(<\?php\s*)(?:\/\*\*.*?\*\/\s*)?/s';
$replacement = '$1' . $header . "\n\n";

$newCode = preg_replace($pattern, $replacement, $code, 1);

if ($newCode === null) {
    fwrite(STDERR, "Regex error while updating header.\n");
    exit(1);
}

file_put_contents($file, $newCode);
echo "Plugin header injected (version: $version)\n";
