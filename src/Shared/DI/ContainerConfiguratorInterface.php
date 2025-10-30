<?php

/**
 * Container Configurator Interface
 *
 * Defines the contract for classes that configure a dependency injection container.
 * Implementing classes should use this interface to ensure consistent container
 * configuration across the application.
 *
 * @package WP\Skeleton\Shared\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Shared\DI;

use DI\ContainerBuilder;
use Psr\Container\ContainerExceptionInterface;

/**
 * Contract for classes that configure a dependency injection container
 *
 * This interface enforces a standard way to configure a PHP-DI container
 * with service definitions, autowiring rules, and other container configurations.
 * Implementations should handle all container configuration in the configure() method.
 */
interface ContainerConfiguratorInterface
{
    /**
     * Configures the dependency injection container
     *
     * This method should be called during the container building process.
     * It should add all necessary service definitions and configurations
     * to the container builder.
     *
     * Example:
     * ```php
     * public function configure(ContainerBuilder $builder): void
     * {
     *     $builder->addDefinitions([
     *         LoggerInterface::class => autowire(FileLogger::class)
     *             ->constructorParameter('logPath', '/path/to/logs/app.log'),
     *         'app.config' => [
     *             'debug' => defined('WP_DEBUG') && WP_DEBUG,
     *             'environment' => wp_get_environment_type(),
     *         ],
     *     ]);
     * }
     * ```
     *
     * @param ContainerBuilder $builder The container builder instance
     * @return void
     * @throws ContainerExceptionInterface If an error occurs while configuring the container
     * @throws \InvalidArgumentException If any configuration is invalid
     */
    public function configure(ContainerBuilder $builder): void;
}