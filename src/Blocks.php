<?php

/**
 * Blocks Manager
 *
 * Handles the registration and management of Gutenberg blocks
 * for the plugin. This class is responsible for initializing
 * block types and their associated assets.
 *
 * @package WP\Skeleton
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton;

use RuntimeException;
use WP_Block_Type_Registry;
use InvalidArgumentException;
use WP\Skeleton\Shared\Plugin\PluginContext;

/**
 * Manages Gutenberg blocks registration and assets
 *
 * This class handles the registration of custom Gutenberg blocks,
 * enqueues block editor assets, and provides helper methods
 * for block-related functionality.
 */
final class Blocks
{
    /**
     * The plugin context instance
     */
    private PluginContext $context;

    /**
     * The blocks directory path
     */
    private string $blocks_dir;

    /**
     * The blocks build directory path
     */
    private string $build_dir;

    /**
     * Constructor
     *
     * @param PluginContext $context The plugin context
     * @throws InvalidArgumentException If required directories don't exist
     */
    public function __construct(PluginContext $context)
    {
        $this->context = $context;
        $this->blocks_dir = $context->getPluginDir('src/Blocks');
        $this->build_dir = $context->getPluginDir('assets/blocks');

        // Validate directories
        $this->validate_directories();
    }

    /**
     * Validate required directories
     */
    private function validate_directories(): void
    {
        if (!file_exists($this->blocks_dir) || !is_dir($this->blocks_dir)) {
            throw new InvalidArgumentException(
                sprintf('Blocks directory does not exist: %s', $this->blocks_dir)
            );
        }

        if (!file_exists($this->build_dir) || !is_dir($this->build_dir)) {
            // Create build directory if it doesn't exist
            if (!wp_mkdir_p($this->build_dir)) {
                throw new InvalidArgumentException(
                    sprintf('Could not create build directory: %s', $this->build_dir)
                );
            }
        }
    }

    /**
     * Initialize blocks
     *
     * @return void
     */
    public function init(): void
    {
        // Only initialize if block editor is available
        if (!function_exists('register_block_type')) {
            return;
        }

        add_action('init', [$this, 'register_blocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_filter('block_categories_all', [$this, 'register_block_category']);
    }

    /**
     * Register all blocks in the blocks directory
     *
     * @return void
     * @throws RuntimeException If block registration fails
     */
    public function register_blocks(): void
    {
        // Find all block directories
        $block_dirs = glob($this->blocks_dir . '/*', GLOB_ONLYDIR);
        
        if (empty($block_dirs)) {
            return;
        }

        foreach ($block_dirs as $block_dir) {
            $this->register_block($block_dir);
        }
    }

    /**
     * Register a single block
     */
    private function register_block(string $block_dir): void
    {
        $block_name = basename($block_dir);
        $block_json = $block_dir . '/block.json';
        
        if (!file_exists($block_json)) {
            error_log(sprintf('Block JSON file not found: %s', $block_json));
            return;
        }

        // Register the block
        $result = register_block_type($block_json);

        if (false === $result) {
            error_log(sprintf('Failed to register block: %s', $block_name));
        } else {
            // Register block styles if they exist
            $this->register_block_styles($block_name);
        }
    }

    /**
     * Register block styles
     */
    private function register_block_styles(string $block_name): void
    {
        $style_path = $this->build_dir . '/' . $block_name . '/style.css';
        $editor_style_path = $this->build_dir . '/' . $block_name . '/editor.css';

        if (file_exists($style_path)) {
            wp_register_style(
                $this->context->getHandlePrefix('block-' . $block_name),
                $this->context->getPluginUrl('assets/blocks/' . $block_name . '/style.css'),
                [],
                $this->context->getVersion()
            );
        }

        if (file_exists($editor_style_path)) {
            wp_register_style(
                $this->context->getHandlePrefix('block-' . $block_name . '-editor'),
                $this->context->getPluginUrl('assets/blocks/' . $block_name . '/editor.css'),
                [],
                $this->context->getVersion()
            );
        }
    }

    /**
     * Enqueue block editor assets
     *
     * @return void
     */
    public function enqueue_block_editor_assets(): void
    {
        $editor_script = 'blocks-editor.js';
        $editor_style = 'blocks-editor.css';
        $script_asset_path = $this->build_dir . '/blocks.asset.php';

        if (!file_exists($script_asset_path)) {
            // Don't throw error, just log and return
            error_log('Block assets not found. Please run the build process.');
            return;
        }

        // Register editor script
        $script_asset = require $script_asset_path;
        
        wp_enqueue_script(
            $this->context->getHandlePrefix('blocks-editor'),
            $this->context->getPluginUrl('assets/blocks/blocks.js'),
            $script_asset['dependencies'] ?? ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            $script_asset['version'] ?? $this->context->getVersion(),
            true
        );

        // Localize script with plugin data
        wp_localize_script(
            $this->context->getHandlePrefix('blocks-editor'),
            'wpSkeletonBlocks',
            $this->get_script_data()
        );

        // Register editor styles
        if (file_exists($this->build_dir . '/' . $editor_style)) {
            wp_enqueue_style(
                $this->context->getHandlePrefix('blocks-editor'),
                $this->context->getPluginUrl('assets/blocks/' . $editor_style),
                ['wp-edit-blocks'],
                $this->context->getVersion()
            );
        }

        // Load translations
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                $this->context->getHandlePrefix('blocks-editor'),
                'wp-skeleton',
                $this->context->getPluginDir('languages')
            );
        }
    }

