<?php

declare(strict_types=1);

namespace N3XT0R\XPub\Adapter;

use WP\Skeleton\Infrastructure\DI\ContainerProvider;
use WP\Skeleton\Shared\Plugin\PluginContext;

final class WordpressPlugin
{
    private function __construct()
    {
    }

    public static function init(string $pluginFile): void
    {
        ContainerProvider::setPluginContext(
            new PluginContext(
                $pluginFile,
                'wp-modern-plugin-skeleton',
                'https://example.com'
            )
        );

        // Build the container. Services can now be fetched via ContainerProvider
        ContainerProvider::getContainer();
    }
}
