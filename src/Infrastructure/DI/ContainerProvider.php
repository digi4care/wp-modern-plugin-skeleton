<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\Container;
use DI\ContainerBuilder;
use WP\Skeleton\Application\DI\ApplicationContainerConfigurator;
use WP\Skeleton\Domain\DI\DomainContainerConfigurator;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;
use WP\Skeleton\Shared\Plugin\PluginContext;

use function DI\value;

final class ContainerProvider
{
    private static ?Container $container = null;
    private static ?PluginContext $pluginContext = null;

    /** @var array<ContainerConfiguratorInterface> */
    private static array $customConfigurators = [];

    public static function addConfigurator(ContainerConfiguratorInterface $configurator): void
    {
        self::$customConfigurators[] = $configurator;
    }

    public static function setPluginContext(PluginContext $context): void
    {
        self::$pluginContext = $context;
    }

    public static function getContainer(): Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        if (self::$pluginContext === null) {
            throw new \RuntimeException('Plugin context must be set before building the container.');
        }

        $projectRoot = dirname(__DIR__, 3);
        $cacheDir = $projectRoot.'/cache/container';

        $builder = new ContainerBuilder();

        $env = function_exists('wp_get_environment_type')
            ? wp_get_environment_type()
            : 'production';

        if (!in_array($env, ['local', 'development'], true)) {
            $builder->writeProxiesToFile(true, $cacheDir.'/proxies');
            $builder->enableCompilation($cacheDir.'/compiled');
        }

        self::configure($builder);

        self::$container = $builder->build();
        return self::$container;
    }

    private static function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            PluginContext::class => value(self::$pluginContext),
        ]);

        $configurators = [
            new ApplicationContainerConfigurator(),
            new DomainContainerConfigurator(),
            new InfrastructureContainerConfigurator(self::$pluginContext),
            ...self::$customConfigurators,
        ];

        foreach ($configurators as $configurator) {
            $configurator->configure($builder);
        }
    }
}
