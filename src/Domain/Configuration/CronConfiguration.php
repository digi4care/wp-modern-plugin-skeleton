<?php

/**
 * Cron Configuration
 *
 * Provides configurable cron settings with filters for customization and caching.
 *
 * @package WP\Skeleton\Domain\Configuration
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Domain\Configuration;

use WP_Error;

/**
 * Manages cron-related configuration with caching
 */
final class CronConfiguration
{
    private const DEFAULT_INTERVAL = 60;
    private const MINIMUM_INTERVAL = 60;
    private const MAX_HOOK_NAME_LENGTH = 64;

    /** @var array<string, array{interval: int, display: string}>|null */
    private static ?array $cachedSchedules = null;

    private static ?int $cachedInterval = null;
    private static ?string $cachedHookName = null;
    private static ?string $cachedScheduleName = null;

    /**
     * Cache group for object caching
     */
    private const CACHE_GROUP = 'wp_skeleton_cron';

    /**
     * Cache expiration in seconds (12 hours)
     */
    private const CACHE_EXPIRATION = 43200;

    /**
     * Get cached value with object cache fallback
     */
    private static function getCached(string $key, callable $callback): mixed
    {
        $cacheKey = "cron_config_{$key}";
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP);

        if ($cached !== false) {
            return $cached;
        }

        $value = $callback();
        wp_cache_set($cacheKey, $value, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return $value;
    }

    /**
     * Get the cron interval in seconds (cached)
     *
     * @return positive-int
     */
    public function getInterval(): int
    {
        if (self::$cachedInterval === null) {
            self::$cachedInterval = self::getCached('interval', function() {
                /** @var int $interval */
                $interval = apply_filters('wp_skeleton_cron_interval', self::DEFAULT_INTERVAL);
                return max(self::MINIMUM_INTERVAL, (int) $interval);
            });
        }

        return self::$cachedInterval;
    }

    /**
     * Get all cron schedules (cached)
     *
     * @return array<string, array{interval: int, display: string}>
     */
    public function getSchedules(): array
    {
        if (self::$cachedSchedules === null) {
            self::$cachedSchedules = self::getCached('schedules', function() {
                /** @var array<string, array{interval: int, display: string}> $schedules */
                $schedules = apply_filters('wp_skeleton_cron_schedules', [
                    'skeleton_every_minute' => [
                        'interval' => $this->getInterval(),
                        'display' => __('Every Minute', 'wp-skeleton'),
                    ],
                ]);

                return $schedules;
            });
        }

        return self::$cachedSchedules;
    }

    /**
     * Get the cron hook name with validation
     *
     * @return non-empty-string
     *
     * @throws \RuntimeException If hook name is invalid (developer error)
     */
    public function getHookName(): string
    {
        if (self::$cachedHookName === null) {
            self::$cachedHookName = self::getCached('hook_name', function() {
                /** @var string $hook */
                $hook = apply_filters('wp_skeleton_cron_hook', 'skeleton_job');
                $sanitizedHook = sanitize_key($hook);

                if (empty($sanitizedHook)) {
                    throw new \RuntimeException('Cron hook name cannot be empty after sanitization');
                }

                if (strlen($sanitizedHook) > self::MAX_HOOK_NAME_LENGTH) {
                    throw new \RuntimeException(
                        sprintf('Cron hook name exceeds maximum length of %d characters', self::MAX_HOOK_NAME_LENGTH)
                    );
                }

                return $sanitizedHook;
            });
        }

        return self::$cachedHookName;
    }

    /**
     * Get the cron schedule name with validation
     *
     * @return non-empty-string
     *
     * @throws \RuntimeException If schedule name is invalid (developer error)
     */
    public function getScheduleName(): string
    {
        if (self::$cachedScheduleName === null) {
            self::$cachedScheduleName = self::getCached('schedule_name', function() {
                /** @var string $schedule */
                $schedule = apply_filters('wp_skeleton_cron_schedule', 'skeleton_every_minute');
                $sanitizedSchedule = sanitize_key($schedule);

                if (empty($sanitizedSchedule)) {
                    throw new \RuntimeException('Cron schedule name cannot be empty after sanitization');
                }

                // Verify the schedule exists
                $schedules = $this->getSchedules();
                if (!isset($schedules[$sanitizedSchedule])) {
                    throw new \RuntimeException(
                        sprintf('Cron schedule "%s" is not defined', $sanitizedSchedule)
                    );
                }

                return $sanitizedSchedule;
            });
        }

        return self::$cachedScheduleName;
    }

    /**
     * Validate cron configuration for user-facing errors
     *
     * @return true|WP_Error True if valid, WP_Error with user-friendly message if invalid
     */
    public function validate(): bool|WP_Error
    {
        try {
            $hookName = $this->getHookName();
            $scheduleName = $this->getScheduleName();
            $interval = $this->getInterval();

            return true;

        } catch (\RuntimeException $e) {
            return new WP_Error(
                'invalid_cron_configuration',
                sprintf(
                    __('Invalid cron configuration: %s', 'wp-skeleton'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Clear cache (useful for testing)
     */
    public static function clearCache(): void
    {
        self::$cachedSchedules = null;
        self::$cachedInterval = null;
        self::$cachedHookName = null;
        self::$cachedScheduleName = null;

        // Clear object cache
        wp_cache_delete('cron_config_interval', self::CACHE_GROUP);
        wp_cache_delete('cron_config_schedules', self::CACHE_GROUP);
        wp_cache_delete('cron_config_hook_name', self::CACHE_GROUP);
        wp_cache_delete('cron_config_schedule_name', self::CACHE_GROUP);
    }
}
