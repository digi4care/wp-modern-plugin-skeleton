#!/usr/bin/env php
<?php
declare(strict_types=1);

$template = __DIR__ . '/../plugin-skeleton.php.dist';
if (!file_exists($template)) {
    fwrite(STDERR, "Template $template not found.\n");
    exit(1);
}

// Get plugin slug from composer.json
$composerJsonPath = __DIR__ . '/../composer.json';
if (!file_exists($composerJsonPath)) {
    fwrite(STDERR, "composer.json not found.\n");
    exit(1);
}

$composerData = json_decode(file_get_contents($composerJsonPath), true, 512, JSON_THROW_ON_ERROR);

if (!isset($composerData['name'])) {
    fwrite(STDERR, "No 'name' field found in composer.json\n");
    exit(1);
}

// Extract slug from vendor/name format (e.g., 'example/wp-modern-plugin-skeleton' -> 'wp-modern-plugin-skeleton')
$slug = basename($composerData['name']);
echo "Using plugin slug: $slug\n";

$pluginFile = __DIR__ . '/../' . $slug . '.php';
if (file_exists($pluginFile)) {
    fwrite(STDERR, "File $pluginFile already exists.\n");
    exit(1);
}

// Create the plugin file from template
if (!copy($template, $pluginFile)) {
    fwrite(STDERR, "Failed to create plugin file.\n");
    exit(1);
}

// Now generate the header using the generate-header script
require __DIR__ . '/generate-header.php';

echo "Created plugin file $pluginFile\n";

$workflowTemplate = __DIR__ . '/../.github/workflows/ci.template.yml';
if (file_exists($workflowTemplate)) {
    $workflow = file_get_contents($workflowTemplate);
    $workflow = str_replace(
        ['xpub-multi-channel-publisher', 'xpub'],
        [$slug, $slug],
        $workflow
    );
    $workflowDir = __DIR__ . '/../.github/workflows';
    if (!is_dir($workflowDir)) {
        mkdir($workflowDir, 0777, true);
    }
    file_put_contents($workflowDir . '/ci.yml', $workflow);
    echo "Generated workflow at .github/workflows/ci.yml\n";
} else {
    fwrite(STDERR, "Workflow template not found.\n");
}

