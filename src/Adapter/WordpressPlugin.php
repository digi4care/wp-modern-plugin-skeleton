<?php

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use WP\Skeleton\Infrastructure\DI\ContainerProvider;
use WP\Skeleton\Shared\Plugin\PluginContext;

use DI\Container;

final class WordpressPlugin
{
    private static ?Container $container = null;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    private static function container(): Container
    {
        if (self::$container === null) {
            throw new \RuntimeException('Container not initialized');
        }

        return self::$container;
    }

    public static function init(string $pluginFile): void
    {
        self::container();
        //do some things
    }
}
