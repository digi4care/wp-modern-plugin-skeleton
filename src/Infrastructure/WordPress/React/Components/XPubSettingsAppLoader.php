<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\React\Components;

use WP\Skeleton\Infrastructure\WordPress\React\ReactAppLoader;
use WP\Skeleton\Shared\Plugin\PluginContext;

final class XPubSettingsAppLoader
{
    public function __construct(
        private ReactAppLoader $appLoader,
        private PluginContext $pluginContext,
    ) {
    }

    public function register(): void
    {
        $data = [
            'nonce' => '',
            'actionUrl' => '',
            'restUrl' => '',
            'restNonce' => '',
        ];

        // Skeleton: enqueue scripts and inject settings data for the React app.
        $this->appLoader->load('main.jsx', 'xpubSettings', $data);
    }
}
