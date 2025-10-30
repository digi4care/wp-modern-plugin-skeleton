<?php

/**
 * Settings Page Registrar
 *
 * Handles the registration and rendering of the WordPress admin settings page
 * for the plugin, including asset management and React application integration.
 *
 * @package WP\Skeleton\Infrastructure\WordPress\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Admin;

use WP\Skeleton\Infrastructure\WordPress\Hook\HookRegistrableInterface;
use WP\Skeleton\Infrastructure\WordPress\React\Components\XPubSettingsAppLoader;
use WP_Screen;

/**
 * Manages the plugin's settings page in the WordPress admin area
 *
 * This class handles the registration, rendering, and asset management
 * for the plugin's settings page, including integration with React components.
 */
final class SettingsPageRegistrar implements HookRegistrableInterface
{
    /**
     * The page slug used in the URL
     */
    private const PAGE_SLUG = 'wp-skeleton-settings';

    /**
     * The capability required to access the settings page
     */
    private const REQUIRED_CAPABILITY = 'manage_options';

    /**
     * The React application loader
     */
    private XPubSettingsAppLoader $appLoader;

    /**
     * Track if React app failed to load
     */
    private bool $reactAppFailed = false;

    public function __construct(XPubSettingsAppLoader $appLoader)
    {
        $this->appLoader = $appLoader;
    }

