<?php

/**
 * Blocks Factory with Lazy Loading
 *
 * Creates Blocks instances only when needed and if block functionality is available.
 *
 * @package WP\Skeleton\Blocks
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Blocks;

use WP\Skeleton\Blocks;
use RuntimeException;

/**
 * Factory for creating and managing Blocks instances with lazy loading
 */
final class BlocksFactory
{
    private ?Blocks $blocksInstance = null;
    private bool $isInitialized = false;
    private string $pluginFile = '';
    private string $pluginSlug = '';

    public function getBlocks(): ?Blocks
    {
        if ($this->blocksInstance === null && $this->shouldLoadBlocks()) {
            $this->blocksInstance = $this->createBlocks();
        }

        return $this->blocksInstance;
    }

    /**
     * Initialize blocks if available
     */
    public function init(string $pluginFile, string $pluginSlug): bool
    {
        if ($this->isInitialized) {
            return $this->blocksInstance !== null;
        }

        $this->pluginFile = $pluginFile;
        $this->pluginSlug = $pluginSlug;

        $blocks = $this->getBlocks();
        if ($blocks !== null) {
            try {
                $blocks->init($pluginFile, $pluginSlug);
                $this->isInitialized = true;
                return true;
            } catch (\Throwable $e) {
                error_log('WP Skeleton: Failed to initialize blocks - ' . $e->getMessage());
                $this->blocksInstance = null;
            }
        }

        return false;
    }

    /**
     * Check if blocks should be loaded
     */
    private function shouldLoadBlocks(): bool
    {
        // Check if Gutenberg/Block Editor is available
        if (!function_exists('register_block_type')) {
            return false;
        }

        // Check if we're in a context where blocks make sense
        if (is_admin() && !$this->isBlockEditorContext()) {
            return false;
        }

        // Allow filtering
        return apply_filters('wp_skeleton_should_load_blocks', true);
    }

    /**
     * Check if we're in block editor context
     */
    private function isBlockEditorContext(): bool
    {
        $current_screen = function_exists('get_current_screen') ? get_current_screen() : null;

        return $current_screen &&
               method_exists($current_screen, 'is_block_editor') &&
               $current_screen->is_block_editor();
    }

    /**
     * Create Blocks instance
     */
    private function createBlocks(): ?Blocks
    {
        try {
            return new Blocks();
        } catch (\Throwable $e) {
            error_log('WP Skeleton: Failed to create Blocks instance - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if blocks are available and initialized
     */
    public function hasBlocks(): bool
    {
        return $this->blocksInstance !== null && $this->isInitialized;
    }

    /**
     * Get block-related performance stats
     */
    public function getStats(): array
    {
        return [
            'has_blocks_instance' => $this->blocksInstance !== null,
            'is_initialized' => $this->isInitialized,
            'should_load_blocks' => $this->shouldLoadBlocks(),
            'block_editor_context' => $this->isBlockEditorContext(),
            'plugin_file' => $this->pluginFile,
            'plugin_slug' => $this->pluginSlug,
        ];
    }
}
