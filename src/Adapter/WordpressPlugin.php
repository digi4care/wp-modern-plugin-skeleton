<?php

/**
 * WordPress Plugin Bootstrap
 * 
 * This class serves as the main entry point for the WordPress plugin.
 * It handles plugin initialization, dependency injection, and lifecycle management.
 * 
 * @package WP\Skeleton\Adapter
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use DI\Container;
use RuntimeException;
use WP\Skeleton\Infrastructure\DI\ContainerProvider;
use WP\Skeleton\Shared\Plugin\PluginContext;
use WP_Error;

/**
 * Main plugin class responsible for bootstrapping the plugin
 */
final class WordpressPlugin
{
    /** @var string The plugin version */
    public const VERSION = '1.0.0';
    
    /** @var string The minimum required PHP version */
    public const MINIMUM_PHP_VERSION = '8.2';
    
    /** @var string The minimum required WordPress version */
    public const MINIMUM_WP_VERSION = '6.0';
    
    /** @var Container|null The dependency injection container */
    private static ?Container $container = null;
    
    /** @var string The plugin file path */
    private static string $plugin_file;

    /**
     * Set the dependency injection container
     * 
     * @param Container $container The container instance
     * @return void
     */
    public static function set_container(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Get the container instance
     * 
     * @return Container
     * @throws RuntimeException If the container is not initialized
     */
    private static function container(): Container
    {
        if (self::$container === null) {
            throw new RuntimeException(
                'Dependency injection container not initialized. ' .
                'Make sure to call set_container() before accessing the container.'
            );
        }

        return self::$container;
    }

    /**
     * Initialize the plugin
     * 
     * @param string $plugin_file The main plugin file path
     * @return void
     * 
     * @throws RuntimeException If initialization fails
     */
    public static function init(string $plugin_file): void
    {
        try {
            self::$plugin_file = $plugin_file;
            
            // Register activation and deactivation hooks
            register_activation_hook($plugin_file, [self::class, 'activate']);
            register_deactivation_hook($plugin_file, [self::class, 'deactivate']);
            
            // Initialize the container if not already set
            if (self::$container === null) {
                $container_provider = new ContainerProvider();
                self::$container = $container_provider->get_container();
            }
            
            // Check requirements
            self::check_requirements();
            
            // Initialize plugin components
            self::initialize_components();
            
            // Hook into WordPress
            add_action('plugins_loaded', [self::class, 'on_plugins_loaded']);
            
        } catch (\Throwable $e) {
            self::handle_initialization_error($e, $plugin_file);
        }
    }
    
    /**
     * Handle initialization errors gracefully
     */
    private static function handle_initialization_error(\Throwable $e, string $plugin_file): void {
        $error_message = sprintf('Failed to initialize plugin: %s', $e->getMessage());
        
        error_log($error_message);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new RuntimeException($error_message, 0, $e);
        }
        
        // Deactivate the plugin if initialization fails
        if (function_exists('deactivate_plugins')) {
            deactivate_plugins(plugin_basename($plugin_file));
            wp_die(
                sprintf(
                    'Plugin activation failed: %s. The plugin has been deactivated.',
                    esc_html($e->getMessage())
                ),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }
    }
    
    /**
     * Check system requirements
     * 
     * @return void
     * @throws RuntimeException If requirements are not met
     */
    private static function check_requirements(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            throw new RuntimeException(
                sprintf(
                    'This plugin requires PHP version %s or higher. Your current version is %s.',
                    self::MINIMUM_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }
        
        // Check WordPress version
        if (function_exists('get_bloginfo')) {
            $wp_version = get_bloginfo('version');
            if (version_compare($wp_version, self::MINIMUM_WP_VERSION, '<')) {
                throw new RuntimeException(
                    sprintf(
                        'This plugin requires WordPress version %s or higher. Your current version is %s.',
                        self::MINIMUM_WP_VERSION,
                        $wp_version
                    )
                );
            }
        }
    }
    
    /**
     * Initialize plugin components
     * 
     * @return void
     */
    private static function initialize_components(): void
    {
        // Initialize cron jobs
        WordpressCron::init();
        
        // Initialize blocks if Gutenberg is available
        if (function_exists('register_block_type')) {
            try {
                $blocks = self::container()->get(Blocks::class);
                $blocks->init();
            } catch (\Throwable $e) {
                error_log('Failed to initialize blocks: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Plugin activation hook
     * 
     * @return void
     */
    public static function activate(): void
    {
        // Ensure the plugin is properly initialized
        if (self::$container === null) {
            $container_provider = new ContainerProvider();
            self::$container = $container_provider->get_container();
        }
        
        // Schedule cron jobs
        WordpressCron::schedule();
        
        // Set default options
        $default_options = [
            'wp_skeleton_version' => self::VERSION,
            'wp_skeleton_installed' => current_time('mysql'),
        ];
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
        
        // Flush rewrite rules on next request
        update_option('wp_skeleton_flush_rewrite_rules', '1');
    }
    
    /**
     * Plugin deactivation hook
     * 
     * @return void
     */
    public static function deactivate(): void
    {
        // Unschedule cron jobs
        WordpressCron::unschedule();
        
        // Clean up temporary data
        delete_option('wp_skeleton_flush_rewrite_rules');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Fires once activated plugins have loaded
     * 
     * @return void
     */
    public static function on_plugins_loaded(): void
    {
        // Load plugin textdomain for translations
        load_plugin_textdomain(
            'wp-skeleton',
            false,
            dirname(plugin_basename(self::$plugin_file)) . '/languages/'
        );
        
        // Initialize any components that need to run after plugins_loaded
        self::after_plugins_loaded();
    }
    
    /**
     * Initialize components that need to run after plugins_loaded
     * 
     * @return void
     */
    private static function after_plugins_loaded(): void
    {
        // Add initialization that needs to happen after plugins_loaded
        do_action('wp_skeleton_loaded');
    }
}