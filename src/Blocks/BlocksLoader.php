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

/**
 * Handles the initialization and management of Gutenberg blocks
 *
 * This class follows the singleton pattern to ensure only one instance
 * manages all block-related functionality. It implements WordPress best practices
 * for block development and handles edge cases gracefully.
 */
final class BlocksLoader
{
    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?self $instance = null;

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
     * Get the singleton instance
     *
     * @return self
     */
    public static function get_instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize the block loader
     *
     * @return void
     * @throws RuntimeException If initialization fails
     */
    public static function init(): void
    {
        $loader = self::get_instance();
        $loader->bootstrap();
    }

    /**
     * Bootstrap the block loader
     *
     * @return void
     * @throws RuntimeException If requirements are not met
     */
    private function bootstrap(): void
    {
        $this->check_requirements();
        
        // Hook into WordPress
        add_action('init', [$this, 'register_blocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_filter('block_categories_all', [$this, 'register_block_category'], 10, 2);
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
     * Enqueue block editor assets
     *
     * @return void
     */
    public function enqueue_editor_assets(): void
    {
        $asset_file = include plugin_dir_path(__FILE__) . '../build/index.asset.php';

        wp_enqueue_script(
            'wp-skeleton-blocks-editor',
            plugins_url('../build/index.js', __FILE__),
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );

        wp_enqueue_style(
            'wp-skeleton-blocks-editor',
            plugins_url('../build/index.css', __FILE__),
            [],
            $asset_file['version']
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
        wp_enqueue_style(
            'wp-skeleton-blocks',
            plugins_url('../build/style-index.css', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . '../build/style-index.css')
        );
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
            'pluginUrl' => plugin_dir_url(dirname(__FILE__)),
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
     * Prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }
}

// Initialize the block loader
add_action('plugins_loaded', function () {
    try {
        BlocksLoader::init();
    } catch (RuntimeException $e) {
        // Log the error
        error_log('Failed to initialize BlocksLoader: ' . $e->getMessage());
        
        // Show admin notice if user can manage options
        if (current_user_can('manage_options')) {
            add_action('admin_notices', function () use ($e) {
                ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html('WP Skeleton Blocks Error: ' . $e->getMessage()); ?></p>
                </div>
                <?php
            });
        }
    }
});