    /**
     * Register all necessary hooks
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addOptionsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_notices', [$this, 'displayAdminNotices']);
    }

    /**
     * Add the options page to the WordPress admin menu
     */
    public function addOptionsPage(): void
    {
        add_options_page(
            __('WP Skeleton Settings', 'wp-skeleton'),
            __('WP Skeleton', 'wp-skeleton'),
            self::REQUIRED_CAPABILITY,
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Register plugin settings
     */
    public function registerSettings(): void
    {
        register_setting(
            'wp_skeleton_settings_group',
            'wp_skeleton_settings',
            [
                'type' => 'array',
                'description' => __('WP Skeleton plugin settings', 'wp-skeleton'),
                'sanitize_callback' => [$this, 'sanitizeSettings'],
                'default' => $this->getDefaultSettings(),
            ]
        );

        // Add settings sections
        add_settings_section(
            'wp_skeleton_general_section',
            __('General Settings', 'wp-skeleton'),
            [$this, 'renderGeneralSection'],
            self::PAGE_SLUG
        );

        // Add settings fields
        add_settings_field(
            'enable_greetings',
            __('Enable Greetings', 'wp-skeleton'),
            [$this, 'renderEnableGreetingsField'],
            self::PAGE_SLUG,
            'wp_skeleton_general_section',
            [
                'label_for' => 'enable_greetings',
                'class' => 'wp-skeleton-row',
            ]
        );

        add_settings_field(
            'default_name',
            __('Default Name', 'wp-skeleton'),
            [$this, 'renderDefaultNameField'],
            self::PAGE_SLUG,
            'wp_skeleton_general_section',
            [
                'label_for' => 'default_name',
                'class' => 'wp-skeleton-row',
            ]
        );
    }

    /**
     * Render the settings page content
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(
                esc_html__('You do not have sufficient permissions to access this page.', 'wp-skeleton')
            );
        }

        // Check for required capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <!-- Traditional WordPress Settings Form -->
            <div id="wp-skeleton-traditional-settings">
                <form action="options.php" method="post">
                    <?php
                    settings_fields('wp_skeleton_settings_group');
                    do_settings_sections(self::PAGE_SLUG);
                    submit_button(__('Save Settings', 'wp-skeleton'));
                    ?>
                </form>
            </div>

            <!-- React App Section -->
            <div id="wp-skeleton-react-settings" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #ccd0d4;">
                <h2><?php esc_html_e('Advanced Settings', 'wp-skeleton'); ?></h2>
                <div id="wp-skeleton-settings-app">
                    <?php if ($this->reactAppFailed): ?>
                        <div class="notice notice-warning">
                            <p>
                                <?php
                                esc_html_e(
                                    'Advanced settings interface is temporarily unavailable. Please use the traditional settings above.',
                                    'wp-skeleton'
                                );
                                ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="spinner is-active"></div>
                        <p><?php esc_html_e('Loading advanced settings...', 'wp-skeleton'); ?></p>
                    <?php endif; ?>
                </div>
                <noscript>
                    <div class="notice notice-error">
                        <p>
                            <?php
                            esc_html_e(
                                'JavaScript is required to use the advanced settings. Please enable JavaScript in your browser.',
                                'wp-skeleton'
                            );
                            ?>
                        </p>
                    </div>
                </noscript>
            </div>
        </div>
        <?php
    }

    /**
     * Render general settings section
     */
    public function renderGeneralSection(array $args): void
    {
        ?>
        <p id="<?php echo esc_attr($args['id']); ?>">
            <?php esc_html_e('Configure the general settings for the WP Skeleton plugin.', 'wp-skeleton'); ?>
        </p>
        <?php
    }

    /**
     * Render enable greetings field
     */
    public function renderEnableGreetingsField(array $args): void
    {
        $options = get_option('wp_skeleton_settings', $this->getDefaultSettings());
        $value = $options['enable_greetings'] ?? true;
        ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="wp_skeleton_settings[<?php echo esc_attr($args['label_for']); ?>]"
            value="1"
            <?php checked($value, true); ?>
        />
        <label for="<?php echo esc_attr($args['label_for']); ?>">
            <?php esc_html_e('Enable greeting functionality', 'wp-skeleton'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, the greeting shortcodes and blocks will be available.', 'wp-skeleton'); ?>
        </p>
        <?php
    }

    /**
     * Render default name field
     */
    public function renderDefaultNameField(array $args): void
    {
        $options = get_option('wp_skeleton_settings', $this->getDefaultSettings());
        $value = $options['default_name'] ?? 'World';
        ?>
        <input
            type="text"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="wp_skeleton_settings[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
        />
        <p class="description">
            <?php esc_html_e('The default name to use when no name is provided in greetings.', 'wp-skeleton'); ?>
        </p>
        <?php
    }

    /**
     * Sanitize settings before saving
     */
    public function sanitizeSettings(array $input): array
    {
        $sanitized = [];

        // Sanitize enable_greetings
        if (isset($input['enable_greetings'])) {
            $sanitized['enable_greetings'] = (bool) $input['enable_greetings'];
        }

        // Sanitize default_name
        if (isset($input['default_name'])) {
            $sanitized['default_name'] = sanitize_text_field($input['default_name']);

            // Validate name
            if (empty(trim($sanitized['default_name']))) {
                add_settings_error(
                    'wp_skeleton_settings',
                    'invalid_default_name',
                    __('Default name cannot be empty.', 'wp-skeleton')
                );
                $sanitized['default_name'] = 'World';
            }
        }

        return wp_parse_args($sanitized, $this->getDefaultSettings());
    }

    /**
     * Get default settings
     */
    private function getDefaultSettings(): array
    {
        return [
            'enable_greetings' => true,
            'default_name' => 'World',
            'version' => '1.0.0',
        ];
    }

    /**
     * Enqueue scripts and styles for the settings page
     *
     * @param string $hook The current admin page
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load assets on our settings page
        if ('settings_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }

        // Enqueue WordPress media scripts for media uploader
        wp_enqueue_media();

        // Try to load React application with error handling
        $result = $this->appLoader->register();

        if (is_wp_error($result)) {
            $this->reactAppFailed = true;
            error_log('WP Skeleton: Failed to load React app - ' . $result->get_error_message());
        }

        // Add admin styles (always load these, even if React fails)
        $adminCssPath = $this->appLoader->getPluginContext()->getAssetPath('css/admin.css');
        if (file_exists($adminCssPath)) {
            wp_enqueue_style(
                'wp-skeleton-admin',
                $this->appLoader->getPluginContext()->getAssetUrl('css/admin.css'),
                [],
                $this->appLoader->getPluginContext()->getVersion()
            );

            // Add inline styles for better appearance
            wp_add_inline_style('wp-skeleton-admin', $this->getAdminStyles());
        }
    }

    /**
     * Get admin CSS styles
     */
    private function getAdminStyles(): string
    {
        return '
            .wp-skeleton-row {
                margin: 1em 0;
            }
            .wp-skeleton-row .description {
                margin-top: 0.5em;
            }
            #wp-skeleton-react-settings {
                background: #fff;
                padding: 1.5em;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            #wp-skeleton-react-settings .notice {
                margin: 0;
            }
        ';
    }

    /**
     * Display admin notices
     */
    public function displayAdminNotices(): void
    {
        settings_errors('wp_skeleton_settings');

        // Show React app loading warning if it failed
        if ($this->reactAppFailed && self::isSettingsPage()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('WP Skeleton Notice:', 'wp-skeleton'); ?></strong>
                    <?php esc_html_e('Advanced settings interface could not be loaded. Basic settings are still available.', 'wp-skeleton'); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get the settings page URL
     */
    public static function getSettingsPageUrl(): string
    {
        return add_query_arg(
            'page',
            self::PAGE_SLUG,
            admin_url('options-general.php')
        );
    }

    /**
     * Check if current page is the settings page
     */
    public static function isSettingsPage(): bool
    {
        $current_screen = get_current_screen();
        return $current_screen && $current_screen->id === 'settings_page_' . self::PAGE_SLUG;
    }

    /**
     * Check if React app loaded successfully
     */
    public function isReactAppLoaded(): bool
    {
        return !$this->reactAppFailed;
    }
}
