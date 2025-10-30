<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Api;

use WP\Skeleton\Infrastructure\WordPress\Hook\HookRegistrableInterface;

class RestApiRegistrar implements HookRegistrableInterface
{
    public function __construct(
        private GreetingController $greetingController
    ) {}

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        $this->greetingController->register_routes();
    }
}
