<?php
/**
 * Tab Partial: Archive Settings.
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

$sort_order = (string) ($settings['archive_sort_order'] ?? 'date_desc');
$per_page   = (int) ($settings['archive_events_per_page'] ?? 12);
$show_count = ($settings['archive_show_photo_count'] ?? 'yes') === 'yes';
$show_loc   = ($settings['archive_show_location'] ?? 'yes') === 'yes';
$show_date  = ($settings['archive_show_date'] ?? 'yes') === 'yes';
?>
<div class="wpcalibrate-section-card">
    <h2><?php esc_html_e('Event Gallery Archive Settings', 'wpcalibrate-event-galleries'); ?></h2>
    <p class="wpcalibrate-section-desc">
        <?php
        printf(
            /* translators: %s: Archive URL link */
            esc_html__('Configure how the public event gallery archive at %s displays and orders galleries.', 'wpcalibrate-event-galleries'),
            '<code>' . esc_html(home_url('/event-galleries/')) . '</code>'
        );
        ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="archive_sort_order"><?php esc_html_e('Archive Sort Order', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <select name="wpcal_eg[archive_sort_order]" id="archive_sort_order" class="regular-text">
                        <option value="date_desc" <?php selected($sort_order, 'date_desc'); ?>><?php esc_html_e('Newest Event Date First', 'wpcalibrate-event-galleries'); ?></option>
                        <option value="date_asc" <?php selected($sort_order, 'date_asc'); ?>><?php esc_html_e('Oldest Event Date First', 'wpcalibrate-event-galleries'); ?></option>
                        <option value="title_asc" <?php selected($sort_order, 'title_asc'); ?>><?php esc_html_e('Event Title (A &ndash; Z)', 'wpcalibrate-event-galleries'); ?></option>
                        <option value="title_desc" <?php selected($sort_order, 'title_desc'); ?>><?php esc_html_e('Event Title (Z &ndash; A)', 'wpcalibrate-event-galleries'); ?></option>
                        <option value="menu_order" <?php selected($sort_order, 'menu_order'); ?>><?php esc_html_e('Custom Order (Menu Order)', 'wpcalibrate-event-galleries'); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Default ordering for event gallery cards on the archive page.', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="archive_events_per_page"><?php esc_html_e('Events per Page', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <input type="number"
                           name="wpcal_eg[archive_events_per_page]"
                           id="archive_events_per_page"
                           min="1"
                           max="50"
                           value="<?php echo esc_attr((string) $per_page); ?>"
                           class="small-text" />
                    <p class="description">
                        <?php esc_html_e('Number of event galleries displayed per archive page before paginating (1 to 50).', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Archive Card Badges', 'wpcalibrate-event-galleries'); ?>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php esc_html_e('Archive Card Badges', 'wpcalibrate-event-galleries'); ?></legend>
                        <label>
                            <input type="checkbox" name="wpcal_eg[archive_show_date]" value="yes" <?php checked($show_date); ?> />
                            <?php esc_html_e('Display Event Date', 'wpcalibrate-event-galleries'); ?>
                        </label>
                        <br />
                        <label>
                            <input type="checkbox" name="wpcal_eg[archive_show_location]" value="yes" <?php checked($show_loc); ?> />
                            <?php esc_html_e('Display Event Location', 'wpcalibrate-event-galleries'); ?>
                        </label>
                        <br />
                        <label>
                            <input type="checkbox" name="wpcal_eg[archive_show_photo_count]" value="yes" <?php checked($show_count); ?> />
                            <?php esc_html_e('Display Photo Count badge', 'wpcalibrate-event-galleries'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
</div>
