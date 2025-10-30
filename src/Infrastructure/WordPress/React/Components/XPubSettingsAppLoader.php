<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\React\Components;

use WP\Skeleton\Infrastructure\WordPress\React\ReactAppLoader;
use WP\Skeleton\Shared\Plugin\PluginContext;
use WP_Error;

final class XPubSettingsAppLoader
{
    public function __construct(
        private readonly ReactAppLoader $appLoader,
        private readonly PluginContext $pluginContext,
    ) {
    }

    /**
     * Register and load the settings app
     *
     * @return true|WP_Error True on success, WP_Error on failure
     */
    public function register(): bool|WP_Error
    {
        $data = [
            'nonce' => wp_create_nonce('wp_rest'),
            'actionUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('wp-skeleton/v1'),
            'restNonce' => wp_create_nonce('wp_rest'),
        ];

        return $this->appLoader->load('main.jsx', 'xpubSettings', $data);
    }

    /**
     * Get plugin context for asset URLs
     */
    public function getPluginContext(): PluginContext
    {
        return $this->pluginContext;
    }
}
