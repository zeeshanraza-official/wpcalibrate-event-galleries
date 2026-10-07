<?php
/**
 * Plugin Deactivator.
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
 * Class Deactivator
 *
 * Handles plugin deactivation routines without deleting user data.
 */
final class Deactivator {

    /**
     * Run deactivation procedures.
     *
     * @return void
     */
    public static function deactivate(): void {
        // Flush rewrite rules on deactivation.
        flush_rewrite_rules(false);
    }
}
