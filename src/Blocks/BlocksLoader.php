<?php

/**
 * Block Loader for Gutenberg Blocks
 *
 * This class is responsible for initializing and managing Gutenberg blocks
 * in the WordPress block editor. It handles block registration, asset loading,
 * and ensures compatibility with different WordPress environments.
 *
 * @package WP\Skeleton\Blocks
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Blocks;

use WP_Error;
use RuntimeException;
use WP_Block_Type_Registry;
use WP\Skeleton\Shared\Plugin\PluginContext;

/**
 * Handles the initialization and management of Gutenberg blocks
 *
 * This class follows dependency injection pattern for better testability
 * and follows WordPress best practices for block development.
 */
final class BlocksLoader
{
    /**
     * Block namespace for this plugin
     *
     * @var string
     */
    private const BLOCK_NAMESPACE = 'wp-skeleton';

    /**
     * Minimum WordPress version required
     *
     * @var string
     */
    private const MIN_WP_VERSION = '5.8';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private const MIN_PHP_VERSION = '7.4';

    /**
     * Cache key for asset data
     */
    private const ASSET_CACHE_KEY = 'blocks_asset_data';

    /**
     * Cache expiration (24 hours)
     */
    private const ASSET_CACHE_EXPIRATION = 86400;

    /**
     * @var PluginContext
     */
    private PluginContext $pluginContext;

    /**
     * @var bool Track if blocks are initialized
     */
    private bool $isInitialized = false;

    /**
     * @var array|null Cached asset data
     */
    private static ?array $assetCache = null;

    public function __construct(PluginContext $pluginContext)
    {
        $this->pluginContext = $pluginContext;
    }

