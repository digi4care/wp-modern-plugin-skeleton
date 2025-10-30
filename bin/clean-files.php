<?php

declare(strict_types=1);

// Simple PHP script to clean up generated files and directories

function removePath(string $path) {
    if (file_exists($path)) {
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            if (rmdir($path)) {
                echo "Removed directory: $path\n";
            }
        } else {
            if (unlink($path)) {
                echo "Removed file: $path\n";
            }
        }
    }
}

// Haal de plugin bestandsnaam op uit composer.json
function getPluginFilenames(): array {
    $composerJson = file_get_contents(__DIR__ . '/../composer.json');
    if ($composerJson === false) {
        die("Error: Could not read composer.json\n");
    }

    $composerData = json_decode($composerJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error: Invalid JSON in composer.json\n");
    }

    // Haal de package naam op (bijv. "example/wp-modern-plugin-skeleton")
    $packageName = $composerData['name'] ?? '';
    // Haal het laatste deel van de package naam (na de laatste /)
    $baseName = basename(str_replace('\\', '/', $packageName));

    // Genereer de bestandsnaam (vervang eventuele ongeldige tekens)
    $generatedName = preg_replace('/[^a-z0-9-]/', '-', strtolower($baseName)) . '.php';

    return [
        $generatedName,  // Bijv. wp-modern-plugin-skeleton.php
        $composerData['extra']['plugin-file'] ?? null  // Eventuele aangepaste bestandsnaam
    ];
}

// Haal alle mogelijke bestandsnamen op
$pluginFilenames = array_filter(getPluginFilenames());

// Files and directories to remove
$filesToRemove = array_merge([
    '.wp-env.json',
    'package.json',
    'package-lock.json',
    'node_modules',
    'vendor',
    '.wp-env'
], $pluginFilenames);

foreach ($filesToRemove as $file) {
    removePath($file);
}

echo "Cleanup complete!\n";
