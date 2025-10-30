<?php

/**
 * Plugin Context
 *
 * Provides runtime context and metadata about the plugin.
 * This class serves as a central source of truth for plugin-related information.
 *
 * @package WP\Skeleton\Shared\Plugin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Shared\Plugin;

use InvalidArgumentException;
use RuntimeException;

/**
 * Represents the runtime context of the plugin
 *
 * This class provides access to plugin metadata, file paths, URLs, and other
 * contextual information needed throughout the plugin's lifecycle.
 */
final class PluginContext
{
    /**
     * The plugin file path relative to the plugins directory
     * @var non-empty-string
     */
    private string $pluginFile;

    /**
     * The plugin's base directory path
     * @var non-empty-string
     */
    private string $pluginDir;

    /**
     * The plugin's base URL
     * @var non-empty-string
     */
    private string $pluginUrl;

    /**
     * The plugin's version
     * @var non-empty-string
     */
    private string $version;

    /**
     * The plugin's text domain
     * @var non-empty-string
     */
    private string $textDomain;

    public function __construct(
        string $pluginFile,
        private readonly string $pluginSlug,
        private readonly string $pluginInfoUrl,
        private readonly string $handlePrefix
    ) {
        // Validate plugin file
        if (!file_exists($pluginFile)) {
            throw new InvalidArgumentException(sprintf(
                'Plugin file does not exist: %s',
                $pluginFile
            ));
        }

        $this->pluginFile = wp_normalize_path($pluginFile);
        $this->pluginDir = plugin_dir_path($this->pluginFile);
        $this->pluginUrl = plugin_dir_url($this->pluginFile);
        $this->version = $this->resolvePluginVersion();
        $this->textDomain = $pluginSlug;
    }

    public function getPluginFile(): string
    {
        return $this->pluginFile;
    }

    public function getPluginDir(string $path = ''): string
    {
        return $this->pluginDir . ltrim($path, '/\\');
    }

    public function getPluginUrl(string $path = ''): string
    {
        return $this->pluginUrl . ltrim($path, '/');
    }

    public function getPluginSlug(): string
    {
        return $this->pluginSlug;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getTextDomain(): string
    {
        return $this->textDomain;
    }

    public function getPluginInfoUrl(): string
    {
        return $this->pluginInfoUrl;
    }

    public function getHandlePrefix(string $suffix = ''): string
    {
        return $this->handlePrefix . $suffix;
    }

    public function getPluginBasename(): string
    {
        return plugin_basename($this->pluginFile);
    }

    public function getPluginDirName(): string
    {
        return basename(dirname($this->pluginFile));
    }

    public function getAssetPath(string $path): string
    {
        return $this->getPluginDir('assets/' . ltrim($path, '/'));
    }

    public function getAssetUrl(string $path): string
    {
        return $this->getPluginUrl('assets/' . ltrim($path, '/'));
    }

    public function isAdmin(): bool
    {
        return is_admin();
    }

    public function isAjax(): bool
    {
        return wp_doing_ajax();
    }

    public function isRest(): bool
    {
        return defined('REST_REQUEST') && REST_REQUEST;
    }

    public function isWpCli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    public function isDebug(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    public function getEnvironment(): string
    {
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        return $this->isDebug() ? 'development' : 'production';
    }

    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'production';
    }

    public function isDevelopment(): bool
    {
        $env = $this->getEnvironment();
        return in_array($env, ['development', 'staging', 'local']);
    }

    /**
     * Resolve the plugin version from the main plugin file header
     */
    private function resolvePluginVersion(): string
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pluginData = get_plugin_data($this->pluginFile, false, false);
        
        if (empty($pluginData['Version'])) {
            // Fallback to constant or default
            if (defined('WP_SKELETON_VERSION')) {
                return WP_SKELETON_VERSION;
            }
            
            return '1.0.0';
        }

        return $pluginData['Version'];
    }
}