<?php
/**
 * Tab Partial: General.
 *
 * @package WPCalibrate\EventGalleries
 *
 * @var array<string, mixed> $settings
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wpcalibrate-section-card">
    <h2><?php esc_html_e('About WPCalibrate Event Galleries', 'wpcalibrate-event-galleries'); ?></h2>
    <p class="wpcalibrate-section-desc">
        <?php esc_html_e('WPCalibrate Event Galleries provides focused, event-specific photo gallery management for administrators and editors. Manage event information, dates, venues, and media with responsive layouts, accessible lightbox, and batch loading.', 'wpcalibrate-event-galleries'); ?>
    </p>

    <div class="wpcalibrate-info-box">
        <h3><?php esc_html_e('Quick Integration Methods', 'wpcalibrate-event-galleries'); ?></h3>
        <table class="wpcalibrate-specs-table">
            <tbody>
                <tr>
                    <th scope="row"><strong><?php esc_html_e('Gutenberg Block', 'wpcalibrate-event-galleries'); ?></strong></th>
                    <td><?php esc_html_e('Insert the "WPCalibrate Event Gallery" block anywhere in the block editor and select your gallery from the sidebar inspector.', 'wpcalibrate-event-galleries'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><strong><?php esc_html_e('Shortcode', 'wpcalibrate-event-galleries'); ?></strong></th>
                    <td>
                        <code>[wpcalibrate_event_gallery id="123"]</code>
                        <p class="description">
                            <?php esc_html_e('Replace 123 with your Event Gallery ID. Compatible with Elementor, Divi, Bricks, and all major page builders.', 'wpcalibrate-event-galleries'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><strong><?php esc_html_e('Public Event URL', 'wpcalibrate-event-galleries'); ?></strong></th>
                    <td>
                        <code><?php echo esc_url(home_url('/event-galleries/')); ?></code>
                        <p class="description">
                            <?php esc_html_e('Each event gallery has its own clean permalink, and an event archive is automatically available at the URL above.', 'wpcalibrate-event-galleries'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
