<?php

declare(strict_types=1);

namespace WP\Skeleton\Domain\DI;

use DI\ContainerBuilder;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;

class DomainContainerConfigurator implements ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            // Domain services
        ]);
    }
}
