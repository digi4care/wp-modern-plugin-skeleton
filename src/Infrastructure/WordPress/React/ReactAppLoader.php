<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\React;

use WP\Skeleton\Shared\Plugin\PluginContext;

final class ReactAppLoader
{
    public function __construct(
        private PluginContext $pluginContext,
    ) {
    }

    public function load(string $scriptName, string $jsVarName, array $dataToInject): void
    {
        // Skeleton: implement asset loading for the React application.
    }
}
