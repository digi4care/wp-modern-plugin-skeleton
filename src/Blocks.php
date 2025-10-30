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

/**
 * Manages Gutenberg blocks registration and assets
 *
 * This class handles the registration of custom Gutenberg blocks,
 * enqueues block editor assets, and provides helper methods
 * for block-related functionality.
 */
final class Blocks
{
    private string $pluginFile;
    private string $pluginSlug;
    private string $blocksDir;
    private string $buildDir;
    private bool $isInitialized = false;

    /**
     * Initialize blocks
     */
    public function init(string $pluginFile, string $pluginSlug): void
    {
        if ($this->isInitialized) {
            return;
        }

        $this->pluginFile = $pluginFile;
        $this->pluginSlug = $pluginSlug;
        $this->blocksDir = plugin_dir_path($pluginFile) . 'src/Blocks';
        $this->buildDir = plugin_dir_path($pluginFile) . 'assets/blocks';

        // Double-check block editor availability
        if (!function_exists('register_block_type')) {
            error_log('WP Skeleton: Block editor not available - skipping blocks initialization');
            return;
        }

        // Only initialize if we're in a relevant context
        if (!$this->isRelevantContext()) {
            return;
        }

        add_action('init', [$this, 'register_blocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_filter('block_categories_all', [$this, 'register_block_category']);

        $this->isInitialized = true;
    }

    /**
     * Check if we're in a context where blocks should be initialized
     */
    private function isRelevantContext(): bool
    {
        // Always initialize in admin (for block editor)
        if (is_admin()) {
            return true;
        }

        // Initialize on frontend if we're rendering blocks
        if (has_blocks() && !wp_doing_ajax()) {
            return true;
        }

        return apply_filters('wp_skeleton_blocks_relevant_context', false);
    }

    /**
     * Register all blocks in the blocks directory
     */
    public function register_blocks(): void
    {
        // Validate directories
        $this->validate_directories();

        // Find all block directories
        $block_dirs = glob($this->blocksDir . '/*', GLOB_ONLYDIR);

        if (empty($block_dirs)) {
            return;
        }

        foreach ($block_dirs as $block_dir) {
            $this->register_block($block_dir);
        }
    }

    /**
     * Validate required directories
     */
    private function validate_directories(): void
    {
        if (!file_exists($this->blocksDir) || !is_dir($this->blocksDir)) {
            throw new InvalidArgumentException(
                sprintf('Blocks directory does not exist: %s', $this->blocksDir)
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
        $style_path = $this->buildDir . '/' . $block_name . '/style.css';
        $editor_style_path = $this->buildDir . '/' . $block_name . '/editor.css';

        $handle = $this->pluginSlug . '-block-' . $block_name;

        if (file_exists($style_path)) {
            wp_register_style(
                $handle,
                plugin_dir_url($this->pluginFile) . 'assets/blocks/' . $block_name . '/style.css',
                [],
                $this->get_version()
            );
        }

        if (file_exists($editor_style_path)) {
            wp_register_style(
                $handle . '-editor',
                plugin_dir_url($this->pluginFile) . 'assets/blocks/' . $block_name . '/editor.css',
                [],
                $this->get_version()
            );
        }
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets(): void
    {
        $script_asset_path = $this->buildDir . '/blocks.asset.php';

        if (!file_exists($script_asset_path)) {
            error_log('Block assets not found. Please run the build process.');
            return;
        }

        // Register editor script
        $script_asset = require $script_asset_path;

        wp_enqueue_script(
            $this->pluginSlug . '-blocks-editor',
            plugin_dir_url($this->pluginFile) . 'assets/blocks/blocks.js',
            $script_asset['dependencies'] ?? ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            $script_asset['version'] ?? $this->get_version(),
            true
        );

        // Localize script with plugin data
        wp_localize_script(
            $this->pluginSlug . '-blocks-editor',
            'wpSkeletonBlocks',
            $this->get_script_data()
        );

        // Register editor styles
        $editor_style = $this->buildDir . '/blocks-editor.css';
        if (file_exists($editor_style)) {
            wp_enqueue_style(
                $this->pluginSlug . '-blocks-editor',
                plugin_dir_url($this->pluginFile) . 'assets/blocks/blocks-editor.css',
                ['wp-edit-blocks'],
                $this->get_version()
            );
        }

        // Load translations
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                $this->pluginSlug . '-blocks-editor',
                'wp-skeleton',
                plugin_dir_path($this->pluginFile) . 'languages'
            );
        }
    }

    /**
     * Enqueue frontend and editor assets
     */
    public function enqueue_block_assets(): void
    {
        $style_path = $this->buildDir . '/blocks-style.css';

        if (file_exists($style_path)) {
            wp_enqueue_style(
                $this->pluginSlug . '-blocks',
                plugin_dir_url($this->pluginFile) . 'assets/blocks/blocks-style.css',
                [],
                $this->get_version()
            );
        }
    }

    /**
     * Register a custom block category
     */
    public function register_block_category(array $categories): array
    {
        return array_merge(
            $categories,
            [
                [
                    'slug' => $this->pluginSlug,
                    'title' => __('WP Skeleton', 'wp-skeleton'),
                    'icon' => 'wordpress',
                ],
            ]
        );
    }

    /**
     * Get script data to pass to the block editor
     */
    private function get_script_data(): array
    {
        return [
            'plugin' => [
                'name' => $this->pluginSlug,
                'version' => $this->get_version(),
                'url' => plugin_dir_url($this->pluginFile),
            ],
            'api' => [
                'root' => esc_url_raw(rest_url($this->pluginSlug . '/v1')),
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
     * Get plugin version
     */
    private function get_version(): string
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data($this->pluginFile, false, false);
        return $plugin_data['Version'] ?? '1.0.0';
    }

    /**
     * Check if a block is registered
     */
    public function is_block_registered(string $block_name): bool
    {
        $registry = WP_Block_Type_Registry::get_instance();
        $full_name = strpos($block_name, '/') === false
            ? $this->pluginSlug . '/' . $block_name
            : $block_name;

        return $registry->is_registered($full_name);
    }

    /**
     * Get all registered blocks
     */
    public function get_registered_blocks(): array
    {
        $registry = WP_Block_Type_Registry::get_instance();
        $blocks = $registry->get_all_registered();
        $prefix = $this->pluginSlug . '/';

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
        $example_block_dir = $this->blocksDir . '/greeting';

        if (!file_exists($example_block_dir)) {
            wp_mkdir_p($example_block_dir);
        }

        // Create block.json
        $block_json = [
            'apiVersion' => 3,
            'name' => $this->pluginSlug . '/greeting',
            'version' => '1.0.0',
            'title' => 'Greeting',
            'category' => $this->pluginSlug,
            'icon' => 'smiley',
            'description' => 'Display a personalized greeting message.',
            'example' => [],
            'supports' => [
                'html' => false,
                'align' => true,
            ],
            'textdomain' => 'wp-skeleton',
            'editorScript' => $this->pluginSlug . '-blocks-editor',
            'editorStyle' => $this->pluginSlug . '-blocks-editor',
            'style' => $this->pluginSlug . '-blocks',
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

    /**
     * Check if blocks are initialized
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }
}
