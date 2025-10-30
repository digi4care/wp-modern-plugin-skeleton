<?php

/**
 * Application Layer Container Configurator
 *
 * This class is responsible for configuring the dependency injection container
 * with application layer services and their dependencies.
 *
 * @package WP\Skeleton\Application\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Application\DI;

use DI\Container;
use DI\ContainerBuilder;
use WP\Skeleton\Application\GreetingApplication;
use WP\Skeleton\Domain\SampleService;
use WP\Skeleton\Domain\Configuration\CronConfiguration;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;
use WP\Skeleton\Shared\Configuration\PluginConfig;
use WP\Skeleton\Blocks\BlocksLoader;

/**
 * Configures the dependency injection container with application layer services
 */
final class ApplicationContainerConfigurator implements ContainerConfiguratorInterface
{
    /**
     * Configure the container with application layer services
     *
     * @param ContainerBuilder $builder The container builder instance
     * @return void
     *
     * @throws \InvalidArgumentException If a service configuration is invalid
     */
    public function configure(ContainerBuilder $builder): void
    {
        /** @var array<string, mixed> $definitions */
        $definitions = [
            // Application services
            GreetingApplication::class => function (Container $container) {
                return new GreetingApplication(
                    $container->get(SampleService::class)
                );
            },

            // Blocks loader with dependency injection
            BlocksLoader::class => \DI\autowire(),

            // Configuration services
            CronConfiguration::class => \DI\autowire(),

            // Plugin configuration
            PluginConfig::class => function (Container $container) {
                return new PluginConfig(
                    '1.0.0',
                    $this->getEnvironment(),
                    $this->isDebug(),
                    $container->get('infrastructure.config')['plugin_dir'] ?? '',
                    $container->get('infrastructure.config')['plugin_url'] ?? ''
                );
            },

            // Configuration values for application layer
            'app.config' => [
                'version' => '1.0.0',
                'environment' => $this->getEnvironment(),
                'debug' => $this->isDebug(),
            ],
        ];

        $builder->addDefinitions($definitions);

        // Enable auto-wiring for application services
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);

        // Configure compilation settings for production
        if ($this->isProduction()) {
            $builder->enableCompilation(
                $this->getCacheDir(),
                'ApplicationCompiledContainer'
            );
        }
    }

    /**
     * Check if the application is running in production mode
     *
     * @return bool True if in production, false otherwise
     */
    private function isProduction(): bool
    {
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type() === 'production';
        }

        return !(defined('WP_DEBUG') && WP_DEBUG);
    }

    /**
     * Check if debug mode is enabled
     */
    private function isDebug(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    /**
     * Get the current environment
     */
    private function getEnvironment(): string
    {
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        return $this->isDebug() ? 'development' : 'production';
    }

    /**
     * Get the cache directory path
     *
     * @return string The absolute path to the cache directory
     */
    private function getCacheDir(): string
    {
        $uploadDir = wp_upload_dir();
        $cacheDir = trailingslashit($uploadDir['basedir']) . 'wp-skeleton/cache/application';

        // Create the directory if it doesn't exist
        if (!file_exists($cacheDir)) {
            wp_mkdir_p($cacheDir);
        }

        return $cacheDir;
    }
}
