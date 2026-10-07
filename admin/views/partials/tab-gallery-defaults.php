<?php
/**
 * Tab Partial: Gallery Defaults.
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

$layout       = (string) ($settings['default_layout'] ?? 'grid');
$col_desktop  = (int) ($settings['default_columns_desktop'] ?? 3);
$col_tablet   = (int) ($settings['default_columns_tablet'] ?? 2);
$col_mobile   = (int) ($settings['default_columns_mobile'] ?? 1);
$gap          = (int) ($settings['default_image_gap'] ?? 16);
$batch        = (int) ($settings['default_images_per_batch'] ?? 24);
$download     = (string) ($settings['default_download_enabled'] ?? 'no');
?>
<div class="wpcalibrate-section-card">
    <h2><?php esc_html_e('Global Gallery Defaults', 'wpcalibrate-event-galleries'); ?></h2>
    <p class="wpcalibrate-section-desc">
        <?php esc_html_e('Configure default presentation settings for all Event Galleries. Individual galleries can override these settings.', 'wpcalibrate-event-galleries'); ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="default_layout"><?php esc_html_e('Default Layout', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <select name="wpcal_eg[default_layout]" id="default_layout" class="regular-text">
                        <option value="grid" <?php selected($layout, 'grid'); ?>><?php esc_html_e('Grid (Uniform Aspect Ratio)', 'wpcalibrate-event-galleries'); ?></option>
                        <option value="masonry" <?php selected($layout, 'masonry'); ?>><?php esc_html_e('Masonry (Preserve Natural Heights)', 'wpcalibrate-event-galleries'); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Select the default layout mode for event galleries.', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_columns_desktop"><?php esc_html_e('Columns (Desktop)', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <select name="wpcal_eg[default_columns_desktop]" id="default_columns_desktop">
                        <?php for ($i = 1; $i <= 8; $i++) : ?>
                            <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($col_desktop, $i); ?>>
                                <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Default columns on screens wider than 1024px (1 to 8).', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_columns_tablet"><?php esc_html_e('Columns (Tablet)', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <select name="wpcal_eg[default_columns_tablet]" id="default_columns_tablet">
                        <?php for ($i = 1; $i <= 6; $i++) : ?>
                            <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($col_tablet, $i); ?>>
                                <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Default columns on screens between 640px and 1024px (1 to 6).', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_columns_mobile"><?php esc_html_e('Columns (Mobile)', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <select name="wpcal_eg[default_columns_mobile]" id="default_columns_mobile">
                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                            <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($col_mobile, $i); ?>>
                                <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Default columns on mobile screens narrower than 640px (1 to 4).', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_image_gap"><?php esc_html_e('Image Spacing (Gap)', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <input type="number"
                           name="wpcal_eg[default_image_gap]"
                           id="default_image_gap"
                           min="0"
                           max="60"
                           value="<?php echo esc_attr((string) $gap); ?>"
                           class="small-text" /> px
                    <p class="description">
                        <?php esc_html_e('Pixel spacing between gallery photos (0 to 60px).', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="default_images_per_batch"><?php esc_html_e('Images per Batch (Load More)', 'wpcalibrate-event-galleries'); ?></label>
                </th>
                <td>
                    <input type="number"
                           name="wpcal_eg[default_images_per_batch]"
                           id="default_images_per_batch"
                           min="1"
                           max="100"
                           value="<?php echo esc_attr((string) $batch); ?>"
                           class="small-text" />
                    <p class="description">
                        <?php esc_html_e('Number of photos rendered in the initial view and each Load More batch (1 to 100). Default is 24.', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Visitor Photo Downloads', 'wpcalibrate-event-galleries'); ?>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php esc_html_e('Visitor Photo Downloads', 'wpcalibrate-event-galleries'); ?></legend>
                        <label>
                            <input type="radio" name="wpcal_eg[default_download_enabled]" value="yes" <?php checked($download, 'yes'); ?> />
                            <?php esc_html_e('Enabled (Expose high-resolution download links)', 'wpcalibrate-event-galleries'); ?>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="wpcal_eg[default_download_enabled]" value="no" <?php checked($download, 'no'); ?> />
                            <?php esc_html_e('Disabled (Hide download links)', 'wpcalibrate-event-galleries'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Disabling downloads removes the download button in thumbnails and the lightbox. Note: this is not DRM; browser users can still inspect image source URLs.', 'wpcalibrate-event-galleries'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
</div>
