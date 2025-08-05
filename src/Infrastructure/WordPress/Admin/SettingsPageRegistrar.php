<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Admin;

use WP\Skeleton\Infrastructure\WordPress\Hook\HookRegistrableInterface;
use WP\Skeleton\Infrastructure\WordPress\React\Components\XPubSettingsAppLoader;

final class SettingsPageRegistrar implements HookRegistrableInterface
{
    public function __construct(
        private XPubSettingsAppLoader $appLoader,
    ) {
    }

    public function register(): void
    {
        // Skeleton: register hooks for adding and displaying the settings page.
    }

    public function addOptionsPage(): void
    {
        // Skeleton: add the options page to WordPress.
    }

    public function renderSettingsPage(): void
    {
        // Skeleton: render the settings page content.
    }

    public function enqueueAssets(string $hook): void
    {
        // Skeleton: enqueue scripts for the settings page.
    }
}