    /**
     * Initialize the block loader
     *
     * @return void
     * @throws RuntimeException If initialization fails
     */
    public function init(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $this->check_requirements();

        // Hook into WordPress
        add_action('init', [$this, 'register_blocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_filter('block_categories_all', [$this, 'register_block_category'], 10, 2);

        $this->isInitialized = true;
    }

    /**
     * Check system requirements
     *
     * @return void
     * @throws RuntimeException If requirements are not met
     */
    private function check_requirements(): void
    {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), self::MIN_WP_VERSION, '<')) {
            throw new RuntimeException(
                sprintf(
                    'This plugin requires WordPress %s or higher. Your current version is %s.',
                    self::MIN_WP_VERSION,
                    get_bloginfo('version')
                )
            );
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            throw new RuntimeException(
                sprintf(
                    'This plugin requires PHP %s or higher. Your current version is %s.',
                    self::MIN_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }

        // Check if Gutenberg is active
        if (!function_exists('register_block_type')) {
            throw new RuntimeException(
                'Gutenberg is not available. Please use WordPress 5.0+ or install the Gutenberg plugin.'
            );
        }
    }

    /**
     * Register custom block category
     *
     * @param array $categories Block categories
     * @return array Modified block categories
     */
    public function register_block_category(array $categories): array
    {
        return array_merge(
            $categories,
            [
                [
                    'slug' => 'wp-skeleton-blocks',
                    'title' => __('WP Skeleton Blocks', 'wp-skeleton'),
                    'icon' => 'layout',
                ],
            ]
        );
    }

    /**
     * Register all blocks
     *
     * @return void
     */
    public function register_blocks(): void
    {
        // Example: Register a block
        $this->register_block('example-block', [
            'editor_script' => 'wp-skeleton-blocks-editor',
            'editor_style'  => 'wp-skeleton-blocks-editor',
            'style'         => 'wp-skeleton-blocks',
            'render_callback' => [$this, 'render_example_block'],
        ]);
    }

    /**
     * Register a single block
     *
     * @param string $block_name The block name (without namespace)
     * @param array $args Block registration arguments
     * @return bool|WP_Block_Type|WP_Error The registered block type on success, or false/WP_Error on failure.
     */
    private function register_block(string $block_name, array $args = [])
    {
        $block_slug = self::BLOCK_NAMESPACE . '/' . $block_name;

        // Check if block is already registered
        if (WP_Block_Type_Registry::get_instance()->is_registered($block_slug)) {
            return false;
        }

        $default_args = [
            'editor_script' => 'wp-skeleton-blocks-editor',
            'editor_style'  => 'wp-skeleton-blocks-editor',
            'style'         => 'wp-skeleton-blocks',
        ];

        $args = wp_parse_args($args, $default_args);

        return register_block_type($block_slug, $args);
    }

    /**
     * Get cached asset data with fallback
     *
     * @return array
     */
    private function getCachedAssetData(): array
    {
        if (self::$assetCache !== null) {
            return self::$assetCache;
        }

        // Try transient cache first
        self::$assetCache = get_transient(self::ASSET_CACHE_KEY);

        if (self::$assetCache !== false) {
            return self::$assetCache;
        }

        // Load fresh data
        self::$assetCache = $this->loadAssetData();

        // Cache for future requests
        set_transient(self::ASSET_CACHE_KEY, self::$assetCache, self::ASSET_CACHE_EXPIRATION);

        return self::$assetCache;
    }

    /**
     * Load asset data from build files
     *
     * @return array
     */
    private function loadAssetData(): array
    {
        $asset_file = $this->pluginContext->getPluginDir('build/index.asset.php');

        if (!file_exists($asset_file)) {
            return [
                'dependencies' => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'version' => $this->pluginContext->getVersion(),
                'script_url' => $this->pluginContext->getPluginUrl('build/index.js'),
                'style_url' => $this->pluginContext->getPluginUrl('build/index.css'),
            ];
        }

        $asset_data = include $asset_file;

        return [
            'dependencies' => $asset_data['dependencies'] ?? ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            'version' => $asset_data['version'] ?? $this->pluginContext->getVersion(),
            'script_url' => $this->pluginContext->getPluginUrl('build/index.js'),
            'style_url' => $this->pluginContext->getPluginUrl('build/index.css'),
        ];
    }

    /**
     * Enqueue block editor assets
     *
     * @return void
     */
    public function enqueue_editor_assets(): void
    {
        $assetData = $this->getCachedAssetData();

        wp_enqueue_script(
            'wp-skeleton-blocks-editor',
            $assetData['script_url'],
            $assetData['dependencies'],
            $assetData['version'],
            true
        );

        wp_enqueue_style(
            'wp-skeleton-blocks-editor',
            $assetData['style_url'],
            [],
            $assetData['version']
        );

        // Localize script with data
        wp_localize_script(
            'wp-skeleton-blocks-editor',
            'wpSkeletonBlocks',
            $this->get_script_localization()
        );
    }

    /**
     * Enqueue frontend and editor assets
     *
     * @return void
     */
    public function enqueue_block_assets(): void
    {
        $style_path = $this->pluginContext->getPluginDir('build/style-index.css');

        if (file_exists($style_path)) {
            wp_enqueue_style(
                'wp-skeleton-blocks',
                $this->pluginContext->getPluginUrl('build/style-index.css'),
                [],
                filemtime($style_path)
            );
        }
    }

    /**
     * Get script localization data
     *
     * @return array
     */
    private function get_script_localization(): array
    {
        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'siteUrl' => get_site_url(),
            'restUrl' => get_rest_url(),
            'pluginUrl' => $this->pluginContext->getPluginUrl(),
            'isAdmin' => current_user_can('edit_posts'),
        ];
    }

    /**
     * Example block render callback
     *
     * @param array $attributes Block attributes
     * @param string $content Block content
     * @return string Rendered block HTML
     */
    public function render_example_block(array $attributes, string $content = ''): string
    {
        // Add your block rendering logic here
        $classes = isset($attributes['className']) ? ' ' . esc_attr($attributes['className']) : '';

        ob_start();
        ?>
        <div class="wp-block-wp-skeleton-example<?php echo $classes; ?>">
            <?php echo wp_kses_post($content); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if blocks are initialized
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * Clear asset cache (useful for development)
     *
     * @return void
     */
    public static function clearAssetCache(): void
    {
        self::$assetCache = null;
        delete_transient(self::ASSET_CACHE_KEY);
    }

    /**
     * Get performance statistics
     *
     * @return array
     */
    public function getPerformanceStats(): array
    {
        return [
            'asset_cache_loaded' => self::$assetCache !== null,
            'is_initialized' => $this->isInitialized,
            'cache_key' => self::ASSET_CACHE_KEY,
        ];
    }
}
