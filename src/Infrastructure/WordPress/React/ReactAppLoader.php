<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\React;

use WP\Skeleton\Shared\Plugin\PluginContext;

final readonly class ReactAppLoader
{
    public function __construct(
        private PluginContext $pluginContext,
    ) {
    }

    public function load(string $scriptName, string $jsVarName, array $dataToInject): void
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->injectDevScripts($scriptName, $jsVarName, $dataToInject);
        } else {
            $this->injectProductionAssets($scriptName, $jsVarName, $dataToInject);
        }
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
        add_action('admin_head', function () use ($scriptName, $jsVarName, $dataToInject) {
            $dataJs = json_encode($dataToInject, JSON_UNESCAPED_SLASHES);
            $var = $jsVarName;
            $script = <<<HTML
                <script type="module" src="http://localhost:5173/@vite/client"></script>
                <script type="module">
                    import RefreshRuntime from "http://localhost:5173/@react-refresh";
                    RefreshRuntime.injectIntoGlobalHook(window);
                    window.\$RefreshReg\$ = () => {};
                    window.\$RefreshSig\$ = () => (type) => type;
                    window.__vite_plugin_react_preamble_installed__ = true;
                </script>
                <script type="module" src="http://localhost:5173/{$scriptName}"></script>
                <script type="module">window.{$var} = {$dataJs};</script>
            HTML;

            echo $script;
        });
    }

    private function injectProductionAssets(string $scriptName, string $jsVarName, array $dataToInject): void
    {
        $manifest = $this->loadViteManifest();
        if (!$manifest) {
            return;
        }

        $entry = $manifest[$scriptName] ?? reset($manifest);
        if (!isset($entry['file'])) {
            return;
        }

        $baseUrl = plugins_url('dist/', $this->pluginContext->pluginFile);
        $handle = $this->pluginContext->handlePrefix.'-'.$jsVarName;

        wp_enqueue_script(
            $handle,
            $baseUrl.$entry['file'],
            ['wp-element', 'wp-i18n'],
            null,
            true
        );

        add_filter('script_loader_tag', function ($tag, $handleFromFilter, $src) use ($handle) {
            if ($handleFromFilter === $handle) {
                return '<script type="module" src="'.esc_url($src).'"></script>';
            }
            return $tag;
        }, 10, 3);

        add_action('admin_head', function () use ($jsVarName, $dataToInject) {
            echo '<script type="module">window.'.$jsVarName.' = '.json_encode(
                    $dataToInject,
                    JSON_UNESCAPED_SLASHES
                ).';</script>';
        });

        if (!empty($entry['css'][0])) {
            wp_enqueue_style(
                $handle.'-style',
                $baseUrl.$entry['css'][0]
            );
        }
    }

    private function loadViteManifest(): ?array
    {
        $path = plugin_dir_path($this->pluginContext->pluginFile).'dist/.vite/manifest.json';
        if (!file_exists($path)) {
            return null;
        }

        $json = file_get_contents($path);
        $manifest = json_decode((string)$json, true);

        return is_array($manifest) ? $manifest : null;
    }
}
