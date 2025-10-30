<?php

/**
 * Domain Layer Container Configuration
 *
 * Configures dependency injection for the domain layer services.
 * This class is responsible for defining and wiring up all domain-specific
 * services and their dependencies.
 *
 * @package WP\Skeleton\Domain\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Domain\DI;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WP\Skeleton\Domain\SampleService;
use WP\Skeleton\Domain\Repositories\SettingsRepositoryInterface;
use WP\Skeleton\Infrastructure\WordPress\SettingsRepository;
use WP\Skeleton\Shared\DI\ContainerConfiguratorInterface;

/**
 * Configures the dependency injection container for the Domain layer
 *
 * This configurator sets up all domain services, repositories, and their
 * dependencies. It implements the ContainerConfiguratorInterface to ensure
 * consistent configuration across all layers of the application.
 */
final class DomainContainerConfigurator implements ContainerConfiguratorInterface
{
    /**
     * Configure the dependency injection container
     *
     * Registers domain services, repositories, and their dependencies.
     * Uses PHP-DI's autowiring for automatic dependency resolution.
     *
     * @param ContainerBuilder $builder The container builder instance
     * @return void
     * @throws \InvalidArgumentException If any service configuration is invalid
     */
    public function configure(ContainerBuilder $builder): void
    {
        $definitions = [
            // Domain Services
            SampleService::class => \DI\autowire(),

            // Interface to implementation bindings
            SettingsRepositoryInterface::class => \DI\get(SettingsRepository::class),

            // Configuration values
            'domain.config' => \DI\factory(function (ContainerInterface $c) {
                return [
                    'default_greeting' => 'Hello',
                    'max_name_length' => 100,
                    'allowed_characters' => '/^[a-zA-Z0-9\s\-_]+$/',
                ];
            }),

            // Domain Events (example)
            'domain.events' => \DI\factory(function () {
                return new \ArrayObject();
            }),
        ];

        // Add environment-specific configurations
        if ($this->isDevelopment()) {
            $definitions['domain.debug'] = true;
        }

        $builder->addDefinitions($definitions);
        
        // Enable autowiring
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);
        
        // Enable compilation in production for better performance
        if ($this->isProduction()) {
            $builder->enableCompilation(
                $this->getCacheDir(),
                'DomainCompiledContainer'
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
     * Get current environment
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
        $cache_dir = trailingslashit($upload_dir['basedir']) . 'wp-skeleton/cache/domain';
        
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        return $cache_dir;
    }
}