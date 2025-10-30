<?php

/**
 * Hook Registrable Interface
 *
 * Defines a contract for classes that need to register WordPress hooks.
 * This ensures consistent hook registration across the plugin.
 *
 * @package WP\Skeleton\Infrastructure\WordPress\Hook
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Hook;

/**
 * Contract for classes that register WordPress hooks
 *
 * Classes implementing this interface can be automatically registered
 * with the plugin's hook management system.
 */
interface HookRegistrableInterface
{
    /**
     * Register all necessary WordPress hooks
     *
     * This method should be called to register actions and filters
     * with WordPress. It should handle all hook registration for the class.
     *
     * @return void
     */
    public function register(): void;
}