    /**
     * Enqueue frontend and editor assets
     *
     * @return void
     */
    public function enqueue_block_assets(): void
    {
        $style_path = $this->build_dir . '/blocks-style.css';

        if (file_exists($style_path)) {
            wp_enqueue_style(
                $this->context->getHandlePrefix('blocks'),
                $this->context->getPluginUrl('assets/blocks/blocks-style.css'),
                [],
                $this->context->getVersion()
            );
        }
    }

    /**
     * Register a custom block category
     *
     * @param array $categories Block categories
     * @return array Filtered block categories
     */
    public function register_block_category(array $categories): array
    {
        return array_merge(
            $categories,
            [
                [
                    'slug' => 'wp-skeleton',
                    'title' => __('WP Skeleton', 'wp-skeleton'),
                    'icon' => 'wordpress',
                ],
            ]
        );
    }

    /**
     * Get script data to pass to the block editor
     *
     * @return array
     */
    private function get_script_data(): array
    {
        return [
            'plugin' => [
                'name' => $this->context->getPluginSlug(),
                'version' => $this->context->getVersion(),
                'url' => $this->context->getPluginUrl(),
            ],
            'api' => [
                'root' => esc_url_raw(rest_url('wp-skeleton/v1')),
                'nonce' => wp_create_nonce('wp_rest'),
            ],
            'i18n' => [
                'pluginName' => __('WP Skeleton', 'wp-skeleton'),
                'greetingBlockTitle' => __('Greeting Block', 'wp-skeleton'),
                'greetingBlockDescription' => __('Display a personalized greeting', 'wp-skeleton'),
            ],
        ];
    }

    /**
     * Check if a block is registered
     *
     * @param string $block_name The block name (with or without namespace)
     * @return bool
     */
    public function is_block_registered(string $block_name): bool
    {
        $registry = WP_Block_Type_Registry::get_instance();
        $full_name = strpos($block_name, '/') === false 
            ? $this->context->getPluginSlug() . '/' . $block_name 
            : $block_name;

        return $registry->is_registered($full_name);
    }

    /**
     * Get all registered blocks
     *
     * @return array List of registered block names
     */
    public function get_registered_blocks(): array
    {
        $registry = WP_Block_Type_Registry::get_instance();
        $blocks = $registry->get_all_registered();
        $prefix = $this->context->getPluginSlug() . '/';

        return array_filter(
            array_keys($blocks),
            fn($name) => str_starts_with($name, $prefix)
        );
    }

    /**
     * Create example block files for demonstration
     */
    public function create_example_block(): void
    {
        $example_block_dir = $this->blocks_dir . '/greeting';
        
        if (!file_exists($example_block_dir)) {
            wp_mkdir_p($example_block_dir);
        }

        // Create block.json
        $block_json = [
            'apiVersion' => 3,
            'name' => $this->context->getPluginSlug() . '/greeting',
            'version' => '1.0.0',
            'title' => 'Greeting',
            'category' => 'wp-skeleton',
            'icon' => 'smiley',
            'description' => 'Display a personalized greeting message.',
            'example' => [],
            'supports' => [
                'html' => false,
                'align' => true,
            ],
            'textdomain' => 'wp-skeleton',
            'editorScript' => $this->context->getHandlePrefix('blocks-editor'),
            'editorStyle' => $this->context->getHandlePrefix('blocks-editor'),
            'style' => $this->context->getHandlePrefix('blocks'),
            'attributes' => [
                'name' => [
                    'type' => 'string',
                    'default' => 'World'
                ],
                'className' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ]
        ];

        file_put_contents(
            $example_block_dir . '/block.json',
            json_encode($block_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}