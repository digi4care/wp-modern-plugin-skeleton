<?php

/**
 * WordPress Plugin Interface
 *
 * Defines the contract for the main plugin class.
 *
 * @package WP\Skeleton\Adapter
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use WP\Skeleton\Blocks;

/**
 * Contract for the main plugin class
 */
interface WordpressPluginInterface
{
    /**
     * Initialize the plugin
     */
    public function init(string $pluginFile): void;

    /**
     * Check if blocks are available and initialized
     */
    public function hasBlocks(): bool;

    /**
     * Get blocks instance if available
     */
    public function getBlocks(): ?Blocks;

    /**
     * Deactivate the plugin - cleanup operations
     */
    public function deactivate(): void;

    /**
     * Uninstall the plugin - remove all data
     */
    public function uninstall(): void;

    /**
     * Get plugin status information
     */
    public function getStatus(): array;

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array;
}
