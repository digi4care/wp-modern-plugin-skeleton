<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\Container;
use DI\ContainerBuilder;

final class ContainerProvider
{
    private static ?Container $container = null;

    public static function getContainer(): Container
    {
        if (self::$container === null) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions(dirname(__DIR__, 3) . '/config/di.php');
            self::$container = $builder->build();
        }

        return self::$container;
    }
}
