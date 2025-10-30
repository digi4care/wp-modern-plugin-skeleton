#!/usr/bin/env php
<?php

declare(strict_types=1);

$composerJsonPath = __DIR__ . '/../composer.json';
$packageJsonPath = __DIR__ . '/../frontend/package.json';

// Read composer.json
if (!file_exists($composerJsonPath)) {
    fwrite(STDERR, "composer.json not found.\n");
    exit(1);
}

$composerData = json_decode(file_get_contents($composerJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "Invalid composer.json: " . json_last_error_msg() . "\n");
    exit(1);
}

// Read package.json
if (!file_exists($packageJsonPath)) {
    fwrite(STDERR, "frontend/package.json not found.\n");
    exit(1);
}

$packageData = json_decode(file_get_contents($packageJsonPath), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "Invalid package.json: " . json_last_error_msg() . "\n");
    exit(1);
}

// Extract plugin slug from composer name (vendor/name -> name)
$pluginSlug = basename($composerData['name']);

// Update package.json with data from composer.json
$packageData['name'] = $pluginSlug . '-frontend';
$packageData['version'] = $composerData['version'] ?? '1.0.0';

// Add metadata from composer.json
$packageData['description'] = $composerData['description'] ?? '';

// Add complete author information
if (!empty($composerData['authors'][0])) {
    $author = $composerData['authors'][0];
    $packageData['author'] = [
        'name' => $author['name'] ?? '',
        'email' => $author['email'] ?? null,
        'url' => $author['homepage'] ?? null
    ];
    
    // Remove null values to keep the output clean
    $packageData['author'] = array_filter($packageData['author'], function($value) {
        return $value !== null;
    });
    
    // Convert to string if it's just the name (for backward compatibility)
    if (count($packageData['author']) === 1 && isset($packageData['author']['name'])) {
        $packageData['author'] = $packageData['author']['name'];
    }
}

// Add license information
if (isset($composerData['license'])) {
    $packageData['license'] = is_array($composerData['license']) 
        ? $composerData['license'][0] 
        : $composerData['license'];
}

// Add repository info if available
if (isset($composerData['homepage'])) {
    $packageData['homepage'] = $composerData['homepage'];
    $packageData['bugs'] = [
        'url' => $composerData['homepage'] . '/issues'
    ];
    $packageData['repository'] = [
        'type' => 'git',
        'url' => $composerData['homepage'] . '.git'
    ];
}

// Preserve essential configuration
$preservedConfig = [
    'scripts' => [
        'dev' => 'vite',
        'build' => 'vite build',
        'preview' => 'vite preview'
    ],
    'dependencies' => [
        '@wordpress/i18n' => '6.7.0',
        'react' => '18.3.0',
        'react-dom' => '18.3.0'
    ],
    'devDependencies' => [
        '@babel/preset-react' => '^7.28.5',
        '@tailwindcss/vite' => '^4.1.16',
        '@vitejs/plugin-react' => '^5.1.0',
        'tailwindcss' => '^4.1.16',
        'vite' => '^7.1.12'
    ]
];

// Merge preserved config with existing package.json
$packageData = array_merge($packageData, $preservedConfig);

// Define the standard property order
$standardOrder = [
    'name',
    'version',
    'private',
    'type',
    'description',
    'main',
    'module',
    'types',
    'files',
    'scripts',
    'dependencies',
    'devDependencies',
    'peerDependencies',
    'browserslist',
    'author',
    'license',
    'repository',
    'bugs',
    'homepage',
    'keywords',
    'engines',
    'publishConfig',
    'prettier',
    'eslintConfig',
    'babel',
    'jest',
    'husky',
    'lint-staged',
    'config'
];

// Sort the package data according to standard order
$sortedData = [];
$customKeys = [];

// First add all standard keys in order
foreach ($standardOrder as $key) {
    if (isset($packageData[$key])) {
        $sortedData[$key] = $packageData[$key];
    }
}

// Then add any remaining custom keys in their original order
foreach ($packageData as $key => $value) {
    if (!in_array($key, $standardOrder)) {
        $customKeys[$key] = $value;
    }
}

// Merge standard and custom keys
$sortedData = array_merge($sortedData, $customKeys);

// Write updated package.json back with sorted properties
file_put_contents(
    $packageJsonPath,
    json_encode($sortedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

echo "✅ Updated frontend/package.json with project settings\n";
