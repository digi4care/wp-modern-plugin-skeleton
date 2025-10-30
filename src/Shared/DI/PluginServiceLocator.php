<?php

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
 * Note: Service Locator should be used sparingly. Prefer direct dependency injection
 * where possible. This is primarily for legacy code or cases where DI is not feasible.
 *
 * @package WP\Skeleton\Shared\DI
 * @since 1.0.0
 */
final class PluginServiceLocator
{
    private static ?Container $container = null;
    private static array $resolvedServices = [];
    private static bool $isEnabled = true;

    /**
     * Critical services to preload for optimal performance
     */
    private static array $preloadedServices = [
        \WP\Skeleton\Application\GreetingApplication::class,
        \WP\Skeleton\Domain\SampleService::class,
        \WP\Skeleton\Domain\Configuration\CronConfiguration::class,
        \WP\Skeleton\Infrastructure\WordPress\SettingsRepository::class,
    ];

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
        if (!self::$isEnabled) {
            throw new RuntimeException(
                'Service Locator is disabled. Use dependency injection instead.'
            );
        }

        if (self::$container === null) {
            throw new RuntimeException(
                'Service container not initialized. Call PluginServiceLocator::setContainer() during plugin bootstrap.'
            );
        }

        // Cache resolved services for O(1) performance on subsequent calls
        if (!isset(self::$resolvedServices[$service])) {
            if (!self::$container->has($service)) {
                throw new RuntimeException(
                    sprintf('Service "%s" not found in container.', $service)
                );
            }
            self::$resolvedServices[$service] = self::$container->get($service);
        }

        return self::$resolvedServices[$service];
    }

    /**
     * Check if a service exists in the container
     */
    public static function has(string $service): bool
    {
        if (!self::$isEnabled || self::$container === null) {
            return false;
        }

        return self::$container->has($service);
    }

    /**
     * Preload critical services for optimal performance
     *
     * This should be called during plugin initialization to warm up the cache
     * for frequently used services.
     *
     * @return void
     */
    public static function preloadCriticalServices(): void
    {
        foreach (self::$preloadedServices as $service) {
            if (self::has($service)) {
                // Force early loading to populate cache
                self::get($service);
            }
        }
    }

    /**
     * Add services to preload list
     *
     * @param array<class-string> $services
     * @return void
     */
    public static function addPreloadedServices(array $services): void
    {
        self::$preloadedServices = array_merge(self::$preloadedServices, $services);
        self::$preloadedServices = array_unique(self::$preloadedServices);
    }

    /**
     * Get list of preloaded services
     *
     * @return array<class-string>
     */
    public static function getPreloadedServices(): array
    {
        return self::$preloadedServices;
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
        self::$isEnabled = true;
        self::$preloadedServices = [
            \WP\Skeleton\Application\GreetingApplication::class,
            \WP\Skeleton\Domain\SampleService::class,
            \WP\Skeleton\Domain\Configuration\CronConfiguration::class,
            \WP\Skeleton\Infrastructure\WordPress\SettingsRepository::class,
        ];
    }

    /**
     * Disable the service locator (encourage dependency injection)
     */
    public static function disable(): void
    {
        self::$isEnabled = false;
    }

    /**
     * Enable the service locator
     */
    public static function enable(): void
    {
        self::$isEnabled = true;
    }

    /**
     * Check if service locator is enabled
     */
    public static function isEnabled(): bool
    {
        return self::$isEnabled;
    }

    /**
     * Alternative method that encourages migration to DI
     *
     * @deprecated Use dependency injection instead
     */
    public static function getWithWarning(string $service): mixed
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(
                sprintf(
                    'Service Locator usage detected for "%s". Consider migrating to dependency injection.',
                    $service
                )
            );
        }

        return self::get($service);
    }

    /**
     * Get performance statistics (for debugging)
     *
     * @return array<string, mixed>
     */
    public static function getPerformanceStats(): array
    {
        return [
            'total_services' => count(self::$resolvedServices),
            'preloaded_services' => self::$preloadedServices,
            'cache_hits' => count(self::$resolvedServices),
            'is_enabled' => self::$isEnabled,
        ];
    }
}
