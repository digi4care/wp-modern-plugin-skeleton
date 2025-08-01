<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\ContainerBuilder;
use WP\Skeleton\Infrastructure\WordPress\SettingsRepository;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;
use WP\Skeleton\Shared\Plugin\PluginContext;

use function DI\autowire;

final readonly class InfrastructureContainerConfigurator implements ContainerConfiguratorInterface
{
    public function __construct(private PluginContext $pluginContext)
    {
    }

    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            SettingsRepository::class => autowire(SettingsRepository::class),
            // Other infrastructure services
        ]);
    }
}
