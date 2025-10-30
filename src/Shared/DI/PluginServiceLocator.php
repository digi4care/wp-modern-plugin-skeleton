<?php
// File: src/Shared/DI/PluginServiceLocator.php

declare(strict_types=1);

namespace WP\Skeleton\Shared\DI;

use DI\Container;
use RuntimeException;

/**
 * Service Locator for plugin services with O(1) performance
 *
 * Provides fast access to services without recreating the container
 * while maintaining proper dependency injection principles.
 *
 * @package WP\Skeleton\Shared\DI
 * @since 1.0.0
 */
final class PluginServiceLocator
{
    private static ?Container $container = null;
    private static array $resolvedServices = [];

    /**
     * Set the container instance (should be called once during plugin bootstrap)
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Get a service from the container with O(1) performance after first access
     *
     * @template T
     * @param class-string<T> $service The service class name or identifier
     * @return T The resolved service instance
     * @throws RuntimeException If container is not initialized or service not found
     */
    public static function get(string $service): mixed
    {
        if (self::$container === null) {
            throw new RuntimeException(
                'Service container not initialized. Call PluginServiceLocator::setContainer() during plugin bootstrap.'
            );
        }

        // Cache resolved services for O(1) performance on subsequent calls
        if (!isset(self::$resolvedServices[$service])) {
            self::$resolvedServices[$service] = self::$container->get($service);
        }

        return self::$resolvedServices[$service];
    }

    /**
     * Check if a service exists in the container
     */
    public static function has(string $service): bool
    {
        if (self::$container === null) {
            return false;
        }

        return self::$container->has($service);
    }

    /**
     * Clear the service cache (useful for testing)
     */
    public static function clearCache(): void
    {
        self::$resolvedServices = [];
    }

    /**
     * Get the container instance (for advanced use cases)
     */
    public static function getContainer(): ?Container
    {
        return self::$container;
    }

    /**
     * Reset the service locator (primarily for testing)
     */
    public static function reset(): void
    {
        self::$container = null;
        self::$resolvedServices = [];
    }
}
