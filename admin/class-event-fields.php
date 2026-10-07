<?php
/**
 * Event Gallery Metaboxes & Custom Fields.
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
 * Class Event_Fields
 *
 * Registers and handles metaboxes for Event Date, Location, Gallery Photos, and Display Overrides.
 */
class Event_Fields {

    /**
     * Settings service.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Constructor.
     *
     * @param Settings $settings Settings.
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * Initialize metabox hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post_' . Post_Type::POST_TYPE, [$this, 'save_meta_boxes'], 10, 2);
    }

    /**
     * Register metaboxes for Event Gallery post type.
     *
     * @return void
     */
    public function register_meta_boxes(): void {
        // Main Details & Photos metabox
        add_meta_box(
            'wpcal_eg_details_box',
            __('Event Details & Photo Gallery', 'wpcalibrate-event-galleries'),
            [$this, 'render_details_box'],
            Post_Type::POST_TYPE,
            'normal',
            'high'
        );

        // Sidebar Display Overrides metabox
        add_meta_box(
            'wpcal_eg_display_overrides_box',
            __('Gallery Display Overrides', 'wpcalibrate-event-galleries'),
            [$this, 'render_overrides_box'],
            Post_Type::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Render the main details and photo gallery manager metabox.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    public function render_details_box(\WP_Post $post): void {
        wp_nonce_field('wpcal_eg_save_event_meta', 'wpcal_eg_event_nonce');

        $event_date  = (string) get_post_meta($post->ID, Meta::EVENT_DATE, true);
        $location    = (string) get_post_meta($post->ID, Meta::LOCATION, true);
        $raw_ids     = get_post_meta($post->ID, Meta::IMAGE_IDS, true);
        $image_ids   = is_array($raw_ids) ? array_map('intval', $raw_ids) : [];
        $ids_string  = implode(',', $image_ids);

        ?>
        <div class="wpcal-eg-metabox-wrap">
            <div class="wpcal-eg-field-row">
                <div class="wpcal-eg-field-col">
                    <label for="wpcal_eg_event_date" class="wpcal-eg-label">
                        <strong><?php esc_html_e('Event Date', 'wpcalibrate-event-galleries'); ?></strong>
                    </label>
                    <input type="date"
                           id="wpcal_eg_event_date"
                           name="wpcal_eg_event_date"
                           value="<?php echo esc_attr($event_date); ?>"
                           class="regular-text wpcal-eg-input" />
                    <p class="description">
                        <?php esc_html_e('The date when this event took place. Used for sorting and display.', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </div>

                <div class="wpcal-eg-field-col">
                    <label for="wpcal_eg_location" class="wpcal-eg-label">
                        <strong><?php esc_html_e('Event Location (Optional)', 'wpcalibrate-event-galleries'); ?></strong>
                    </label>
                    <input type="text"
                           id="wpcal_eg_location"
                           name="wpcal_eg_location"
                           value="<?php echo esc_attr($location); ?>"
                           placeholder="<?php esc_attr_e('e.g. Grand Plaza Hotel, London', 'wpcalibrate-event-galleries'); ?>"
                           class="regular-text wpcal-eg-input" />
                    <p class="description">
                        <?php esc_html_e('Venue or location where the event took place.', 'wpcalibrate-event-galleries'); ?>
                    </p>
                </div>
            </div>

            <hr class="wpcal-eg-separator" />

            <div class="wpcal-eg-gallery-manager">
                <div class="wpcal-eg-gallery-header">
                    <div class="wpcal-eg-gallery-info">
                        <h4 class="wpcal-eg-subtitle"><?php esc_html_e('Event Photos', 'wpcalibrate-event-galleries'); ?></h4>
                        <span class="wpcal-eg-photo-counter">
                            <?php
                            /* translators: %d: photo count */
                            echo esc_html(sprintf(_n('%d photo selected', '%d photos selected', count($image_ids), 'wpcalibrate-event-galleries'), count($image_ids)));
                            ?>
                        </span>
                    </div>

                    <div class="wpcal-eg-gallery-actions">
                        <button type="button" class="button button-primary wpcal-eg-btn-select-photos" id="wpcal-eg-btn-select-photos">
                            <span class="dashicons dashicons-format-gallery" style="vertical-align: middle; margin-top: -2px;"></span>
                            <?php esc_html_e('Add Photos from Media Library', 'wpcalibrate-event-galleries'); ?>
                        </button>
                        <button type="button" class="button wpcal-eg-btn-clear-photos" id="wpcal-eg-btn-clear-photos" <?php echo empty($image_ids) ? 'style="display:none;"' : ''; ?>>
                            <?php esc_html_e('Remove All', 'wpcalibrate-event-galleries'); ?>
                        </button>
                    </div>
                </div>

                <p class="description" style="margin-bottom: 12px;">
                    <?php esc_html_e('Drag and drop thumbnails to reorder photos. Removing a photo here removes it from this gallery only; it will not delete the photo from your WordPress Media Library.', 'wpcalibrate-event-galleries'); ?>
                </p>

                <!-- Hidden field holding ordered list of attachment IDs -->
                <input type="hidden" name="wpcal_eg_image_ids" id="wpcal-eg-image-ids" value="<?php echo esc_attr($ids_string); ?>" />

                <ul class="wpcal-eg-thumbnail-grid" id="wpcal-eg-thumbnail-grid">
                    <?php
                    foreach ($image_ids as $id) :
                        $thumb = wp_get_attachment_image_src($id, 'thumbnail');
                        if (!$thumb) {
                            continue; // Skip invalid or deleted media attachments gracefully
                        }
                        $title = get_the_title($id);
                        ?>
                        <li class="wpcal-eg-thumb-item" data-id="<?php echo esc_attr((string) $id); ?>" title="<?php echo esc_attr($title); ?>">
                            <div class="wpcal-eg-thumb-card">
                                <img src="<?php echo esc_url($thumb[0]); ?>" alt="<?php echo esc_attr($title); ?>" class="wpcal-eg-thumb-img" />
                                <button type="button" class="wpcal-eg-thumb-remove" aria-label="<?php esc_attr_e('Remove photo from gallery', 'wpcalibrate-event-galleries'); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                                <span class="wpcal-eg-thumb-handle" title="<?php esc_attr_e('Drag to reorder', 'wpcalibrate-event-galleries'); ?>">
                                    <span class="dashicons dashicons-move"></span>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="wpcal-eg-empty-state" id="wpcal-eg-empty-state" <?php echo !empty($image_ids) ? 'style="display:none;"' : ''; ?>>
                    <span class="dashicons dashicons-images-alt2" style="font-size: 40px; width: 40px; height: 40px; color: #a7aaad;"></span>
                    <p><?php esc_html_e('No photos selected yet. Click "Add Photos from Media Library" to build your event gallery.', 'wpcalibrate-event-galleries'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the sidebar display overrides metabox.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    public function render_overrides_box(\WP_Post $post): void {
        $layout          = (string) get_post_meta($post->ID, Meta::LAYOUT, true);
        $columns_desktop = (int) get_post_meta($post->ID, Meta::COLUMNS_DESKTOP, true);
        $columns_tablet  = (int) get_post_meta($post->ID, Meta::COLUMNS_TABLET, true);
        $columns_mobile  = (int) get_post_meta($post->ID, Meta::COLUMNS_MOBILE, true);
        $images_per_page = (int) get_post_meta($post->ID, Meta::IMAGES_PER_PAGE, true);
        $download        = (string) get_post_meta($post->ID, Meta::DOWNLOAD_ENABLED, true);

        if ($layout === '') {
            $layout = 'default';
        }
        if ($download === '') {
            $download = 'default';
        }
        ?>
        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_layout"><strong><?php esc_html_e('Layout', 'wpcalibrate-event-galleries'); ?></strong></label>
            <select name="wpcal_eg_layout" id="wpcal_eg_layout" class="widefat">
                <option value="default" <?php selected($layout, 'default'); ?>><?php esc_html_e('Inherit Global Default', 'wpcalibrate-event-galleries'); ?></option>
                <option value="grid" <?php selected($layout, 'grid'); ?>><?php esc_html_e('Grid', 'wpcalibrate-event-galleries'); ?></option>
                <option value="masonry" <?php selected($layout, 'masonry'); ?>><?php esc_html_e('Masonry', 'wpcalibrate-event-galleries'); ?></option>
            </select>
        </div>

        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_columns_desktop"><strong><?php esc_html_e('Desktop Columns', 'wpcalibrate-event-galleries'); ?></strong></label>
            <select name="wpcal_eg_columns_desktop" id="wpcal_eg_columns_desktop" class="widefat">
                <option value="0" <?php selected($columns_desktop, 0); ?>><?php esc_html_e('Inherit Global Default', 'wpcalibrate-event-galleries'); ?></option>
                <?php for ($i = 1; $i <= 8; $i++) : ?>
                    <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($columns_desktop, $i); ?>>
                        <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_columns_tablet"><strong><?php esc_html_e('Tablet Columns', 'wpcalibrate-event-galleries'); ?></strong></label>
            <select name="wpcal_eg_columns_tablet" id="wpcal_eg_columns_tablet" class="widefat">
                <option value="0" <?php selected($columns_tablet, 0); ?>><?php esc_html_e('Inherit Global Default', 'wpcalibrate-event-galleries'); ?></option>
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($columns_tablet, $i); ?>>
                        <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_columns_mobile"><strong><?php esc_html_e('Mobile Columns', 'wpcalibrate-event-galleries'); ?></strong></label>
            <select name="wpcal_eg_columns_mobile" id="wpcal_eg_columns_mobile" class="widefat">
                <option value="0" <?php selected($columns_mobile, 0); ?>><?php esc_html_e('Inherit Global Default', 'wpcalibrate-event-galleries'); ?></option>
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <option value="<?php echo esc_attr((string) $i); ?>" <?php selected($columns_mobile, $i); ?>>
                        <?php echo esc_html(sprintf(_n('%d Column', '%d Columns', $i, 'wpcalibrate-event-galleries'), $i)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_images_per_page"><strong><?php esc_html_e('Batch Size (Load More)', 'wpcalibrate-event-galleries'); ?></strong></label>
            <input type="number"
                   name="wpcal_eg_images_per_page"
                   id="wpcal_eg_images_per_page"
                   min="0"
                   max="100"
                   value="<?php echo esc_attr((string) $images_per_page); ?>"
                   class="small-text" />
            <p class="description"><?php esc_html_e('0 to inherit global default.', 'wpcalibrate-event-galleries'); ?></p>
        </div>

        <div class="wpcal-eg-side-field">
            <label for="wpcal_eg_download_enabled"><strong><?php esc_html_e('Visitor Photo Downloads', 'wpcalibrate-event-galleries'); ?></strong></label>
            <select name="wpcal_eg_download_enabled" id="wpcal_eg_download_enabled" class="widefat">
                <option value="default" <?php selected($download, 'default'); ?>><?php esc_html_e('Inherit Global Default', 'wpcalibrate-event-galleries'); ?></option>
                <option value="yes" <?php selected($download, 'yes'); ?>><?php esc_html_e('Enabled', 'wpcalibrate-event-galleries'); ?></option>
                <option value="no" <?php selected($download, 'no'); ?>><?php esc_html_e('Disabled', 'wpcalibrate-event-galleries'); ?></option>
            </select>
        </div>
        <?php
    }

    /**
     * Save metabox data upon post save.
     *
     * @param int      $post_id Post ID.
     * @param \WP_Post $post    Post object.
     * @return void
     */
    public function save_meta_boxes(int $post_id, \WP_Post $post): void {
        // Verify nonce
        if (!isset($_POST['wpcal_eg_event_nonce']) || !wp_verify_nonce((string) $_POST['wpcal_eg_event_nonce'], 'wpcal_eg_save_event_meta')) {
            return;
        }

        // Avoid autosave or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Check capability
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save Event Date
        if (isset($_POST['wpcal_eg_event_date'])) {
            $sanitized_date = Meta::sanitize_event_date($_POST['wpcal_eg_event_date']);
            update_post_meta($post_id, Meta::EVENT_DATE, $sanitized_date);
        }

        // Save Location
        if (isset($_POST['wpcal_eg_location'])) {
            $sanitized_location = sanitize_text_field((string) $_POST['wpcal_eg_location']);
            update_post_meta($post_id, Meta::LOCATION, $sanitized_location);
        }

        // Save Image IDs
        if (isset($_POST['wpcal_eg_image_ids'])) {
            $sanitized_ids = Meta::sanitize_image_ids($_POST['wpcal_eg_image_ids']);
            update_post_meta($post_id, Meta::IMAGE_IDS, $sanitized_ids);
        }

        // Save Layout override
        if (isset($_POST['wpcal_eg_layout'])) {
            $sanitized_layout = Meta::sanitize_layout($_POST['wpcal_eg_layout']);
            update_post_meta($post_id, Meta::LAYOUT, $sanitized_layout);
        }

        // Save Desktop Columns override
        if (isset($_POST['wpcal_eg_columns_desktop'])) {
            $sanitized_cd = Meta::sanitize_columns_desktop($_POST['wpcal_eg_columns_desktop']);
            update_post_meta($post_id, Meta::COLUMNS_DESKTOP, $sanitized_cd);
        }

        // Save Tablet Columns override
        if (isset($_POST['wpcal_eg_columns_tablet'])) {
            $sanitized_ct = Meta::sanitize_columns_tablet($_POST['wpcal_eg_columns_tablet']);
            update_post_meta($post_id, Meta::COLUMNS_TABLET, $sanitized_ct);
        }

        // Save Mobile Columns override
        if (isset($_POST['wpcal_eg_columns_mobile'])) {
            $sanitized_cm = Meta::sanitize_columns_mobile($_POST['wpcal_eg_columns_mobile']);
            update_post_meta($post_id, Meta::COLUMNS_MOBILE, $sanitized_cm);
        }

        // Save Images Per Page override
        if (isset($_POST['wpcal_eg_images_per_page'])) {
            $sanitized_ipp = Meta::sanitize_images_per_page($_POST['wpcal_eg_images_per_page']);
            update_post_meta($post_id, Meta::IMAGES_PER_PAGE, $sanitized_ipp);
        }

        // Save Download Enabled override
        if (isset($_POST['wpcal_eg_download_enabled'])) {
            $sanitized_dl = Meta::sanitize_download_enabled($_POST['wpcal_eg_download_enabled']);
            update_post_meta($post_id, Meta::DOWNLOAD_ENABLED, $sanitized_dl);
        }
    }
}
