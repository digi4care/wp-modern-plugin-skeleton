<?php

declare(strict_types=1);

namespace WP\Skeleton\Adapter;

use DI\Container;

final class WordpressCron
{

    public const CRON_HOOK = 'skeleton_job';
    public const CRON_SCHEDULE = 'skeleton_every_minute';
    private const CRON_INTERVAL = 60;

    private static ?Container $container = null;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    private static function container(): Container
    {
        if (self::$container === null) {
            throw new \RuntimeException('Container not initialized');
        }

        return self::$container;
    }

    public function register(): void
    {
        // TODO: register cron events
    }

    public static function schedule(): void
    {
        if (!self::isRegistered()) {
            wp_schedule_event(time(), self::CRON_SCHEDULE, self::CRON_HOOK);
        }
    }

    public static function unschedule(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp !== false) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    public static function isRegistered(): bool
    {
        return wp_next_scheduled(self::CRON_HOOK) !== false;
    }

    public static function init(): void
    {
        self::container();
        add_filter('cron_schedules', [self::class, 'addSchedule']);
        add_action('skeleton_some_job', [self::class, 'run']);
    }

    public static function run(): void
    {

    }
}
