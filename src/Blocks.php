<?php

declare(strict_types=1);

namespace WP\Skeleton;

class Blocks
{
    /**
     * Initialize blocks
     */
    public function init(): void
    {
        add_action('init', [$this, 'registerBlocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
    }

    /**
     * Register blocks
     */
    public function registerBlocks(): void
    {
        // Automatically register all blocks in the blocks directory
        $blocks_dir = dirname(__DIR__) . '/frontend/blocks';
        
        if (!file_exists($blocks_dir)) {
            return;
        }

        $block_dirs = array_filter(glob($blocks_dir . '/*'), 'is_dir');
        
        foreach ($block_dirs as $block_dir) {
            $block_name = basename($block_dir);
            $block_json = $block_dir . '/block.json';
            
            if (file_exists($block_json)) {
                register_block_type($block_json);
            }
        }
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueueBlockEditorAssets(): void
    {
        $asset_file = dirname(__DIR__) . '/dist/blocks.asset.php';
        
        if (file_exists($asset_file)) {
            $assets = include $asset_file;
            
            wp_enqueue_script(
                'wp-modern-plugin-blocks',
                plugins_url('dist/blocks.js', dirname(__FILE__)),
                $assets['dependencies'] ?? [],
                $assets['version'] ?? filemtime(dirname(__DIR__) . '/dist/blocks.js')
            );

            // Localize script with plugin settings if needed
            wp_localize_script(
                'wp-modern-plugin-blocks',
                'wpModernPlugin',
                [
                    'restUrl' => esc_url_raw(rest_url()),
                    'nonce' => wp_create_nonce('wp_rest'),
                ]
            );
        }
    }
}
