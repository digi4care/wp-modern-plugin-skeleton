<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\React;

use WP\Skeleton\Shared\Plugin\PluginContext;
use WP_Error;
use InvalidArgumentException;
use RuntimeException;

final class ReactAppLoader
{
    private static ?array $cachedManifest = null;
    private static ?string $manifestPath = null;
    private static array $loadedScripts = [];

    public function __construct(
        private readonly PluginContext $pluginContext,
    ) {
    }

    /**
     * Load React assets
     *
     * @return true|WP_Error True on success, WP_Error on failure with user-friendly message
     */
    public function load(string $scriptName, string $jsVarName, array $dataToInject): bool|WP_Error
    {
        // Prevent duplicate loading
        $cacheKey = $scriptName . ':' . $jsVarName;
        if (isset(self::$loadedScripts[$cacheKey])) {
            return true;
        }

        try {
            if ($this->isDevelopmentEnvironment()) {
                $this->injectDevScripts($scriptName, $jsVarName, $dataToInject);
            } else {
                $this->injectProductionAssets($scriptName, $jsVarName, $dataToInject);
            }

            self::$loadedScripts[$cacheKey] = true;
            return true;

        } catch (InvalidArgumentException $e) {
            return new WP_Error(
                'invalid_script_name',
                __('Invalid script name provided.', 'wp-skeleton')
            );
        } catch (RuntimeException $e) {
            return new WP_Error(
                'asset_loading_failed',
                __('Failed to load React assets. Please check the build process.', 'wp-skeleton')
            );
        }
    }

    public function register(): void
    {
        add_action('admin_enqueue_scripts', function () {
            $this->ensureReactDependencies();
        });
    }

    public function getPluginContext(): PluginContext
    {
        return $this->pluginContext;
    }

    private function isDevelopmentEnvironment(): bool
    {
        $env = function_exists('wp_get_environment_type')
            ? wp_get_environment_type()
            : 'production';

        return in_array($env, ['local', 'development'], true);
    }

    private function injectDevScripts(string $scriptName, string $jsVarName, array $dataToInject): void
    {
        if (!$this->isValidScriptName($scriptName)) {
            throw new InvalidArgumentException('Invalid script name');
        }

        $sanitizedScriptName = sanitize_file_name($scriptName);
        $sanitizedVarName = sanitize_key($jsVarName);

        $dataJs = wp_json_encode(
            $dataToInject,
            JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );

        if ($dataJs === false) {
            throw new RuntimeException('Failed to encode data for script injection');
        }

        $output = sprintf(
            '<script type="module" src="%s"></script>
            <script type="module">
                import RefreshRuntime from "%s";
                RefreshRuntime.injectIntoGlobalHook(window);
                window.$RefreshReg$ = () => {};
                window.$RefreshSig$ = () => (type) => type;
                window.__vite_plugin_react_preamble_installed__ = true;
            </script>
            <script type="module" src="%s"></script>
            <script type="module">window.%s = %s;</script>',
            esc_url('http://localhost:5173/@vite/client'),
            esc_url('http://localhost:5173/@react-refresh'),
            esc_url('http://localhost:5173/' . $sanitizedScriptName),
            esc_js($sanitizedVarName),
            $dataJs
        );

        echo $output;
    }

    private function isValidScriptName(string $scriptName): bool
    {
        return preg_match('/^[a-zA-Z0-9._-]+$/', $scriptName) === 1;
    }

    private function injectProductionAssets(string $scriptName, string $jsVarName, array $dataToInject): void
    {
        $manifest = $this->loadViteManifest();
        if (!$manifest) {
            throw new RuntimeException('Vite manifest not found. Please build the React application.');
        }

        $entry = $manifest[$scriptName] ?? reset($manifest);
        if (!isset($entry['file'])) {
            throw new RuntimeException('Entry point not found in Vite manifest.');
        }

        $baseUrl = $this->pluginContext->getPluginUrl('assets/react/');
        $handle = $this->pluginContext->getHandlePrefix($jsVarName);

        // Enqueue the main script
        wp_enqueue_script(
            $handle,
            $baseUrl . $entry['file'],
            ['wp-element', 'wp-i18n', 'wp-api-fetch'],
            $this->pluginContext->getVersion(),
            true
        );

        // Set script type to module
        add_filter('script_loader_tag', function ($tag, $handleFromFilter, $src) use ($handle) {
            if ($handleFromFilter === $handle) {
                $tag = sprintf(
                    '<script type="module" src="%s" id="%s-js"></script>',
                    esc_url($src),
                    esc_attr($handle)
                );
            }
            return $tag;
        }, 10, 3);

        // Inject data
        add_action('admin_head', function () use ($jsVarName, $dataToInject) {
            $data = wp_json_encode($dataToInject, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            printf(
                '<script type="module">window.%s = %s;</script>',
                esc_js(sanitize_key($jsVarName)),
                $data
            );
        });

        // Enqueue CSS files
        if (!empty($entry['css'])) {
            foreach ($entry['css'] as $index => $cssFile) {
                wp_enqueue_style(
                    $handle . '-style-' . $index,
                    $baseUrl . $cssFile,
                    [],
                    $this->pluginContext->getVersion()
                );
            }
        }

        // Load translations
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                $handle,
                'wp-skeleton',
                $this->pluginContext->getPluginDir('languages')
            );
        }
    }

    private function loadViteManifest(): ?array
    {
        if (self::$cachedManifest !== null) {
            return self::$cachedManifest;
        }

        $path = $this->pluginContext->getPluginDir('assets/react/.vite/manifest.json');
        self::$manifestPath = $path;

        if (!file_exists($path)) {
            error_log('Vite manifest not found: ' . $path);
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false) {
            error_log('Could not read Vite manifest: ' . $path);
            return null;
        }

        $manifest = json_decode($json, true);
        self::$cachedManifest = is_array($manifest) ? $manifest : null;

        return self::$cachedManifest;
    }

    private function ensureReactDependencies(): void
    {
        if (!wp_script_is('wp-element', 'registered')) {
            wp_register_script('wp-element', '', [], false, true);
        }

        if (!wp_script_is('wp-i18n', 'registered')) {
            wp_register_script('wp-i18n', '', [], false, true);
        }

        if (!wp_script_is('wp-api-fetch', 'registered')) {
            wp_register_script('wp-api-fetch', '', [], false, true);
        }
    }

    /**
     * Clear cache (useful for testing)
     */
    public static function clearCache(): void
    {
        self::$cachedManifest = null;
        self::$manifestPath = null;
        self::$loadedScripts = [];
    }
}
