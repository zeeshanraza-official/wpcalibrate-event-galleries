<?php
/**
 * Tab Partial: Data & Tools.
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

global $wp_version;
$delete_data = ($settings['delete_data_on_uninstall'] ?? 'no') === 'yes';
?>
<div class="wpcalibrate-section-card">
    <h2><?php esc_html_e('Data Retention & Cleanup', 'wpcalibrate-event-galleries'); ?></h2>
    <p class="wpcalibrate-section-desc">
        <?php esc_html_e('Control whether event galleries, metadata, and configuration are deleted when this plugin is completely uninstalled from the WordPress Plugins screen.', 'wpcalibrate-event-galleries'); ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Uninstall Cleanup', 'wpcalibrate-event-galleries'); ?>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php esc_html_e('Uninstall Cleanup', 'wpcalibrate-event-galleries'); ?></legend>
                        <label>
                            <input type="checkbox" name="wpcal_eg[delete_data_on_uninstall]" value="yes" <?php checked($delete_data); ?> />
                            <strong style="color: #d63638;"><?php esc_html_e('Delete all plugin data on uninstall', 'wpcalibrate-event-galleries'); ?></strong>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When OFF (recommended): All your Event Galleries, gallery layouts, dates, and plugin settings are safely preserved even if the plugin is uninstalled.', 'wpcalibrate-event-galleries'); ?>
                        </p>
                        <p class="description">
                            <?php esc_html_e('When ON: If you delete this plugin, all Event Gallery posts, custom meta, and plugin settings will be permanently erased. Photos in your Media Library will NEVER be deleted.', 'wpcalibrate-event-galleries'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="wpcalibrate-section-card" style="margin-top: 24px;">
    <h2><?php esc_html_e('System Diagnostics & Environment', 'wpcalibrate-event-galleries'); ?></h2>
    <p class="wpcalibrate-section-desc">
        <?php esc_html_e('Host environment diagnostics to assist with troubleshooting and support requests.', 'wpcalibrate-event-galleries'); ?>
    </p>

    <table class="wpcalibrate-specs-table">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Plugin Version', 'wpcalibrate-event-galleries'); ?></th>
                <td><code><?php echo esc_html(WPCALIBRATE_EVENT_GALLERIES_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Database Schema Version', 'wpcalibrate-event-galleries'); ?></th>
                <td><code><?php echo esc_html(WPCALIBRATE_EVENT_GALLERIES_DB_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('WordPress Version', 'wpcalibrate-event-galleries'); ?></th>
                <td><code><?php echo esc_html($wp_version ?? 'unknown'); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('PHP Version', 'wpcalibrate-event-galleries'); ?></th>
                <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Max Upload File Size', 'wpcalibrate-event-galleries'); ?></th>
                <td><code><?php echo esc_html(size_format((float) wp_max_upload_size())); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('REST API Namespace', 'wpcalibrate-event-galleries'); ?></th>
                <td><code>wpcalibrate-event-galleries/v1</code></td>
            </tr>
        </tbody>
    </table>
</div>
