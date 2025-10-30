<?php

declare(strict_types=1);

use WP\Skeleton\Adapter\WordpressPlugin;
use WP\Skeleton\Adapter\WordpressCron;
use WP\Skeleton\Blocks;
use WP\Skeleton\Blocks\BlocksFactory;
use WP\Skeleton\Domain\Configuration\CronConfiguration;
use WP\Skeleton\Application\GreetingApplication;
use WP\Skeleton\Domain\SampleService;
use WP\Skeleton\Infrastructure\WordPress\Api\GreetingController;
use WP\Skeleton\Infrastructure\WordPress\Api\RestApiRegistrar;

return [
    // Core plugin
    WordpressPlugin::class => \DI\create(WordpressPlugin::class)
        ->constructor(
            \DI\get(WordpressCron::class),
            \DI\get(BlocksFactory::class),
            \DI\get(RestApiRegistrar::class)
        ),

    // Cron system
    WordpressCron::class => \DI\create(WordpressCron::class)
        ->constructor(\DI\get(CronConfiguration::class)),

    CronConfiguration::class => \DI\create(CronConfiguration::class),

    // Blocks Factory (lazy loading)
    BlocksFactory::class => \DI\create(BlocksFactory::class),

    // Original Blocks class (for direct usage if needed)
    Blocks::class => \DI\factory(function (\DI\Container $container) {
        $factory = $container->get(BlocksFactory::class);
        return $factory->getBlocks();
    }),

    // Application layer
    GreetingApplication::class => \DI\create(GreetingApplication::class)
        ->constructor(\DI\get(SampleService::class)),

    SampleService::class => \DI\create(SampleService::class),

    // REST API
    GreetingController::class => \DI\create(GreetingController::class)
        ->constructor(\DI\get(GreetingApplication::class)),

    RestApiRegistrar::class => \DI\create(RestApiRegistrar::class)
        ->constructor(\DI\get(GreetingController::class)),
];
