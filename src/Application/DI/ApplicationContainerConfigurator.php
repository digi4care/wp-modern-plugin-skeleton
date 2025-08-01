<?php

declare(strict_types=1);

namespace WP\Skeleton\Application\DI;

use DI\ContainerBuilder;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;

class ApplicationContainerConfigurator implements ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Application services
        ]);
    }
}
