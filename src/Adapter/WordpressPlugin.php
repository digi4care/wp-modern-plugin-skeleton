<?php

/**
 * WordPress Plugin Adapter
 *
 * Main plugin class that coordinates all components and acts as the entry point.
 *
 * @package WP\Skeleton\Adapter
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use WP\Skeleton\Blocks;
use WP\Skeleton\Blocks\BlocksFactory;
use WP\Skeleton\Infrastructure\WordPress\Api\RestApiRegistrar;

/**
 * Main plugin class that coordinates all components
 */
final class WordpressPlugin
{
    private ?Blocks $blocks = null;
    private bool $blocksInitialized = false;
    private string $pluginFile;
    private string $pluginSlug;
    private bool $shouldInitializeBlocks = false;

    public function __construct(
        private WordpressCron $cron,
        private BlocksFactory $blocksFactory,
        private RestApiRegistrar $restApiRegistrar
    ) {}

    /**
     * Initialize the plugin
     */
    public function init(string $pluginFile, string $pluginSlug): void
    {
        $this->pluginFile = $pluginFile;
        $this->pluginSlug = $pluginSlug;

        // Check if we should initialize blocks (only in admin or block editor)
        $this->shouldInitializeBlocks = $this->shouldInitializeBlocks();

        // Initialize cron
        $this->cron->init();

        // Initialize REST API
        $this->restApiRegistrar->register();

        // Initialize blocks only if needed
        if ($this->shouldInitializeBlocks) {
            $this->initializeBlocks();
        }
    }

    /**
     * Check if blocks should be initialized
     */
    private function shouldInitializeBlocks(): bool
    {
        // Never initialize during AJAX requests (except for block editor AJAX)
        if (wp_doing_ajax()) {
            return false;
        }

        // Always initialize in admin (for block editor)
        if (is_admin()) {
            return true;
        }

        // Initialize on frontend only if we're rendering blocks
        if (has_blocks() && !wp_doing_ajax()) {
            return true;
        }

        // Allow filtering
        return apply_filters('wp_skeleton_should_initialize_blocks', false);
    }

    /**
     * Initialize blocks if available
     */
    private function initializeBlocks(): void
    {
        // Early return if blocks already initialized
        if ($this->blocksInitialized) {
            return;
        }

        if ($this->blocksFactory->init($this->pluginFile, $this->pluginSlug)) {
            $this->blocks = $this->blocksFactory->getBlocks();
            $this->blocksInitialized = true;

            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                error_log('WP Skeleton: Blocks initialized successfully');
            }
        } else {
            // Only log in debug mode with debug log enabled
            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                error_log('WP Skeleton: Blocks not available - Gutenberg might be disabled or not needed');
            }
        }
    }

    /**
     * Check if blocks are available and initialized
     */
    public function hasBlocks(): bool
    {
        return $this->blocksInitialized && $this->blocks !== null;
    }

    /**
     * Get blocks instance if available
     */
    public function getBlocks(): ?Blocks
    {
        return $this->blocks;
    }

    /**
     * Get cron instance
     */
    public function getCron(): WordpressCron
    {
        return $this->cron;
    }

    /**
     * Get blocks factory instance
     */
    public function getBlocksFactory(): BlocksFactory
    {
        return $this->blocksFactory;
    }

    /**
     * Get REST API registrar instance
     */
    public function getRestApiRegistrar(): RestApiRegistrar
    {
        return $this->restApiRegistrar;
    }

    /**
     * Get plugin file
     */
    public function getPluginFile(): string
    {
        return $this->pluginFile;
    }

    /**
     * Get plugin slug
     */
    public function getPluginSlug(): string
    {
        return $this->pluginSlug;
    }

    /**
     * Deactivate the plugin - cleanup operations
     */
    public function deactivate(): void
    {
        // Unschedule cron jobs
        $this->cron->unschedule();

        // Clear any plugin-specific transients
        $this->cleanupTransients();

        if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
            error_log('WP Skeleton: Plugin deactivated - cleanup completed');
        }
    }

    /**
     * Uninstall the plugin - remove all data
     */
    public function uninstall(): void
    {
        // Delete plugin options
        $this->cleanupOptions();

        // Clear any remaining transients
        $this->cleanupTransients();

        if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
            error_log('WP Skeleton: Plugin uninstalled - all data removed');
        }
    }

    /**
     * Clean up plugin options
     */
    private function cleanupOptions(): void
    {
        $options = [
            'wp_skeleton_settings',
            'wp_skeleton_pending_tasks',
            'wp_skeleton_last_cron_run',
        ];

        foreach ($options as $option) {
            delete_option($option);
        }
    }

    /**
     * Clean up plugin transients
     */
    private function cleanupTransients(): void
    {
        global $wpdb;

        $transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM $wpdb->options
                 WHERE option_name LIKE %s
                 OR option_name LIKE %s",
                $wpdb->esc_like('_transient_wp_skeleton_') . '%',
                $wpdb->esc_like('_transient_timeout_wp_skeleton_') . '%'
            )
        );

        foreach ($transients as $transient) {
            $key = str_replace(['_transient_', '_transient_timeout_'], '', $transient);
            delete_transient($key);
        }
    }

    /**
     * Get plugin status information
     */
    public function getStatus(): array
    {
        return [
            'blocks_available' => $this->hasBlocks(),
            'blocks_initialized' => $this->blocksInitialized,
            'should_initialize_blocks' => $this->shouldInitializeBlocks,
            'cron_registered' => $this->cron->is_registered(),
            'cron_next_run' => wp_next_scheduled($this->cron->getHookName()),
            'rest_api_registered' => true,
        ];
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array
    {
        $stats = [
            'cron' => [
                'registered' => $this->cron->is_registered(),
                'next_run' => wp_next_scheduled($this->cron->getHookName()),
            ],
            'blocks' => [
                'available' => $this->hasBlocks(),
                'initialized' => $this->blocksInitialized,
                'should_initialize' => $this->shouldInitializeBlocks,
            ],
        ];

        // Add blocks factory stats if available
        if ($this->blocksFactory) {
            $stats['blocks_factory'] = $this->blocksFactory->getStats();
        }

        return $stats;
    }
}
