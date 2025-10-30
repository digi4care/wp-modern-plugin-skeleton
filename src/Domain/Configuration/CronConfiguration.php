<?php

/**
 * Cron Configuration
 *
 * Provides configurable cron settings with filters for customization.
 *
 * @package WP\Skeleton\Domain\Configuration
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Domain\Configuration;

/**
 * Manages cron-related configuration
 */
final class CronConfiguration
{
    private const DEFAULT_INTERVAL = 60;
    private const MINIMUM_INTERVAL = 60;

    /**
     * Get the cron interval in seconds
     */
    public function getInterval(): int
    {
        /** @var int $interval */
        $interval = apply_filters('wp_skeleton_cron_interval', self::DEFAULT_INTERVAL);
        $interval = (int) $interval;
        
        return max(self::MINIMUM_INTERVAL, $interval);
    }

    /**
     * Get all cron schedules
     * 
     * @return array<string, array{interval: int, display: string}>
     */
    public function getSchedules(): array
    {
        /** @var array<string, array{interval: int, display: string}> $schedules */
        $schedules = apply_filters('wp_skeleton_cron_schedules', [
            'skeleton_every_minute' => [
                'interval' => $this->getInterval(),
                'display' => __('Every Minute', 'wp-skeleton'),
            ],
        ]);

        return $schedules;
    }

    /**
     * Get the cron hook name
     */
    public function getHookName(): string
    {
        /** @var string $hook */
        $hook = apply_filters('wp_skeleton_cron_hook', 'skeleton_job');
        return sanitize_key($hook);
    }

    /**
     * Get the cron schedule name
     */
    public function getScheduleName(): string
    {
        /** @var string $schedule */
        $schedule = apply_filters('wp_skeleton_cron_schedule', 'skeleton_every_minute');
        return sanitize_key($schedule);
    }
}