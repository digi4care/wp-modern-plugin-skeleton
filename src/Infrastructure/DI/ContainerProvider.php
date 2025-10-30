<?php

/**
 * Dependency Injection Container Provider
 *
 * This class is responsible for managing the dependency injection container
 * and its configuration. It follows the singleton pattern to ensure only one
 * container instance exists throughout the application.
 * 
 * @package WP\Skeleton\Infrastructure\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\Container;
use DI\ContainerBuilder;
use RuntimeException;
use WP\Skeleton\Application\DI\ApplicationContainerConfigurator;
use WP\Skeleton\Domain\DI\DomainContainerConfigurator;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;
use WP\Skeleton\Shared\Plugin\PluginContext;

use function DI\value;

/**
 * Manages the dependency injection container for the plugin
 * 
 * This class follows the singleton pattern to ensure only one container instance
 * exists throughout the application. It handles the configuration and building
 * of the DI container.
 */
final class ContainerProvider
{
    /** @var Container|null The container instance */
    private static ?Container $container = null;
    
    /** @var PluginContext|null The plugin context */
    private static ?PluginContext $plugin_context = null;

    /** 
     * @var array<ContainerConfiguratorInterface> Custom container configurators
     */
    private static array $custom_configurators = [];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Add a custom container configurator
     * 
     * @param ContainerConfiguratorInterface $configurator The configurator to add
     * @return void
     */
    public static function add_configurator(ContainerConfiguratorInterface $configurator): void
    {
        if (self::$container !== null) {
            throw new RuntimeException('Cannot add configurator after container is built');
        }
        
        self::$custom_configurators[] = $configurator;
    }

    /**
     * Set the plugin context
     * 
     * @param PluginContext $context The plugin context
     * @return void
     * 
     * @throws RuntimeException If the container is already built
     */
    public static function set_plugin_context(PluginContext $context): void
    {
        if (self::$container !== null) {
            throw new RuntimeException('Cannot set context after container is built');
        }
        
        self::$plugin_context = $context;
    }

    /**
     * Get the container instance
     * 
     * @return Container The container instance
     * 
     * @throws RuntimeException If the plugin context is not set
     */
    public static function get_container(): Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        if (self::$plugin_context === null) {
            throw new RuntimeException('Plugin context must be set before building the container.');
        }

        $builder = new ContainerBuilder();
        self::configure_builder($builder);

        try {
            self::$container = $builder->build();
            return self::$container;
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to build the container: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Configure the container builder
     */
    private static function configure_builder(ContainerBuilder $builder): void
    {
        $env = function_exists('wp_get_environment_type')
            ? wp_get_environment_type()
            : 'production';

        $cache_dir = self::get_cache_dir();

        // Enable caching and proxy generation in production environments
        if (!in_array($env, ['local', 'development'], true)) {
            self::ensure_cache_directory($cache_dir);
            $builder->writeProxiesToFile(true, $cache_dir . '/proxies');
            $builder->enableCompilation($cache_dir . '/compiled');
        }

        // Enable autowiring and annotations
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);

        self::configure_container($builder);
    }

    /**
     * Get cache directory path
     */
    private static function get_cache_dir(): string
    {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . 'wp-skeleton/cache/container';
    }

    /**
     * Ensure cache directory exists
     */
    private static function ensure_cache_directory(string $cache_dir): void
    {
        $directories = [
            $cache_dir,
            $cache_dir . '/proxies',
            $cache_dir . '/compiled'
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir) && !wp_mkdir_p($dir)) {
                throw new RuntimeException(sprintf('Could not create cache directory: %s', $dir));
            }
        }
    }

    /**
     * Configure the container with all required services
     */
    private static function configure_container(ContainerBuilder $builder): void
    {
        if (self::$plugin_context === null) {
            throw new RuntimeException('Plugin context must be set before configuring the container.');
        }

        // Add the plugin context to the container
        $builder->addDefinitions([
            PluginContext::class => value(self::$plugin_context),
        ]);

        // Register all container configurators
        $configurators = [
            new ApplicationContainerConfigurator(),
            new DomainContainerConfigurator(),
            new InfrastructureContainerConfigurator(self::$plugin_context),
            ...self::$custom_configurators,
        ];

        // Apply each configurator
        foreach ($configurators as $configurator) {
            try {
                $configurator->configure($builder);
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    sprintf('Failed to configure container with %s: %s', 
                        get_class($configurator), 
                        $e->getMessage()
                    ), 
                    0, 
                    $e
                );
            }
        }
    }

    /**
     * Check if container is built
     */
    public static function is_built(): bool
    {
        return self::$container !== null;
    }

    /**
     * Reset the container (mainly for testing)
     */
    public static function reset(): void
    {
        self::$container = null;
        self::$plugin_context = null;
        self::$custom_configurators = [];
    }
}