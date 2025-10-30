<?php

/**
 * Infrastructure Layer Container Configuration
 *
 * Configures dependency injection for infrastructure layer services.
 * This includes external services, database connections, caching,
 * and other infrastructure concerns.
 *
 * @package WP\Skeleton\Infrastructure\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WP\Skeleton\Domain\Repositories\SettingsRepositoryInterface;
use WP\Skeleton\Infrastructure\WordPress\SettingsRepository;
use WP\Skeleton\Infrastructure\WordPress\Api\GreetingController;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;
use WP\Skeleton\Shared\Plugin\PluginContext;

use function DI\autowire;
use function DI\get;

/**
 * Configures the dependency injection container for the Infrastructure layer
 *
 * This configurator is responsible for setting up infrastructure services
 * and their dependencies, including WordPress-specific services, database
 * connections, caching, and other infrastructure concerns.
 */
final class InfrastructureContainerConfigurator implements ContainerConfiguratorInterface
{
    /**
     * @var PluginContext The plugin context containing runtime information
     */
    private PluginContext $pluginContext;

    public function __construct(PluginContext $pluginContext)
    {
        $this->pluginContext = $pluginContext;
    }

    /**
     * Configure the dependency injection container
     *
     * @param ContainerBuilder $builder The container builder instance
     * @return void
     * @throws \InvalidArgumentException If any service configuration is invalid
     */
    public function configure(ContainerBuilder $builder): void
    {
        $definitions = [
            // Core Services
            PluginContext::class => $this->pluginContext,

            // Repositories
            SettingsRepository::class => autowire()
                ->constructorParameter('optionPrefix', $this->pluginContext->getPluginSlug() . '_'),

            // Interface to implementation bindings
            SettingsRepositoryInterface::class => get(SettingsRepository::class),

            // API Controllers
            GreetingController::class => autowire(),

            // Configuration
            'infrastructure.config' => [
                'plugin_dir' => $this->pluginContext->getPluginDir(),
                'plugin_url' => $this->pluginContext->getPluginUrl(),
                'version' => $this->pluginContext->getVersion(),
                'environment' => $this->getEnvironment(),
                'text_domain' => $this->pluginContext->getTextDomain(),
            ],

            // WordPress-specific services
            'wordpress.filesystem' => function (ContainerInterface $c) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
                global $wp_filesystem;
                return $wp_filesystem;
            },

            // REST API namespace
            'api.namespace' => $this->pluginContext->getPluginSlug() . '/v1',
        ];

        // Add development-only services
        if ($this->isDevelopment()) {
            $definitions['infrastructure.debug'] = true;
        }

        $builder->addDefinitions($definitions);

        // Enable compilation in production for better performance
        if ($this->isProduction()) {
            $builder->enableCompilation(
                $this->getCacheDir(),
                'InfrastructureCompiledContainer'
            );
        }
    }

    /**
     * Check if the current environment is production
     */
    private function isProduction(): bool
    {
        return $this->getEnvironment() === 'production';
    }

    /**
     * Check if the current environment is development
     */
    private function isDevelopment(): bool
    {
        $env = $this->getEnvironment();
        return in_array($env, ['development', 'staging', 'local']);
    }

    /**
     * Get the current environment name
     */
    private function getEnvironment(): string
    {
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        return (defined('WP_DEBUG') && WP_DEBUG) ? 'development' : 'production';
    }

    /**
     * Get cache directory
     */
    private function getCacheDir(): string
    {
        $upload_dir = wp_upload_dir();
        $cache_dir = trailingslashit($upload_dir['basedir']) . 'wp-skeleton/cache/infrastructure';
        
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        return $cache_dir;
    }
}