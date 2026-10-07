<?php
/**
 * Plugin Activator.
 *
 * @package WPCalibrate\EventGalleries
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Activator
 *
 * Handles plugin activation routines.
 */
final class Activator {

    /**
     * Run activation procedures.
     *
     * @return void
     */
    public static function activate(): void {
        global $wp_version;

        // Verify PHP requirement.
        if (version_compare(PHP_VERSION, WPCALIBRATE_EVENT_GALLERIES_MIN_PHP, '<')) {
            deactivate_plugins(WPCALIBRATE_EVENT_GALLERIES_BASENAME);
            wp_die(
                sprintf(
                    /* translators: 1: Required PHP version, 2: Current PHP version */
                    esc_html__('WPCalibrate Event Galleries requires PHP version %1$s or higher. Your server is running PHP %2$s.', 'wpcalibrate-event-galleries'),
                    esc_html(WPCALIBRATE_EVENT_GALLERIES_MIN_PHP),
                    esc_html(PHP_VERSION)
                ),
                esc_html__('Plugin Activation Error', 'wpcalibrate-event-galleries'),
                ['back_link' => true]
            );
        }

        // Verify WordPress requirement.
        if (version_compare($wp_version ?? '0.0.0', WPCALIBRATE_EVENT_GALLERIES_MIN_WP, '<')) {
            deactivate_plugins(WPCALIBRATE_EVENT_GALLERIES_BASENAME);
            wp_die(
                sprintf(
                    /* translators: 1: Required WP version, 2: Current WP version */
                    esc_html__('WPCalibrate Event Galleries requires WordPress version %1$s or higher. Your site is running WordPress %2$s.', 'wpcalibrate-event-galleries'),
                    esc_html(WPCALIBRATE_EVENT_GALLERIES_MIN_WP),
                    esc_html($wp_version ?? 'unknown')
                ),
                esc_html__('Plugin Activation Error', 'wpcalibrate-event-galleries'),
                ['back_link' => true]
            );
        }

        // Assign capabilities to Administrator and Editor roles.
        Capabilities::add_capabilities();

        // Initialize default settings if not already present.
        $existing_settings = get_option(Settings::OPTION_NAME, null);
        if ($existing_settings === null) {
            $settings_service = new Settings();
            add_option(Settings::OPTION_NAME, $settings_service->get_defaults(), '', 'yes');
        }

        // Register post type before rewrite flush so rules are recognized.
        $post_type = new Post_Type();
        $post_type->register_post_type();

        // Flush rewrite rules once.
        flush_rewrite_rules(false);

        // Record schema and release versions.
        if (get_option('wpcalibrate_event_galleries_db_version') === false) {
            add_option('wpcalibrate_event_galleries_db_version', WPCALIBRATE_EVENT_GALLERIES_DB_VERSION, '', 'no');
        } else {
            update_option('wpcalibrate_event_galleries_db_version', WPCALIBRATE_EVENT_GALLERIES_DB_VERSION);
        }

        if (get_option('wpcalibrate_event_galleries_version') === false) {
            add_option('wpcalibrate_event_galleries_version', WPCALIBRATE_EVENT_GALLERIES_VERSION, '', 'no');
        } else {
            update_option('wpcalibrate_event_galleries_version', WPCALIBRATE_EVENT_GALLERIES_VERSION);
        }
    }
}
