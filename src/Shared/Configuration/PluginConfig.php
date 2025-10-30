<?php

/**
 * Plugin Configuration Object
 *
 * Immutable configuration object for plugin settings.
 *
 * @package WP\Skeleton\Shared\Configuration
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Shared\Configuration;

/**
 * Immutable plugin configuration
 */
final class PluginConfig
{
    public function __construct(
        public readonly string $version,
        public readonly string $environment,
        public readonly bool $debug,
        public readonly string $pluginDir,
        public readonly string $pluginUrl
    ) {
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function isDevelopment(): bool
    {
        return in_array($this->environment, ['development', 'staging', 'local']);
    }
}