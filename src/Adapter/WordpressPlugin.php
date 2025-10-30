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

use WP\Skeleton\Shared\Plugin\PluginContext;
use WP\Skeleton\Shared\Exception\ConfigurationException;
use WP\Skeleton\Domain\Configuration\CronConfiguration;
use WP\Skeleton\Blocks;

/**
 * Main plugin class responsible for bootstrapping the plugin
 */
final class WordpressPlugin
{
    /** @var non-empty-string The plugin version */
    public const VERSION = '1.0.0';
    
    /** @var non-empty-string The minimum required PHP version */
    public const MINIMUM_PHP_VERSION = '8.2';
    
    /** @var non-empty-string The minimum required WordPress version */
    public const MINIMUM_WP_VERSION = '6.0';
    
    /** @var non-empty-string The plugin file path */
    private string $plugin_file;

    public function __construct(
        private WordpressCron $cron,
        private PluginContext $context,
        private CronConfiguration $cronConfig,
        private ?Blocks $blocks = null
    ) {}

    /**
     * Initialize the plugin
     * 
     * @param non-empty-string $plugin_file The main plugin file path
     * @return void
     * 
     * @throws ConfigurationException If initialization fails
     */
    public function init(string $plugin_file): void
    {
        try {
            $this->plugin_file = $plugin_file;
            
            // Register activation and deactivation hooks
            register_activation_hook($plugin_file, [$this, 'activate']);
            register_deactivation_hook($plugin_file, [$this, 'deactivate']);
            
            // Check requirements
            $this->check_requirements();
            
            // Initialize plugin components
            $this->initialize_components();
            
            // Hook into WordPress
            add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
            
        } catch (\Throwable $e) {
            $this->handle_initialization_error($e, $plugin_file);
        }
    }
    
    /**
     * Handle initialization errors gracefully
     */
    private function handle_initialization_error(\Throwable $e, string $plugin_file): void {
        $error_message = sprintf('Failed to initialize plugin: %s', $e->getMessage());
        
        error_log($error_message);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new ConfigurationException($error_message, 0, $e);
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
     * @throws ConfigurationException If requirements are not met
     */
    private function check_requirements(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            throw new ConfigurationException(
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
                throw new ConfigurationException(
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
    private function initialize_components(): void
    {
        // Initialize cron jobs
        $this->cron->init();
        
        // Initialize blocks if available
        if ($this->blocks !== null && function_exists('register_block_type')) {
            try {
                $this->blocks->init();
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
    public function activate(): void
    {
        // Schedule cron jobs
        $this->cron->schedule();
        
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
    public function deactivate(): void
    {
        // Unschedule cron jobs
        $this->cron->unschedule();
        
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
    public function on_plugins_loaded(): void
    {
        // Load plugin textdomain for translations
        load_plugin_textdomain(
            'wp-skeleton',
            false,
            dirname(plugin_basename($this->plugin_file)) . '/languages/'
        );
        
        // Initialize any components that need to run after plugins_loaded
        $this->after_plugins_loaded();
    }
    
    /**
     * Initialize components that need to run after plugins_loaded
     * 
     * @return void
     */
    private function after_plugins_loaded(): void
    {
        // Add initialization that needs to happen after plugins_loaded
        do_action('wp_skeleton_loaded');
    }
}