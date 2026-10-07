<?php
/**
 * Settings Service.
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
 * Class Settings
 *
 * Manages plugin settings storage, defaults, and sanitization.
 */
class Settings {

    /**
     * Option name in WordPress options table.
     *
     * @var string
     */
    public const OPTION_NAME = 'wpcal_eg_settings';

    /**
     * Cached settings array.
     *
     * @var array<string, mixed>|null
     */
    private ?array $settings = null;

    /**
     * Get default settings array.
     *
     * @return array<string, mixed>
     */
    public function get_defaults(): array {
        return [
            // Gallery Layout Defaults
            'default_layout'           => 'grid',       // 'grid' or 'masonry'
            'default_columns_desktop'  => 3,            // 1 to 8
            'default_columns_tablet'   => 2,            // 1 to 6
            'default_columns_mobile'   => 1,            // 1 to 4
            'default_image_gap'        => 16,           // 0 to 60 (px)
            'default_images_per_batch' => 24,           // 1 to 100
            'default_download_enabled' => 'no',         // 'yes' or 'no'

            // Archive Defaults
            'archive_sort_order'       => 'date_desc',  // 'date_desc', 'date_asc', 'title_asc', 'title_desc', 'menu_order'
            'archive_events_per_page'  => 12,           // 1 to 50
            'archive_show_photo_count' => 'yes',        // 'yes' or 'no'
            'archive_show_location'    => 'yes',        // 'yes' or 'no'
            'archive_show_date'        => 'yes',        // 'yes' or 'no'

            // Data Retention
            'delete_data_on_uninstall' => 'no',         // 'yes' or 'no'
        ];
    }

    /**
     * Retrieve all settings merged with defaults.
     *
     * @return array<string, mixed>
     */
    public function get_all(): array {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $stored = get_option(self::OPTION_NAME, []);
        if (!is_array($stored)) {
            $stored = [];
        }

        $this->settings = wp_parse_args($stored, $this->get_defaults());
        return $this->settings;
    }

    /**
     * Retrieve a specific setting value.
     *
     * @param string $key Setting key.
     * @param mixed  $default Optional fallback if key is missing.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed {
        $settings = $this->get_all();
        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default ?? ($this->get_defaults()[$key] ?? null);
    }

    /**
     * Update a single setting value.
     *
     * @param string $key Setting key.
     * @param mixed  $value Setting value.
     * @return bool
     */
    public function update(string $key, mixed $value): bool {
        $settings       = $this->get_all();
        $settings[$key] = $value;
        return $this->update_all($settings);
    }

    /**
     * Sanitize and persist entire settings array.
     *
     * @param array<string, mixed> $input Raw settings input.
     * @return bool
     */
    public function update_all(array $input): bool {
        $sanitized      = $this->sanitize($input);
        $this->settings = $sanitized;
        return update_option(self::OPTION_NAME, $sanitized);
    }

    /**
     * Sanitize settings inputs with strict validation.
     *
     * @param array<string, mixed> $input Raw input.
     * @return array<string, mixed> Sanitized settings.
     */
    public function sanitize(array $input): array {
        $defaults  = $this->get_defaults();
        $sanitized = [];

        // Layout: 'grid' or 'masonry'
        $layout = isset($input['default_layout']) ? sanitize_key((string) $input['default_layout']) : $defaults['default_layout'];
        $sanitized['default_layout'] = in_array($layout, ['grid', 'masonry'], true) ? $layout : $defaults['default_layout'];

        // Desktop columns: 1-8
        $col_desktop = isset($input['default_columns_desktop']) ? (int) $input['default_columns_desktop'] : (int) $defaults['default_columns_desktop'];
        $sanitized['default_columns_desktop'] = max(1, min(8, $col_desktop));

        // Tablet columns: 1-6
        $col_tablet = isset($input['default_columns_tablet']) ? (int) $input['default_columns_tablet'] : (int) $defaults['default_columns_tablet'];
        $sanitized['default_columns_tablet'] = max(1, min(6, $col_tablet));

        // Mobile columns: 1-4
        $col_mobile = isset($input['default_columns_mobile']) ? (int) $input['default_columns_mobile'] : (int) $defaults['default_columns_mobile'];
        $sanitized['default_columns_mobile'] = max(1, min(4, $col_mobile));

        // Image Gap: 0-60 px
        $gap = isset($input['default_image_gap']) ? (int) $input['default_image_gap'] : (int) $defaults['default_image_gap'];
        $sanitized['default_image_gap'] = max(0, min(60, $gap));

        // Batch size: 1-100
        $batch = isset($input['default_images_per_batch']) ? (int) $input['default_images_per_batch'] : (int) $defaults['default_images_per_batch'];
        $sanitized['default_images_per_batch'] = max(1, min(100, $batch));

        // Download enabled: 'yes' or 'no'
        $download = isset($input['default_download_enabled']) ? sanitize_key((string) $input['default_download_enabled']) : $defaults['default_download_enabled'];
        $sanitized['default_download_enabled'] = $download === 'yes' ? 'yes' : 'no';

        // Archive sort order: allowlist
        $allowed_sorts = ['date_desc', 'date_asc', 'title_asc', 'title_desc', 'menu_order'];
        $sort = isset($input['archive_sort_order']) ? sanitize_key((string) $input['archive_sort_order']) : $defaults['archive_sort_order'];
        $sanitized['archive_sort_order'] = in_array($sort, $allowed_sorts, true) ? $sort : $defaults['archive_sort_order'];

        // Archive events per page: 1-50
        $per_page = isset($input['archive_events_per_page']) ? (int) $input['archive_events_per_page'] : (int) $defaults['archive_events_per_page'];
        $sanitized['archive_events_per_page'] = max(1, min(50, $per_page));

        // Archive display toggles
        $sanitized['archive_show_photo_count'] = (!empty($input['archive_show_photo_count']) && $input['archive_show_photo_count'] === 'yes') ? 'yes' : 'no';
        $sanitized['archive_show_location']    = (!empty($input['archive_show_location']) && $input['archive_show_location'] === 'yes') ? 'yes' : 'no';
        $sanitized['archive_show_date']        = (!empty($input['archive_show_date']) && $input['archive_show_date'] === 'yes') ? 'yes' : 'no';

        // Data retention on uninstall: 'yes' or 'no'
        $delete = isset($input['delete_data_on_uninstall']) ? sanitize_key((string) $input['delete_data_on_uninstall']) : $defaults['delete_data_on_uninstall'];
        $sanitized['delete_data_on_uninstall'] = $delete === 'yes' ? 'yes' : 'no';

        return $sanitized;
    }
}
