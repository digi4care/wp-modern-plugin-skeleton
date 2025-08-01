<?php

declare(strict_types=1);

namespace WP\Skeleton\Shared\DI;

use DI\ContainerBuilder;

interface ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void;
}
