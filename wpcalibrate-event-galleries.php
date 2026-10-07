<?php
/**
 * Plugin Name:       WPCalibrate Event Galleries
 * Plugin URI:        https://wpcalibrate.com/
 * Description:       Create event-specific photo galleries, manage event information and photos, and display event galleries with responsive grid/masonry layouts, lightbox, and load more.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Author:            WPCalibrate
 * Author URI:        https://wpcalibrate.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcalibrate-event-galleries
 * Domain Path:       /languages
 * Update URI:        https://github.com/zeeshanraza-official/wpcalibrate-event-galleries
 * GitHub Plugin URI: zeeshanraza-official/wpcalibrate-event-galleries
 * Primary Branch:    main
 *
 * @package WPCalibrate\EventGalleries
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants.
if (!defined('WPCALIBRATE_EVENT_GALLERIES_VERSION')) {
    define('WPCALIBRATE_EVENT_GALLERIES_VERSION', '1.0.0');
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_DB_VERSION')) {
    define('WPCALIBRATE_EVENT_GALLERIES_DB_VERSION', '1.0.0');
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_MIN_PHP')) {
    define('WPCALIBRATE_EVENT_GALLERIES_MIN_PHP', '8.2');
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_MIN_WP')) {
    define('WPCALIBRATE_EVENT_GALLERIES_MIN_WP', '6.6');
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_FILE')) {
    define('WPCALIBRATE_EVENT_GALLERIES_FILE', __FILE__);
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_PATH')) {
    define('WPCALIBRATE_EVENT_GALLERIES_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_URL')) {
    define('WPCALIBRATE_EVENT_GALLERIES_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPCALIBRATE_EVENT_GALLERIES_BASENAME')) {
    define('WPCALIBRATE_EVENT_GALLERIES_BASENAME', plugin_basename(__FILE__));
}

/**
 * Autoloader for WPCalibrate Event Galleries classes.
 *
 * Maps namespace WPCalibrate\EventGalleries\... to corresponding class-*.php files.
 */
spl_autoload_register(static function (string $class_name): void {
    $prefix = 'WPCalibrate\\EventGalleries\\';
    if (!str_starts_with($class_name, $prefix)) {
        return;
    }

    $relative_class = substr($class_name, strlen($prefix));
    $parts          = explode('\\', $relative_class);
    $class_base     = array_pop($parts);

    // Convert class name to WordPress standard filename (e.g. Gallery_Renderer -> class-gallery-renderer.php)
    $formatted_base = 'class-' . strtolower(str_replace('_', '-', $class_base)) . '.php';

    // Subdirectories if present (e.g. Admin -> admin/, Public -> public/)
    $sub_path = '';
    if (!empty($parts)) {
        $sub_path = strtolower(implode(DIRECTORY_SEPARATOR, $parts)) . DIRECTORY_SEPARATOR;
    }

    $search_locations = [
        WPCALIBRATE_EVENT_GALLERIES_PATH . 'includes/' . $sub_path . $formatted_base,
        WPCALIBRATE_EVENT_GALLERIES_PATH . 'admin/' . $sub_path . $formatted_base,
        WPCALIBRATE_EVENT_GALLERIES_PATH . 'public/' . $sub_path . $formatted_base,
        WPCALIBRATE_EVENT_GALLERIES_PATH . $sub_path . $formatted_base,
    ];

    foreach ($search_locations as $file_path) {
        if (file_exists($file_path)) {
            require_once $file_path;
            return;
        }
    }
});

/**
 * Check if the host environment satisfies minimal plugin prerequisites.
 *
 * @return bool True if environment meets all requirements, false otherwise.
 */
function wpcalibrate_event_galleries_check_environment(): bool {
    global $wp_version;

    $php_compatible = version_compare(PHP_VERSION, WPCALIBRATE_EVENT_GALLERIES_MIN_PHP, '>=');
    $wp_compatible  = version_compare($wp_version ?? '0.0.0', WPCALIBRATE_EVENT_GALLERIES_MIN_WP, '>=');

    if (!$php_compatible || !$wp_compatible) {
        add_action('admin_notices', static function () use ($php_compatible, $wp_compatible): void {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            echo '<div class="notice notice-error"><p>';
            echo '<strong>' . esc_html__('WPCalibrate Event Galleries cannot run:', 'wpcalibrate-event-galleries') . '</strong> ';
            if (!$php_compatible) {
                /* translators: 1: Required PHP version, 2: Current PHP version */
                printf(
                    esc_html__('Requires PHP %1$s or higher (current: %2$s). ', 'wpcalibrate-event-galleries'),
                    esc_html(WPCALIBRATE_EVENT_GALLERIES_MIN_PHP),
                    esc_html(PHP_VERSION)
                );
            }
            if (!$wp_compatible) {
                global $wp_version;
                /* translators: 1: Required WordPress version, 2: Current WordPress version */
                printf(
                    esc_html__('Requires WordPress %1$s or higher (current: %2$s).', 'wpcalibrate-event-galleries'),
                    esc_html(WPCALIBRATE_EVENT_GALLERIES_MIN_WP),
                    esc_html($wp_version ?? 'unknown')
                );
            }
            echo '</p></div>';
        });

        return false;
    }

    return true;
}

// Register activation and deactivation lifecycle hooks.
register_activation_hook(WPCALIBRATE_EVENT_GALLERIES_FILE, ['WPCalibrate\\EventGalleries\\Activator', 'activate']);
register_deactivation_hook(WPCALIBRATE_EVENT_GALLERIES_FILE, ['WPCalibrate\\EventGalleries\\Deactivator', 'deactivate']);

// Bootstrap the plugin once all plugins are loaded.
add_action('plugins_loaded', static function (): void {
    if (!wpcalibrate_event_galleries_check_environment()) {
        return;
    }

    // Load translations.
    load_plugin_textdomain(
        'wpcalibrate-event-galleries',
        false,
        dirname(WPCALIBRATE_EVENT_GALLERIES_BASENAME) . '/languages'
    );

    // Initialize the main plugin instance.
    Plugin::get_instance()->init();
});
