<?php
/**
 * Plugin Upgrader & Migrations.
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
 * Class Upgrader
 *
 * Handles version comparisons and safe migration procedures.
 */
class Upgrader {

    /**
     * Settings instance.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Constructor.
     *
     * @param Settings $settings Settings service.
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * Initialize upgrader checks.
     *
     * @return void
     */
    public function init(): void {
        add_action('admin_init', [$this, 'check_upgrade'], 5);
    }

    /**
     * Compare stored version and run migrations if needed.
     *
     * @return void
     */
    public function check_upgrade(): void {
        $stored_db_version = (string) get_option('wpcalibrate_event_galleries_db_version', '0.0.0');

        if (version_compare($stored_db_version, WPCALIBRATE_EVENT_GALLERIES_DB_VERSION, '<')) {
            $this->run_migrations($stored_db_version);
            update_option('wpcalibrate_event_galleries_db_version', WPCALIBRATE_EVENT_GALLERIES_DB_VERSION);
            update_option('wpcalibrate_event_galleries_version', WPCALIBRATE_EVENT_GALLERIES_VERSION);
        }
    }

    /**
     * Execute versioned migration routines.
     *
     * @param string $from_version Prior version.
     * @return void
     */
    private function run_migrations(string $from_version): void {
        // Ensure roles always possess the latest capability assignments
        Capabilities::add_capabilities();

        // Ensure new settings options are safely merged with defaults
        $current = get_option(Settings::OPTION_NAME, []);
        if (is_array($current)) {
            $defaults = $this->settings->get_defaults();
            $merged   = wp_parse_args($current, $defaults);
            update_option(Settings::OPTION_NAME, $merged);
        }
    }
}
