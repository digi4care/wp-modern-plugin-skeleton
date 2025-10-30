<?php

/**
 * WordPress Cron Job Handler
 * 
 * This class manages scheduled tasks (WP-Cron) for the plugin.
 * It provides methods to register, schedule, and execute recurring tasks.
 * 
 * @package WP\Skeleton\Adapter
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use DI\Container;
use RuntimeException;
use WP_Error;

/**
 * Handles WordPress cron jobs with dependency injection support
 */
final class WordpressCron
{
    /** @var string The cron hook name */
    public const CRON_HOOK = 'skeleton_job';
    
    /** @var string The cron schedule name */
    public const CRON_SCHEDULE = 'skeleton_every_minute';
    
    /** @var int The cron interval in seconds */
    private const CRON_INTERVAL = 60;

    /** @var Container|null The dependency injection container */
    private static ?Container $container = null;

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
     * @throws RuntimeException If container is not initialized
     */
    private static function container(): Container
    {
        if (self::$container === null) {
            throw new RuntimeException('Container not initialized. Call set_container() first.');
        }

        return self::$container;
    }

    /**
     * Register cron events and schedules
     * 
     * @return void
     * 
     * @throws RuntimeException If there's an error registering the cron events
     */
    public function register(): void
    {
        try {
            add_action(self::CRON_HOOK, [self::class, 'run']);
            
            // Register custom cron schedule
            add_filter('cron_schedules', function (array $schedules): array {
                return $this->add_custom_schedule($schedules);
            });
            
            // Ensure the cron job is scheduled
            if (!self::is_registered()) {
                self::schedule();
            }
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to register cron events: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Add custom cron schedule
     */
    private function add_custom_schedule(array $schedules): array
    {
        if (!isset($schedules[self::CRON_SCHEDULE])) {
            $schedules[self::CRON_SCHEDULE] = [
                'interval' => self::CRON_INTERVAL,
                'display'  => __('Every Minute', 'wp-skeleton'),
            ];
        }
        return $schedules;
    }

    /**
     * Schedule the cron job
     * 
     * @return void
     * 
     * @throws RuntimeException If scheduling fails
     */
    public static function schedule(): void
    {
        if (!self::is_registered()) {
            $scheduled = wp_schedule_event(
                time() + self::CRON_INTERVAL, // Start in 1 minute
                self::CRON_SCHEDULE,
                self::CRON_HOOK
            );
            
            if ($scheduled === false || $scheduled instanceof WP_Error) {
                throw new RuntimeException(
                    'Failed to schedule cron event: ' . 
                    ($scheduled instanceof WP_Error ? $scheduled->get_error_message() : 'Unknown error')
                );
            }
        }
    }

    /**
     * Unschedule the cron job
     * 
     * @return void
     */
    public static function unschedule(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp !== false) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
        
        // Also clear any scheduled hooks
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * Check if the cron job is registered
     * 
     * @return bool True if the cron job is scheduled, false otherwise
     */
    public static function is_registered(): bool
    {
        return wp_next_scheduled(self::CRON_HOOK) !== false;
    }

    /**
     * Initialize the cron system
     * 
     * @return void
     * 
     * @throws RuntimeException If initialization fails
     */
    public static function init(): void
    {
        try {
            self::container();
            
            // Register custom schedule
            add_filter('cron_schedules', function (array $schedules): array {
                $instance = new self();
                return $instance->add_custom_schedule($schedules);
            });
            
            // Register the cron action
            add_action(self::CRON_HOOK, [self::class, 'run']);
            
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to initialize cron system: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Execute the cron job
     * 
     * This method contains the actual logic to be executed when the cron runs.
     * 
     * @return void
     */
    public static function run(): void
    {
        try {
            // Log cron execution
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf('Cron job %s executed at %s', self::CRON_HOOK, current_time('mysql')));
            }
            
            // Example: Process scheduled tasks
            self::process_scheduled_tasks();
            
            // Example: Clean up temporary data
            self::cleanup_temporary_data();
            
        } catch (\Throwable $e) {
            self::handle_cron_error($e);
        }
    }

    /**
     * Process scheduled tasks - EXAMPLE IMPLEMENTATION
     */
    private static function process_scheduled_tasks(): void
    {
        // Example: Get pending tasks from database
        $pending_tasks = get_option('wp_skeleton_pending_tasks', []);
        
        if (!empty($pending_tasks)) {
            foreach ($pending_tasks as $task_id => $task) {
                // Process each task
                self::process_task($task_id, $task);
            }
            
            // Update last processed time
            update_option('wp_skeleton_last_cron_run', current_time('mysql'));
        }
    }

    /**
     * Process individual task - EXAMPLE IMPLEMENTATION
     */
    private static function process_task(string $task_id, array $task): void
    {
        // Example task processing logic
        try {
            // Simulate task processing
            do_action('wp_skeleton_process_task', $task_id, $task);
            
            // Remove processed task
            $pending_tasks = get_option('wp_skeleton_pending_tasks', []);
            unset($pending_tasks[$task_id]);
            update_option('wp_skeleton_pending_tasks', $pending_tasks);
            
        } catch (\Throwable $e) {
            error_log(sprintf('Failed to process task %s: %s', $task_id, $e->getMessage()));
        }
    }

    /**
     * Clean up temporary data - EXAMPLE IMPLEMENTATION
     */
    private static function cleanup_temporary_data(): void
    {
        // Example: Clean up old transient data
        global $wpdb;
        
        $time = time();
        $expired = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM $wpdb->options 
                 WHERE option_name LIKE %s 
                 AND option_value < %d",
                $wpdb->esc_like('_transient_timeout_wp_skeleton_') . '%',
                $time
            )
        );
        
        foreach ($expired as $transient) {
            $key = str_replace('_transient_timeout_', '', $transient);
            delete_transient($key);
        }
    }

    /**
     * Handle cron execution errors
     */
    private static function handle_cron_error(\Throwable $e): void
    {
        $error_message = sprintf(
            'Cron job %s failed: %s in %s on line %d',
            self::CRON_HOOK,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        
        error_log($error_message);
        
        // Optionally send email notification in production
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            self::send_error_notification($error_message);
        }
        
        // Re-throw in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw $e;
        }
    }

    /**
     * Send error notification - EXAMPLE IMPLEMENTATION
     */
    private static function send_error_notification(string $message): void
    {
        // Example: Send email to admin
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            wp_mail(
                $admin_email,
                sprintf('Cron Job Failed: %s', self::CRON_HOOK),
                $message
            );
        }
    }
}